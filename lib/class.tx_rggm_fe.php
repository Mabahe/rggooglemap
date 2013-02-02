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
 * Hook Examples' for the 'rggooglemap' extension.
 *
 * @author	Georg Ringer <http://www.rggooglemap.com/>
 */



require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');


class tx_rggm_fe extends tslib_pibase {
	var $cObj; // The backReference to the mother cObj object set at call time
	// Default plugin variables:
	var $prefixId 		= 'tx_rggm_fe';		// Same as class name
	var $scriptRelPath 	= 'class.tx_rggm_fe.php';	// Path to this script relative to the extension dir.
	var $extKey 		= 'rggooglemap';	// The extension key.

	var $pObj;
	var $conf;
	var $markerArray;
	var $calledBy;

	/**
	 * main function which executes all steps
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	array		modified marker array
	 */
	function main($markerArray, $conf) {
		$this->init($markerArray, $conf);
		$this->substituteMarkers($conf);

		return $this->markerArray;
	}

	/**
	 * initializes the configuration for the extension
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	void
	 */
	function init($markerArray, $conf) {
		$this->pi_loadLL(); // Loading language-labels
		$this->conf2 = $conf;

		$this->markerArray = $markerArray;
	}

	/**
	 * The function to manipulate the markerArray
	 *
	 * @param array $conf: configuration array
	 * @return array the modified $saveData array
	 */
	function substituteMarkers($conf) {
		// default settings for the map
		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);
		$key = $this->realConf->conf['rggm.']['mapKey'] ? $this->realConf->conf['rggm.']['mapKey'] : $this->confArr['googleKey'];
		$lng = $this->realConf->conf['rggm.']['mapLng'] ? $this->realConf->conf['rggm.']['mapLng'] : $this->confArr['startLong'];
		$lat = $this->realConf->conf['rggm.']['mapLat'] ? $this->realConf->conf['rggm.']['mapLat'] : $this->confArr['startLat'];
		$selected = '';


		// who called
		#####################
		# EXT ve_guestbook
		#####################

		if ($this->calledBy == 've_guestbook') {
			if ($this->realConf->conf['rggm.']['loadEverything'] == 1) {
				require_once(t3lib_extMgm::extPath('rggooglemap') . 'pi1/class.tx_rggooglemap_pi1.php');
				$this->rggm2 = t3lib_div::makeInstance('tx_rggooglemap_pi1');
				$this->markerArray['###INSERT_MAP###'] = $this->rggm2->showMap('', $this->realConf->conf['rggm.']);
			} else {
				$this->markerArray['###INSERT_MAP###'] = $this->getMap();
			}
			$this->markerArray['###TX_RGGMVEGUESTBOOK_LAT###'] = $this->conf['data']['tx_rggmveguestbook_lat'];
			$this->markerArray['###TX_RGGMVEGUESTBOOK_LNG###'] = $this->conf['data']['tx_rggmveguestbook_lng'];
			$this->markerArray['###UID###'] = $this->conf['data']['uid'];

			if ($this->conf['data']['tx_rggmveguestbook_lat'] !== '' && $this->conf['data']['tx_rggmveguestbook_lng'] !== '') {
				$this->markerArray['###SHOWONMAP###'] = '<a href="javascript:void(0)" onClick=myclick(' . $this->conf['data']['uid'] . ',' . $this->conf['data']['tx_rggmveguestbook_lng'] . ',' . $this->conf['data']['tx_rggmveguestbook_lat'] . ',"tx_veguestbook_entries") >' . $this->realConf->conf['rggm.']['showOnMap'] . '</a>';
			} else {
				$this->markerArray['###SHOWONMAP###'] = '';
			}


		#####################
		# EXT tt_news
		#####################
		} elseif ($this->calledBy === 'tt_news' && $this->realConf->conf['rggm.']) {
			$id = $GLOBALS['TSFE']->id;
			$uid = $this->conf['data']['uid'];

			// get from session
			$idList = $GLOBALS['TSFE']->fe_user->getKey('ses', 'rggmttnews2');
			$idList[$uid] = $uid;

			// save selected uids into session
			$GLOBALS['TSFE']->fe_user->setKey('ses', 'rggmttnews2', $idList);
			$GLOBALS['TSFE']->fe_user->storeSessionData();

			if ($this->conf['data']['tx_rggmttnews_lng'] !== '' && $this->conf['data']['tx_rggmttnews_lat'] !== '') {
				$this->markerArray['###SHOWONMAP###'] = '<a href="javascript:void(0)" onClick="myclick(' . $this->conf['data']['uid'] . ',' . $this->conf['data']['tx_rggmttnews_lng'] . ',' . $this->conf['data']['tx_rggmttnews_lat'] . ',\'tt_news\')" >' . $this->realConf->conf['rggm.']['showOnMap'] . '</a>';
			} else {
				$this->markerArray['###SHOWONMAP###'] = '';
			}
			if ($this->realConf->conf['rggm.']['detail'] == 1) {
				$this->markerArray['###INSERT_SINGLEMAP###'] = $this->singleMap($key);
			}


			#####################
			# EXT th_mailformplus
			#####################
		} elseif ($this->calledBy === 'th_mailformplus') {


			// delete a entry
			$postvars = t3lib_div::_GP('delete');
			if ($postvars && $this->recordBelongsToUser(intval($postvars['id']))) {
				$where = 'uid = ' . intval($postvars['id']);
				$table = 'tt_address';
				$query = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, array('deleted' => 1));
				header('Location: ' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . $this->pi_getPageLink($GLOBALS['TSFE']->id, '', ''));
			}

