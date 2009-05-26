<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Georg Ringer <typo3@ringerge.org>
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
 * Module 'Google-Map' for the 'rggooglemap' extension.
 *
 * @author	Georg Ringer <typo3@ringerge.org>
 */



	// DEFAULT initialization of a module [BEGIN]
if (!$GLOBALS['SOBE'])	{
	unset($MCONF);
	require_once("conf.php");
	require_once($BACK_PATH."init.php");
	require_once($BACK_PATH."template.php");
	$LANG->includeLLFile("EXT:rggooglemap/locallang.xml");
	require_once (PATH_t3lib."class.t3lib_scbase.php");
	require_once(PATH_t3lib.'class.t3lib_stdgraphic.php');
	$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
		// DEFAULT initialization of a module [END]

		#ajax
	require_once(t3lib_extMgm::extPath("xajax")."class.tx_xajax.php");
}

class tx_rggooglemap_module1 extends t3lib_SCbase {
	var $pageinfo;

	/**
	 * Initializes the Module
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		parent::init();

    /**
     *  Instantiate the tx_xajax object
     */
    $this->xajax = new tx_xajax();
 #   $this->xajax->debugOn ();
    /**
     *  Register the names of the PHP functions you want to be able to call through xajax
     *
     *  $xajax->registerFunction(array('functionNameInJavascript',&$object,'methodName'));
     */
    $this->xajax->registerFunction(array("getPoi",&$this,"xajaxGetPoi"));
	  $this->xajax->registerFunction(array("insertPoi",&$this,"xajaxInsertPoi"));
	  $this->xajax->registerFunction(array("listRecords",&$this,"xajaxListRecords"));
	  $this->xajax->registerFunction(array("autocomplete",&$this,"xajaxAutoComplete"));
	  $this->xajax->registerFunction(array("autoload",&$this,"xajaxAutoLoad"));
  }

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			"function" => Array (
				"1" => $LANG->getLL("function1"),
				"2" => $LANG->getLL("function2"),
				"3" => $LANG->getLL("function3"),
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return	[type]		...
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

    $this->google=t3lib_div::_GP('google');
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

		if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id) )	{

				// Draw the header.
			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;
	#		$this->doc->form='<form action="" method="POST">';
			
			$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);
			
			// get the key if multidomain is used
			if (trim($this->confArr['googleKey2']) != '') {
				$keyListTmp = explode('#####', $this->confArr['googleKey2']);
				$keyList = array();
				foreach($keyListTmp as $key) {
					$split = explode('=', $key);
					$keyList[$split[0]] = $split[1];
				}

				$currentDomain = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
				if ($keyList[$currentDomain] != '') {
					$this->confArr['googleKey'] = $keyList[$currentDomain];
				}
			}
			
			// some settings from Extension Manager (map control, ..)
			$settings = '';
      if ($this->confArr['mapNavigation'] == 'large') {
        $settings .= 'map.addControl(new GLargeMapControl());';
      } else {
        $settings .= 'map.addControl(new GSmallMapControl());';
      }
			if ($this->confArr['mapType'] == 'on') $settings .= 'map.addControl(new GMapTypeControl());';
			if ($this->confArr['mapOverview'] == 'on') $settings .= 'map.addControl(new GOverviewMapControl());';
			
			// onload just for map view
			if ((string)$this->MOD_SETTINGS["function"] == 1) $onload = 'window.onload = load;';
			else $onload = '';
			$onload = 'window.onload = load;';

				// JavaScript
			$this->doc->JScode = $this->genJScode($settings, $onload);
			$this->doc->postCode = $this->genPostJScode();
			
			$headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br />".$LANG->sL("LLL:EXT:lang/locallang_core.xml:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section("",$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,"SET[function]",$this->MOD_SETTINGS["function"],$this->MOD_MENU["function"])));
			$this->content.=$this->doc->divider(5);

  	  
      // include generic table access
      require_once(t3lib_extMgm::extPath('rggooglemap').'lib/class.tx_rggooglemap_table.php');
  	  $this->generic = t3lib_div::makeInstance('tx_rggooglemap_table');


			// Render content:
			switch((string)$this->MOD_SETTINGS["function"])	{
  			case 1: 
  				$this->content.=$this->viewMap();  
  			break;
  			case 2:
  				if(t3lib_div::_GP('updateGeoData')){
						$this->updateGeoData(t3lib_div::_GP('updateGeoData'));
	  			}
  				if(t3lib_div::_GP('updateGeoData2')){
						$this->updateGeoData2(t3lib_div::_GP('updateGeoData2'));
	  			}	  			
	  			$this->content.=$this->viewRecordList();

  			break;
  			case 3:
  				$this->content.=$this->viewMassGeocoding();
  			break;
  		}

		} else {
				// If no access or if ID == zero

			$this->doc = t3lib_div::makeInstance("mediumDoc");
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL("title"));
			$this->content.=$this->doc->header($LANG->getLL("title"));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
			$this->content.='<div style="color:red;text-align:center;font-weight:bold;">'.$LANG->getLL("noPageChosen").'</div>';
			$this->content.=$this->doc->spacer(10);
		}

	}
	function genJScode($settings, $onload, $scripttags = true)	{
			// get the key if multidomain is used
			if (trim($this->confArr['googleKey2']) != '') {
				$keyListTmp = explode('#####', $this->confArr['googleKey2']);
				$keyList = array();
				foreach($keyListTmp as $key) {
					$split = explode('=', $key);
					$keyList[$split[0]] = $split[1];
				}

				$currentDomain = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
				if ($keyList[$currentDomain] != '') {
					$this->confArr['googleKey'] = $keyList[$currentDomain];
				}
			}	
		$content = chr(10).($scripttags?'<script language="javascript" type="text/javascript">':'').'
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				</script>
				<script src="http://maps.google.com/maps?file=api&amp;v=2.58&amp;key='.$this->confArr['googleKey'].'" type="text/javascript"></script>
    <script type="text/javascript">
    //<![CDATA[
    
    var map = null;
    var geocoder = null;

    function load() {
    	doload("map");
    }

    function doload(mapname) {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
      	var lat = document.getElementById("centerlatitude");
      	var lng  = document.getElementById("centerlongitude");
        var center = new GLatLng(lat.value, lng.value);
        map.setCenter(center, '.$this->confArr['startZoom'].');
        geocoder = new GClientGeocoder();
        
        '.$settings.'
        var marker = new GMarker(center, {draggable: true});

        GEvent.addListener(marker, "dragstart", function() {
          map.closeInfoWindow();
        });

        GEvent.addListener(marker, "dragend", function() {
          document.getElementById("centerlatitude").value = marker.getPoint().lat();
          document.getElementById("centerlongitude").value = marker.getPoint().lng();
        });
        
        GEvent.addListener(map, "moveend", function() {
          document.getElementById("centerlatitude").value = marker.getPoint().lat();
          document.getElementById("centerlongitude").value = marker.getPoint().lng();

        });

        GEvent.addListener(map, "click", function(overlay, point) {
					if (point)	{
						marker.setPoint(point);
						document.getElementById("centerlatitude").value = marker.getPoint().lat();
						document.getElementById("centerlongitude").value = marker.getPoint().lng();
					}
        });  


        map.addOverlay(marker);
      }
    }

    function loadPOI() { 
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        var center = new GLatLng(document.getElementById("centerlatitude").value, document.getElementById("centerlongitude").value );
        map.setCenter(center, '.$this->confArr['startZoom'].');
        geocoder = new GClientGeocoder();
        
        '.$settings.'
        var marker = new GMarker(center, {draggable: true});

        GEvent.addListener(marker, "dragstart", function() {
          map.closeInfoWindow();
        });

        GEvent.addListener(marker, "dragend", function() {
          document.getElementById("centerlatitude").value = marker.getPoint().lat();
          document.getElementById("centerlongitude").value = marker.getPoint().lng();
        });
        
        GEvent.addListener(map, "moveend", function() {
          document.getElementById("centerlatitude").value = marker.getPoint().lat();
          document.getElementById("centerlongitude").value = marker.getPoint().lng();

        });

        GEvent.addListener(map, "click", function(overlay, point) {
            marker.setPoint(point);
            document.getElementById("centerlatitude").value = marker.getPoint().lat();
            document.getElementById("centerlongitude").value = marker.getPoint().lng();  
        });  


        map.addOverlay(marker);
      }
    }
    
    
     function loadhover(name, lng, lat) {
      if (GBrowserIsCompatible()) {
      
      var l = document.getElementById("mapLink"); 
      l.style.visibility = "visible";
      
        var m = document.getElementById("mapHover");
        m.style.height = "230px";
        m.style.width = "470px";
        m.style.visibility = "visible";

        var center = new GLatLng(lng, lat);
        var map = new GMap2(m);
        map.setCenter(center, 13);
        map.addControl(new GSmallMapControl());

        var marker = new GMarker(center);
        var info = "<strong>"+name+"</strong><br />Long:"+lng+"<br />Lat:"+lat;
        map.addOverlay(marker);
        
        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(info);
        });
        // marker.openInfoWindowHtml(info);
      }
    }

     function loadPoint(lat,lng) {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
        
        var center = new GLatLng(lat, lng);
        map.setCenter(center, '.$this->confArr['startZoom'].');
        geocoder = new GClientGeocoder();
        
        '.$settings.'
        var marker = new GMarker(center, {draggable: true});

        GEvent.addListener(marker, "dragstart", function() {
          map.closeInfoWindow();
        });

        GEvent.addListener(marker, "dragend", function() {
          document.getElementById("centerlatitude").value = marker.getPoint().lat();
          document.getElementById("centerlongitude").value = marker.getPoint().lng();
        });
        
        GEvent.addListener(map, "moveend", function() {
          document.getElementById("centerlatitude").value = marker.getPoint().lat();
          document.getElementById("centerlongitude").value = marker.getPoint().lng();

        });

        GEvent.addListener(map, "click", function(overlay, point) {
            marker.setPoint(point);
            document.getElementById("centerlatitude").value = marker.getPoint().lat();
            document.getElementById("centerlongitude").value = marker.getPoint().lng();  
        });  
        
                
        document.getElementById("centerlatitude").value = lat;
        document.getElementById("centerlongitude").value = lng;

        map.addOverlay(marker);
        
        ShowHide("options");
      }
    }

    function showAddress(address) {
      if (geocoder) {
        geocoder.getLatLng(
          address,
          function(point) {
            if (!point) {
              alert(address + " not found");
            } else {

              var map = new GMap2(document.getElementById("map"));
              '.$settings.'
              map.setCenter(point, 13);
              var marker = new GMarker(point, {draggable: true});
              map.addOverlay(marker);
              marker.openInfoWindowHtml(address);

              document.getElementById("centerlatitude").value = marker.getPoint().lat();
              document.getElementById("centerlongitude").value = marker.getPoint().lng();

      
              GEvent.addListener(marker, "dragstart", function() {
                map.closeInfoWindow();
              });
      
              GEvent.addListener(marker, "dragend", function() {
                document.getElementById("centerlatitude").value = marker.getPoint().lat();
                document.getElementById("centerlongitude").value = marker.getPoint().lng();
              });
              
              GEvent.addListener(map, "moveend", function() {
                document.getElementById("centerlatitude").value = marker.getPoint().lat();
                document.getElementById("centerlongitude").value = marker.getPoint().lng();
      
              });

              GEvent.addListener(map, "click", function(overlay, point) {
                  marker.setPoint(point);
                  document.getElementById("centerlatitude").value = marker.getPoint().lat();
                  document.getElementById("centerlongitude").value = marker.getPoint().lng();  
              });  
                      
              ShowHide("options");

            }
          }
        );
      }
    }
    
    function ShowHide(id) {
        obj = document.getElementsByTagName("div");


        if (id=="options") {
          if (obj[id].style.visibility == "visible"){
            obj[id].style.visibility = "hidden";
            obj[id].style.height = "0px";
          }
          else {
            obj[id].style.visibility = "visible";
            obj[id].style.height = "150px";
          }
          
        // id != options
        } else {    
         var m = document.getElementById("mapHover");   
          if (obj[id].style.visibility == "visible"){
            obj[id].style.visibility = "hidden";
            m.style.height = "0px";
          }
          else {
            obj[id].style.visibility = "visible";
            m.style.height = "230px";
          }
        
        
        }
        
    }
    function autocomplete2 (text, id) {
       document.getElementById("selectAuto").value = text;
       document.getElementById("select").value = id;
       
       obj = document.getElementById("completeresult");
       obj.style.display = "none";
    }
    function autocompleteShow () {
       obj = document.getElementById("completeresult");
       obj.style.display = "inline";    
    }   
    function autoload (text, id) {
       document.getElementById("selectAutoLoad").value = text;
       document.getElementById("selectLoad").value = id;
       
       obj = document.getElementById("completeresultLoad");
       obj.style.display = "none";
    }
    function autoloadShow () {
       obj = document.getElementById("completeresultLoad");
       obj.style.display = "inline";    
    }     
    '.$onload.'
    //]]>
    </script><script type="text/javascript" src="sort.js"></script>
	 '.$this->xajax->getJavascript('../../../../'.t3lib_extMgm::extRelPath("xajax"))
	.($scripttags?'':'<script type="text/javascript">/*<![CDATA[*/
   	');
		return $content;
	}


	function genPostJScode()	{
		$content = '
				<script language="javascript" type="text/javascript">
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = 0;
				</script>
				<style type="text/css">
        <!--
          .error { 
            color:red;text-align:center;font-weight:bold;
          }
          .success { 
            color:green;text-align:center;font-weight:bold;
          }
          .right {
            text-align:right;
          }
          .catcolor {
            font-size:1px; width:6px;height:8px;float:left; margin-right:5px;
          }
          
          h4 {
            border-bottom:1px solid #777;
            Xborder-top:1px solid #777;
            background:#e6e6e6;
            padding:1px 10px;
          }
          #centerlatitude,#centerlongitude{ width:130px; }
          a:hover {text-decoration:underline;}
          
                    #autopreselect a, #autopreselect a:link, #autopreselect a:visited, #autopreselect a:active {
            display:block;
            padding:2px 0;margin:0;
            width:250px;
            background:#fff;
            text-decoration:none;
          }
          #autopreselect a:hover {
           background:#eee;
           text-decoration:none;
          }
          #autopreselect {
            padding:0;margin:0;
            position:absolute; 
            height:100px; 
            overflow:auto;
            border:1px solid #ccc;
            background:#fff;
          }

        -->
        </style>
	';
		return $content;
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	void
	 */
	function printContent()	{
	  $this->xajax->processRequests();    // Before your script sends any output, have xajax handle any requests
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}


