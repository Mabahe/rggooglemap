<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Georg Ringer (just2b) <http://www.ringer.it>
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
 * Userfunctions for the 'rggooglemap' extension.
 *
 * @author	Georg Ringer (just2b) <http://www.ringer.it>
 * @package	TYPO3
 * @subpackage	tx_rggooglemap
 */
class user_mapInFlexforms {

	/**
	 * Generate a Google Maps inside flexforms
	 *
	 * @param	array		$conf: configuration of the flexform element
	 * @return	Googlemaps including geocoding
	 */
	function map($conf) {

 		$ll = $GLOBALS['LANG']->includeLLFile('EXT:rggooglemap/locallang_flex.xml');
			// load settings from EM	
		$tmp_confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);

			// error check, required for 4.2, if settings are saved in EM
		if ($tmp_confArr['googleKey']=='') {
			return 'Please go back to the Extension Manager, click on the ext rggooglemap and press the "Update"-Button!';
		}
		
			// get the default values, either from the flexforms or as fallback from EM settings
		$xml = $conf['row']['pi_flexform'];
		$array = t3lib_div::xml2array($xml);
		if (!is_array($array)) return 'Update once';
				
		$tmpDefault = $array['data']['sDEF']['lDEF'];
		
		$lat = ($tmpDefault['lat']['vDEF'] != '') ? $tmpDefault['lat']['vDEF'] : $tmp_confArr['startLat'];
		$lng = ($tmpDefault['lng']['vDEF'] != '') ? $tmpDefault['lng']['vDEF'] : $tmp_confArr['startLong'];
	
			// set all the js we need
		$js = '
			var map = null;
			var geocoder = null;
			var marker = null;
			var point = null;
			
			function simpelMapLoad() {
				if (GBrowserIsCompatible()) {
					var point = new GLatLng('.$lat.','.$lng.');
					
				  map = new GMap2(document.getElementById("map"));
					map.setCenter(point,'.$tmp_confArr['startZoom'].');
					map.enableContinuousZoom();
					map.addControl(new GLargeMapControl());
					map.addControl(new GMapTypeControl());


					geocoder = new GClientGeocoder();
				
					
					marker = new GMarker(point, {draggable: true});
					map.addOverlay(marker);
				
					GEvent.addListener(marker, "dragend", function() {
						document.getElementById("rggmlatlng").value = marker.getPoint().lat() + "," + marker.getPoint().lng();
					});
					
					GEvent.addListener(map, "click", function(overlay, point) {
						if (point)	{
							marker.setPoint(point);
						document.getElementById("rggmlatlng").value = marker.getPoint().lat() + "," + marker.getPoint().lng();
						}
					);
				}
			}		
			
			function showAddress(address) {
				var address = document.getElementById("geocodeaddress").value;
				if (geocoder) {
						geocoder.getLatLng(
						address,
						function(newpoint) {
						if (!newpoint) {
								alert(address + " not found");
							} else {
								marker.setPoint(newpoint);
								map.setCenter(newpoint);
								document.getElementById("rggmlatlng").value = marker.getPoint().lat() + "," + marker.getPoint().lng();
							}
						}
				);
			}
			} 

		  function requestAjax() {
		    new Ajax.Request("ajax.php", {
					parameters: {
						"ajaxID": "tx_rggooglemap_ajax::getMap", 
						"rggm[table]"  : $("rggmtable").value,
						"rggm[title]"  : $("rggmtitle").value,
						"rggm[latlng]" : $("rggmlatlng").value,
						"rggm[pid]"    : $("rggmpid").value,
						"rggm[cat]"    : $("rggmcategory").value
					},
					onComplete: function(xhr) {
						var response = xhr.responseText.evalJSON();
						var messages = "";

						if (response["error"]) {
							messages = response["error"];
						} else {
							 messages = response["result"];
						}
						document.getElementById("rggmresult").innerHTML = messages;

					}.bind(this),
					onT3Error: function(response) {
						alert(response.responseJSON.result);
					}.bind(this)

		    });
		  }
		';

		// put the JS and the needed HTML together
		$paddingTop = ($tmp_confArr['mapHeight'] / 2) - 20;
		$mapHeight = $tmp_confArr['mapHeight'] - $paddingTop;
		$labelStyles = ' style="display:block;float:left;width:90px;" ';
		$fieldsetStyles = ' style="width:440px;" ';
		$map = '
			<div id="map" 
				style="color:#ccc;padding-top:'.$paddingTop.'px;font-size:20px;text-align:center;width:'.$tmp_confArr['mapWidth'].'px;height:'.$mapHeight.'px;border:1px solid #ccc;">
						 ... '.$this->ll('wait').' ....
			</div>
			<div style="margin:5px 10px">
				<fieldset '.$fieldsetStyles.'>
					<legend>'.$this->ll('geocode.legend').'</legend>
					<input id="geocodeaddress" value="Gosau, Austria" style="width:300px;" /> 
					<input type="button" value="'.$this->ll('geocode.start').'" onclick="showAddress();" />
				</fieldset>
				<br />
				
				<fieldset '.$fieldsetStyles.'>
					<legend>'.$this->ll('save.legend').'</legend>
					<input id="rggmlatlng" type="hidden" style="width:300px" value="'.$lat.','.$lng.'" />
					
					<label for="rggmtitle" '.$labelStyles.'>'.$this->hoverHelpText('title').$this->ll('save.title').'</label>
						<input id="rggmtitle" type="text" value="" style="width:177px" />
					<br />
	
					<label for="rggmcategory" '.$labelStyles.'>'.$this->hoverHelpText('cat').$this->ll('save.cat').'</label>
						'.$this->getCategoryRecords().'	
					<br />
					<label for="rggmtable" '.$labelStyles.'>'.$this->hoverHelpText('table').$this->ll('save.table').'</label>
						'.$this->getTables($tmp_confArr['tables']).'
					<br />
					<label for="rggmpid" '.$labelStyles.'>'.$this->hoverHelpText('pid').$this->ll('save.pid').'</label>
						'.$this->getPidRecords($tmpDefault['startingpoint']['vDEF'], $conf['row']['pid']).'
					<br />
					<input type="button" value="'.$this->ll('save.start').'" onclick="requestAjax();" /><br />
				</fieldset>
			</div>

			<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$tmp_confArr['googleKey'].'" type="text/javascript"></script>
			<script language="javascript" type="text/javascript">
				'.$js.'
				setTimeout(" simpelMapLoad()",500);
			</script>
			
			<div id="rggmresult"> </div>
			';

		return $map;
	}