			$count = 0;

			// somebody logged in?
			if (is_array($GLOBALS['TSFE']->fe_user->user)) {

				$tempArray = array(
					'value_lastname' => 'last_name',
					'value_firstname' => 'first_name',
					'value_city' => 'city',
					'value_plz' => 'zip',
					'value_tel' => 'telephone',
					'value_email' => 'email',
					'value_street' => 'address',
					'value_additionalInformation' => 'comments',
				);
				foreach ($tempArray as $k => $v) {
					$this->markerArray['###' . $k . '###'] = $GLOBALS['TSFE']->fe_user->user[$v];
				}

				$myPois = $this->getMyPoi();
				$count = $myPois['count'];
				$this->markerArray['###INSERT_MYPOI###'] = $myPois['content'];
				$this->markerArray['###INSERT_USERINFO###'] = '<input type="hidden" name="user_id" value="' . $GLOBALS['TSFE']->fe_user->user['uid'] . '" />';
				$this->markerArray['###INSERT_SUBMIT###'] = ($count<$this->realConf->conf['rggm.']['maxPerUser']) ? $this->realConf->conf['rggm.']['submitField'] : $this->realConf->conf['rggm.']['submitFieldMaximum'];
			}

			// if edit-postvars
			$postvars = t3lib_div::_GP('edit');
			if ($postvars && $this->recordBelongsToUser(intval($postvars['id']))) {
				$recordId = intval($postvars['id']);
				// query to fill the fields with values from the DB
				$field = '*';
				$where = 'hidden = 0 AND deleted = 0 AND uid = ' . $recordId;
				$table = 'tt_address';
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where);
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

				// fill mailform with existing values of DB
				$tempArray = array(
					'value_lastname' => 'name',
					'value_plz' => 'zip',
					'value_lat' => 'tx_rggooglemap_lat',
					'value_lng' => 'tx_rggooglemap_lng',
					'value_tel' => 'phone',
					'value_email' => 'email',
					'value_street' => 'address',
					'value_tel' => 'phone',
					'value_additionalInformation' => 'description',
					'value_plz' => 'zip'
				);
				foreach ($tempArray as $k => $v) {
					$this->markerArray['###' . $k . '###'] = $row[$v];
				}

				$lat = $row['tx_rggooglemap_lat'] ? $row['tx_rggooglemap_lat'] : $lat;
				$lng = $row['tx_rggooglemap_lng'] ? $row['tx_rggooglemap_lng'] : $lng;
				$selected = $row['tx_rggooglemap_cat2'];
				$this->markerArray['###INSERT_RECORDID###'] = '<input type="hidden" name="uniqueid" value="' . $recordId . '" />';