/* ======== List view
==========================*/


	/**
	 * Shows all the records and initalize xajax-request for changing hide/show
	 */	    
  function viewRecordList() {
    require_once (PATH_t3lib."class.t3lib_tcemain.php");
    global $BACK_PATH,$LANG,$TCA,$BE_USER,$TSFE;

    //  geocoding
    $geocode = array();
    $tables = explode(',',$this->confArr['tables']);
    
    foreach ($tables as $key=>$singleTable) {
      $singleTable = trim($singleTable);    
      if (is_object($serviceObj = t3lib_div::makeInstanceService('rggmData',$singleTable))) {
            if ($call = $serviceObj->addressFields()) {
              $geocode[$singleTable] = $call;
            }
      }
    } 

    
    $content .= $this->doc->section($LANG->getLL("headerList"),'',0,1);

		// override tree depth
		$content.= '<form method="post"> 
									'.$this->getTreeDepth().'
									<input type="submit" />
								</form>';

    // query
		$vars = t3lib_div::_POST('rggm');
		$treeDepth = (isset($vars['depth']) && intval($vars['depth'])>0) ? intval($vars['depth']) : $this->confArr['recursive'];
    
    
    $field = '*';
    $where = 'pid IN('.$this->getTreeList($this->id,$treeDepth,$level=0,' AND deleted = 0 AND hidden = 0').') AND deleted = 0';
    $res = $this->generic->exec_SELECTquery($field,$this->confArr['tables'],$where,$groupBy,$orderBy,$offset);
    
    if (count($res) != 0) { # at lease one record available
	     $out.='
             <form id="listForm" name="listForm">
             <div style="height:400px;overflow:auto;"><table cellpadding="1" cellspacing="1" class="bgColor4 sortable" id="listrecord" width="100%">       
              <tr class="tableheader bgColor5">
                <td></td>
                <td>'.$LANG->getLL("name").'</td>
                <td>'.$LANG->getLL("category").'</td>
                <td align="center">'.$LANG->getLL("table").'</td>
                <!--<td align="center">'.$LANG->getLL("display").'</td>--!>
                <td align="center">'.$LANG->getLL("map").'</td>
              </tr>';
              
        $catTitle = $this->getCatTitle();
        $tableTitles = array();
        
        foreach(t3lib_div::trimExplode(',',$this->confArr['tables']) as $key) {  
	        $tableTitles[$key] = $GLOBALS['TCA'][$key]['ctrl']['label'];
				}

        while($row=array_shift($res)) { # single record
      		$title = htmlspecialchars($row[$tableTitles[$row['table']]]);
          $show = ($record['show']==1) ? ' checked ' : '' ;
          
          if (intval($row['lng'])==0 || intval($row['lat'])==0) {
            $hide=' disabled="disabled" ';
            // geocoding for records
            if ($geocode[$row['table']]!='') {
              $map = '<span style="color:red;"><a href="?id='.$this->id.'&amp;SET[function]=2&amp;updateGeoData2='.$row['uid'].'|'.$row['table'].'|'.$geocode[$row['table']].'">'.$LANG->getLL('loadgeodata').'</a></span>';
            } else {
            // if no geocoding possible
             $map = '<span style="color:red;text-decoration:line-through;">'.$LANG->getLL("mapLink").'</span>';
            }
          }else {
            $hide='';
            $map = '<a style="cursor: help;" onclick="loadhover(\''.$title.'\','.$row['lat'].','.$row['lng'].')">'.$LANG->getLL("mapLink").'</a>' ; 
          }

      		$j++;
      		$catArr = explode(',',$row['rggmcat']);
      		$firstCat = $catArr[0];
          $out.='<tr class="'.($i++ % 2==0 ? 'bgColor3' : 'bgColor4').'">
                  <td>'.$this->getIconFromRecord($row['table'],$row['uid']).'</td>
                  <td>'.$title.'</td>
                  <td>'.$catTitle[$firstCat].'</td>
                  <td align="center">'.$this->getLL($row['table']).'</td>                      
                  <!-- <td align="center"><input type="checkbox" name=new['.$row['table'].']['.$row['uid'].'] value="'.$row['uid'].'" '.$show.$hide.' />'.' </td>--!>
                  <td align="center">'.$map.'</td>
            		</tr>
                <input type="hidden" name=old['.$row['table'].']['.$row['uid'].'] value="'.$row['uid'].'">';            	
        }
          
    	$out.='</table></div>
              <div id="mapHover"></div>
              <div id="mapLink" style="visibility:hidden;" class="right">
								<a href="javascript:ShowHide(\'mapHover\')">'.$LANG->getLL("showHideMap").'</a>
							</div>
             <!--<fieldset><legend>'.$LANG->getLL("headerListSave").'</legend>
             <input type="checkbox" name="control" id="control" value="1" /> <label for="control">'.$LANG->getLL("confirmation").'</label>
             <br />
             <div class="right"><input id="submitButton" type="button" value="'.$LANG->getLL("save").'" onclick=\'xajax_listRecords(xajax.getFormValues("listForm"));\' /></div>
             </fieldset>--!></form>';
    }
    $out .= '<div id="listOut"></div>';
    $content.=$out;

		$content.= $this->getLinksToNewRecords();

    return $content;
  } # end function viewRecordList()


	/**
	 * xajax request to save changes off display-feature
	 * DEPRICATED	 
	 */	 
  function xajaxListRecords($arg) { 
    global $BACK_PATH,$LANG,$TCA,$BE_USER,$TSFE;
    
    // if checkbox set
    if ($arg['control'] == 1) {
      foreach ($arg['old'] as $k=>$v) { // for every table
        $old = $arg['old'][$k]; // previous activated checkbox (before editing)
        $new = $arg['new'][$k]; // checkboxes after editing
        #$content.= 'OLD'.t3lib_div::view_array($old);
        #$content.= 'NEW'.t3lib_div::view_array($new);  
       
        // set display = 0 > hide
        if (!isset($new)) {
          $deleteIDs = implode(',',$old);
        } else {
          $delete = array_diff_assoc($old, $new); 
          $deleteIDs = implode(',',$delete);  
        }
       	$where = 'uid IN ('.$deleteIDs.') AND deleted = 0';
        $saveData['tx_rggooglemap_display'] = 0;
        $table = $k;
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$saveData); 
                       
        // set display = 1  > show
        if (isset($new)) {
          $insert = array_intersect($new,$old); 
          $insertIDs = implode(',',$insert);           
         	$where = 'uid IN ('.$insertIDs.') AND deleted = 0';
          $saveData['tx_rggooglemap_display'] = 1;
          $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$saveData); 
        }
      	
      }
      $content.= '<div class="success">'.$LANG->getLL("saveDisplay").'</div>';
    } else {
      $content.= '<div class="error">'.$LANG->getLL("saveErrorCheckbox").'</div>';
    }
    // Instantiate the tx_xajax_response object
    $objResponse = new tx_xajax_response();
    $objResponse->addAssign("listOut","innerHTML", $content );
      
    return $objResponse->getXML();  
  }
  
