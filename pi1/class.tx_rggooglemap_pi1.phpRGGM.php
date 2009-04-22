<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Georg Ringer <typo3 et ringerge dot org>
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


/**
 * Plugin 'Google Map (rggooglemap)' for the 'rggooglemap' extension.
 *
 * @author	Georg Ringer <typo3@ringerge.org>
 */

class tx_rggooglemap_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_rggooglemap_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_rggooglemap_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'rggooglemap';	// The extension key.

	/**
	 * Just some intialization, mainly reading the settings in the flexforms
	 *
	 * @param	array		$conf: The PlugIn configuration
	 */	
	function init($conf) {
	require_once (PATH_tslib.'/class.tslib_content.php');
		$this->cObj2 = t3lib_div::makeInstance('tslib_cObj'); // Local cObj. 
    $this->conf = $conf; // Storing configuration as a member var
		$this->pi_loadLL(); // Loading language-labels
		$this->pi_setPiVarDefaults(); // Set default piVars from TS
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);

		// Template code
		$this->templateCode = $this->cObj2->fileResource($this->conf['templateFile']);

    /*
    * 1st sheet: Map settings 
    */   

		$pid_list =  $this->getFlexform('sDEF', 'startingpoint', 'pidList');
		if (intval($this->piVars['pidList'])!=0) {
			$pid_list = intval($this->piVars['pidList']);
		}
		$recursive = intval($this->getFlexform('sDEF', 'recursive', 'recursive'));
		
		if ($pid_list!='') {
			if ($recursive > 0) {
				$this->config['pid_list'] 				= $this->pi_getPidList($pid_list,10);
			} else {
				$this->config['pid_list'] 				= $pid_list;
			}
			$this->config['pid_list'] = ' AND pid IN('.$this->config['pid_list'].') ';
		}
		 

		$this->config['show'] 							= $this->getFlexform('sDEF', 'show', 'show'); // show		
		$this->config['categories'] 				= $this->getFlexform('sDEF', 'categoriesselected', 'mapAvailableCats');		// loaded POI categories
		$this->config['categoriesActive'] 	= $this->getFlexform('sDEF', 'categories', 'mapActiveCats'); 		// active POI categories  
		$this->config['mapDiv'] 						= $this->conf['mapDiv']; 		// map div id
		$this->config['mapWidth'] 					= $this->getFlexform('sDEF', 'width', 'mapWidth');		// width
		$this->config['mapHeight'] 					= $this->getFlexform('sDEF', 'height', 'mapHeight');		// height
		$this->config['mapLng'] 						= $this->getFlexform('sDEF', 'lng', 'mapLng');    // lng
		$this->config['mapLat'] 						= $this->getFlexform('sDEF', 'lat', 'mapLat');    // lat
		$this->config['mapZoom'] 						= $this->getFlexform('sDEF', 'zoom', 'mapZoom');    // zoom
    $key = $this->getFlexform('sDEF', 'key', 'mapKey');    // google map key
		$this->config['mapKey'] 						= ($key!='') ? $key : $this->confArr['googleKey'];

		$this->config['mapType'] 						= $this->getFlexform('sDEF', 'type', 'mapType');    // map control 
		$this->config['mapTypeControl'] 		= $this->getFlexform('sDEF', 'type_controls', 'mapControl'); 
		$this->config['mapNavControl'] 			= $this->getFlexform('sDEF', 'nav_controls', 'mapNavigation');
	  $this->config['mapControlOnMouseOver'] = $this->getFlexform('sDEF', 'controlonmouseouver', 'mapNavigationOnMouseOver');
    $this->config['mapOverview'] 				= $this->getFlexform('sDEF', 'mapoverview', 'mapOverview');
    
		$this->config['mapShowOnDefault'] 	= $this->getFlexform('sDEF', 'showondefault', 'showOnDefault');    // default POI to show on begin
    $this->config['loadDynamicList'] 	= $this->getFlexform('sDEF', 'loadDynamicList', 'loadDynamicList'); 
    
    /*
    * 3rd sheet: Config for Menu-output 
    */     
    $this->config['menu-cat']						= $this->getFlexform('menu', 'categories'); 
    $this->config['menu-include'] 			= $this->getFlexform('menu', 'include');    // Checkbox to include header + description
    $this->config['menu-map'] 					= $this->getFlexform('menu', 'map');    // ID of the map page
    $this->config['menu-catSort'] 			= $this->getFlexform('menu', 'menucatsortorder', 'menu.catOrder');    // orderBy of categories and records
    $this->config['menu-catSortBy'] 		= $this->getFlexform('menu', 'menucatsortorderby', 'menu.catOrderBy');
    $this->config['menu-recordSort'] 		= $this->getFlexform('menu', 'menurecordsort', 'menu.recordsOrder');
    $this->config['menu-recordSortBy'] 	= $this->getFlexform('menu', 'menurecordsortby', 'menu.recordsOrderBy');
    $this->config['menu-categorytree'] 	= $this->getFlexform('menu', 'usecategorytree');    // Use category-tree in menu view
    $this->config['menu-searchbox'] 		= $this->getFlexform('menu', 'usesearchbox');    // Use searchbbox in menu view

		// search tab
    $this->config['search']['defaultCountry'] 	= $this->getFlexform('search', 'defaultCountry', 'search.defaultCountry');
    $this->config['search']['radiusSearch'] 		= $this->getFlexform('search', 'radiusSearch', 'search.radiusSearch');


    // which tables should be uses
    $tmp_confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);   
    $this->config['tables'] = ($this->confArr['tables']!='') ? $this->confArr['tables'] : $tmp_confArr['tables'];

		require_once( t3lib_extMgm::siteRelpath('rggooglemap').'res/class.tx_rggooglemap_table.php');
	  $this->generic = t3lib_div::makeInstance('tx_rggooglemap_table');	 

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
		
		// emulate dynamic filtering
		$this->loadOnDefault =  '14';
		#$this->config['recordsPerPage'] =10 ;

		$showItems = t3lib_div::trimExplode(',', $this->config['show']);
		if (count($showItems)>0) {
			foreach($showItems as $key) {

		    if ($key == 'MAP') { $content.= $this->showMap($contentx,$conf); }
		    if ($key == 'LOCATION') { $content .= $this->showLocationBox($contentx,$conf); }    
		    if ($key == 'RECORDSONMAP') { $content .= $this->recordsOnMap($contentx,$conf); }
		    if ($key == 'MENU') { $content .= $this->showMenu($contentx,$conf); }        
		    if ($key == 'SEARCH') { $content .= $this->showSearch($contentx,$conf);}

			}
		}
		
    

		$content = $this->pi_wrapInBaseClass($content);
    
    return $content;
	}
	
	/**
	 * View "MENU": Show all records of the selected categories and starting point, linking to the map on a different page
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * $param array $additional: Function can be called by processCatTree to change used categories dynamically	 
	 * @return	The plugin content
	 */	
  function showMenu ($content, $conf, $additionalCat='', $additionalWhere='') {
    $template["total"] = $this->cObj->getSubpart($this->templateCode,"###TEMPLATE_MENU###");  
    $template["item"] = $this->cObj->getSubpart($template["total"],"###ITEM_SINGLE###");
    $template["item2"] = $this->cObj->getSubpart($template["total"],"###ITEM_SINGLE2###");

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
      foreach ($row as $key=>$value) {
      	$markerArray['###'.strtoupper($key).'###'] = $this->cObj->stdWrap($value,$his->conf['menuCategory.'][$key.'.']);
      }
      #t3lib_div::print_array($markerArray);

      
      // query for single records in category

      $firstCategory = explode(',',$row['uid']);
      // display not yet

      $where2 = 'deleted = 0 AND hidden = 0 '.$this->config['pid_list'].' AND rggmcat = '.$firstCategory{0}.' AND lat!=0 AND lng!=0 AND lng != \'\' AND lat != \'\' ';


      
     


      // search mode 
      if ($additionalWhere!= '') {
        $where2 .= $additionalWhere;      
      }
      $table = $this->config['tables'];

      if ($this->config['menu-recordSort']) {
      	$orderBy2 = $this->config['menu-recordSort'].' '.$this->config['menu-recordSortBy'];
      }
      
      
      $res2 = $this->generic->exec_SELECTquery('*',$table,$where2,$groupBy='',$orderBy2,$limit='');
      // List of single records
      $content_item2 = '';

      //x while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)) {
      while($row2=array_shift($res2)) {
        $i++;
        /*foreach ($row2 as $key=>$value) {
           $this->cObj->data[$key]=$value; // thanks to tobi
        	$markerArray['###'.strtoupper($key).'###'] = $this->cObj->stdWrap($value,$this->conf['menu.'][$key.'.']);
        } */

        $tmp = $this->getMarker($row2, 'menu.');
        $markerArray2 = $tmp['markerArray'];
        $wrappedSubpartArray = $tmp['wrappedSubpartArray'];
        // odd/even
        $markerArray2['###ZEBRA###'] = ($i%2==0) ? 'odd' : 'even';
                
        // Linkfields
        $allFields = explode(',',$this->conf['linkFields']);    
        foreach ($allFields as $key=>$field) {
          $vars = explode('|',$row2[$field]);
          // static link text > TS: link.www.value = xxx
          if ($this->conf['link.'][$field.'.']['value']) $vars[1] = $this->conf['link.'][$field.'.']['value'];
          // dynamic link text from other field
          if ($this->conf['link.'][$field.'.']['field']) $vars[1] = $row2[ $this->conf['link.'][$field.'.']['field'] ];
          
          $param = array('parameter'=>$vars[0].' '.$this->conf['marker.'][$key.'Link']);
          $link = $this->cObj->typoLink($this->cObj->stdWrap($vars[1],$this->conf['marker.'][$field.'.']),$param);
          
          $markerArray2['###'.strtoupper($field).'###'] = $this->cObj->stdWrap($link,$this->conf['menu.'][$field.'.']);    
        }       
        
        // no page ID for map > suggesting plugin is on the same page => javascript links
        if ($this->config['menu-map']!='') {
          $vars['poi'] = $row2["uid"];

          if ($row2['table'] !='tt_address') {
            $vars['table'] = $row2['table'];
          }
         
          $wrappedSubpartArray['###LINK_ITEM###'] = explode('|', $this->pi_linkTP_keepPIvars('|', $vars, 1,1,$this->config['menu-map']));
        } else {
          $wrappedSubpartArray['###LINK_ITEM###'] = explode('|', '<a onclick="myclick('.$row2['uid'].','.$row2['lng'].','.$row2['lat'].', \''.$row2['table'].'\')" href="javascript:void(0)">|</a>'); 
        }

        $content_item2 .=$this->cObj->substituteMarkerArrayCached($template["item2"],$markerArray2, $subpartArray,$wrappedSubpartArray );	
      } # end while
      
      $subpartArray["###CONTENT2###"] = $content_item2 ;
			$content_item .=($i>0) ? $this->cObj->substituteMarkerArrayCached($template["item"],$markerArray, $subpartArray,$wrappedSubpartArray ) :'';			

		} # end while
		$subpartArray["###CONTENT###"] =($i>0) ? $content_item : '';

    $content.= $this->cObj->substituteMarkerArrayCached($template["total"],$markerArray, $subpartArray,$wrappedSubpartArray);
		return $content;
	} 


	/**
	 * Plugin mode SEARCH: Presents a form to search for records
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The plugin content
	 */	
  function showSearch ($content, $conf) {

    $template["list"] = $this->cObj->getSubpart($this->templateCode,"###TEMPLATE_SEARCH###");
    $subpartArray = array();
		$markerArray = $this->getLLMarkers(array(), $this->conf['search.']['LL'], 'search');    
    
    if ($this->config['search']['radiusSearch']!=1) {
			$subpartArray['###HIDE_RADIUSSEARCH###'] = '';
		}
		$markerArray['##DEFAULT_COUNTRY###'] = $this->config['search']['defaultCountry'];

    $cat = $this->getRecursiveCat($this->config['categories']);
    $markerArray['###CATEGORY###'] = $cat;
    
    $content.= $this->cObj->substituteMarkerArrayCached($template["list"],$markerArray, $subpartArray);
		return $content;
	}
	

	/**
	 * Get the recursive categories
	 *
	 * @param	string		$allowedCat: the allowed categories
	 * @param	int	  $parentId: Parent id of the record
   * @return	array with all allowed categories
	 */	
	 
  function getRecursiveCat($allowedCat, $parentId=0,$level=0 ) {
  #  $catArr = array();
  $level++;
    
    $where = 'hidden = 0 AND deleted=0 AND parent_uid='.$parentId;
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pid,uid,title,parent_uid','tx_rggooglemap_cat',$where);
 /*
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        if (in_array($row['uid'], explode(',',$allowedCat))) {
          $catArr[$row['uid']]['name']= $row['title'];
           $recursiveCat =   $this->getRecursiveCat($allowedCat,$row['uid']);       
          if (is_array($recursiveCat))  $catArr[$row['uid']]['child']= $recursiveCat;
        }     	
    }*/
    
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        if (in_array($row['uid'], explode(',',$allowedCat))) {
          $catArr .= '<option class="searchlvl'.$level.'" value="'.$row['uid'].'">'.$row['title'].'</option>';
           $recursiveCat =   $this->getRecursiveCat($allowedCat,$row['uid'],$level);       
          if ($recursiveCat!="")  $catArr.= $recursiveCat;
        }     	
    }
        
    return $catArr;

    
  }	

	/**
	 * Geocode an adress string, which needs already to be in the correct order
	 *
	 * @param	string		$address: address
	 * @param	string	  $zip: zip
	 * @param	string	  $city: city
	 * @param	string	  $country: country	 	 
   * @return	lat,lng
	 */	
  function geoCodeAddress($address="", $zip="", $city="", $country="") {
    $geocode = array();
    $search = false;
    if ($address!="") {
      $geocode[] = $address;
      $search = true;
    }
    if ($zip!="") {
      $geocode[] = $zip;
      $search = true;
    }      
    if ($city!="") {
    $geocode[] = $city;
      $search = true;
    }  
    if ($country!="")  {
      $geocode[] = $country;
    }  else {
      $geocode[] = $this->conf['geocodeDefaultCountry'];
    }
    
    // just if there are some values
    if ($search) {
      $geocode = implode(',', $geocode);
      
      // call google service
      $url = 'http://maps.google.com/maps/geo?q='.urlencode($geocode).'&output=csv&key='.$this->config['mapKey'];
      $response=stripslashes(file_get_contents($url));
  
      // determain the result
      $response = explode(',',$response);
      
      // if there is a result
      if ($response[0]=='200' && $response[2]!= '' && $response[3] != '') {
        return $response[2].','.$response[3];       // lat,lng
      } else {
        return '';
      }
    } else {
      return '';
    }
  } 
	
	/**
	 * Function for the ajax search
	 *
	 * @param	string		$searchFields: The search fiels (search word & search on map only)
	 * @return	Records found with search value
	 */	
	function search($searchForm)	{
    $template['list'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_SEARCH_RESULTS###');
    $template["item"] = $this->cObj->getSubpart( $template["list"],"###SINGLE###");
    $objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
    
    $test ="";
    $debug  =array();

    // minimum characters needed, default = 3
    if (strlen($searchForm['rggmsearchValue']) >= $this->conf['search.']['minChars'] || ($searchForm['rggmActivateRadius']=="on" && $searchForm['rggmRadius']>0)   ) {
      $res = Array();
      
      // search for a text


        // escaping the search-value
        $delete = array("'", "\"", "\\", "/", "");
        $searchExpression = $searchForm['rggmsearchValue'];
        $searchExpression = str_replace($delete, "", $searchExpression);
        
        
        $tablelist = explode(',',$this->config['tables']);
        
        foreach ($tablelist as $key=>$table) {
          $where = 'lng!=0 AND lat!=0 AND deleted = 0 '.$this->config['pid_list'];
          if ($this->conf['search.'][$table]) {
            $select = '*';
            $searchField = explode(',',$this->conf['search.'][$table]);
            $where2 = '';
            foreach ($searchField as $key=>$value) {
              $where2.= " $value LIKE '%$searchExpression%' OR";
            }
            $where .= ' AND ( '.substr($where2,0,-3).' ) ';
            
            // search only within the map area
            if ($searchForm['rggmOnMap']=='on') {
                $areaArr=split("%2C%20",$searchForm['rggmBound']);
                $where.= 'AND lng between '.$areaArr[1].' AND '.$areaArr[3].'
                          AND	lat between '.$areaArr[0].' AND '.$areaArr[2];
            }

            // radius search (umkreissuche)
            if ($searchForm['rggmActivateRadius']=="on") {
              $coordinates = $this->geoCodeAddress("",$searchForm['rggmZip'], '', $searchForm['rggmCountry']);
              if ($coordinates!="") {
                $coordinates = explode(',',$coordinates);
                $select = '*,SQRT(POW('.$coordinates[1].'-lng,2)*6400 + POW('.$coordinates[0].'-lat,2)*12100) AS distance';
                $where.= ' AND SQRT(POW('.$coordinates[1].'-lng,2)*6400 + POW('.$coordinates[0].'-lat,2)*12100) <'.intval($searchForm['rggmRadius']);
                $orderBy = 'distance';
               

                $offset = '0,'.$this->conf['recordsPerPage'];
                $offset = '0,10';

              } 
            }

            // category
            if ($searchForm['rggmCat']!='') {
              foreach (explode(',',$searchForm['rggmCat']) as $key=>$value) {
                $whereCat.= ' FIND_IN_SET('.$value.',rggmcat) OR';
              }
              $where.= ' AND ( '.substr($whereCat,0,-3).' ) ';              
            }            
                  
            $limit = '0,'.$this->conf['recordsPerPage'];
            $limit = '';
            $res += $this->generic->exec_SELECTquery($select,$table,$where,$groupBy,$orderBy, $limit);
            #$content.=t3lib_div::view_array($res);

          }
          
        	$debug[$table]['where'] = $where;
        }
        

			// todo
			$res = array_slice($res, 0,99);
      
      #$res = $this->generic->exec_SELECTquery('*',$table,$where,$groupBy,$orderBy,'0,'.$this->conf['recordsPerPage'],1);

      $i = 0;
      $jsResultDelete = 'deleteSearchResult();';
      $jsResultUpdate = 'var bounds = new GLatLngBounds();';
$debug['count'] = 0;
            $debug[$table]['res'] = $res;
            
            
      while($row=array_shift($res)) { 
				$debug['count']++;
    		if (intval($row['lat'])==0 || intval($row['lng'])==0) {
					continue;
				}

        $tmp = $this->getMarker($row,'search.');
        $markerArray = $tmp['markerArray'];
        $wrappedSubpartArray = $tmp['wrappedSubpartArray']; 
       
				$markerArray['###ODDEVEN###'] = ($i % 2==0) ? 'odd' : 'even';
        $markerArray['###SEARCHID###'] = $i+1;
        
        
        $title = 'marker'.($i+1).'.png';
        $icon = 'map-pointer.gif';
        $icon = $title;

        $jsResultUpdate .= '
          //marker = createMarker(new GLatLng('.$row['lat'].','.$row['lng'].'), '. $row['uid'].', \''.htmlspecialchars($title).'\', \''.$row[name].'\', \''.$row[table].'\');
            marker = createMarker(new GLatLng('.$row['lat'].','.$row['lng'].'), '. $row['uid'].', \''. $icon.'\', \''. htmlspecialchars($row['rggmtitle']).'\', \''. $row['table'].'\', 1);
         
          map.addOverlay( marker ); 
          searchresultmarkers['.$i.'] = marker; 
          bounds.extend(new GLatLng('.$row['lat'].','.$row['lng'].'));
        ';

          $i++;      

  			$content_item .= $this->cObj->substituteMarkerArrayCached($template["item"],$markerArray, array(), $wrappedSubpartArray);

      }
      
      $jsResultUpdate.= $test;
   
      $markerArray['###SEARCHEXPRESSION###'] = $searchForm['rggmsearchValue'];
      $markerArray['###SEARCHCOUNT###'] = $i;
 
    
      $subpartArray["###CONTENT###"] = $content_item;
      
      $jsResultUpdate .= '
        var zoom=map.getBoundsZoomLevel(bounds);
        
        var centerLat = (bounds.getNorthEast().lat() + bounds.getSouthWest().lat()) /2;
        var centerLng = (bounds.getNorthEast().lng() + bounds.getSouthWest().lng()) /2;
        map.setCenter(new GLatLng(centerLat,centerLng),zoom); 
      ';
      
     
      // Nothing found
      if ($i ==0) {
        $subpartArray["###CONTENT###"] = $this->pi_getLL('searchNoResult'); 
        $jsResultUpdate = '';
      }   
      
    
      $content.= $this->cObj->substituteMarkerArrayCached($template['list'],$markerArray, $subpartArray,$wrappedSubpartArray);
      
      if ($debug==1) {
				$content = t3lib_div::view_array($debug).$content;
			}
      
  		$objResponse->addScript($jsResultDelete);
  		$objResponse->addAssign('searchFormResult', 'innerHTML', $content);
  		$objResponse->addScript($jsResultUpdate);
  		
  		$objResponse->addAssign('searchFormError', 'innerHTML','');

    // minimum character length not reached
    
    } else {
        $content.= sprintf($this->pi_getLL('searchMinChars'), $this->conf['search.']['minChars']);
        $objResponse->addAssign('searchFormError', 'innerHTML',$content);
    }
       
    
	//	$objResponse->addScript('fdTableSort.init()');
		return $objResponse->getXML();
	}	


	/**
	 * Plugin mode RECORDSONMAP: Presents a list of on the map visible records
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The plugin content
	 */	
  function recordsOnMap () {

    $template["list"] = $this->cObj->getSubpart($this->templateCode,"###TEMPLATE_RECORDSONMAP###");
    $content.= $this->cObj->substituteMarkerArrayCached($template["list"],$markerArray, $subpartArray,$wrappedSubpartArray);
		return $content;
	} // function recordsOnMap
		
		
	/**
	 * Plugin mode SEARCHBOX: Presents a form to search for a location, working with geocoding 
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The plugin content
	 */	
  function showLocationBox ($content, $conf) {	
	
    

    $template["list"] = $this->cObj2->getSubpart($this->templateCode,"###TEMPLATE_LOCATIONBOX###");
    
		$markerArray = $this->getLLMarkers(array(), $this->conf['location.']['LL'], 'location');
    
    $content.= $this->cObj2->substituteMarkerArrayCached($template["list"],$markerArray);
    #echo '<pre>'; print_r($GLOBALS['TSFE']).'</pre>';
     #var_dump($GLOBALS['TSFE']);

		return $content;
	} // function showLocationBox($content, $conf)
	
	
	
	/**
	 * Get specific language markers
	 *
	 * @param	array		$markerArray: the markerarray which will be filled with the language markers
	 * @param	string		$conf: The keys of the language markers
	 * @param	string		$prefix: Prefix which is used in the locallang file
	 * @return the marker array with the language markers
	 */	
	function getLLMarkers($markerArray, $conf, $prefix) {
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
	
	
	
	/*   Main mapview
	**/  	
	function showMap ($content, $conf) {
		$this->init($conf);
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		/**
		 * Instantiate the xajax object and configure it
		 */
		require_once (t3lib_extMgm::extPath('xajax') . 'class.tx_xajax.php');
  
		$this->xajax = t3lib_div::makeInstance('tx_xajax'); // Make the instance
		if ($GLOBALS['TSFE']->metaCharset == 'utf-8') {$this->xajax->decodeUTF8InputOn(); }		// Decode form vars from utf8 
		$this->xajax->setCharEncoding($GLOBALS['TSFE']->metaCharset); 		// Encode of the response to utf-8 ???
		$this->xajax->setWrapperPrefix($this->prefixId); 		// To prevent conflicts, prepend the extension prefix
		$this->xajax->statusMessagesOn(); 		// Do you wnat messages in the status bar?
		#$this->xajax->debugOn();
		$this->xajax->registerFunction(array('infomsg', &$this, 'infomsg'));
		$this->xajax->registerFunction(array('activeRecords', &$this, 'activeRecords'));
		$this->xajax->registerFunction(array('processCat', &$this, 'processCat'));
		$this->xajax->registerFunction(array('processFormData', &$this, 'processFormData'));
		$this->xajax->registerFunction(array('getPoiList', &$this, 'getPoiList'));	
		$this->xajax->registerFunction(array('resultSet', &$this, 'resultSet'));
		$this->xajax->registerFunction(array('tab', &$this, 'tab'));
		$this->xajax->registerFunction(array('search', &$this, 'search'));
		$this->xajax->registerFunction(array('processCatTree', &$this, 'processCatTree'));		
		$this->xajax->registerFunction(array('processSearchInMenu', &$this, 'processSearchInMenu'));
      
  	$this->xajax->processRequests(); 		// Else create javascript and add it to the header output
  	$path = t3lib_extMgm::siteRelpath('rggooglemap');
		$GLOBALS['TSFE']->additionalHeaderData['b121000'] = $this->xajax->getJavascript(t3lib_extMgm::siteRelPath('xajax'));
    #$GLOBALS['TSFE']->additionalHeaderData['121212'] = '<script type="text/javascript" src="typo3conf/ext/rggooglemap/res/simpletreemenu.js"></script>';
    $GLOBALS['TSFE']->additionalHeaderData['b121212'] = '<link rel="stylesheet" type="text/css" href="'.$path.'res/pde.css" />
                                                      <script type="text/javascript" src="'.$path.'res/pde.js"></script>';    
    $GLOBALS['TSFE']->additionalHeaderData['b121211'] = '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$this->config['mapKey'].'" type="text/javascript"></script>';
    $GLOBALS['TSFE']->additionalHeaderData['b121216'] = '<script type="text/javascript" src="'.$path.'res/gxmarker1.js"></script>';
    $GLOBALS['TSFE']->additionalHeaderData['b121217'] = '<script type="text/javascript" src="'.$path.'res/clusterer.js"></script>';
    $GLOBALS['TSFE']->additionalHeaderData['b121223'] = '<script type="text/javascript" src="'.$path.'res/gm.js"></script>';    
    $GLOBALS["TSFE"]->additionalHeaderData["b221213"] = '<script type="text/javascript">'.$this->getJs().'</script>';
    $GLOBALS['TSFE']->additionalHeaderData['b121214'] = '<script type="text/javascript" src="'.$path.'res/popup.js"></script>';
     $GLOBALS['TSFE']->additionalHeaderData['c121214'] = '<script type="text/javascript" src="'.$path.'res/pdmarker.js"></script>';   
 
 #   $GLOBALS['TSFE']->additionalHeaderData['121299'] = '<script type="text/javascript" src="'.$path.'res/largeoverview.js"></script>';
 /* $GLOBALS['TSFE']->additionalHeaderData['124444'] = 
    '					<script type="text/javascript">
						
							var resizeSpeed = 7;	// controls the speed of the image resizing (1=slowest and 10=fastest)	
							var fileLoadingImage = "typo3conf/ext/kj_imagelightbox2/lightbox/images/loading.gif";		
							var fileBottomNavCloseImage = "typo3conf/ext/kj_imagelightbox2/lightbox/images/closelabel.gif";
							var numberDisplayLabelFirst = "Image";
							var numberDisplayLabelLast = "of";
					</script>
				
<link rel="stylesheet" href="typo3conf/ext/kj_imagelightbox2/lightbox/css/lightbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="typo3conf/ext/kj_imagelightbox2/lightbox/js/prototype.js"></script>
<script type="text/javascript" src="typo3conf/ext/kj_imagelightbox2/lightbox/js/scriptaculous.js?load=effects"></script>
<script type="text/javascript" src="typo3conf/ext/kj_imagelightbox2/lightbox/js/lightbox.js"></script>';  */
    $template["list"] = $this->cObj2->getSubpart($this->templateCode,"###MAP###");
    if ($this->config['menu-categorytree'] == 1 ) {
      $template["list"] = $this->cObj2->getSubpart($this->templateCode,"###TEMPLATE_CATMENU_MENU###");
    }
    
  	// title, text - markers
    $markerArray['###CAT_MENU###'] = $this->displayCatMenu(0);
    $markerArray['###CAT_LIST###'] = ($this->config['categoriesActive']!='') ? $this->config['categoriesActive'] : '9999';
    $markerArray['###MAP_WIDTH###'] = $this->config['mapWidth'];
    $markerArray['###MAP_HEIGHT###'] = $this->config['mapHeight'];
        
    $content.= $this->cObj2->substituteMarkerArrayCached($template["list"],$markerArray, $subpartArray,$wrappedSubpartArray);
		return $content;
	} // function main($content, $conf)


	/**
	 * Load the info message popup window
	 *
	 * @param	string	$uid: id of reocord
	 * @param	string	$table: table of record 
	 * @param int     $prefix: Prefix for tabs in info window	 
	 * @return	The content of the info window
	 */
  function infomsg($uid, $table,$tmplPrefix=1)	{
 
    $template["infobox"] = $this->cObj->getSubpart($this->templateCode,"###TEMPLATE_INFOBOX_$tmplPrefix###");
 
    // query for single record
    $field = '*';
    $where = 'uid = '.intval($uid);


    

    //x $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$limit='');
    $res = $this->generic->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$limit='');
    $row=array_shift($res);
    
    
    $tmp = $this->getMarker($row,'popup.');
    $markerArray = $tmp['markerArray'];
    $wrappedSubpartArray = $tmp['wrappedSubpartArray']; 

    // query for categories of a single record
    if ($row['rggmcat']) {
      $template["item"] = $this->cObj->getSubpart( $template["infobox"],"###SINGLE###");
      $field = '*';
      $where = 'uid IN ('.$row['rggmcat'].')';
      $table = 'tx_rggooglemap_cat';
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy='',$limit='');
  		if ($res) {
        while($row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
          foreach ($row2 as $key=>$value) {
          	$markerArray['###CAT_'.strtoupper($key).'###'] = $row2[$key];
          }   
    			$content_item .= $this->cObj->substituteMarkerArrayCached($template["item"],$markerArray, array(), $wrappedSubpartArray);
    		}
  		} 
  	} else {
      $content_item = '';
    }
		$subpartArray["###CONTENT###"] = $content_item;
		
    $content.= $this->cObj->substituteMarkerArrayCached($template["infobox"],$markerArray, $subpartArray,$wrappedSubpartArray);

		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		$objResponse->addAssign('infobox', 'innerHTML', $content);

		return $objResponse->getXML();
	} 
	
	/**
	 * Creates the "Show record 1 to 9 of 9"
	 *
	 * @param	string	$offset: offset value
	 * @param	string	$table: table of query 
	 * @param	string	$field: $field of query
	 * @param	string	$where: $where of query
	 * @return	Array with information for the page browser
	 */  
  function pageBrowserStatistic($offset=0, $table, $field, $where) {
    $records=$this->generic->exec_COUNTquery($table,$where);   
    $pages=ceil($records/$this->conf['recordsPerPage']);   
        
    $max = ($this->conf['recordsPerPage']>= $records) ? $records :  ($offset*$this->conf['recordsPerPage']+$this->conf['recordsPerPage']);

     $content['text'] =  	sprintf(
      $this->pi_getLL('pagebrowser'),
			$offset*$this->conf['recordsPerPage']+1, 
			$max,
			$records
		);
		
		$content['pages'] = $pages;
		$content['offset'] = $offset;
  
    return $content;
  }
	
	/**
	 * Creates the result records for the first page
	 *
	 * @param	array	$data: selected checboxes
	 * @return	Result records including the pagebrowser for the 1st result page
	 */  
	function processCat($data)	{
		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
    
		if (is_Array($data['cb'])){
      $objResponse->addAssign('mapcatlist', 'innerHTML',implode(',',$data['cb']));
    } else {
      $objResponse->addAssign('mapcatlist', 'innerHTML','9999');
    }

		if ($this->config['loadDynamicList'] != 1) {
			return $objResponse->getXML();
		}
	
		$markerArray=Array();
		$where = '';
		$field='';$table='';$where;$orderBy='';
		

		// save selected categories into session
		$GLOBALS["TSFE"]->fe_user->setKey('ses', 'data2',  $data['cb']);
		$GLOBALS['TSFE']->fe_user->storeSessionData();

		// if at least one checkbox is activated
    if (count($data['cb']) > 0 || $data =='default') {
      if($data!='default') {
        $test = implode(',',$data['cb']);
        foreach ($data['cb'] as $key=>$value) {
          $where2.= ' FIND_IN_SET('.$key.',rggmcat) OR';
        }
        $where2 = ' AND ( '.substr($where2,0,-3).' ) ';
      }
      
      #$where2 = "AND  rggmcat REGEXP   '(,|^)<$test>(,|$)' ";
      
      // template
		  $template["resultSet"] = $this->cObj2->getSubpart($this->templateCode,"###TEMPLATE_RECORDLIST_FIRST###");
      $template["item"] = $this->cObj2->getSubpart( $template["resultSet"],"###SINGLE###");
         
  		// db query
      $i = 0;
      $table = $this->config['tables']; 
      $field = '*';
      $where = '1=1 '.$this->config['pid_list'].' AND hidden= 0 AND deleted = 0  ' ;
    
		  $where.=' AND lng!=0 AND lat !=0  '.$where2;
  		$GLOBALS["TSFE"]->fe_user->setKey('ses', 'where',  $where);
  		$GLOBALS['TSFE']->fe_user->storeSessionData();

      $res = $this->generic->exec_SELECTquery($field,$table,$where,$groupBy,$orderBy,'0,'.$this->conf['recordsPerPage']);
      while($row=array_shift($res)) {
        $x++;
        $tmp = $this->getMarker($row,'recordlist.');
        $markerArray = $tmp['markerArray'];
        
        $markerArray['###ZEBRA###'] = ($i%2==0) ? '' : 'alt';
        $i++;

        $wrappedSubpartArray = $tmp['wrappedSubpartArray'];         
  			$content_item .= $this->cObj2->substituteMarkerArrayCached($template["item"],$markerArray, array(), $wrappedSubpartArray);
      }    
      $subpartArray["###CONTENT###"] = $content_item;
      
      // initalize pagebrowser
      $pagebrowser = $this->pageBrowserStatistic($offset, $table, $field, $where);
		  $text = $pagebrowser['text'];
		  $pages = $pagebrowser['pages'];
		  $offset = $pagebrowser['offset'];

      // Pagebrower statistic
      $markerArray['###PB_STATISTIC###'] = $pagebrowser['text'];     

  		// next link
      if ($offset +1 < $pages) {
  		  $new = $offset+1;
  		  $pb = ' onClick="'.$this->prefixId.'resultSet('.$new.')" ';
        /*$markerArray['###PB_NEXT###'] = 	sprintf(
          $this->pi_getLL('pagebrowser_next'),
          $pb,
          $offset+2
        );*/
        $wrappedSubpartArray['###PB_NEXT###'] = explode('|', '<a href="javascript:void(0);"'.$pb.'>|</a>');
      } else {
        $wrappedSubpartArray['###PB_NEXT###'] = '';
      } 
         
      $content.= $this->cObj2->substituteMarkerArrayCached($template["resultSet"],$markerArray, $subpartArray,$wrappedSubpartArray);
      
      $objResponse->addAssign('formResult', 'innerHTML',$content);
            
    } // checkboxes selected
    




		return $objResponse->getXML();
  } 
  
	/**
	 * Modifies the menu output with including the cateory selection
	 *
	 * @param	array	$data: selected checboxes
	 * @return	Result records 
	 */  
	function processCatTree($data)	{
	  $content.= $this->showMenu('', $this->conf, $data['cb']);  
 
		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		$objResponse->addAssign('rggooglemap-menu', 'innerHTML',$content);
		
		return $objResponse->getXML();
  }   

	/**
	 * Modifies the menu output with including the search box
	 *
	 * @param	array	$data: selected uids
	 * @return	where clause for search
	 */  
	function processSearchInMenu ($data)	{
	
	  $searchExpression = $data['rggmsearchValue'];

    // minimum characters needed, default = 3
    if (strlen($searchExpression) >= $this->conf['search.']['minChars']) {
      // escaping the search-value
      $delete = array("'", "\"", "\\", "/", "");
      $searchExpression = trim(str_replace($delete, "", $searchExpression));
  
      // query for the search
      $searchField = explode(',',$this->conf['search.']['tt_address']);
      foreach ($searchField as $key=>$value) {
        $where2.= " $value LIKE '%$searchExpression%' OR";
      }
      $where = ' AND ( '.substr($where2,0,-3).' ) ';
      
      // search only within the map area
      if ($data['rggmOnMap']=='on') {
          $areaArr=split("%2C%20",$searchForm['rggmBound']);
          $where.= 'AND tx_rggooglemap_lng between '.$areaArr[1].' AND '.$areaArr[3].'
                    AND	tx_rggooglemap_lat between '.$areaArr[0].' AND '.$areaArr[2];
      }
    }
    
    // Adds hook for processing of extra search expressions
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraSearchHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraSearchHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$where = $_procObj->extraSearchProcessor($where, $data, $this->config, $this);
			}
		}

