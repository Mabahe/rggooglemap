<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Georg Ringer (just2b) <http://www.just2b.com>
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
class tx_rggooglemap_tcemainprocdm {


	/**
	 * Set coordinates for a table if the table is configured to and no coordinates are set yet
	 *
	 * @param	string		$status mode of change 
	 * @param	string		$table the table which gets changed
	 * @param	string		$id uid of the record
	 * @param	array		$fieldArray the updateArray
	 * @param	array		$this obj    	 
	 * @return	an updated fieldArray()
	 */
  function processDatamap_postProcessFieldArray ($status, $table, $id, &$fieldArray, &$that) {

    // get settings from the EM
    $tmp_confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);
    
    // service for this table
    $serviceObj = t3lib_div::makeInstanceService('rggmData',$table);

    // if autosearch is enabled and there is a service for that table    
    if ($tmp_confArr['autoGeocode']!=1 || !is_object($serviceObj)) {
      return $fieldArray;
    }

    // get the lat/lng fields of that table
    $lat = $serviceObj->getTable('lat');
    $lng = $serviceObj->getTable('lng');
    
    // if there is geocoding available
    $geocodeFields = $serviceObj->addressFields();

    // if lat/lang is in fieldArray, user wants to set it himself
    if ($geocodeFields!="" && (!isset($fieldArray[$lat]) && !isset($fieldArray[$lng]))) {

      // get the record
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,'.$geocodeFields.','.$lat.','.$lng,$table,'uid = '.$id);
      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

      // if the lat/lng fields are empty
      if ($row[$lat]=="" && $row[$lng]=="") {

        // build the string for the search, in the correct order with helping array
        $address = explode(',',$geocodeFields);
        $geocode = Array();
        foreach ($address as $key=>$value) {
        	$geocode[] = $row[$value];
        }
        $geoAdress = implode(',',$geocode);
       
        // call google service
        $url = 'http://maps.google.com/maps/geo?q='.urlencode($geoAdress).'&output=csv&key='.$tmp_confArr['googleKey'];
        $response=stripslashes(file_get_contents($url));
  
        // determain the result
        $response = explode(',',$response);
        
        // if there is a result
        if ($response[0]=='200' && $response[2]!= '' && $response[3] != '') {
          // add the coordinates to the updateArray
          $fieldArray[$lat] = $response[2]; 
          $fieldArray[$lng] = $response[3];

        }
      } 
    } 
    
    return $fieldArray;
  }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/lib/class.tx_rggooglemap_tcemainprocdm.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/lib/class.tx_rggooglemap_tcemainprocdm.php']);
}

?>