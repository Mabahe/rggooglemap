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

require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Plugin '' for the 'rggooglemap' extension.
 *
 * @category    Plugin
 * @package     TYPO3
 * @subpackage  tx_rggooglemap
 * @author      Georg Ringer (just2b) <http://www.ringer.it>
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
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
		$linkText = $this->cObj->getCurrentVal();
		$params = $this->cObj->parameters;
		unset($params['allParams']); // unset this key to count correctly
		
		$params = array_keys($params);
		$tableType = '';


		// just id set: <map ID>text</map>
		if (count($params) == 1) {
			
			$tableType = $conf['tableType'];

		// id + target-id set: <map ID TARGET>text</map>
		} elseif (count($params) == 2) {
			
			// if 2nd param is no integer, assume it is the tablename (or its given name by TS)
			if (intval($params[1]) == 0) {
				$tableType	= $params[1];
				$mapId 			= $this->cObj->stdWrap($conf['mapId'], $conf['mapId.']);
			} else {
				$mapId 			= $params[1];
			}
			
		// id + target + table set: <map ID TARGET TABLE>text</table>
		} elseif (count($params) == 3) {
			$mapId 			= $params[1]; 
			$tableType	= $params[2];
		}

		// Table
		$confArr		= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);
		$tableList	= t3lib_div::trimExplode(',', $confArr['tables']);

		if ($tableType != '' && $conf['tables.'][$tableType] != '') {
			$table 			= $conf['tables.'][$tableType];
		} else {
			$table			= $tableList[0];
		}
		
		// Check if the minium requirements are fulfilled
		if ($table =='' || intval($params[0]) == 0) {
			return $link;
		} 

		// get the generic query class
		require_once( t3lib_extMgm::siteRelpath('rggooglemap').'lib/class.tx_rggooglemap_table.php');
		$this->generic = t3lib_div::makeInstance('tx_rggooglemap_table');

		// query
		$where = 'deleted = 0 AND hidden=0 AND lat!= "" AND lng!= "" AND uid ='.$params[0];

		$res = $this->generic->exec_SELECTquery('uid, lng, lat',$table,$where);
		$row=array_shift($res);

		if (intval($row['lng'])!=0 && intval($row['lat'])!=0) {

			// generate the link
			// use a js link if the map is on the same page
			if ($conf['useJSlinkOnSamePage'] == 1 && $GLOBALS['TSFE']->id == $mapId) {
				$link = '<a href="javascript:void(0);" onclick="myclick('.$row['uid'].','.$row['lng'].','.$row['lat'].',"'.$table.'");">'.$linkText.'</a>';
			
			// generate a typolink
			} else {
				$linkConf = $conf['link.'];
				$linkConf['parameter'] = $mapId;
				$linkConf['additionalParams'] .= '&tx_rggooglemap_pi1[poi]='.$row['uid'];
				
				// add tablename only if it is no the default table
				if ($table != $tableList[0]) {
					$linkConf['additionalParams'] .= '&tx_rggooglemap_pi1[table]='.$table;
				}
			
				$link = $this->cObj->typolink($linkText, $linkConf);	
			}
	
		}
		
		// output the link if generated
		if (!empty($link)) {
			$link = '<span class="maplink">'.$link.'</span>';
		} else {
			$link = $linkText;
		}
		
		return $link;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/pi2/class.tx_rggooglemap_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/pi2/class.tx_rggooglemap_pi2.php']);
}

?>