/**
	 * Update the GeoData in a Data Row
	 * @param	string var: id|table|fields
	 */

	function updateGeoData2($var){

    $var = explode('|',$var);
    $uid = intval($var[0]);
    $table = $var[1];
    $fields = $var[2];
    
    #echo $uid.'__'.$table.'___'.$fields;
    
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,'.$fields,$table,'uid = '.$uid);
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    if ($row['uid']!='') {
      $address = explode(',',$fields);
      $geocode = Array();
      foreach ($address as $key=>$value) {
      	$geocode[] = $row[$value];
      }
      
      $geoAdress = implode(',',$geocode);
      $geoAdress = str_replace(array('ß', 'ö', 'ä', 'ü'), array('ss', 'oe', 'ae', 'ue'), $geoAdress);
     
      $url = 'http://maps.google.com/maps/geo?q='.urlencode($geoAdress).'&output=csv&key='.$this->confArr['googleKey'];
      $response=stripslashes(t3lib_div::getUrl($url));

      $response = explode(',',$response);
      if ($response[0]=='200' && $response[2]!= '' && $response[3] != '' && (is_object($serviceObj = t3lib_div::makeInstanceService('rggmData',$table)))) {
        // get the lng + lat field to save
      $lat = $serviceObj->getTable('lat');
      $lng = $serviceObj->getTable('lng');   
      
      $update[$lat] = $response[2]; 
      $update[$lng] = $response[3];
 
      #t3lib_div::print_array($update);
      	$success = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,'uid='.$uid,$update);
      	
      	if ($success) {
					return '1';
				} else {
					return '0';
				}
      } else {
				return 'error with geocoding with: '.$geoAdress.' => error code: '.implode(',',$response);
			}
    
    
    } else {
			return 'error: no id';
		}
    
    

		
		
	}	
