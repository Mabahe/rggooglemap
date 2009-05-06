<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Georg Ringer <www.ringer.it>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * PLUGIN 'Google-Map' for the 'rggooglemap' extension.
 *
 * @author	Georg Ringer <http://www.rggooglemap.com/>
 */

require_once(PATH_tslib.'class.tslib_pibase.php');
	/*
	 * ToDO :
	 * - group pois
	 * - json
	 */	 	 	 	 	 

	/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   98: class tx_rggooglemap_pi1 extends tslib_pibase
 *  109:     function init($conf)
 *  226:     function main($content,$conf)
 *  297:     function showMap()
 *  322:     function showRecordsOnMap ()
 *  336:     function showLocationBox ()
 *  355:     function showMenu ($additionalCat='', $additionalWhere='')
 *  447:     function showSearch ()
 *  472:     function showCatMenu()
 *  487:     function showDirections()
 *  539:     function helperGeocodeAddress($address='', $zip='', $city='', $country='')
 *   
 *  594:     function ajaxSearch($searchForm)
 *  825:     function ajaxGetActiveRecords($area, $cat)
 *  863:     function initMap()
 *  909:     function ajaxGetInfomsg($uid, $table,$tmplPrefix=1)
 *  991:     function ajaxProcessCat($data)
 * 1093:     function ajaxProcessCatTree($data)
 * 1157:     function ajaxGetResultSet($var)
 * 1249:     function displayCatMenu($id=0)
 * 1308:     function getJs ()
 * 1440:     function getPoiOnStart()
 * 1488:     function ajaxGetPoiTab($id,$tab,$table)
 * 1505:     function getPoiContent($id,$tab,$table)
 * 1549:     function getMarker($row, $prefix, $i)
 *  
 * 1612:     function helperGetAvailableRecords($catList='', $areaSearch='')
 * 1646:     function helperGetRecursiveCat($allowedCat, $parentId=0,$level=0 )
 * 1677:     function helperGetLLMarkers($markerArray, $conf, $prefix)
 * 1707:     function helperCheckForWrongUrl()
 * 1730:     function helperGetFlexform($sheet, $key, $confOverride='')
 * 1762:     function helperGetCategoryImage($catImg, $parent=0)
 *  
 * 1791:     function xmlFunc($content,$conf)
 * 1889:     function xmlAddRecord($table, $row,$conf, $img, $test)
 * 1911:     function xmlGetRowInXML($row,$conf)
 * 1926:     function xmlNewLevel($name,$beginEndFlag=0,$params=array())
 * 1949:     function xmlGetResult()
 * 1961:     function xmlFieldWrap($field,$value)
 * 1966:     function xmlTopLevelName()
 * 1971:     function xmlRenderHeader()
 * 1975:     function xmlRenderFooter()
 *
 * TOTAL FUNCTIONS: 40
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



/**
 * Plugin 'Google Map (rggooglemap)' for the 'rggooglemap' extension.
 *
 * @author	Georg Ringer <www.ringer.it>
 */

class tx_rggooglemap_pi1 extends tslib_pibase {
	var $prefixId				= 'tx_rggooglemap_pi1';		// Same as class name
	var $scriptRelPath	= 'pi1/class.tx_rggooglemap_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey					= 'rggooglemap';	// The extension key.

	/**
	 * Just some intialization, mainly reading the settings in the flexforms
	 *
	 * @param	array		$conf: The PlugIn configuration
	 */
	function init($conf) {
		require_once (PATH_tslib.'/class.tslib_content.php');
		$this->cObj2 = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$this->conf = $conf;
		$this->pi_loadLL();
		$this->pi_setPiVarDefaults();
		$this->pi_initPIflexForm();

		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);

		// Template code
		$this->templateCode = $this->cObj2->fileResource($this->conf['templateFile']);

		// pidList & recursive function
		$pid_list = $this->helperGetFlexform('sDEF', 'startingpoint', 'pidList');
		if (intval($this->piVars['pidList'])!=0) {
			$pid_list = intval($this->piVars['pidList']);
		}
		$recursive = intval($this->helperGetFlexform('sDEF', 'recursive', 'recursive'));

		if ($pid_list!='') {
			if ($recursive > 0) {
				$this->config['pid_list'] 				= $this->pi_getPidList($pid_list,10);
			} else {
				$this->config['pid_list'] 				= $pid_list;
			}
			$this->config['pid_list'] = ' AND deleted=0 AND hidden=0 AND pid IN('.$this->config['pid_list'].') ';
		} else {
			$this->config['pid_list'] = ' AND deleted=0 AND hidden=0 ';
		}

		// 1st: General settings
		$this->config['show'] 							= $this->helperGetFlexform('sDEF', 'show', 'show'); // show
		$this->config['categories'] 				= $this->helperGetFlexform('sDEF', 'categoriesselected', 'mapAvailableCats'); // loaded POI categories
		$this->config['categoriesActive'] 	= $this->helperGetFlexform('sDEF', 'categories', 'mapActiveCats'); // active POI categories
		$this->config['mapDiv'] 						= $this->conf['mapDiv']; 		// map div id

		// 2nd:  Map settings
		$this->config['mapWidth'] 					= $this->helperGetFlexform('map', 'width', 'mapWidth'); // width
		$this->config['mapHeight'] 					= $this->helperGetFlexform('map', 'height', 'mapHeight'); // height
		$this->config['mapLng'] 						= $this->helperGetFlexform('map', 'lng', 'mapLng'); // lng
		$this->config['mapLat'] 						= $this->helperGetFlexform('map', 'lat', 'mapLat'); // lat
		$this->config['mapZoom'] 						= $this->helperGetFlexform('map', 'zoom', 'mapZoom'); // zoom
		$key = $this->helperGetFlexform('sDEF', 'key', 'mapKey');		// google map key
		$this->config['mapKey'] 						= ($key!='') ? $key : $this->confArr['googleKey'];
		$this->config['mapKey']							= $this->helperGetMultiDomainKey($this->config['mapKey'], $this->confArr['googleKey2']);

		$this->config['mapType'] 						= $this->helperGetFlexform('map', 'type', 'mapType'); // map control
		$this->config['mapTypeControl'] 		= $this->helperGetFlexform('map', 'type_controls', 'mapControl');
		$this->config['mapNavControl'] 			= $this->helperGetFlexform('map', 'nav_controls', 'mapNavigation');
		$this->config['mapControlOnMouseOver'] = $this->helperGetFlexform('map', 'controlonmouseouver', 'mapNavigationOnMouseOver');
		$this->config['mapOverview'] 				= $this->helperGetFlexform('map', 'mapoverview', 'mapOverview');

		$this->config['mapShowOnDefault'] 	= $this->helperGetFlexform('map', 'showondefault', 'showOnDefault'); // default POI to show on begin
		$this->config['loadDynamicList'] 	= $this->helperGetFlexform('map', 'loadDynamicList', 'loadDynamicList');

		/*
		* 3rd sheet: Config for Menu-output
		*/
		$this->config['menu-map'] 					= $this->helperGetFlexform('menu', 'map'); // ID of the map page
		$this->config['menu-catSort'] 			= $this->helperGetFlexform('menu', 'menucatsortorder', 'menu.catOrder'); // orderBy of categories and records
		$this->config['menu-catSortBy'] 		= $this->helperGetFlexform('menu', 'menucatsortorderby', 'menu.catOrderBy');
		$this->config['menu-recordSort'] 		= $this->helperGetFlexform('menu', 'menurecordsort', 'menu.recordsOrder');
		$this->config['menu-recordSortBy'] 	= $this->helperGetFlexform('menu', 'menurecordsortby', 'menu.recordsOrderBy');
		$this->config['menu-categorytree'] 	= $this->helperGetFlexform('menu', 'usecategorytree'); // Use category-tree in menu view

		// search tab
		$this->config['defaultCountry'] 	= $this->helperGetFlexform('search', 'defaultCountry', 'defaultCountry');
		$this->config['search']['radiusSearch'] 		= $this->helperGetFlexform('search', 'radiusSearch', 'search.radiusSearch');