#	$where.=t3lib_div::view_array($data);
	  $content.= $this->showMenu('', $this->conf, '', $where);  

		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		$objResponse->addAssign('rggm-menu', 'innerHTML',$content);
		return $objResponse->getXML();
  } 
  
	/**
	 * Creates the result records from 2nd page to last page
	 *
	 * @param	string	$offset: offset value
	 * @return	Result records including the pagebrowser
	 */  
  function resultSet($var) {
		$offset = intval($var);
		
		// template
		$template["resultSet"] = $this->cObj->getSubpart($this->templateCode,"###TEMPLATE_RECORDLIST###");
    $template["item"] = $this->cObj->getSubpart( $template["resultSet"],"###SINGLE###");
    		
    // pagebrowser (prev <> next)
    $table = $this->config['tables'];
    $field = '*';
    $where = $GLOBALS["TSFE"]->fe_user->getKey('ses','where');
    $pagebrowser = $this->pageBrowserStatistic($offset, $table, $field, $where);
		$offset = $pagebrowser['offset'];  
		$pages = $pagebrowser['pages'];
    $begin = intval($var)*$this->conf['recordsPerPage'];    

 
    // query for the results
    $limit= $begin.','.$this->conf['recordsPerPage'];
    $res = $this->generic->exec_SELECTquery($field,$table,$where,$groupBy,$orderBy,$limit);
    $i=0;
    while($row=array_shift($res)) {    
      foreach ($row as $key=>$value) {
      	#$markerArray['###'.strtoupper($key).'###'] = $this->cObj->stdWrap($value,$this->conf['recordlist.'][$key.'.']);
      } 
      $tmp =  $this->getMarker($row,'recordlist.');
      $markerArray = $tmp['markerArray'];
      $wrappedSubpartArray = $tmp['wrappedSubpartArray'];       
        // odd/even
        $markerArray['###ZEBRA###'] = ($i%2==0) ? '' : 'alt';
        $i++;
      
			$content_item .= $this->cObj->substituteMarkerArrayCached($template["item"],$markerArray, array(), $wrappedSubpartArray);

    }


    // Pagebrower statistic
    $markerArray['###PB_STATISTIC###'] = $pagebrowser['text'];
    
    
    // actual page
    $markerArray['###PB_ACT###'] = sprintf(
        $this->pi_getLL('pagebrowser_act'),
        $offset+1
    );

    // previous link
		if ($offset > 0) {
      $pb = ' onClick="'.$this->prefixId.'resultSet('.($offset-1).')" ';
      /*$markerArray['###PB_PREV###'] = 	sprintf(
        $this->pi_getLL('pagebrowser_prev'),
        $pb,
        $offset
      );*/
      $wrappedSubpartArray['###PB_PREV###'] = explode('|', '<a href="javascript:void(0);"'.$pb.'>|</a>');
    } else {
      $subpartArray['###PB_PREV###'] = '';
    }
    
		// next link
    if ($offset +1 < $pages) {
		  $new = $offset+1;
		  $pb = ' onClick="'.$this->prefixId.'resultSet('.$new.')" ';
      /*$markerArray['###PB_NEXT###'] = 	sprintf(
        $this->pi_getLL('pagebrowser_next'),
        $pb,
        $offset+2
      );*/
      $wrappedSubpartArray['###PB_NEXT###'] = explode('|', '<a href="javascript:void(0);"'.$pb.'>|</a>');
    } else {
      $subpartArray['###PB_NEXT###'] = '';
    } 
    
    $subpartArray["###CONTENT###"] = $content_item;
    $content.= $this->cObj->substituteMarkerArrayCached($template["resultSet"],$markerArray, $subpartArray,$wrappedSubpartArray);

    $objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);

    $objResponse->addAssign('resultdiv', 'innerHTML', $content);
    //$objResponse->addScript('setTimeout("fdTableSort.init()", 1000);');    
    
    return $objResponse->getXML();
  }
  
  
	/**
	 * Creates the categorymenu
	 *
	 * @param	int	$id: parent_id for the recursive function
	 * @return	categorymenu with parent_id = $id
	 */  
	function displayCatMenu($id=0) {
		// template
    if ($this->config['menu-categorytree'] == 0) {
      $template["total"] = $this->cObj2->getSubpart($this->templateCode,"###TEMPLATE_CATMENU###");
    } else {
      $template["total"] = $this->cObj2->getSubpart($this->templateCode,"###TEMPLATE_CATMENU_TREE###");
    }
    
    $table = 'tx_rggooglemap_cat';
    $field = '*';
    $where = 'hidden= 0 AND deleted = 0 AND parent_uid = '.$id;
    $where.= ($this->config['categories']!='') ? ' AND uid IN('.$this->config['categories'].')' : '';
 
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$limit='');
    
    // actived checkbox for selected category
    $checkedBox = explode(',',$this->config['categoriesActive']);
       
    if ($res) {
      $i = 0;
      
      $first = ($id == 0) ? '<ul id="treemenu1" class="pde">' : '<ul >';
      while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        $i++;
        // Category Image > TS catIcon
        $imgTSConfig = $this->conf['catIcon.'];
        $imgTSConfig['file'] = 'uploads/tx_rggooglemap/'.$row['image'];
         
        $tmp = $this->getMarker($row,'cattree.');
        $markerArray = $tmp['markerArray'];
        $wrappedSubpartArray = $tmp['wrappedSubpartArray']; 
                
        $markerArray['###CHECKED###'] = (in_array($row['uid'],$checkedBox)) ? ' checked ="checked" ' : '';
        $markerArray['###ICON###'] = $this->cObj2->IMAGE($imgTSConfig);
        $markerArray['###RECURSIVE###'] = $this->displayCatMenu($row['uid']);
        if ($markerArray['###RECURSIVE###'] != '') {
          $template["total"] = $this->cObj2->getSubpart($this->templateCode,"###TEMPLATE_CATMENU_NOCHECKBOX###");
        }

        $record.= $this->cObj2->substituteMarkerArrayCached($template["total"],$markerArray, $subpartArray,$wrappedSubpartArray); 
      }
      
      $last= '</ul>';
      if ($i > 0) {
      	$out.= $first.$record.$last;
      }
    }     
    $content.= $out;
      
		return $content;
	}
	
	
	/**
	 * Creates the javascript which needs to be build dynamically
	 *
	 * @return	all the js
	 */ 	
	function getJs () {  
    $postvars = t3lib_div::GPvar('tx_rggooglemap_pi1');
    
    
      // some settings for controlling
      $settings = 'map.setMapType('.$this->config['mapType'].');';
      if ($this->config['mapNavControl'] == 'large') $settings .= 'map.addControl(new GLargeMapControl());'; 
      if ($this->config['mapNavControl'] == 'small') $settings .= 'map.addControl(new GSmallMapControl());';
      if ($this->config['mapTypeControl'] == 'show') $settings .= 'map.addControl(new GMapTypeControl());';
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
      if ($this->conf['enableDoubleClickZoom']== 1) $settings .= 'map.enableDoubleClickZoom();';
      if ($this->conf['enableContinuousZoom']== 1) $settings .= 'map.enableContinuousZoom();';
      	
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
      

    
    // Adds hook for processing of extra javascript
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraGetJsHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraGetJsHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$out = $_procObj->extraGetJsProcessor($out, $data, $this->config, $this);
			}
		}
		
		$markerArray['###HIDECONTROLSMOUSEOUT###'] = $hideControlsOnMouseOut;
		$markerArray['###POI_ON_START###'] = $this->showPOIonStart();
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
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('image', 'tx_rggooglemap_cat', 'hidden=0 AND deleted=0');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$iconSize = getimagesize('uploads/tx_rggooglemap/'.$row['image']);
					
			$key = 'gicons["'.$row['image'].'"]';
			$gicon .= $key.'= new GIcon(baseIcon);';
			$gicon .= $key.'.image = "'.$urlForIcons.$row['image'].'";';
			$gicon .= $key.'.iconSize = new GSize('.$iconSize[0].', '.$iconSize[0].');';
			$gicon .= $key.'.infoWindowAncho = new GPoint('.($iconSize[0]/2).', '.($iconSize[0]/2).');';
		}
		
		$markerArray['###GICONS###'] = $gicon;

		
		
		$jsTemplateCode = $this->cObj2->fileResource($this->conf['templateFileJS']);
    $template['all'] = $this->cObj2->getSubpart($jsTemplateCode,'###ALL###');
    
		$out.= $this->cObj2->substituteMarkerArrayCached($template['all'],$markerArray);

    
    return $out;
  }


	function showPOIonStart() {
    $showPOIonStart = '';
    $defaultPOI = ($this->piVars['poi']!='') ? $this->piVars['poi'] : $this->config['mapShowOnDefault'];
  	
  	if ($defaultPOI!='') {
			// split it up to get a possible table
			$split = explode('-', $defaultPOI);
			
			if (count($split)==1) {
				$table	= 'tt_address';
				$uid		= $split[0]; 
			} else {
				$table	= $split[0]; 
				$uid		= $split[1]; 
			}
			
			$uid = intval($uid);
			
			if ($uid > 0) {
				$where = 'lng!="" AND lat!= "" AND hidden= 0 AND deleted = 0 AND uid = '.$uid;
				$res = $this->generic->exec_SELECTquery('uid, lng, lat',$table,$where,$groupBy,$orderBy,$offset);
				$row=array_shift($res);
				
				if ($row['lng']!='' && $row['lat']) {
					$showPOIonStart = 'myclick('.$row['uid'].','.$row['lng'].','.$row['lat'].',"'.$table.'");';
				}
			}
			
		}
		return $showPOIonStart;
	}
  
  
	/**
	 * Shows the content of a POI bubble
	 *
	 * @param	int		$var: the id of the record
	 * @param	int		$tan: the id of the tab which should get filled (every tab has got an own template)	 
	 * @param	string		$tbl: the type of table
   * @return the content
	 */  
  function poiContent($var,$tab,$tbl) {
    $tab = intval($tab);

    // query for single record
    $field = '*';
    $table = ($tbl=='undefined'|| $tbl=='') ? 'tt_address' : $tbl;
    $table = $tbl;
    $where = 'uid = '.intval($var);
    //x $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$limit='');
    //x $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    $res = $this->generic->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$offset='');
    $row=array_shift($res);

    
    $tmp = $this->getMarker($row, 'poi.');
    $markerArray = $tmp['markerArray'];
    $wrappedSubpartArray = $tmp['wrappedSubpartArray'];
    #		$this->templateCode = $this->cObj->fileResource('EXT:rggooglemap/template.html');
    $tablePrefix = ($table!='tt_address') ? '_'.strtoupper($table) : '';

    #$content.= '###TEMPLATE_INFOPOI'.$tablePrefix.$markerArray['###TABPREFIX###'].'_'.$tab.'###'; #debug 
    $template["infopoi"] = $this->cObj2->getSubpart($this->templateCode,'###TEMPLATE_INFOPOI'.$tablePrefix.$markerArray['###TABPREFIX###'].'_'.$tab.'###'); 
    $markerArray['###TABLE###'] = $table;
    #$content.= '###TEMPLATE_INFOPOI'.$tablePrefix.$markerArray['###TABPREFIX###'].'_'.$tab.'###';
        
    $content.= $this->cObj2->substituteMarkerArrayCached($template["infopoi"],$markerArray, $subpartArray,$wrappedSubpartArray);
    return $content;
  }
  
	/**
	 * Fills the markerArray with all fields and special detection for link- & imageFields
	 *
	 * @param	Array		$row: row of the db query
	 * @param	string	$prefix: prefix needed for the stdwrap functions
	 * @return the content
	 */    
  function getMarker($row,$prefix) {
    
    // language setting
    if ($GLOBALS['TSFE']->sys_language_content) {
      $OLmode = ($this->sys_language_mode == 'strict'?'hideNonTranslated':'');
      $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($row['table'], $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
    }
  

    // general handling 
    $short = $this->conf[$prefix][$row['table'].'.'];
    foreach ($row as $key=>$value) {
      $this->cObj2->data[$key]=$value; // thanks tobi
    	$markerArray['###'.strtoupper($key).'###'] = $this->cObj2->stdWrap($value,$short[$key.'.']);
    }
    
    $markerArray['###POPUP###'] = ' onClick=\' show("infobox"); ' . $this->prefixId . 'infomsg('.$row['uid'].', "'.$row['table'].'"); \'  ';
    $markerArray['###PREFIX###'] = $prefix;    

		// *new 
		$short = $this->conf[$prefix][$row['table'].'.']['generic.'];
		if (is_array($short)) {
			foreach($short as $key=>$value) {
				$key2 = trim($key, '.');
				$markerArray['###GENERIC_'.strtoupper($key2).'###'] = $this->cObj2->cObjGetSingle($short[$key2] , $short[$key] );
			}
		}	
    
		// Adds hook for processing of extra item markers
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraItemMarkerHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rggooglemap']['extraItemMarkerHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$markerArray = $_procObj->extraItemMarkerProcessor($markerArray, $row, $this->config, $this);
			}
		}
		

		$tmp = Array();
		$tmp['markerArray'] = $markerArray;
		$tmp['wrappedSubpartArray'] = $wrappedSubpartArray;
		


    
    return $tmp;
  }
  
  

	/**
	 * Ajax call for the function poiContent
	 *
	 * @param	int		$var: the id of the record
	 * @param	int		$tan: the id of the tab which should get filled (every tab has got an own template)	 
	 * @return the content
	 */  
	function tab($id,$tab,$table)	{
	 # $table = ($table=='' || !$table ||strlen($table)) ? 'tt_address' : $table;
		$content = $this->poiContent($id,$tab, $table);
		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		$objResponse->addAssign('poi', 'innerHTML', $content);
		return $objResponse->getXML();
	}
	

	/**
	 * Shows all records which are visible on the map (not all which are available through selected categories!)
	 *
	 * @param	string		$area: the area of the map
	 * @return all available (=visible) records
	 */
	function activeRecords($area, $cat)	{	
    // template
    $template['allrecords'] = $this->cObj2->getSubpart($this->templateCode,'###TEMPLATE_ACTIVERECORDS###');
    $template["item"] = $this->cObj2->getSubpart( $template["allrecords"],"###SINGLE###");

    // build the query
    $areaArr=split("%2C%20",$area);
    $where = 'hidden= 0 AND deleted = 0 '.$this->config['pid_list'].'  
              AND lng between '.$areaArr[1].' AND '.$areaArr[3].'
              AND	lat between '.$areaArr[0].' AND '.$areaArr[2];

    

    if($cat==9999) {
    	$where .= ' AND 1=2 ';
    } else {
    	$catList = explode(',',$cat);
			foreach ($catList as $key=>$value) {
	      $where2.= ' FIND_IN_SET('.$value.',rggmcat) OR';
	    }
	    $where .= ' AND ( '.substr($where2,0,-3).' ) ';
	  }
  
		
		$table = $this->config['tables'];
    $field = '*';
       
    // query
    $res = $this->generic->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$offset='');
    while($row=array_shift($res)) {
    
      foreach ($row as $key=>$value) {
      	$markerArray['###'.strtoupper($key).'###'] = $this->cObj->stdWrap($value,$this->conf['activeRecords.'][$key.'.']);
      } 

			$content_item .= $this->cObj->substituteMarkerArrayCached($template["item"],$markerArray, array(), $wrappedSubpartArray);

    }
    $subpartArray["###CONTENT###"] = $content_item;

    $content.= $this->cObj2->substituteMarkerArrayCached($template['allrecords'],$markerArray, $subpartArray,$wrappedSubpartArray);

		$objResponse = new tx_xajax_response($GLOBALS['TSFE']->metaCharset);
		$objResponse->addAssign('rggooglemap-recordsonmap', 'innerHTML',$content);
		return $objResponse->getXML();
	}
	
  /*
  * **********************************
  *  ********** X M L **************
  * **********************************  
  **/    
  function xmlFunc($content,$conf)	{		  
    $this->init($conf);
  	$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin

    $postvars = t3lib_div::GPvar('tx_rggooglemap_pi1');

    if ($postvars['detail']!='') {
      return $this->poiContent($postvars['detail'],1,$postvars['table']);
      // debug
      $content='<div style="width:120px">Marker #'.$postvars['detail'].'<br />'.time().'<hr />'.t3lib_div::view_array($this->conf['poi.']).'</div>';
      return $content;
    } else {
    
     // categories
     $cat = $postvars['cat'];
     if ($cat) { // cat selected
        if ($cat!=9999) { // nothing selected
          $catList = explode(',',$cat);        
        }
     } else { // nothing selected means 1st call!!
        $catList =  explode(',',$this->config['categoriesActive']);
     }

      // category image
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
          $catImg[$row['uid']] = $row2['image'];
        } else {
          $catImg[$row['uid']] = $row['image'];
        }
      }

      $this->renderHeader();

      if ($catList) {
        $table =  $this->config['tables'];
      	$field = '*';
      	$where = 'hidden= 0 AND deleted = 0 '.$this->config['pid_list'].' AND lng!=0 AND lat!= 0 AND lng!=\'\' AND lat!=\'\' ';
  
      	if (strlen($postvars['area'])>5) {
          $areaArr=array();
         	$areaArr=split(", ",$postvars['area']);
    	  	$where.= ' AND lng between '.$areaArr[1].' AND '.$areaArr[3].'
    				        AND	lat between '.$areaArr[0].' AND '.$areaArr[2];
    				        
        }      

        // category selection
  
  
  #      $catList = ($cat!=0) ? explode(',',$cat) : explode(',',$this->config['categoriesActive']);

        $catTmp = false;
        foreach ($catList as $key=>$value) {
          if ($value) {
            $catTmp=true;
            $where2.= ' FIND_IN_SET('.$value.',rggmcat) OR';}
        }
        $where .= ($catTmp) ? ' AND ( '.substr($where2,0,-3).' ) ' : '';
        
        
        
          $limit = '';
  
          if ($this->conf['extraquery']==1) {
            $extraquery = ($GLOBALS["TSFE"]->fe_user->getKey('ses','rggmttnews2'));
            if ($extraquery!= '') {
              $where.= ' AND uid IN ('.$extraquery.') ';
            }
          }
  
    		
    					        
  //x2      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$limit);
  //x2       while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
  
  

          #$limit = '0,100';
          $res = $this->generic->exec_SELECTquery($field,$table,$where,$groupBy,$orderBy,$limit);
          while($row=array_shift($res)) {
    
          $catDivider = strpos($row['rggmcat'],',');
          if ($catDivider == false) {
            if (!$catImg[$row['rggmcat']]) {
              if (!file_exists('uploads/tx_rggooglemap/dot.png')) {
                copy($this->conf['defaultPOIIcon'],'uploads/tx_rggooglemap/dot.png');
              }
              $catImg[$row['rggmcat']] = 'dot.png';
            
            }          
          	$img = $catImg[$row['rggmcat']];
          	#
          } else {
            $firstCat = substr($row['rggmcat'],0,$catDivider);
            $img = $catImg[$firstCat];
          }
          
          $test = '';
  
        
  #          $conf['recursive'] = 2;

          $this->addRecord($table, $row,$conf, $img,$test);
        }
      }
      $this->renderFooter();       
  
  		$result = $this->getResult();
  		
  	#	$result = json_encode($result);
  		
  		return $result;
    }
  }



	/**
	 * adds a single record to the xml file 
	 *
	 * @param	array		$row: all fields of one record
	 * @param	array		$conf: The PlugIn configuration
	 * @return single line @ xml
	 */
  function addRecord($table, $row,$conf, $img,$test) {  

    // language setting
    if ($GLOBALS['TSFE']->sys_language_content) {
    
      $OLmode = ($this->sys_language_mode == 'strict'?'hideNonTranslated':'');
      $row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($table, $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
    }
      
    $this->lines[]=$this->Icode.'<marker cat="'.$row['rggmcat'].'"  uid="'.$row["uid"].'" lng="'.$row['lng'].'" lat="'.$row['lat'].'"  img="'.$img.'" table="'.$row['table'].'" test="'.$test.'" >';

    $this->indent(1);
    $this->getRowInXML($row,$conf);
    $this->indent(0);
    $this->lines[]=$this->Icode.'</marker>';
  }
 
	/**
	 *  inserts the element/node "html" for every record => the content of every POI 
	 *
	 * @param	array		$row: all fields of one record
	 * @param	array		$conf: The PlugIn configuration
	 * @return element "html" @ xml
	 */
  function getRowInXML($row,$conf) {
  	$table = $row['table'];
    $title = $this->cObj2->stdWrap($row[$this->conf['title.'][$table]], $this->conf['title.'][$table.'.']);
    $content = '<![CDATA[ '.$title.' ]]>';    
    $this->lines[]=$this->Icode.$this->fieldWrap('t',(($content)));  
  }
  
	/**
	 *  filling the marker of the template with values of the records. Image processing and so on
	 *
	 * @param	array		$row: all fields of one record
	 * @param	array		$conf: The PlugIn configuration
	 * @return element "html" @ xml
	 */  


  // following functions see class.t3lib_xml.php.
  // http://typo3.org/fileadmin/typo3api-4.0.0/de/d97/class_8t3lib__xml_8php-source.html     
  function newLevel($name,$beginEndFlag=0,$params=array())        {
          if ($beginEndFlag)      {
                  $pList='';
                  if (count($params))     {
                          $par=array();
                          reset($params);
                          while(list($key,$val)=each($params))    {
                                  $par[]=$key.'="'.htmlspecialchars($val).'"';
                          }
                          $pList=' '.implode(' ',$par);
                  }
                  $this->lines[]=$this->Icode.'<'.$name.$pList.'>';
                  $this->indent(1);
          } else {
                  $this->indent(0);
                  $this->lines[]=$this->Icode.'</'.$name.'>';
          }
  }

  function getResult()    {
         $content = implode(chr(10),$this->lines);
         return $this->output($content);
  }
  
  function output($content)       {
          if ($this->XMLdebug)    {
                  return '<pre>'.htmlspecialchars($content).'</pre>
                  <hr /><font color="red">Size: '.strlen($content).'</font>';
          } else {
                  return $content;
          }
  }
  
  function indent($b)     {
          if ($b) $this->XMLIndent++; else $this->XMLIndent--;
          $this->Icode='';
          for ($i=0;$i<$this->XMLIndent;$i++)     {
                  $this->Icode.=chr(9);
          }
          return $this->Icode;
  }
  function utf8($content) {
          return utf8_encode($content);
  }
  
  
  function fieldWrap($field,$value)       {
          return '<'.$field.'>'.$value.'</'.$field.'>';
  }
  
  // just returns the top level name 
  function topLevelName() {
         return 'markers';
  }
  
  // rendering header
  function renderHeader() {
        $this->newLevel($this->topLevelName(),1);
  }
  // rendering footer
  function renderFooter() {
          $this->newLevel($this->topLevelName(),0);
  } 

	/**
	 * Get the value out of the flexforms and if empty, take if from TS
	 *
	 * @param	string		$sheet: The sheed of the flexforms
	 * @param	string		$key: the name of the flexform field
	 * @param	string		$confOverride: The value of TS for an override
	 * @return	string	The value of the locallang.xml
	 */
	function getFlexform ($sheet, $key, $confOverride='') {
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


} // class tx_rggooglemap



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/pi1/class.tx_rggooglemap_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/pi1/class.tx_rggooglemap_pi1.php']);
}

?>