/* ======== Map View
======================*/

	function viewMap() {
		global $BACK_PATH,$LANG,$TCA,$BE_USER;
		$content .= $this->doc->section($LANG->getLL("headerChoosePOI"),'',0,1);

		if ($this->wizardMode)	{
			$content .=	$LANG->getLL("latitude").': <input id="centerlatitude" name="google[centerlatitude]" value="'.$this->confArr['startLat'].'" type="text"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'.
					$LANG->getLL("longitude").': <input id="centerlongitude" name="google[centerlongitude]" value="'.$this->confArr['startLong'].'" type="text"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
					<input id="submitButton" type="button" value="'.$LANG->getLL("save").'" onclick=\'savePoint("map'.$this->mapIdx.'");\' /> &nbsp; &nbsp;
					<input id="submitButton" type="button" value="'.$LANG->getLL("save_and_close").'" onclick=\'savePoint("map'.$this->mapIdx.'"); window.close();\' /> &nbsp; &nbsp;
					<input id="submitButton" type="button" value="'.$LANG->getLL("close").'" onclick=\'window.close();\' /><br />

                  <div id="options">
                    <form action="#" onsubmit="showAddress(this.address.value); return false">
                      <fieldset>
                        <legend>'.$LANG->getLL("headerGeocode").'</legend>
                        <input type="text" size="70" name="address" value="'.$LANG->getLL("geocodeSearchValue").'" onfocus="if(this.value==\''.$LANG->getLL("geocodeSearchValue").'\')this.value=\'\';" />
                        <input type="submit" value="'.$LANG->getLL("geocodeButton").'"  />
                      </fieldset>
                    </form>   

                  </div>

                  			<div id="map" style="width: '.$this->confArr['mapWidth_popup'].'px; height: '.$this->confArr['mapHeight_popup'].'px"></div>
                  <br />';
		} else	{
			$content .= '
                  <div id="map" style="width: '.$this->confArr['mapWidth'].'px; height: '.$this->confArr['mapHeight'].'px"></div>
                  <br />
                  <form id="testForm2" name="testForm2">
                      '.$LANG->getLL("latitude").': <input id="centerlatitude" name="google[centerlatitude]" value="'.$this->confArr['startLat'].'" type="text">
                      '.$LANG->getLL("longitude").': <input id="centerlongitude" name="google[centerlongitude]" value="'.$this->confArr['startLong'].'" type="text">
                      <input type="hidden" name="google[step2]" value="1">
                    <br />
                    <input id="submitButton" type="button" value="'.$LANG->getLL("save").'" onclick=\'xajax_getPoi(xajax.getFormValues("testForm2"));\' />
                  </form>
                  <br />
                  
                  <a href="javascript:ShowHide(\'options\')">'.$LANG->getLL("showHideOptions").'</a>
                  <div id="options" style="visibility:hidden;height:0px;">
                    <form action="#" onsubmit="showAddress(this.address.value); return false">
                      <fieldset>
                        <legend>'.$LANG->getLL("headerGeocode").'</legend>
                        <input type="text" size="70" name="address" value="'.$LANG->getLL("geocodeSearchValue").'" onfocus="if(this.value==\''.$LANG->getLL("geocodeSearchValue").'\')this.value=\'\';" />
                        <input type="submit" value="'.$LANG->getLL("geocodeButton").'"  />
                      </fieldset>
                    </form>

                    <form id="loadForm" name="loadForm">'.$selection.'  
                      <fieldset>
                        <legend>'.$LANG->getLL("headerloadRecord").'</legend>
                        <input type="text" style="width:250px;" name="selectAutoLoad" id="selectAutoLoad" value=""  onkeyup=\'autoloadShow(); xajax_autoload(xajax.getFormValues("loadForm")); \'  />
                        <div id="completeresultLoad"></div>
                      </fieldset>
                    </form>
                
                    <fieldset>
                      <legend>'.$LANG->getLL("headerloadPOI").'</legend>
                      <a href="#" onClick="loadPOI()">'.$LANG->getLL("loadPOILink").'</a>
                    </fieldset>
                  </div>
                  
                  
                  <div id="savePoints"></div>';
		}
		return $content;
	}


	/**
	 * xajax-request after clicking 1st save-Button
	 *  => checking if there is a long/lat	 
	 */	
  function xajaxGetPoi($arg)        {       
    global $BACK_PATH,$LANG,$TCA,$BE_USER;
    #$content.=t3lib_div::view_array($arg);
    $arg = $arg['google'];

    // Check if a POI chosen
    if ($arg['centerlongitude'] != '') {
      $content.= $this->getPOIs($arg);
    } else {
      $content .= '<br /><div class="error">'.$LANG->getLL("saveError").'</div>';
    }
   
    // Instantiate the tx_xajax_response object
    $objResponse = new tx_xajax_response();
    $objResponse->addAssign("savePoints","innerHTML", $content);
      
    return $objResponse->getXML();
  } 

	/**
	 * called by xajaxGetPoi to get 
	 *    1) dropdown to save POI to a record
	 *    2) Link to save POI to new record	 
	 *    and to initalize xajax-request for saving the record	 
	 */	
  function getPOIs ($arg) {
    global $BACK_PATH,$LANG,$TCA,$BE_USER;
    $content.= $this->doc->section($LANG->getLL("headerSave"),'',0,1);    
    
    // 1) inserts POI to a new record
    $table = 'tt_address';
    $content.='<h4>'.$LANG->getLL("headerSavePOINew").'</h4><ul>';
    
    $tableList = t3lib_div::trimExplode(',', $this->confArr['tables']);
    foreach ($tableList as $key) {

					$content.=$this->getNewRecord($key,$this->id, $arg['centerlongitude'], $arg['centerlatitude']);		
		}
		
		$content.= '</ul>';
    
    $content.='<h4>'.$LANG->getLL("headerSavePOI").'</h4>';
    
     # fancy new auto suggest/complete
      $selection = '<input type="text" style="width:250px;" name="selectAuto" id="selectAuto" value=""  onkeyup=\'autocompleteShow(); xajax_autocomplete(xajax.getFormValues("saveForm")); \'  />
                    <input type="hidden" name="select" id="select" />
                    <div id="completeresult"></div> ';

    $content.= '
                <form id="saveForm" name="saveForm">'.$selection.'
                  <input type="hidden" name="long" value="'.$arg['centerlongitude'].'"/>
                  <input type="hidden" name="lat" value="'.$arg['centerlatitude'].'"/>  
                  <input type="checkbox" name="override" id="override" value="on" /> <label for="override">'.$LANG->getLL("confirmation").'</label>
                  <br /><br />
                  <input id="submitButton" type="button" value="'.$LANG->getLL("save").'" onclick=\'xajax_insertPoi(xajax.getFormValues("saveForm"));\' />
                </form>
                <div id="resultDiv"></div>';
    
    return $content;
  }

	/**
	 * xajax-request to save the POI to a record
	 */	
  function xajaxInsertPoi($arg)        {     
  
    global $BACK_PATH,$LANG,$TCA,$BE_USER;      
    if ($arg['override'] == 'on' && $arg['select']!='') {
      $content.= $this->newSaveRecord($arg);

    } else {
      if ($arg['override'] != 'on') $content.= '<div class="error">'.$LANG->getLL("saveErrorCheckbox").'!</div>';
      if ($arg['select'] =='') $content.='<div class="error">'.$LANG->getLL("saveErrorDropdown").'</div>';
    }

    $objResponse = new tx_xajax_response();
    $objResponse->addAssign("resultDiv","innerHTML", $content);
      
    return $objResponse->getXML();
  } 

	/**
	 * saving procedure and checking first if there is already a record with the same POI
	 */	
  function newSaveRecord ($arg) {
    global $BACK_PATH,$LANG,$TCA,$BE_USER;
    #$content = t3lib_div::view_array($arg);
    $divider = strpos($arg['select'], ';');
    $table = substr($arg['select'],0,$divider);
    $id = substr($arg['select'],$divider+1,strlen($arg['select']));
    
     $serviceChain='';
    while (is_object($serviceObj = t3lib_div::makeInstanceService('rggmData', $table, $serviceChain))) {
      $serviceChain.=','.$serviceObj->getServiceKey();    
      if ($tempuser=$serviceObj->init()) {
  
        // user found, just stop to search for more
        $latField = $serviceObj->getTable('lat');
        $lngField = $serviceObj->getTable('lng');
     
        // Security check > no double entries    
        $i = 0;
        $field = 'name,uid,lng';
        $where = 'pid IN('.$this->getTreeList($this->id,$this->confArr['recursive'],$level=0,' AND deleted = 0 AND hidden = 0').') AND deleted = 0 AND hidden = 0 AND lng = '.$arg['long'].' AND lat = '.$arg['lat'];
        $res = $this->generic->exec_SELECTquery($field,$this->confArr['tables'],$where,$groupBy,$orderBy,'0,10');
        
        while($row=array_shift($res)) { # single record    
          $i++;
          $doubleTitle = $row['name'];
          $doubleTable = $row['table'];
        }
        
        if ($i > 0) { # double entry saveSamePosition
          $content .= '<div class="error">'.sprintf($LANG->getLL("saveSamePosition"), $doubleTitle).'</div>';
        } else {
          $where = 'uid = '.$id;
          $saveData[$lngField] = $arg['long'];
         	$saveData[$latField] = $arg['lat'];
      		$saveData['tstamp'] = time();
      
          $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$saveData);
          
          $content .= '<div class="success">'.$LANG->getLL("saveSuccss").'</div>';       
        }
        return $content;
      } 
        }
    }

	/**
	 * xajax-request: filling out a div with the auto loading results	 
	 */	  
  function xajaxAutoComplete($arg)        {       
    global $BACK_PATH,$LANG,$TCA,$BE_USER;
    
    $autoselect = $arg['selectAuto'];
    $i = 0;
    $content = '';
    
    if (strlen($autoselect) >0 || $autoselect == '*') {
      // query for autocomplete
      $field = 'uid, lng, lat,name,rggmcat';
      // search expression = *
      $where = 'pid IN('.$this->getTreeList($this->id,$this->confArr['recursive'],$level=0,' AND deleted = 0 AND hidden = 0').') ';
      // if searchexpression is a word
      if ($autoselect != '*') $where.= 'AND name LIKE \'%'.$autoselect.'%\'';
      
      $res = $this->generic->exec_SELECTquery($field,$this->confArr['tables'],$where,$groupBy,$orderBy,$offset);
      while($row=array_shift($res)) { # single record    
        $onclick = 'onclick=\'autocomplete2("'.$row['name'].'", "'.$row['table'].';'.$row['uid'].'");\'  ' ;
     	  $selection.='<a href="#" '.$onclick.$empty.'>'.$this->getIconFromRecordNoLink($row['table']).$row['name'].'</a>';
    		$i++;
      }      
      if ($i > 0) { 
        $content = '<div id="autopreselect" >'.$selection.'</div>';
      } 
    } else {
      $content = '';
    }
   
    // Instantiate the tx_xajax_response object
    $objResponse = new tx_xajax_response();
    $objResponse->addAssign("completeresult","innerHTML", $content);
      
    return $objResponse->getXML();
  } 

  function viewMassGeocoding() {
    require_once (PATH_t3lib."class.t3lib_tcemain.php");
    global $BACK_PATH,$LANG,$TCA,$BE_USER,$TSFE;

    $content=$this->doc->section('Geocode Massindex','',0,1);
    
    // create form
    $content.= 
		'
			<form method="post">
				'.$this->getTreeDepth().'
				'.$this->getTableSelection().'	
				
				<input type="hidden" name="rggm[start]" value="1" />
				<input type="submit" />			
			</form>
		';
    
    
    
    
  #  $content.= '<a href="index.php?&id='.$this->id.'&SET[function]=3&rggm[start]=1">Start Mass Index</a>';
    
    $vars = t3lib_div::_GP('rggm');
    
    
    $content.= t3lib_div::view_array($vars);


		
    if ($vars['start']==1 && count($vars['tables'])>0) {

			$countYes = $countAll = 0;

			$tableList = array();
			foreach($vars['tables'] as $key => $value) {
				$tableList[] = $key;
			}

	    //  geocoding
	    $geocode = array();
	    $tables = explode(',',$this->confArr['tables']);
	    
	    foreach ($tableList as $key=>$singleTable) {
	      $singleTable = trim($singleTable);    
	      if (is_object($serviceObj = t3lib_div::makeInstanceService('rggmData',$singleTable))) {
	            if ($call = $serviceObj->addressFields()) {
	              $geocode[$singleTable] = $call;
	            }
	      }
	    } 
	
	    // query
	    $field = '*';
	    $tables = implode(',', $tableList);
	    $where = 'pid IN('.$this->getTreeList($this->id,$vars['depth'],$level=0,' AND deleted = 0 AND hidden = 0').')  AND deleted = 0';
	    $res = $this->generic->exec_SELECTquery($field,$tables,$where,$groupBy,$orderBy,$offset);

      foreach(t3lib_div::trimExplode(',',$this->confArr['tables']) as $key) {  
        $tableTitles[$key] = $GLOBALS['TCA'][$key]['ctrl']['label'];
			}
	    
	    while($row=array_shift($res)) { # single record

	    	if (intval($row['lng'])!=0 && intval($row['lat'])!=0) {
					continue;
				}
	    
	    	$countAll++;
	      if ($geocode[$row['table']]!='') {
	    		$title = htmlspecialchars($row[$tableTitles[$row['table']]]);
					$msg = $this->updateGeoData2($row['uid'].'|'.$row['table'].'|'.$geocode[$row['table']]);
	    		
					if ($msg==1) {
						$countYes++;
						$content.= $row['uid'].' - '.$title.' => yeah <br /><br />';
					} else {
						$content.= $row['uid'].' - '.$title.' => error <br /><br />';
					}
					
	    		
	    		
	      } else {
					$content.= 'no geocoding <br /><br />';
				}

	    
	    }
	    $content.= 'Geocoded records: '.$countYes.' from '.$countAll;
	#              $map = '<span style="color:red;"><a href="?id='.$this->id.'&amp;SET[function]=2&amp;updateGeoData2='.$row['uid'].'|'.$row['table'].'|'.$geocode[$row['table']].'">'.$LANG->getLL('loadgeodata').'</a></span>';
		}	
	
    
    return $content;
  }

  