				$link = ($count<$this->realConf->conf['rggm.']['maxPerUser']) ? $this->cObj->typolink($this->realConf->conf['rggm.']['newEntry'], Array('parameter' => $GLOBALS['TSFE']->id)) : '';
				$this->markerArray['###INSERT_WARNING###'] = $this->realConf->conf['rggm.']['insertWarning'].$link.'<br />';
				$this->markerArray['###INSERT_SUBMIT###'] = ($count<=$this->realConf->conf['rggm.']['maxPerUser']) ? $this->realConf->conf['rggm.']['submitFieldChange'] : '';
			}

			$this->markerArray['###INSERT_MAP###'] = $this->getMapInline($key, $lng, $lat);
			$this->markerArray['###INSERT_CAT###']  = $this->getCategories($selected);
		}
	}

	/**
	 * creates a map for a single/detail page.
	 * Getting uid from postvars and lng&lat from DB
	 *
	 * @param string $key: google map key
	 * @return array the modified $saveData array
	 */
  function singleMap($key) {
  		// generic function
      require_once( t3lib_extMgm::siteRelpath('rggooglemap').'res/class.tx_rggooglemap_table.php');
	    $this->generic = t3lib_div::makeInstance('tx_rggooglemap_table');

      // making the query
      $postvars = $this->postvars = t3lib_div::_GP($this->realConf->conf['rggm.']['detailUidParam']);
      $id = (int)$postvars[$this->realConf->conf['rggm.']['detailUidParamID']];

      $table = $this->realConf->conf['rggm.']['detailTable'];
      $where = 'hidden = 0 AND deleted = 0 AND uid='.$id;
      $res = $this->generic->exec_SELECTquery('*',$table,$where,'','','');
      $row=array_shift($res);
         /*
      // if lng+lat available
      if ($row['lng']!='' && $row['lat']!='') {
        // url for the POI content
        $url = t3lib_div::getIndpEnv('TYPO3_SITE_URL').'index.php?id='.$GLOBALS["TSFE"]->id.'&type=500';
        $url.= ($GLOBALS['TSFE']->sys_language_uid != 0) ? '&L='.$GLOBALS['TSFE']->sys_language_uid : '';

        // if ==1, pi1 is loaded to get all tabs, otherwise lower load, lower content
      #  if ( $this->realConf->conf['rggm.']['fullDetail'] ==1) {
    	    require_once(t3lib_extMgm::extPath('rggooglemap').'pi1/class.tx_rggooglemap_pi1.php');
          $this->rggm2 = t3lib_div::makeInstance('tx_rggooglemap_pi1');
       # }

        // set an icon, if none set, take the default icon
        if ($this->realConf->conf['rggm.']['detailIcon']) {
          $icon = 'var icon = new GIcon();
                    icon.image = "'.$this->realConf->conf['rggm.']['detailIcon'].'";
                    icon.iconAnchor = new GPoint(6, 20);
                    icon.infoWindowAnchor = new GPoint(5, 1);
                    var marker = new GMarker (center,icon);
                  ';
        } else {
          $icon = 'var marker = new GMarker (center);';
        }

    	   // the needed js for the googlemap
        $GLOBALS['TSFE']->additionalHeaderData['b121211'] = '<script src="http://maps.google.com/maps?file=api&amp;v=2.61&amp;key='.$key.'" type="text/javascript"></script>';
        $GLOBALS['TSFE']->additionalHeaderData['b121212ttnews'] = '<script type="text/javascript">

          function makeMap() {
            if (true /*GBrowserIsCompatible()*/) {
              var map = new google.maps.Map(document.getElementById("mapDetail"));
              var center = new google.maps.LatLng('.$row['lat'].', '.$row['lng'].');
              map.setCenter(center, 3);

          '.$icon.'

            google.maps.event.addListener(marker, "click", function() {
            var url = "'.$url.'&no_cache=1&tx_rggooglemap_pi1[detail]='.$row['uid'].'&tx_rggooglemap_pi1[table]='.$table.'";
              var req = GXmlHttp.create();
              req.open("GET", url, true );
              req.onreadystatechange = function() {
                if ( req.readyState == 4 ) {
                  marker.openInfoWindowHtml( req.responseText );
                }
              };
              req.send(null);

            });
            map.addOverlay(marker);

            }
          }
          </script>';
  		$map = '<div style="height:'.$this->realConf->conf['rggm.']['mapHeight'].'px;width:'.$this->realConf->conf['rggm.']['mapWidth'].'px" id="mapDetail"></div>';
  	} else {
      $map = '';
    }
    */
      		// save selected uids into session
  		$GLOBALS["TSFE"]->fe_user->setKey('ses', 'rggmttnews2', $id);
  		$GLOBALS['TSFE']->fe_user->storeSessionData();

    require_once(t3lib_extMgm::extPath('rggooglemap').'pi1/class.tx_rggooglemap_pi1.php');
    $this->rggm2 = t3lib_div::makeInstance('tx_rggooglemap_pi1');
    $map = $this->rggm2->showMap('',$this->realConf->conf['rggm.']);

    return $map;
	}


	/**
	 * shortest function to get a map
	 *
	 * @return the map
	 */
	function getMap() {
  	$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);

    $GLOBALS['TSFE']->additionalHeaderData['b121211'] = '<script src="http://maps.google.com/maps?file=api&amp;v=2.61&amp;key='.$this->confArr['googleKey'].'" type="text/javascript"></script>';
    $GLOBALS['TSFE']->additionalHeaderData['b121212'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('rggooglemap').'res/rggm_import.js"></script>';
		$map = '<div style="height:'.$this->realConf->conf['rggm.']['insertMapHeight'].'px;width:'.$this->realConf->conf['rggm.']['insertMapWidth'].'px" id="mapLoad"></div>';
    return $map;
  }

	/**
	 * creates a map for entering data via FE
	 *
	 * @param string $key: google map key
	 * @param string $lng: start longtitude
	 * @param string $lat: start latitude
	 * @return array the modified $saveData array
	 */
  function getMapInline($key, $lng, $lat) {
    $GLOBALS['TSFE']->additionalHeaderData['b121211'] = '<script src="http://maps.google.com/maps?file=api&amp;v=2.61&amp;key='.$key.'" type="text/javascript"></script>';
    $GLOBALS['TSFE']->additionalHeaderData['b121212'] = '<script type="text/javascript">
function makeMap() {
  if (true /*GBrowserIsCompatible()*/) {
		var myOptions = {
			center: new google.maps.LatLng('.$lat.', '.$lng.'),
			zoom: ' . $this->confArr['startZoom'] . ',
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			scaleControl: true,
		  	overviewMapControl: true,
			overviewMapControlOptions: {
				opened: true
			}
		};
		var map2 = new google.maps.Map(document.getElementById("mapLoad"), myOptions);
		var geocoder2 = new google.maps.Geocoder();

      // ============================
      //var pos = new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(0,0));
      //pos.apply(document.getElementById("geocode"));
      //map2.getContainer().appendChild(document.getElementById("geocode"));
      // ============================

		var marker = new google.maps.Marker({
			position: center,
			draggable: true,
			map: map2
		});
		map2.enableDragging();

		google.maps.event.addListener(marker, "dragstart", function() {
		map2.closeInfoWindow();
    });

    google.maps.event.addListener(marker, "dragend", function() {
      document.getElementById("rggm_liad_lat").value = marker.getPoint().lat();
      document.getElementById("rggm_liad_lng").value = marker.getPoint().lng();
    });

    google.maps.event.addListener(map2, "moveend", function() {
      document.getElementById("rggm_liad_lat").value = marker.getPoint().lat();
      document.getElementById("rggm_liad_lng").value = marker.getPoint().lng();

    });

    google.maps.event.addListener(map2, "click", function(overlay, point) {
        marker.setPoint(point);
        document.getElementById("rggm_liad_lat").value = marker.getPoint().lat();
        document.getElementById("rggm_liad_lng").value = marker.getPoint().lng();
    });

  }

}
</script>';
		$map = '<div style="height:'.$this->realConf->conf['rggm.']['insertMapHeight'].'px;width:'.$this->realConf->conf['rggm.']['insertMapWidth'].'px" id="mapLoad"></div>';
    return $map;

  }

	/**
	 * Get all available categories
	 *
	 * @param int $selected: the selected value
	 * @return <select>-field with all categories
	 */
  function getCategories($selected) {
    $cat = '<select name="poi_cat" class="formInput validate-selection formSelect">';

  // query for the categories
    $table = 'tx_rggooglemap_cat';
    $field = '*';
    $where = 'deleted = 0 AND hidden = 0 '.$this->realConf->conf['rggm.']['catWhere'];
    $orderBy = $this->realConf->conf['rggm.']['catOrderBy'] ? $this->realConf->conf['rggm.']['catOrderBy'] : 'title ASC';
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy);
    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $active = ($selected==$row['uid']) ? ' selected="selected" ' : '';
      $cat.= '<option '.$active.'class="formOption" value="'.$row['uid'].'"> '.$row['title'].'</option>';
    }

    $cat.= '</select>';
    return $cat;
  }

	/**
	 * Get all records of a FE-User with a edit & delete link
	 *
	 * @return the records
	 */
  function getMyPoi() {
    $poi = '	<div class="row"><label for="mypoi">Meine Punkte</label><div class="formText"><ul>';
    // query for the POIs of a user
    $table = 'tt_address';
    $field = '*';
    $where = 'deleted = 0 AND hidden = 0 AND tx_rgthmailformplus_feuser ='.$GLOBALS["TSFE"]->fe_user->user['uid'];
    $orderBy = $this->realConf->conf['rggm.']['myPoiOrderBy'] ? $this->realConf->conf['rggm.']['myPoiOrderBy'] : 'name ASC';
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy);
    $i = 0;
    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
      $i++;
        $conf = Array('parameter' => $GLOBALS['TSFE']->id, 'additionalParams' => '&edit[id]='.$row['uid']);
        $linkEdit = $this->cObj->typolink($row['name'],$conf);
        $conf = Array('parameter' => $GLOBALS['TSFE']->id, 'additionalParams' => '&delete[id]='.$row['uid']);
        $linkDelete = $this->cObj->typolink('<img src="'.t3lib_extMgm::siteRelPath('rggooglemap').'res/icons/icon_delete.gif" alt="Delete POI" onclick="return confirm(\'Soll dieser POI wirklich gelöscht werden?\');"/>',$conf);

        $poi.= "<li>$linkEdit $linkDelete</li>";
    }
    $poi.= '</ul></div></div>';
    $poi = ($i>0) ? $poi : '';
    $myPois = Array('count'=>$i, 'content'=>$poi);
    return $myPois;
  }

	/**
	 * Checks if a record belongs to a user
	 * neccessary for editing & deleting a record
	 *
	 * @param int $id: user id
	 * @return true if record belongs to a user, otherwise false
	 */
	function recordBelongsToUser($id) {
	 if ($GLOBALS["TSFE"]->fe_user->user['uid'] == '') return false; # no login user
   $field = 'uid';
   $where = 'hidden = 0 AND deleted = 0 AND tx_rgthmailformplus_feuser ='.$GLOBALS["TSFE"]->fe_user->user['uid'].' AND uid = '.$id;
   $table = 'tt_address';
   $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where);
   $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
   if ($row['uid']!='') return true;
   else return false;
  }


	/***********************************************
	 *
	 * Hook Connectors
	 *
	 **********************************************/

	/**
	 * connects into the different extensions and fills out the markers
	 *
	 * @param	array		an array of markers coming from the extension
	 * @param	array		the current record of the extension
	 * @param	array		the configuration coming from the extension
	 * @param	object		the parent object calling this method
	 * @return	array		processed marker array
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj'); // local cObj.

		$this->conf['data'] = $row;
		$this->pObj = &$pObj;
		$this->realConf = $pObj;
		$this->calledBy = $pObj->extKey; //who is calling?
    if ($this->realConf->conf['rggm.']) return $this->main($markerArray, $lConf);
    else return $markerArray;
	}

	/**
	 * connects into the different extensions and fills out the markers II
	 * currenty only needed for tt_news > MAP
	 *
	 * @param	object		the parent object calling this method
	 * @param	array		an array of markers coming from the extension
	 * @return	array		processed marker array
	 */
	function extraGlobalMarkerProcessor(&$pObj, $markerArray) {
    $this->cObj = t3lib_div::makeInstance('tslib_cObj'); // local cObj.
    $this->realConf = $pObj;
    $this->realConf->query['selectFields'] = ' tt_news.uid';

    // just if plugin.tt_news.rggm
    if ($this->realConf->conf['rggm.']) {
   		$res = $pObj->exec_getQuery('tt_news', $this->realConf->query); //get query for list contents
  		$uidList = Array();
      while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
        $uidList[] = $row['uid'];
  		}



      // get from session
  		$GLOBALS["TSFE"]->fe_user->setKey('ses', 'rggmttnews2',  implode(',',$uidList));
  		$GLOBALS['TSFE']->fe_user->storeSessionData();

      require_once(t3lib_extMgm::extPath('rggooglemap').'pi1/class.tx_rggooglemap_pi1.php');
      $this->rggm2 = t3lib_div::makeInstance('tx_rggooglemap_pi1');
      $this->realConf->conf['rggm.']['whereUid'] = implode(',',$uidList);
      $this->realConf->conf['rggm.']['recursive'] = 3;



      $markerArray['###INSERT_MAP###'] =$this->rggm2->showMap('',$this->realConf->conf['rggm.']);
    }

    return $markerArray;
	}

	/**
	 * connects to the hook in ve_guestbook to pre process a comment entry
	 * to save lng & lat & cat into the DB
	 *
	 * @param array $saveData: the data which will be written to the DB
	 * @param object $pObj: parent ve_guestbook object
	 * @return array the modified $saveData array
	 */
	function preEntryInsertProcessor($saveData, &$pObj) {
		$this->pObj = $pObj;
		$this->postvars = $pObj->postvars;
		$this->init(array(), array());

    $saveData['tx_rggmveguestbook_lat'] = $this->postvars['tx_rggmveguestbook_lat'];
    $saveData['tx_rggmveguestbook_lng'] = $this->postvars['tx_rggmveguestbook_lng'];
    $saveData['tx_rggmveguestbook_cat'] = $this->postvars['tx_rggooglemap_cat2'];

		return $saveData;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/lib/class.tx_rggm_fe.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/lib/class.tx_rggm_fe.php']);
}

?>