		// which tables should be uses
		$tmp_confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);
		$this->config['tables'] = ($this->confArr['tables']!='') ? $this->confArr['tables'] : $tmp_confArr['tables'];
		// avoid using t3lib_div::trimExplode everytime, delete blanks just once
		$this->config['tables'] = str_replace(' ', '', $this->config['tables']);

		// get default table 
		$defaultTableFound = false;
		if (empty($this->conf['defaultTable']) || t3lib_div::inList($this->config['tables'], $this->conf['defaultTable'])) {
			$split = explode(',', $this->config['tables']);
			$this->conf['defaultTable'] = $split[0];
		} 
		
		// get the generic select functions
		require_once( t3lib_extMgm::siteRelpath('rggooglemap').'lib/class.tx_rggooglemap_table.php');
		$this->generic = t3lib_div::makeInstance('tx_rggooglemap_table');

		// CSS file
		if (isset($this->conf['cssFile']) && $this->conf['cssFile'] != '') {
			$pathToCSS = $GLOBALS['TSFE']->tmpl->getFileName($this->conf['cssFile']);
			if ($pathToCSS != '') {
				$GLOBALS['TSFE']->additionalHeaderData['rggooglemap_css'] = '<link rel="stylesheet" href="' . $pathToCSS . '" type="text/css" />';
			}
		}
		
		// Adds hook for changing the configuration
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['configHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['configHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$this->config = $_procObj->extraSearchProcessor($this);
			}
		}		

	}


	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->init($conf);
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		
		// check if the static TS is loaded.
		if (count($this->conf) <= 3) {
			return $this->pi_getLL('error_no-static-ts');
		}
		
		// check if this is the correct domain (no cross domain scripts for ajax requests
		$check = $this->helperCheckForWrongUrl();
		if (count($check) > 0) {
			return sprintf($this->pi_getLL('error_wrong-domains'), $check['current'], $check['link']);
		}


		// what should be displayed
		$showItems = t3lib_div::trimExplode(',', $this->config['show']);
		foreach($showItems as $key) {
			$key = strtoupper($key);
			switch ($key) {
				case 'MAP':
					$content .= $this->showMap();
					break;
				case 'LOCATION':
					$content .= $this->showLocationBox();
					break;
				case 'RECORDSONMAP':
					$content .= $this->showRecordsOnMap();
					break;
				case 'MENU':
					$content .= $this->showMenu();
					break;
				case 'CATMENU':
					$content .= $this->showCatMenu();
					break;
				case 'SEARCH':
					$content .= $this->showSearch();
					break;
				case 'DIRECTIONS':
					$content .= $this->showDirections();
					break;
				default:
					// Adds hook for processing of extra codes
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraCodesHook'])) {
						foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraCodesHook'] as $_classRef) {
							$_procObj = & t3lib_div::getUserObj($_classRef);
							$content .= $_procObj->extraCodesProcessor($this);
						}
					}
					break;
			}

		}

		// check if any content is available
		if ($content =='') {
			$content .= $this->pi_getLL('errror_no-status');
		} else {
			$content	= $this->pi_wrapInBaseClass($content);
		}

		return $content;
	}


	/**
	 * Show the google maps
	 *
	 * @return	the HTML content
	 */
	function showMap() {
		$this->initMap();

		$template['list'] = $this->cObj2->getSubpart($this->templateCode,'###MAP###');

		// title, text - markers
		$markerArray = $this->helperGetLLMarkers(array(), $this->conf['map.']['LL'], 'map');
		$markerArray['###CAT_MENU###'] = $this->displayCatMenu(0);
		$markerArray['###CAT_LIST###'] = ($this->config['categoriesActive']!='') ? $this->config['categoriesActive'] : '9999';
		$markerArray['###MAP_WIDTH###'] = $this->config['mapWidth'];
		$markerArray['###MAP_HEIGHT###'] = $this->config['mapHeight'];
		
		$content.= $this->cObj2->substituteMarkerArrayCached($template['list'],$markerArray, $subpartArray,$wrappedSubpartArray);
		return $content;
	}


	/**
	 * Plugin mode RECORDSONMAP: Presents a list of on the map visible records
	 *
	 * @return	The plugin content
	 */
	function showRecordsOnMap () {
		$template['list'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_RECORDSONMAP###');
		$markerArray = $this->helperGetLLMarkers(array(), $this->conf['recordsOnMap.']['LL'], 'recordsonmap');
		
		$content.= $this->cObj->substituteMarkerArrayCached($template['list'],$markerArray);
		return $content;
	}


	/**
	 * Plugin mode SEARCHBOX: Presents a form to search for a location, working with geocoding
	 *
	 * @return	The plugin content
	 */
	function showLocationBox () {
		$template['list'] = $this->cObj2->getSubpart($this->templateCode,'###TEMPLATE_LOCATIONBOX###');
		$markerArray = $this->helperGetLLMarkers(array(), $this->conf['location.']['LL'], 'location');
		
		$content.= $this->cObj2->substituteMarkerArrayCached($template['list'],$markerArray);
		return $content;
	}


	/**
	 * View "MENU": Show all records of the selected categories and starting point, linking to the map on a different page
	 *

	 * $param array $additional: Function can be called by ajaxProcessCatTree to change used categories dynamically
	 * @return	The plugin content
	 */
	function showMenu ($additionalCat='', $additionalWhere='') {
		$confSmall = $this->conf['menu.'];
		
		$template['total'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_MENU###');
		$template['item'] = $this->cObj->getSubpart($template['total'],'###ITEM_SINGLE###');
		$template['item2'] = $this->cObj->getSubpart($template['total'],'###ITEM_SINGLE2###');
		
		// query for the categories
		$table = 'tx_rggooglemap_cat';
		
		// if the tree is used in menu view, take the IDs from there, otherwise out of the plugin
		if ($additionalCat!='') {
			$menuCatList.= ' AND uid IN ('.implode(',',$additionalCat).') ';
		} else {
			$menuCatList = (($this->config['categories']!='') ? ' AND uid IN ('.$this->config['categories'].') ' : '');
		}
		
		$where = '1=1 '.$this->cObj->enableFields($table).$menuCatList;
		$orderBy = $this->config['menu-catSort'].' '.$this->config['menu-catSortBy'];
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$table,$where,$groupBy='',$orderBy,$limit='');
		
		$i = 0;
		$count = array();
		
		// List of the Categories
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			// stdwrap for the categories
			foreach ($row as $key=>$value) {
				$markerArray['###'.strtoupper($key).'###'] = $this->cObj->stdWrap($value,$confSmall['category.'][$key.'.']);
			}
			
			// query for single records in category
			$firstCategory = explode(',',$row['uid']);
			
			$where2 = ' rggmcat = '.$firstCategory{0}.' AND lat!=0 AND lng!=0 AND lng != \'\' AND lat != \'\' '.$this->config['pid_list'];
			
			// search mode
			if ($additionalWhere!= '') {
				$where2 .= $additionalWhere;
			}
			
			// orderBy
			if ($this->config['menu-recordSort']) {
				$orderByRecords = $this->config['menu-recordSort'].' '.$this->config['menu-recordSortBy'];
			}
			
			$res2 = $this->generic->exec_SELECTquery('*',$this->config['tables'],$where2,$groupBy='',$orderByRecords,$limit='');
			
			// List of single records
			$content_item2 = '';
			
			// run through the reocrds of the category
			while($row2=array_shift($res2)) {
				$i++;
				
				$markerArray2 = $this->getMarker($row2, 'menu.', $i);
				
				// no page ID for map > suggesting plugin is on the same page => javascript links
				if ($this->config['menu-map']!='') {
					$vars['poi'] = $row2['uid'];
					
					if ($row2['table'] != $this->conf['defaultTable']) {
						$vars['table'] = $row2['table'];
					}
					
					$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', $vars, 1,1,$this->config['menu-map']));
				} else {
					$wrappedSubpartArray['###LINK_ITEM###'] = explode('|', '<a onclick="myclick('.$row2['uid'].','.$row2['lng'].','.$row2['lat'].', \''.$row2['table'].'\')" href="javascript:void(0)">|</a>');
				}
				
				$content_item2 .=$this->cObj->substituteMarkerArrayCached($template['item2'],$markerArray2, $subpartArray,$wrappedSubpartArray );
			}
			
			
			$subpartArray['###CONTENT2###'] = $content_item2 ;
			$content_item .=($i>0) ? $this->cObj->substituteMarkerArrayCached($template['item'],$markerArray, $subpartArray,$wrappedSubpartArray ) :'';
			
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		$subpartArray['###CONTENT###'] =($i>0) ? $content_item : '';
		
		$content.= $this->cObj->substituteMarkerArrayCached($template['total'],$markerArray, $subpartArray,$wrappedSubpartArray);
		return $content;
	}


	/**
	 * Plugin mode SEARCH: Presents a form to search for records
	 *
	 * @return	The plugin content
	 */
	function showSearch () {
		$template['list'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_SEARCH###');
		$subpartArray = array();
		$markerArray = $this->helperGetLLMarkers(array(), $this->conf['search.']['LL'], 'search');

		// hide the radisus search by using a subpart
		if ($this->config['search']['radiusSearch']!=1) {
			$subpartArray['###HIDE_RADIUSSEARCH###'] = '';
		}

		// set the default country
		$markerArray['##DEFAULT_COUNTRY###'] = $this->config['defaultCountry'];

		// fetch the allowed categories as option list
		$markerArray['###CATEGORY###'] = $this->helperGetRecursiveCat($this->config['categories']);

		$content.= $this->cObj->substituteMarkerArrayCached($template['list'],$markerArray, $subpartArray);
		return $content;
	}


	/**
	 * Show the category menu
	 *
	 * @return directions
	 */	
	function showCatMenu() {
		$template['list'] = $this->cObj2->getSubpart($this->templateCode,'###TEMPLATE_CATMENU_MAIN###');
		$markerArray = array();		
		$markerArray['###ITEMS###'] = $this->displayCatMenu();
		
		$content = $this->cObj2->substituteMarkerArrayCached($template['list'],$markerArray);
		return $content;
	}
	
	
	/**
	 * Show the directions to some records
	 *
	 * @return directions
	 */	
	function showDirections() {
		$smallConf = $this->conf['directions.'];
		
		$subpartArray = array();
		$markerArray = $this->helperGetLLMarkers(array(), $smallConf['LL'], 'directions');
 

		// query
		$table = $this->config['tables'];
		$field = '*';
		$where = $this->helperGetAvailableRecords($this->config['categories']);
		$orderBy = $smallConf['orderBy'];
		$limit = $smallConf['limit'];
		$res = $this->generic->exec_SELECTquery($field,$table,$where,'',$orderBy, $limit);

		// if just 1 result, render a different subpart
		if (count($res)==1) {
			$suffix = '_SINGLE';
			$subpartArray['###HIDE_MULTISELECTION###'] = '';			
		} else {
			$suffix = '';
			$subpartArray['###HIDE_SINGLESELECTION###'] = '';
		}
		
		$template['list'] = $this->cObj2->getSubpart($this->templateCode,'###TEMPLATE_DIRECTIONS###');
		$template['item'] = $this->cObj2->getSubpart($template['list'],'###SINGLE'.$suffix.'###');
		

		while($row=array_shift($res)) {
			$markerArray = $this->getMarker($row, 'directions.');
			$content_item .= $this->cObj->substituteMarkerArrayCached($template['item'],$markerArray, array(), $wrappedSubpartArray);
		}
		
		$subpartArray['###CONTENT'.$suffix.'###'] = $content_item;
		
		$markerArray['###DEFAULT_COUNTRY###'] = $this->config['defaultCountry'];


		$content.= $this->cObj2->substituteMarkerArrayCached($template['list'],$markerArray, $subpartArray);
		return $content;
	}
	

	/**
	* Function for the ajax search results
	*
	* @param	string		$searchFields: The search fiels (search word & search on map only)
	* @return	Records found with search value
	*/
	function ajaxSearch($searchForm)	{
		$template['list'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_SEARCH_RESULTS###');
		$template['item'] = $this->cObj->getSubpart( $template['list'],'###SINGLE###');
		$smallConf = $this->conf['search.'];
		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);

		$test		= '';
		$debug	= array();
		$error	= array();
		
		$jsResultDelete = 'deleteSearchResult();';		
		$markerArray = $this->helperGetLLMarkers(array(), $this->conf['search.']['LL'], 'search');		

		// minimum characters needed, default = 3
		if (strlen($searchForm['rggmsearchValue']) >= $this->conf['search.']['minChars'] ||
			 ($searchForm['rggmActivateRadius']=='1' && $searchForm['rggmRadius']>0)) {
			$res = Array();
			$coordinatesSaved = array();

			/*
			 * Search for the text
			 */

			// escaping the search-value
			$delete = array("'", "\"", "\\", "/", "");
			$searchExpression = $searchForm['rggmsearchValue'];
			$searchExpression = str_replace($delete, '', $searchExpression);


			$tablelist = explode(',',$this->config['tables']);

			foreach ($tablelist as $key=>$table) {
				$searchClause = array();
				$searchClause['general'] = 'lng!=0 AND lat!=0 '.$this->config['pid_list'];

				// just search the tables where search fields are specified
				if ($this->conf['search.']['fields.'][$table]) {
					$select = '*';
					$searchField = explode(',',$this->conf['search.'][$table]);
					$where2 = '';
					foreach ($searchField as $key=>$value) {
						$where2.= " $value LIKE '%$searchExpression%' OR";
					}
					$searchClause['text'] = ' ( '.substr($where2,0,-3).' ) ';

					// search only within the map area
					if ($searchForm['rggmOnMap']=='on') {
						$areaArr=split("%2C%20",$searchForm['rggmBound']);
						$searchClause['maparea'] = ' lng between '.$areaArr[1].' AND '.$areaArr[3].'
						AND	lat between '.$areaArr[0].' AND '.$areaArr[2];
					}

					// radius search (umkreissuche)
					if ($searchForm['rggmActivateRadius']=='on') {
						
						// avoid multiple geocoding calls, just 1 is necessary
						if (count($coordinatesSaved) == 0) {
							$coordinates = $this->helperGeocodeAddress('',$searchForm['rggmZip'], '', $searchForm['rggmCountry']);
							$coordinatesSaved = $coordinates;
						} else {
							$coordinates = $coordinatesSaved;
						}

						// if status is ok (200) and accuracy fits settings in TS
						if ($coordinates['status'] == 200 && (intval($coordinates['accuracy']) >= intval($this->conf['search.']['radiusSearch.']['minAccuracy']))) {
							$select = '*,SQRT(POW('.$coordinates['lng'].'-lng,2)*6400 + POW('.$coordinates['lat'].'-lat,2)*12100) AS distance';
							$searchClause['radius']= ' SQRT(POW('.$coordinates['lng'].'-lng,2)*6400 + POW('.$coordinates['lat'].'-lat,2)*12100) <'.intval($searchForm['rggmRadius']);
							$orderBy = 'distance';
						} else {
							$searchClause['errorWithRadiusSearch'] = '1=2';
							
							// if status is ok, the accuracy failed
							if ($coordinates['status']==200) {
								$error['accuracy']	= $this->pi_getLL('search_error_geocode-accuracy');
							} else {
								$error['status'] 		= $this->pi_getLL('search_error_geocode-status-'.$coordinates['status']);
							}

						}
					}

					// if a category is used, search for it
					if ($searchForm['rggmCat']!='') {
						foreach (explode(',',$searchForm['rggmCat']) as $key=>$value) {
							$whereCat.= ' FIND_IN_SET('.$value.',rggmcat) OR';
						}
						$searchClause['cat']= ' ( '.substr($whereCat,0,-3).' ) ';
					}

					$limit = ''; // no limit, because this is done afterwards from the whole list

					// Adds hook for processing of extra search expressions
					if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraSearchHook'])) {
						foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraSearchHook'] as $_classRef) {
							$_procObj = & t3lib_div::getUserObj($_classRef);
							$searchClause = $_procObj->extraSearchProcessor($table,$searchClause,$orderBy, $limit, $error, $this);
						}
					}

					$where = implode(' AND ', $searchClause);

					if (count($error) == 0) {
						$res += $this->generic->exec_SELECTquery($select,$table,$where,$groupBy,$orderBy, $limit);
					}
				}

				$debug[$table]['where'] = $where;
			}


			if (count($error) == 0) {
				// todo Limit
				$res = array_slice($res, 0,99);
	
	
				/*
				 * Create the output of the search
				 */
				$i = 0;

				$jsResultUpdate = 'var bounds = new GLatLngBounds();';
				$debug['count'] = 0;
				$debug[$table]['res'] = $res;
	
				// run through the results
				while($row=array_shift($res)) {
					$debug['count']++;
	
					// check if there is really no records with lng/lat = 0
					if (intval($row['lat'])==0 || intval($row['lng'])==0) {
						continue;
					}
	
	
					$markerArray = $this->getMarker($row,'search.', $i);
	
	
					$markerArray['###SEARCHID###'] = $i+1;
	
					// set the title right
					$title = ($this->cObj2->stdWrap(htmlspecialchars($row['rggmtitle']), $this->conf['title.']['searchresult.']));
					$title = str_replace('\'', '"', $title);
	
					// icon for the map
					$icon = 'marker'.($i+1).'.png';
	
					// JS which displayes the search markers
					$jsResultUpdate .= '
						marker = createMarker(new GLatLng('.$row['lat'].','.$row['lng'].'), '. $row['uid'].', \''. $icon.'\', \''. $title.'\', \''. $row['table'].'\', 1);
	
						map.addOverlay( marker );
						searchresultmarkers['.$i.'] = marker;
						bounds.extend(new GLatLng('.$row['lat'].','.$row['lng'].'));
					';
	
					$i++;
	
					$content_item .= $this->cObj->substituteMarkerArrayCached($template['item'],$markerArray, array(), $wrappedSubpartArray);
				}
	
				$jsResultUpdate.= $test;
	
				$markerArray['###SEARCHEXPRESSION###'] = $searchForm['rggmsearchValue'];
				$markerArray['###SEARCHCOUNT###'] = $i;
	
	
				$subpartArray['###CONTENT###'] = $content_item;
	
				$jsResultUpdate .= '
					var zoom=map.getBoundsZoomLevel(bounds);
	
					var centerLat = (bounds.getNorthEast().lat() + bounds.getSouthWest().lat()) /2;
					var centerLng = (bounds.getNorthEast().lng() + bounds.getSouthWest().lng()) /2;
					map.setCenter(new GLatLng(centerLat,centerLng),zoom);
				';
	
	
				// Nothing found
				if ($i ==0) {
					$subpartArray['###CONTENT###'] = $this->pi_getLL('searchNoResult');
					$jsResultUpdate = '';
				}

				$content.= $this->cObj->substituteMarkerArrayCached($template['list'],$markerArray, $subpartArray,$wrappedSubpartArray);
			}

			$debugOut = 0;
			if ($debugOut==1) {
				$content = t3lib_div::view_array($debug).$content;
			}
			
		// minimum character length not reached
		} else {
			$error['minChars'] = sprintf($this->pi_getLL('searchMinChars'), $this->conf['search.']['minChars']);
			$objResponse->addAssign('searchFormError', 'innerHTML',$content);
		}

		// if any errors are found, load the error template
		if (count($error) > 0) {
			$template['list'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_SEARCH_RESULTS_ERROR###');
			$template['item'] = $this->cObj->getSubpart( $template['list'],'###SINGLE###');
			
			foreach ($error as $key) {
				$markerArray['###ERROR###'] = $key;
				$content_item .= $this->cObj->substituteMarkerArrayCached($template['item'],$markerArray);
			}
			$subpartArray['###CONTENT###'] = $content_item;

			$markerArray['###LL_HEADER###'] = $this->pi_getLL('search_error_header');				

			$content.= $this->cObj->substituteMarkerArrayCached($template['list'],$markerArray, $subpartArray);
			
		}
		
		$objResponse->addScript($this->cObj->stdWrap($jsResultDelete, $smallConf['modify.']['deleteJS']));
		$objResponse->addAssign('searchFormResult', 'innerHTML', $content);
		$objResponse->addScript($this->cObj->stdWrap($jsResultUpdate, $smallConf['modify.']['updateJS']));


		return $objResponse->getXML();
	}


	/**
	 * Shows all records which are visible on the map (not all which are available through selected categories!)
	 *
	 * @param	string		$area: the area of the map
	 * @return all available (=visible) records
	 */
	function ajaxGetActiveRecords($area, $cat)	{
		// template
		$template['allrecords'] = $this->cObj2->getSubpart($this->templateCode,'###TEMPLATE_ACTIVERECORDS###');
		$template['item'] = $this->cObj2->getSubpart( $template['allrecords'],'###SINGLE###');
		$smallConf = $this->conf['recordsOnMap.'];

		// language markers
		$languageMarkers = $this->helperGetLLMarkers(array(), $smallConf['LL'], 'recordsonmap');

		// query
		$table 		= $this->config['tables'];
		$field 		= '*';
		$where 		= $this->helperGetAvailableRecords($cat, $area);		
		$orderBy 	= $smallConf['orderBy'];
		$limit 		= $smallConf['limit'];
		$res 			= $this->generic->exec_SELECTquery($field,$table,$where,'',$orderBy, $limit);

		while($row=array_shift($res)) {
			$markerArray = $this->getMarker($row, 'recordsOnMap.', $i);
			$content_item .= $this->cObj->substituteMarkerArrayCached($template['item'],$markerArray, array(), $wrappedSubpartArray);
		}
		
		$subpartArray['###CONTENT###'] = $content_item;

		$markerArray = $languageMarkers;
		$content.= $this->cObj2->substituteMarkerArrayCached($template['allrecords'],$markerArray, $subpartArray);

		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		$objResponse->addAssign('rggooglemap-recordsonmap', 'innerHTML',$content);
		return $objResponse->getXML();
	}


	/**
	 * Initialize the map and all of its needed JS
	 *
	 * @return	void
	 */
	function initMap() {

		// Instantiate the xajax object and configure it
		require_once (t3lib_extMgm::extPath('xajax') . 'class.tx_xajax.php');
		$this->xajax = t3lib_div::makeInstance('tx_xajax'); // Make the instance
		if ($GLOBALS['TSFE']->metaCharset == 'utf-8') {
			$this->xajax->decodeUTF8InputOn(); // Decode form vars from utf8
		}
		$this->xajax->setCharEncoding($GLOBALS['TSFE']->metaCharset); 		// Encode of the response to utf-8 ???
		$this->xajax->setWrapperPrefix($this->prefixId); 		// To prevent conflicts, prepend the extension prefix
		$this->xajax->statusMessagesOn(); 		// Do you wnat messages in the status bar?

		// register the functions of the ajax requests
		$this->xajax->registerFunction(array('infomsg', &$this, 'ajaxGetInfomsg'));
		$this->xajax->registerFunction(array('activeRecords', &$this, 'ajaxGetActiveRecords'));
		$this->xajax->registerFunction(array('processCat', &$this, 'ajaxProcessCat'));
		$this->xajax->registerFunction(array('tab', &$this, 'ajaxGetPoiTab'));
		$this->xajax->registerFunction(array('search', &$this, 'ajaxSearch'));
		$this->xajax->registerFunction(array('processCatTree', &$this, 'ajaxProcessCatTree'));
		$this->xajax->registerFunction(array('getDynamicList', &$this, 'ajaxGetDynamicList'));
		
		$this->xajax->processRequests();

		// additional output using a template
		$template['total'] = $this->cObj2->getSubpart($this->templateCode,'###HEADER###');
		$markerArray = array();
		$markerArray['###PATH###'] = t3lib_extMgm::siteRelpath('rggooglemap');
		$markerArray['###MAP_KEY###'] = $this->config['mapKey'];
		$markerArray['###DYNAMIC_JS###'] = $this->getJs();

		$totalJS = $this->cObj2->substituteMarkerArrayCached($template['total'],$markerArray);

		$GLOBALS['TSFE']->additionalHeaderData['rggooglemap_xajax'] = $this->xajax->getJavascript(t3lib_extMgm::siteRelPath('xajax'));
		$GLOBALS['TSFE']->additionalHeaderData['rggooglemap_js'] = $totalJS;
	}


	/**
	 * Load the info message popup window
	 *
	 * @param	string	$uid: id of reocord
	 * @param	string	$table: table of record
	 * @param int	$prefix: Prefix for tabs in info window
	 * @return	The content of the info window
	 */
	function ajaxGetInfomsg($uid, $table, $tmplPrefix=1)	{
		$template['infobox'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_INFOBOX_'.$tmplPrefix.'###');
		
		// query for single record
		$field = '*';
		$where = 'uid = '.intval($uid);
		$res = $this->generic->exec_SELECTquery($field,$table,$where);
		$row=array_shift($res);
		
		$markerArray = $this->getMarker($row,'popup.');		
		$content.= $this->cObj->substituteMarkerArrayCached($template['infobox'], $markerArray);
		
		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		$objResponse->addAssign('infobox', 'innerHTML', $content);
		
		return $objResponse->getXML();
	}


	/**
	 * Creates the result records
	 *
	 * @param	string	$catList: list of categories
	 * @param	string	$page: page to display
	 * @return	Html $currentPage List
	 */	
	function getDynamicList($catList, $currentPage) {
		$content			= '';
		$smallConf		= $this->conf['recordList.'];
		$currentPage	= intval($currentPage);		
		
		// no result if no categories selected
		if ($catList=='') {
			return $content;
		}
		
		// template
		$template['all']	= $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_RECORDLIST###');
		$template['item']	= $this->cObj->getSubpart( $template['all'],'###SINGLE###');
		$markerArray = $subpartArray = $wrappedSubpartArray = array();
		
		// general query parts
		$field = '*';
		$table = $this->config['tables'];
		$where = $this->helperGetAvailableRecords($catList);
		$limit = ($currentPage*$smallConf['limit']).','.$smallConf['limit'];
				
		// count
		$maxRecords	= $this->generic->exec_COUNTquery($table,$where);
		$maxPages		= ceil($maxRecords/$smallConf['limit']);
		$max				= ($smallConf['limit']>= $maxRecords) ? $maxRecords : ($currentPage*$smallConf['limit'] + $smallConf['limit']);
		

		// query for the results
		$i=0;
		$res = $this->generic->exec_SELECTquery($field, $table, $where, $groupBy, $smallConf['orderBy'], $limit);
		while($row=array_shift($res)) {
			$markerArray = $this->getMarker($row,'recordList.', $i);
			$i++;
			
			$content_item .= $this->cObj->substituteMarkerArrayCached($template['item'],$markerArray);
		}
		$subpartArray['###CONTENT###'] = $content_item;
		$markerArray = $this->helperGetLLMarkers(array(), $smallConf['LL'], 'recordlist');		

		/*
		 * Pagebrowser 
		 */
		// general text (Record x - y from z)		 
		$markerArray['###PB_STATISTIC###'] = sprintf(
			$this->pi_getLL('pagebrowser'),
			$currentPage*$smallConf['limit']+1,
			$max,
			$maxRecords
		);
		
		// current page (Page x)
		$markerArray['###PB_ACT###'] = sprintf(
			$this->pi_getLL('pagebrowser_act'),
			$currentPage+1
		);
		
		// previous page
		if ($currentPage > 0) {
			$pb = ' onClick="'.$this->prefixId.'getDynamicList(\''.$catList.'\','.($currentPage-1).')" ';
			$wrappedSubpartArray['###PB_PREV###'] = explode('|', '<a href="javascript:void(0);"'.$pb.'>|</a>');
		} else {
			$subpartArray['###HIDE_PREV###'] = ' ';
		}
		
		// next page
		if ($currentPage +1 < $maxPages) {
			$new = $currentPage+1;
			$pb = ' onClick="'.$this->prefixId.'getDynamicList(\''.$catList.'\','.($new).')" ';
			$wrappedSubpartArray['###PB_NEXT###'] = explode('|', '<a href="javascript:void(0);"'.$pb.'>|</a>');
		} else {
			$subpartArray['###HIDE_NEXT###'] = 'x';
		}
		
		$content.= $this->cObj->substituteMarkerArrayCached($template['all'], $markerArray, $subpartArray, $wrappedSubpartArray);

		return $content;
	}

	/**
	 * Creates the result records for the first page
	 *
	 * @param	array	$data: selected checboxes
	 * @return	Result records including the pagebrowser for the 1st result page
	 */
	function ajaxProcessCat($data, $page=1)	{
		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		
		if (is_Array($data['cb'])){
			$objResponse->addAssign('mapcatlist', 'innerHTML',implode(',',$data['cb']));
		} else {
			$objResponse->addAssign('mapcatlist', 'innerHTML','9999');
		}
		
		// if the dynamic List is needed
		if ($this->config['loadDynamicList'] == 1) {
			$objResponse->addAssign('formResult', 'innerHTML',$this->getDynamicList(implode(',',$data['cb']), 0));
		}
		
		return $objResponse->getXML();
	}
	
	
	/**
	 * Ajax wrapper function which calles the "real" list function
	 *
	 * @param	string	$catList: list of categories
	 * @param	string	$page: page to display
	 * @return	Html Dynamix List
	 */	
	function ajaxGetDynamicList($catList, $page) {
		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		$content.= $this->getDynamicList($catList, $page);

		$objResponse->addAssign('formResult', 'innerHTML', $content);
		//$objResponse->addScript('setTimeout("fdTableSort.init()", 1000);');		
		return $objResponse->getXML();	
	}		


	/**
	 * Modifies the menu output with including the cateory selection
	 *
	 * @param	array	$data: selected checboxes
	 * @return	Result records
	 */
	function ajaxProcessCatTree($data)	{
		$content.= $this->showMenu($data['cb']);

		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		$objResponse->addAssign('rggooglemap-menu', 'innerHTML',$content);

		return $objResponse->getXML();
  }
  

	/**
	 * Creates the categorymenu
	 *
	 * @param	int	$id: parent_id for the recursive function
	 * @return	categorymenu with parent_id = $id
	 */
	function displayCatMenu($id=0, $level=0) {
		$level++;
		$i = 0;
		
		// actived checkbox for selected category
		$checkedBox = explode(',',$this->config['categoriesActive']);

		// template
		if ($this->config['menu-categorytree'] == 0) {
			$template['total'] = $this->cObj2->getSubpart($this->templateCode,'###TEMPLATE_CATMENU###');
		} else {
			$template['total'] = $this->cObj2->getSubpart($this->templateCode,'###TEMPLATE_CATMENU_TREE###');
		}
		$template['item']	= $this->cObj2->getSubpart($template['total'],'###SINGLE###');
		
		// query
		$table = 'tx_rggooglemap_cat';
		$field = '*';
		$where = 'hidden= 0 AND deleted = 0 AND parent_uid = '.$id;
		$where.= ($this->config['categories']!='') ? ' AND uid IN('.$this->config['categories'].')' : '';
	
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$limit='');
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$i++;
							
			$markerArray = $this->getMarker($row,'catMenu.', $i);
			
			// category image
			$imgTSConfig = $this->conf['catMenu.']['icon.'];
			$imgTSConfig['file'] = 'uploads/tx_rggooglemap/'.$row['image'];
			$markerArray['###ICON###'] = $this->cObj2->IMAGE($imgTSConfig);
			
			$markerArray['###CHECKED###'] = (in_array($row['uid'],$checkedBox)) ? ' checked ="checked" ' : '';
			$markerArray['###RECURSIVE###'] = $this->displayCatMenu($row['uid']);
			
			if ($markerArray['###RECURSIVE###'] != '') {
				// todo
	#			$template['total'] = $this->cObj2->getSubpart($this->templateCode,'###TEMPLATE_CATMENU_NOCHECKBOX###');
			}
			
			$content_item .= $this->cObj2->substituteMarkerArrayCached($template['item'], $markerArray);
		}
		
		$subpartArray['###CONTENT###'] = $content_item;
		
		// general markers
		$markerArray['###LEVEL###'] = $level;
		$markerArray['###FIRST_LEVEL###'] = ($id == 0) ? ' id="rggmmenulvl1" ' : '';
		
		
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		

		if ($i > 0) {
			$content.= $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);
		} else {
			$content = '';
		}

		return $content;
	}


	/**
	 * Creates the javascript which needs to be build dynamically
	 *
	 * @return	all the js
	 */
	function getJs () {
    // some settings for controlling

		// map type
		if ($this->config['mapType']!='') {
			$markerArray['###MAP_TYPES###'] = '{mapTypes:['.$this->config['mapType'].']}';
		}
		
		if ($this->config['mapNavControl'] == 'large' || $this->config['mapNavControl'] == 2) $settings .= 'map.addControl(new GLargeMapControl());';
		elseif ($this->config['mapNavControl'] == 'small' || $this->config['mapNavControl'] == 1) $settings .= 'map.addControl(new GSmallMapControl());';
		elseif ($this->config['mapTypeControl'] == 'show' || $this->config['mapTypeControl'] == '1') $settings .= 'map.addControl(new GMapTypeControl());';
		if ($this->config['mapOverview'] == 1) $settings .= 'map.addControl(new GOverviewMapControl());';
		
		if ($this->config['mapControlOnMouseOver'] == 1) {
			$hideControlsOnMouseOut = 'map.hideControls();
				GEvent.addListener(map, "mouseover", function(){
				map.showControls();
				});
				GEvent.addListener(map, "mouseout", function(){
				map.hideControls();
				});';
		}
		if ($this->conf['enableDoubleClickZoom']== 1)	$settings .= 'map.enableDoubleClickZoom();';
		if ($this->conf['enableContinuousZoom']== 1)	$settings .= 'map.enableContinuousZoom();';
		if ($this->conf['enableScrollWheelZoom']== 1) $settings .= 'map.enableScrollWheelZoom();';
		
		
		// urls
		$xmlUrlConf = $this->conf['xmlURL.'];
		$url = $this->cObj2->typolink('', $xmlUrlConf);
		
		$urlForIcons = t3lib_div::getIndpEnv('TYPO3_SITE_URL').'uploads/tx_rggooglemap/';
		$urlExt = t3lib_div::getIndpEnv('TYPO3_SITE_URL').t3lib_extMgm::siteRelpath('rggooglemap');
		
		// records for the selected categories
		if ($this->config['categoriesActive']!= '') {
			$selectedCat = 'var cat = new Array();
			cat["cb"] = new Object();';
			$cats = explode(',',$this->config['categoriesActive']);
			foreach ($cats as $key=>$value) {
				$selectedCat .= 'cat["cb"]['.$value.'] = '.$value.';';			
			}
			$selectedCat.= ' tx_rggooglemap_pi1processCat(cat);';
		} else {
			$selectedCat.= ' tx_rggooglemap_pi1processCat("default");';
		}
		
		// use cluster, default = 0
		$addMarker = ($this->conf['activateCluster']==1) ? 'clusterer.AddMarker(marker,title);' : 'map.addOverlay( marker );';

		$markerArray['###HIDECONTROLSMOUSEOUT###'] = $hideControlsOnMouseOut;
		$markerArray['###POI_ON_START###'] = $this->getPoiOnStart();
		$markerArray['###SETTINGS###'] = $settings;
		$markerArray['###MAP_ZOOM###'] = $this->config['mapZoom'];
		$markerArray['###MAP_LNG###'] = $this->config['mapLng'];
		$markerArray['###MAP_LAT###'] = $this->config['mapLat'];
		$markerArray['###MAP_DIV###'] = $this->config['mapDiv'];
		$markerArray['###SELECTED_CAT###'] = $selectedCat;
		$markerArray['###ADD_MARKER###'] = $addMarker;
		$markerArray['###URL_ICONS###'] = $urlForIcons;
		$markerArray['###URL###'] = $url;

		// create the gicons JS, needed for valid sizes, don't trust JS on that...
		$gicon = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,image', 'tx_rggooglemap_cat', 'hidden=0 AND deleted=0');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			// set the correct paths if no icon is found
			if ($row['image']=='') {
				$iconPath 	= $this->conf['map.']['defaultIcon'];
				$iconPathJS	= t3lib_div::getIndpEnv('TYPO3_SITE_URL').$this->conf['map.']['defaultIcon'];
			} else {
				$iconPath = 'uploads/tx_rggooglemap/'.$row['image'];
				$iconPathJS	= $urlForIcons.$row['image'];
			}
 
		
			$iconSize = @getimagesize($iconPath);
			$width = 0;
			$height = 0;

			// If icon size can't be get with php, use settings from TS
			if (!is_array($iconSize)) {
				$iconSizeConf = $this->conf['map.']['iconSize.'];

				$current = $row['uid'].'.';
				$width = (intval($iconSizeConf[$current]['width']) > 0) ? intval($iconSizeConf[$current]['width']) : intval($iconSizeConf['default.']['width']);
				$height = (intval($iconSizeConf[$current]['height']) > 0) ? intval($iconSizeConf[$current]['height']) : intval($iconSizeConf['default.']['height']);
			} else {
				$width = $iconSize[0];
				$height = $iconSize[1];
			}

			$key = 'gicons['.$row['uid'].']';
			$gicon .= $key.'= new GIcon(baseIcon);'.chr(10);
			$gicon .= $key.'.image = "'.$iconPathJS.'";'.chr(10);
			$gicon .= $key.'.iconSize = new GSize('.$width.', '.$height.');'.chr(10);
			$gicon .= $key.'.infoWindowAnchor = new GPoint('.($width/2).', '.($height/2).');'.chr(10).chr(10);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		$markerArray['###GICONS###'] = $gicon;


    // Adds hook for processing of extra javascript
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraGetJsHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraGetJsHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$markerArray = $_procObj->extraGetJsProcessor($markerArray, $this);
			}
		}


		$jsTemplateCode = $this->cObj2->fileResource($this->conf['templateFileJS']);
		$template['all'] = $this->cObj2->getSubpart($jsTemplateCode,'###ALL###');

		$js.= $this->cObj2->substituteMarkerArrayCached($template['all'],$markerArray);

		return $js;
	}


	/**
	 * Get the correct JS to show a poi after loading the page.
	 * Tablename + uid can come from piVars or Flexform/TS
	 *
	 * @param	int		$id: the id of the record
	 * @param	int		$tan: the id of the tab which should get filled (every tab has got an own template)
	 * @param	string		$tbl: the table name
   * @return the content
	 */
	function getPoiOnStart() {
		$showPOIonStart = '';

		// pivars overrules flexform/ts
		$defaultPOI = ($this->piVars['poi']!='') ? $this->piVars['poi'] : $this->config['mapShowOnDefault'];
		$table = $this->conf['defaultTable']; // default table

		if ($defaultPOI!='') {
			// split it up by using '-' to get a possible table
			$split = explode('-', $defaultPOI);

			if (count($split)==1) {
				$uid		= $split[0];

				if (!empty($this->piVars['table']) && t3lib_div::inList($this->config['tables'], $this->piVars['table'])) {
					$table = $this->piVars['table'];
				} 
			} else {
				$table	= $split[0];
				$uid		= $split[1];
			}

			$uid = intval($uid);

			// fetch coords for this record
			if ($uid > 0) {
				$where = 'uid = '.$uid.' AND '.$this->helperGetAvailableRecords($this->config['categories']);
				$res = $this->generic->exec_SELECTquery('uid, lng, lat',$table,$where,$groupBy,$orderBy,$offset);
				$row=array_shift($res);

				if (intval($row['lng'])!=0 && intval($row['lat'])!=0) {
					$showPOIonStart = 'myclick('.$row['uid'].','.$row['lng'].','.$row['lat'].',"'.$table.'");';
				}
			}
		}

		return $showPOIonStart;
	}


	/**
	 * Ajax call for the function getPoiContent
	 *
	 * @param	int		$var: the id of the record
	 * @param	int		$tan: the id of the tab which should get filled (every tab has got an own template)
	 * @return the content
	 */
	function ajaxGetPoiTab($id,$tab,$table)	{
		$content = $this->getPoiContent($id,$tab, $table);
		
		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		$objResponse->addAssign('poi', 'innerHTML', $content);
		return $objResponse->getXML();
	}


	/**
	 * Shows the content of a POI bubble
	 *
	 * @param	int		$id: the id of the record
	 * @param	int		$tan: the id of the tab which should get filled (every tab has got an own template)
	 * @param	string		$tbl: the table name
   * @return the content
	 */
	function getPoiContent($id,$tab,$table) {
		$id		= intval($id);
		$tab	= intval($tab);

		// check if all params are valid
		if (!t3lib_div::inList($this->config['tables'], $table) || $tab==0 || $id==0) {
			return sprintf($this->pi_getLL('error_poi-no-valid-params'), $table, $id, $tab);
		}

		// query for single record
		$field = '*';
		$where = 'uid = '.$id;
		$res = $this->generic->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$offset='');
		$row=array_shift($res);


		$markerArray = $this->getMarker($row, 'poi.');

		$tablePrefix = '_'.strtoupper($table);

 		// get the correct template subpart
		$template['all'] = $this->cObj2->getSubpart($this->templateCode,'###TEMPLATE_INFOPOI'.$tablePrefix.$markerArray['###TABPREFIX###'].'_'.$tab.'###');

		// check if subpart is defined, if not, print out an error which will help to find correct syntax of subpart
		if ($template['all']=='') {
			$content = sprintf($this->pi_getLL('error_poi-no-template'), '###TEMPLATE_INFOPOI'.$tablePrefix.$markerArray['###TABPREFIX###'].'_'.$tab.'###');
		} else {
			// having the tablename in every record available
			$markerArray['###TABLE###'] = $table;

			$content.= $this->cObj2->substituteMarkerArrayCached($template['all'],$markerArray, $subpartArray,$wrappedSubpartArray);
		}

		return $content;
	}


	/**
	 * Fills the markerArray with all needed markers
	 *
	 * @param	Array		$row: row of the db query
	 * @param	string	$prefix: prefix needed for the stdwrap functions
	 * @param	integer		$oddEven: item number to set a odd/even class
	 * @return the marker array
	 */
  function getMarker($row, $prefix, $oddEven=0) {
		$prefiWithOutDot = trim($prefix, '.');
		
		// language setting
		if ($GLOBALS['TSFE']->sys_language_content && $this->conf['getRecordOverlay'] == 1) {
			$OLmode = ($this->sys_language_mode == 'strict'?'hideNonTranslated':'');
			$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($row['table'], $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
		}
		
		// general stdWrap handling
		$short = $this->conf[$prefix][$row['table'].'.'];
		foreach ($row as $key=>$value) {
			$this->cObj2->data[$key]=$value; // thanks tobi
			$markerArray['###'.strtoupper($key).'###'] = $this->cObj2->stdWrap($value,$short[$key.'.']);
		}
		
		$markerArray['###POPUP###'] = ' onClick=\' show("infobox"); ' . $this->prefixId . 'infomsg('.$row['uid'].', "'.$row['table'].'"); \'  ';
		$markerArray['###PREFIX###'] = $prefix;
		
		// general odd/even  to avoid this in every function
		if ($oddEven != 0) {
			$markerArray['###ODD_EVEN###'] = ($oddEven%2 == 0) ? 'odd' : 'even';
		} else {
			$markerArray['###ODD_EVEN###'] = '';
		}
		
		
		// get the prefix from the 1st category record for the record
		if ($row['rggmcat']) {
			$catIds = explode(',',$row['rggmcat']);
			
			$resPrefix = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tabprefix','tx_rggooglemap_cat','uid = '.$catIds[0]);
			$rowPrefix = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resPrefix);
			$GLOBALS['TYPO3_DB']->sql_free_result($resPrefix);
			$rowPrefix['tabprefix'] = str_replace(' ', '', $rowPrefix['tabprefix']); // avoid any blanks
			$markerArray['###TABPREFIX###'] = ($rowPrefix['tabprefix']) ? '_'.$rowPrefix['tabprefix'] : '';
		} else {
			$markerArray['###TABPREFIX###'] = '';
		}

		// generic markers
		$short = $this->conf[$prefix][$row['table'].'.']['generic.'];
		if (is_array($short)) {
			foreach($short as $key=>$value) {
				$key2 = trim($key, '.');
				$markerArray['###GENERIC_'.strtoupper($key2).'###'] = $this->cObj2->cObjGetSingle($short[$key2] , $short[$key] );
			}
		}

		// language markers
		$markerArray = $this->helperGetLLMarkers($markerArray, $this->conf[$prefix]['LL'], strtolower($prefiWithOutDot));

		// Adds hook for processing of extra item markers
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraItemMarkerHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraItemMarkerHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$markerArray = $_procObj->extraItemMarkerProcessor($markerArray, $row, $this->config, $this);
			}
		}

    return $markerArray;
  }


	/**
	 * Predefine the where clause
	 *
	 * @param	string		$catList: List of current categories
	 * @param	string	$areaSearch: Coordinates of the map holding the corner points
	 * @return the marker array
	 */  
  function helperGetAvailableRecords($catList='', $areaSearch='') {
		$where = ' lng!=0 AND lat!=0 '.$this->config['pid_list'];

		if (!empty($areaSearch)) {
		// build the query
		$areaArr=split('%2C%20',$areaSearch);
		$where .= ' AND lng between '.$areaArr[1].' AND '.$areaArr[3].'
							AND	lat between '.$areaArr[0].' AND '.$areaArr[2];
		}

		// if no category chosen, be sure no result gets displayed
		if($catList==9999) {
			$where .= ' AND 1=2 ';
		} elseif ($catList!='') {
		
			$catList = explode(',',$catList);
			foreach ($catList as $key=>$value) {
				$where2.= ' FIND_IN_SET('.$value.',rggmcat) OR';
			}
			$where .= ' AND ( '.substr($where2,0,-3).' ) ';
		}
		
		return $where;
	}


	/**
	 * Get the recursive categories
	 *
	 * @param	string		$allowedCat: the allowed categories
	 * @param	int	  $parentId: Parent id of the record
   * @return	array with all allowed categories
	 */
	function helperGetRecursiveCat($allowedCat, $parentId=0,$level=0 ) {
		#  $catArr = array();
		$level++;
		
		$where = 'hidden = 0 AND deleted=0 AND parent_uid='.$parentId;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pid,uid,title,parent_uid','tx_rggooglemap_cat',$where);
		
		// recursive query
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if (in_array($row['uid'], explode(',',$allowedCat))) {
				$catArr .= '<option class="searchlvl'.$level.'" value="'.$row['uid'].'">'.$row['title'].'</option>';
				$recursiveCat =   $this->helperGetRecursiveCat($allowedCat,$row['uid'],$level);
				if ($recursiveCat!='')  {
					$catArr.= $recursiveCat;
				}
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		
		return $catArr;
	}


	/**
	 * Geocode an adress string, which needs already to be in the correct order
	 *
	 * @param	string		$address: address
	 * @param	string	  $zip: zip
	 * @param	string	  $city: city
	 * @param	string	  $country: country
	 * @return	array with the status
	 */
	function helperGeocodeAddress($address='', $zip='', $city='', $country='') {
		$geocode	= array();
		$coords		= array();
		$search		= false;
		
		if ($address!='') {
			$geocode[] = $address;
			$search = true;
		}
		if ($zip!='') {
			$geocode[] = $zip;
			$search = true;
		}
		if ($city!='') {
			$geocode[] = $city;
			$search = true;
		}
		if ($country!='')  {
			$geocode[] = $country;
		}  else {
			$geocode[] = $this->config['defaultCountry'];
		}
		
		// just if there are some values additional to the country
		if ($search) {
			$geocode = implode(',', $geocode);
			
			// call google service
			$url = 'http://maps.google.com/maps/geo?q='.urlencode($geocode).'&output=csv&key='.$this->config['mapKey'];
			$response=stripslashes(t3lib_div::getURL($url));
			
			// determain the result
			$response = explode(',',$response);
			
			// if there is a result
			$coords['status'] 	= $response[0];
			$coords['accuracy']	= $response[1];
			$coords['lat']			= $response[2];
			$coords['lng']			= $response[3];
			return $coords; // lat,lng

		} else {
				$coords['status']	= 601;
		}
		
		return $coords;
  }
  

	/**
	 * Get specific language markers
	 *
	 * @param	array		$markerArray: the markerarray which will be filled with the language markers
	 * @param	string		$conf: The keys of the language markers
	 * @param	string		$prefix: Prefix which is used in the locallang file
	 * @return the marker array with the language markers
	 */
	function helperGetLLMarkers($markerArray, $conf, $prefix) {
		// add the general language markers
		if ($this->conf['generalLL']!='') {
			$markerList = t3lib_div::trimExplode(',', $this->conf['generalLL']);
			foreach($markerList as $key) {
				$markerArray['###LL_'.strtoupper($key).'###'] = $this->pi_getLL('general_'.$key);
			}
		}

		// add the specific language markers
		if($conf=='') {
			return $markerArray;
		} else {
			$prefix = trim($prefix).'_';
			$markerList = t3lib_div::trimExplode(',', $conf);
			foreach($markerList as $key) {
				$markerArray['###LL_'.strtoupper($key).'###'] = $this->pi_getLL($prefix.$key);
			}
		}

		return $markerArray;
	}


	/**
	 * Check if ajax url is the same domain as for current url
	 * no cross site ajax requests possible!	 
	 *
	 * @return	array holding the infos for the error msg
	 */

	function helperCheckForWrongUrl() {
		$status = array();
		
		if ($GLOBALS['TSFE']->config['config']['baseURL'] != '' || $GLOBALS['TSFE']->config['config']['absRefPrefix']) {
			$currentDomain = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
			$linkDomain = $this->pi_getPageLink($GLOBALS['TSFE']->id);
			
			if ($linkDomain != '' && strpos($linkDomain, $currentDomain) === false) {
				$status['current']	= $currentDomain;
				$status['link']			= $linkDomain;
			}
		}
		
		return $status;
	}
	
	
	/**
	 * Get the correct Google Maps API key if multidomains are used and key 
	 * for the current domain is found 
	 *
	 * @param	string		$defaultKey: The default key
	 * @param	string		$multiKey: Setting of the EM holding all domains and the keys
	 * @return string the key for the current domain
	 */
	function helperGetMultiDomainKey($defaultKey, $multiKey) {
		$finalKey = $defaultKey;
		$multiKey = trim($multiKey);

		// if any multidomain key is found
		if (trim($multiKey) != '') {
			$keyListTmp = explode('#####', $multiKey); // split by #### because EXT constantsextended uses that
			$keyList = array();
			foreach($keyListTmp as $key) {
				$split = explode('=', $key);
				$keyList[$split[0]] = $split[1];
			}

			// get current domain and check if there is a API for it
			$currentDomain = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
			if ($keyList[$currentDomain] != '') {
				$finalKey = $keyList[$currentDomain];
			}
		}
		
		return $finalKey;
	}


	/**
	 * Get the image of the categories
	 * todo: create a real recursive function	 

	 * @param	array		$catImg: array holding the record
	 * @param	int		$parent: id of the parent category
	 * @return	array list of category records with their images
	 */
	function helperGetCategoryImage($catImg, $parent=0) {
		$table = 'tx_rggooglemap_cat';
		$field = 'uid,image,parent_uid';
		$where = 'deleted = 0 AND hidden=0 ';
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$limit='');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($row['image']=='') {
				// get image of parent category
				$whereTemp = 'deleted = 0 AND hidden=0 AND uid = '.$row['parent_uid'];
				$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$whereTemp);
				$row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2);
				$GLOBALS['TYPO3_DB']->sql_free_result($res2);
				$catImg[$row['uid']] = $row2['image'];
			} else {
				$catImg[$row['uid']] = $row['image'];
			}
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		
		return $catImg;
	}


	/**
	 * Get the value out of the flexforms and if empty, take if from TS
	 *
	 * @param	string		$sheet: The sheed of the flexforms
	 * @param	string		$key: the name of the flexform field
	 * @param	string		$confOverride: The value of TS for an override
	 * @return	string	The value of the locallang.xml
	 */
	function helperGetFlexform($sheet, $key, $confOverride='') {
		// Default sheet is sDEF
		$sheet = ($sheet=='') ? $sheet = 'sDEF' : $sheet;
		$flexform = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $key, $sheet);

		// possible override through TS
		if ($confOverride=='') {
			return $flexform;
		} else {

			// hack to work with multiple TS arrays
			$tsparts = explode('.', $confOverride);
			if (count($tsparts)==1) { // default with no .
				$value = $flexform ? $flexform : $this->conf[$confOverride];
				$value = $this->cObj->stdWrap($value,$this->conf[$confOverride.'.']);
			} elseif (count($tsparts)==2) { // 1 sub array
				$value = $flexform ? $flexform : $this->conf[$tsparts[0].'.'][$tsparts[1]];
				$value = $this->cObj->stdWrap($value,$this->conf[$tsparts[0].'.'][$tsparts[1].'.']);
			}

			return $value;
		}
	}
	
	/*
	* **********************************
	* ********** X M L *****************
	* **********************************
	**/
	function xmlFunc($content,$conf)	{
		$this->init($conf);
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		
		$postvars = t3lib_div::GPvar('tx_rggooglemap_pi1');
		
		// fetch the content of a single poi
		if ($postvars['detail']!='') {
			$content = $this->getPoiContent($postvars['detail'],1,$postvars['table']);
			return $content;
		}
			
		// categories
		$cat = $postvars['cat'];
		if ($cat) { // cat selected
			if ($cat!=9999) { // nothing selected
				$catList = explode(',', $cat);
			}
		} else { // nothing selected means 1st call!
			$catList =  explode(',', $this->config['categoriesActive']);
		}
		
		$this->xmlRenderHeader();
		
		if ($catList) {
			$catImg = $this->helperGetCategoryImage(array()); // category images
						
			$table =  $this->config['tables'];
			
			// smaller and faster query, use smaller select, otherwise fetch all
			if ($this->conf['title.']['useRggmTitle'] == 1)
				$field = 'uid,rggmcat,rggmtitle,lat,lng';
			else {
				$field = '*';
			}
			$where = 'lng!=0 AND lat!= 0 AND lng!=\'\' AND lat!=\'\' '.$this->config['pid_list'];
			
			if (strlen($postvars['area'])>5) {
				$areaArr=array();
				$areaArr=split(', ',$postvars['area']);
				$where.= ' AND lng between '.$areaArr[1].' AND '.$areaArr[3].'
									 AND	lat between '.$areaArr[0].' AND '.$areaArr[2];
			}
			
			// category selection
			$catTmp = false;
			foreach ($catList as $key=>$value) {
				if ($value) {
					$catTmp=true;
					$where2.= ' FIND_IN_SET('.$value.',rggmcat) OR';
				}
			}
			$where .= ($catTmp) ? ' AND ( '.substr($where2,0,-3).' ) ' : '';
			
			
			
			$limit = '';
			
			if ($this->conf['extraquery']==1) {
				$extraquery = ($GLOBALS['TSFE']->fe_user->getKey('ses','rggmttnews2'));
				if ($extraquery!= '') {
					$where.= ' AND uid IN ('.$extraquery.') ';
				}
			}
			
			
			// Adds hook for processing of the xml func
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['xmlFuncHook'])) {
				foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['xmlFuncHook'] as $_classRef) {
					$_procObj = & t3lib_div::getUserObj($_classRef);
					$where = $_procObj->extraSearchProcessor($table,$where,$orderBy, $limit, $postvars, $this);
				}
			}
			
			$count = 0;
			$res = $this->generic->exec_SELECTquery($field,$table,$where,$groupBy,$orderBy,$limit);
			
			while($row=array_shift($res)) {
				$test = '';
				$count++;
				$catList = explode(',', $row['rggmcat']);
				$img = $catImg[$catList[0]];	
				$img = $catList[0];
				
				$this->xmlAddRecord($table, $row,$conf, $img, $test);
			}
			#echo $count;
		}
		$this->xmlRenderFooter();
		
		$result = $this->xmlGetResult();
		return $result;
	}


	/**
	 * adds a single record to the xml file
	 *
	 * @param	array		$row: all fields of one record
	 * @param	array		$conf: The PlugIn configuration
	 * @return single line @ xml
	 */
	function xmlAddRecord($table, $row,$conf, $img, $test) {
		// language setting
		if ($GLOBALS['TSFE']->sys_language_content && $this->conf['getRecordOverlay'] == 1 ) {
			$OLmode = ($this->sys_language_mode == 'strict'?'hideNonTranslated':'');
			$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($table, $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
		}

		$this->xmlLines[]=$this->xmlIcode.'<marker cat="'.$row['rggmcat'].'"  uid="'.$row['uid'].'" lng="'.$row['lng'].'" lat="'.$row['lat'].'"  img="'.$img.'" table="'.$row['table'].'"  >';


		$this->xmlGetRowInXML($row,$conf);
		$this->xmlLines[]=$this->xmlIcode.'</marker>';
	}


	/**
	*  inserts the element/node "html" for every record => the content of every POI
	*
	* @param	array		$row: all fields of one record
	* @param	array		$conf: The PlugIn configuration
	* @return element "html" @ xml
	*/
	function xmlGetRowInXML($row,$conf) {
		$table = $row['table'];

		// if useRggmTitle > title field is hardcoded
		$field = ($this->conf['title.']['useRggmTitle'] == 1) ? 'rggmtitle' : $this->conf['title.'][$table];
		
		$title = $this->cObj2->stdWrap($row[$field], $this->conf['title.'][$table.'.']);
		$content = '<![CDATA[ '.$title.' ]]>';
		$this->xmlLines[]=$this->xmlIcode.$this->xmlFieldWrap('t',(($content)));
	}


	/**
	*  filling the marker of the template with values of the records. Image processing and so on
	*
	* @param	array		$row: all fields of one record
	* @param	array		$conf: The PlugIn configuration
	* @return element "html" @ xml
	*/
	function xmlNewLevel($name,$beginEndFlag=0,$params=array()) {
		if ($beginEndFlag){
			$pList='';
			if (count($params)) {
				$par=array();
				reset($params);
				while(list($key,$val)=each($params)) {
					$par[]=$key.'="'.htmlspecialchars($val).'"';
				}
				$pList=' '.implode(' ',$par);
			}

			$this->xmlLines[]=$this->xmlIcode.'<'.$name.$pList.'>';
		} else {
			$this->xmlLines[]=$this->xmlIcode.'</'.$name.'>';
		}
	}


	function xmlGetResult() {
		$content = implode(chr(10),$this->xmlLines);
		return $content;
	}


	function xmlFieldWrap($field,$value) {
		return '<'.$field.'>'.$value.'</'.$field.'>';
	}


	// just returns the top level name
	function xmlTopLevelName() {
		return 'markers';
	}


	// rendering header
	function xmlRenderHeader() {
		$this->xmlNewLevel($this->xmlTopLevelName(),1);
	}
	
	
	// rendering footer
	function xmlRenderFooter() {
		$this->xmlNewLevel($this->xmlTopLevelName(),0);
	}

} // class tx_rggooglemap



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/pi1/class.tx_rggooglemap_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/pi1/class.tx_rggooglemap_pi1.php']);
}

?>