/* ======== div functions
==========================*/

	/**
	 * returns droddown with all POIs
	 */	  
  function xajaxAutoLoad ($arg) {

      global $BACK_PATH,$LANG,$TCA,$BE_USER;
    
    $autoselect = $arg['selectAutoLoad'];
    $i = 0;
   # $content = t3lib_div::view_array($arg);
    
    if (strlen($autoselect) >1 || $autoselect == '*') {
      // query for autoload
      $field = 'uid, lng, lat,name';
      // search expression = *
      $where = 'pid IN('.$this->getTreeList($this->id,$this->confArr['recursive'],$level=0,' AND deleted = 0 AND hidden = 0').') ';
      // if searchexpression is a word
      if ($autoselect != '*') $where.= 'AND name LIKE \'%'.$autoselect.'%\'';
      
      $res = $this->generic->exec_SELECTquery($field,$this->confArr['tables'],$where,$groupBy,$orderBy,$offset);
      while($row=array_shift($res)) { # single record    
        $onclick = 'onclick=\'loadPoint("'.$row['lat'].'", "'.$row['lng'].'");\'  ' ;
     	  $selection.='<a href="#" '.$onclick.$empty.'>'.$this->getIconFromRecordNoLink($row['table']).$row['name'].'</a>';
    		$i++;
      }      
      if ($i > 0) { 
        $content .= '<div id="autopreselect" >'.$selection.'</div>';
      } 
    } else {
      $content ='';
    }
   
    // Instantiate the tx_xajax_response object
    $objResponse = new tx_xajax_response();
    $objResponse->addAssign("completeresultLoad","innerHTML", $content);
      
    return $objResponse->getXML();
  } 



	/**
	 * returns icon with edit-link
	 */	  	
  function getIconFromRecord($table,$row) {
    global $BACK_PATH,$LANG,$TCA,$BE_USER;
    $iconAltText = t3lib_BEfunc::getRecordIconAltText($row,$table);
    $elementTitle=t3lib_BEfunc::getRecordPath($row,'1=1',0);
    $elementTitle=t3lib_div::fixed_lgd_cs($elementTitle,-($BE_USER->uc['titleLen']));
    $elementIcon=t3lib_iconworks::getIconImage($table,'',$BACK_PATH,'class="c-recicon" title="'.$iconAltText.'"');
    
    $params='&edit['.$table.']['.$row.']=edit';
    $editOnClick=t3lib_BEfunc::editOnClick($params,$BACK_PATH);
    
    return '<a href="#" onclick="'.htmlspecialchars($editOnClick).'">'.$elementIcon.'</a>';
  }	# end function getIconFromRecord()

	/**
	 * returns icon without edit-link
	 */	  	
  function getIconFromRecordNoLink($table) {
    global $BACK_PATH,$LANG,$TCA,$BE_USER;
    $elementIcon=t3lib_iconworks::getIconImage($table,'',$BACK_PATH,'');

    return $elementIcon;
  }	# end function getIconFromRecordNoLink()
    
  
	/**
	 * Returns title with edit-link
	 */	    
  function getTextFromRecord ($table, $row) {
    global $BACK_PATH,$LANG,$TCA,$BE_USER;
    $params='&edit['.$table.']['.$row['uid'].']=edit';
    $editOnClick=t3lib_BEfunc::editOnClick($params,$BACK_PATH);  
    return '<a href="#" onclick="'.htmlspecialchars($editOnClick).'" title="'.$row['name'].' ('.$row['uid'].')">'.$row['name'].'</a>'; 
  } # end function getTextFromRecord()
  

	/**
	 * Returns onClick for link to new record with the coordinates
	 */	  
  function getNewRecord ($table,$pid, $lngVal, $latVal) {
    global $BACK_PATH,$LANG,$TCA,$BE_USER;
		$out = $latField = $lngField = '';
		while (is_object($serviceObj = t3lib_div::makeInstanceService('rggmData', $table, $serviceChain))) {
			$serviceChain.=','.$serviceObj->getServiceKey();    
			if ($tempuser=$serviceObj->init()) {
					$latField = $serviceObj->getTable('lat');
					$lngField = $serviceObj->getTable('lng');
			}
		}
		
		// just create the link if the service object found a translation
		if ($latField != '' && $lngField != '') {
	    $params = '&edit['.$table.']['.$pid.']=new';
	    $params.='&defVals['.$table.']['.$lngField.']='.$lngVal;
	    $params.='&defVals['.$table.']['.$latField.']='.$latVal;
	    $out = '<li><a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'])).'">'.'<b>'.$table.'</b>: '.$LANG->getLL("clickHere").'</a></li>';
		}

    return $out;
  } # end getNewRecord
	
	/**
	 * Returns the title of a category, based on the uid of the category
	 */	
  function getCatTitle() {
    $where = 'hidden = 0 AND deleted = 0';
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, title','tx_rggooglemap_cat',$where,$groupBy='',$orderBy,$limit='');
    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $data[$row['uid']] = $row['title'];
    }
    return $data;
  }
	/**
	 * Returns the title of a category, based on the uid of the category
	 */	

  function getCatColor() {
    $where = 'hidden = 0 AND deleted = 0';
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, color','tx_rggooglemap_cat',$where,$groupBy='',$orderBy,$limit='');
    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
    if ($row['color'] != '') $data [$row['uid']] = '<div style="background:'.$row['color'].';" class="catcolor"></div>';
    }
    return $data;
  }  

  function getLL($key) {
    global $LANG;
    $value = $LANG->getLL($key);
    $value = ($value) ? $value : $key;
    return $value;
  }


	/**
	* Get all pids recursive
	*/	  
	function getTreeList($id, $depth, $begin=0, $perms_clause) {
		$depth = intval($depth);
		$begin = intval($begin);
		$id = intval($id);
		
		if ($begin==0) {
			$theList = $id;
		} else {
			$theList = '';
		}
		
		if ($id && $depth > 0) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'pid='.$id.$perms_clause);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($begin <= 0) {
					$theList .= ','.$row['uid'];
				}
				if ($depth > 1) {
					$theList .= $this->getTreeList($row['uid'], $depth-1, $begin-1, $perms_clause);
				}
			}
		}
		
		return $theList;
	}


	/**
	* Get a select field for the tree depth
	*/	
	function getTreeDepth() {
		$content = '<label for="rggmdepth">
									<select id="rggmdepth" name="rggm[depth]>';
		$vars = t3lib_div::_POST('rggm');
		$act = (isset($vars['depth']) && intval($vars['depth'])>0) ? intval($vars['depth']) : $this->confArr['recursive'];
		
		for($i=0;$i<=10;$i++) {
			$selected = ($act==$i) ? ' selected="selected" ' : '';
			$content.= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
		}
		
		$content.= '</select>'.$GLOBALS['LANG']->getLL("depth").'</label>';

		return $content;
	}	
	
	function getTableSelection() {
		$tableList = explode(',', $this->confArr['tables']);
		$vars = t3lib_div::_POST('rggm');
		
		$content.= '<div class="field">';
		
		foreach ($tableList as $key) {
			$selected = ($vars['tables'][$key]=='on' || $vars['start']!=1) ? ' checked="checked"' : '';
			$content.= '<label for="rggmtable-'.$key.'">
										<input '.$selected.' type="checkbox" type="checkbox" name="rggm[tables]['.$key.']" id="rggmtable-'.$key.'" />'.$key.'<br />
									</label>
			';
		}
		
		$content.= '</div>';
	
		return $content;
	}

	/**
	* Create links to create records for all available tables 
	*/		
	function getLinksToNewRecords() {
    $content = '<br />'.$GLOBALS['LANG']->getLL("createNewRecordPlain").': <ul>';
    
    $tableArr = explode(',',$this->confArr['tables']);
    foreach ($tableArr as $key => $table) {
      $params='&edit['.$table.']['.$this->id.']=new';
      $editOnClick=t3lib_BEfunc::editOnClick($params,$BACK_PATH);
      $tableName = $GLOBALS['LANG']->getLL($table,1);
      $tableName = ($tableName!='') ? $tableName : $table;
      $content.= '<li><a href="#" onclick="'.htmlspecialchars($editOnClick).'">'.$tableName.'</a></li> ';
    }
    $content.= '</ul>';
		
		return $content;
	}

} # END ALL	


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/mod1/index.php']);
}

if (!$GLOBALS['SOBE'])	{
  // Make instance:
  $SOBE = t3lib_div::makeInstance('tx_rggooglemap_module1');
  $SOBE->init();
  
  // Include files?
  foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);
  
  $SOBE->main();
  $SOBE->printContent();
}

?>