	/**
	 * Get a value from the language file, used as shortcut
	 *
	 * @param	string		$key: key in the language file
	 * @return	string the translation
	 */	
	function ll($key) {
		return $GLOBALS['LANG']->getLL('usermap.'.$key);
	}
	
	function hoverHelpText($key) {
		$title = $this->ll('help.'.$key.'.title');
		if ($title != '') {
			$title = '<span class="header">'.$title.'</span>';
		}

		$descr = $this->ll('help.'.$key.'.descr');
		if ($descr != '') {
			$descr = '<span class="paragraph">'.$descr.'</span>';
		}

		$content = '
			<a href="#" class="typo3-csh-link">
				<img hspace="2" height="16" border="0" width="16" alt="" style="cursor: help;" class="absmiddle" src="sysext/t3skin/icons/gfx/helpbubble.gif"/>
				<span class="typo3-csh-inline">
					'.$title.$descr.'
				</span>
			</a>
		';
		
		return $content;
	}

	/**
	 * Get a select field holding the table names
	 *
	 * @param	string		$tableList: all allowed tables
	 * @return the select form
	 */		
	function getTables($tableList) {
		$tableList = t3lib_div::trimExplode(',', $tableList);
		
		$content.= '<select id="rggmtable" style="width:122px">';
		
		foreach ($tableList as $table) {
			$value = $GLOBALS['LANG']->sL($GLOBALS['TCA'][$table]['ctrl']['title']). ' ('.$table.')';
			$content.= '<option value="'.$table.'">'.$value.'</option>';
		}
	
		$content.= '</select>';
	
		return $content;
	}
	
	function getPidRecords($startingpoint, $pageId) {
		
		$content.= '<select id="rggmpid" style="width:122px">
									<option value="'.$pageId.'">'.$this->ll('save.currentpage').' ('.$pageId.')</option>
								';
		
		// Pids from startingpoint
		if ($startingpoint != '') {
			$tempList = explode(',', $startingpoint);
	
			foreach ($tempList as $key) {
				$pos1 = strpos($key, '_');
				$pos2 = strpos($key, '|');
				
				$id = substr($key, $pos1+1, ($pos2-$pos1-1));
				$name = htmlspecialchars(urldecode(substr($key, $pos2+1)));
				
				$content.= '<option value="'.$id.'">'.$name.' ('.$id.')</option>';
			}
		}
		
		// Pids from page TSConfig
		$pagesTSC = t3lib_BEfunc::getPagesTSconfig($pageId);
		$pidList = $pagesTSC['rggooglemap.']['pid.'];
		
		if (is_array($pidList) && count($pidList) > 0) {
			foreach ($pidList as $key => $value) {
				$content.= '<option value="'.$key.'">'.htmlspecialchars($value).' ('.$key.')</option>';
			}
		}
		
		$content.= '</select>';
	
		return $content;
	}	

	
	/**
	 * Get a select field holding the googlemap categories
	 *
	 * @return the select form
	 */		
	function getCategoryRecords() {
		$tableList = t3lib_div::trimExplode(',', $tableList);
		
		$content.= '<select id="rggmcategory" style="width:122px">
									<option value="0">-</option>
								';
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title', 'tx_rggooglemap_cat', 'deleted=0 AND hidden=0', '', 'title DESC');
		
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$content.= '<option value="'.$row['uid'].'">'.$row['title'].'</option>';
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
	
		$content.= '</select>';
	
		return $content;
	}	

}


?>