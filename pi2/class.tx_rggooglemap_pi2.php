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
 * Plugin '' for the 'rggooglemap' extension.
 *
 * @author	Georg Ringer <typo3@ringerge.org>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_rggooglemap_pi2 extends tslib_pibase {
	var $prefixId = 'tx_rggooglemap_pi1';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_rggooglemap_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey = 'rggooglemap';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website (TypoTag)
	 */
	function main($content,$conf)	{
		$tag_content = $this->cObj->getCurrentVal();
		$id = array_keys($this->cObj->parameters);
    
    // just id set: <map ID>text</map>
    if (count($id)==2) {  
      $mapId = $conf['mapId']; 
      $tableType = $conf['tableType'];
    }
    // id + target-id set: <map ID TARGET>text</map>
    if (count($id)==3) { 
      $mapId = $id[1]; 
      $tableType = $conf['tableType'];	
    }
    // id + target + table set: <map ID TARGET TABLE>text</table>
    if (count($id)==4) { 
      $mapId = $id[1]; 
      $tableType = $id[2];
    }
    
    // underscore dissappears > get correct table name 
    switch ($tableType) {
      case 'ttaddress': $table = 'tt_address'; break;
      case 'feusers': $table = 'fe_users'; break;
      default: $table = 'tt_address'; 	break;
    }
    
    // Debug
    #t3lib_div::debug($id);
    #echo $table.': '.$id[0].'@ '.$mapId.' ('.$tag_content.') '.count($id).'<hr>';
    		
    
    $field = 'tx_rggooglemap_lat, tx_rggooglemap_lng, uid';
    $where = 'deleted = 0 AND tx_rggooglemap_display = 1 AND tx_rggooglemap_lat!= \'\' AND tx_rggooglemap_lng!= \'\' AND uid ='.$id[0]; 
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$limit='');
    
    $link = $tag_content;

    if($res) {
    	$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    	if ($row['tx_rggooglemap_lng']!= '' || $row['tx_rggooglemap_lat']!= '') {
    	   if (count($id) < 4) {
    	    $paramArr = Array('tx_rggooglemap_pi1[poi]'=>$row['uid']);
    	    $paramArr = Array('poi'=>$row['uid']);
         } else {
          $paramArr =  Array('tx_rggooglemap_pi1[poi]'=>$row['uid'], 'tx_rggooglemap_pi1[table]'=>$table);
          $paramArr =  Array('poi'=>$row['uid'], 'tbl'=>$table);
    	   }
    	   $link = $this->pi_linkToPage($tag_content, $mapId, $target='', $paramArr);
    	   $link = $this->pi_linkTP_keepPIvars($tag_content, $paramArr, 1,1,$mapId);
      }
    }

    
#    	    $link = $this->pi_linkToPage($tag_content, $mapId, $target='', Array('lng'=>$row['tx_rggooglemap_lng'], 'lat'=>$row['tx_rggooglemap_lat']));


   return '<span class="maplink">'.$link.'</span>';
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/pi2/class.tx_rggooglemap_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/pi2/class.tx_rggooglemap_pi2.php']);
}

?>
