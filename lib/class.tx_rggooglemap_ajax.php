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
 * Ajax request handler for the 'rggooglemap' extension.
 *
 * @author	Georg Ringer (just2b) <http://www.ringer.it>
 * @package	TYPO3
 * @subpackage	tx_rggooglemap
 */
 
require_once('init.php');

class tx_rggooglemap_ajax {

	/**
	 * Handles the request for a new record
	 *
	 * @param	string		$params: Params of the request
	 * @param	obj		$ajaxObj: Request object
	 * @return	$ajaxObj with the result
	 */
	public function getMap($params, $ajaxObj) {

		// return json
		$ajaxObj->setContentFormat('json');
		
		// some initializations
		$vars 				= t3lib_div::_POST('rggm'); // post vars
		$tmp_confArr	= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);		
		$errorList 		= array();
		
		// security check
		$table 		= trim($vars['table']);
		$title		= trim($vars['title']);
		$cat			= intval($vars['cat']);
		$pid			= intval($vars['pid']);
		$latlng 	= explode(',', $vars['latlng']);
		$lat 			= floatval($latlng[0]);
		$lng 			= floatval($latlng[1]);

		if (!t3lib_div::inList($tmp_confArr['tables'], $table)) {
			$errorList[] = 'tablenotfound';
		} elseif($lat == 0 || $lng == 0) {
			$errorList[] = 'latlngwrong';
		} elseif($title == '') {
			$errorList[] = 'notitle';
		}
		
		// if all values are present and correct
		if (count($errorList) == 0) {

			// init the generic functions
			require_once('class.tx_rggooglemap_table.php');
			$this->generic = t3lib_div::makeInstance('tx_rggooglemap_table');
	
			// check if a record like this already exists
			$alreadyExists = false;
			$where = 'lat!="" AND lng!="" AND lat="'.$lat.'" AND lng = "'.$lng.'" AND deleted=0';
			$res = $this->generic->exec_SELECTquery('uid', $vars['table'], $where);

			if (count($res) > 0) {
				$errorList[] = 'recordexists';
			}
			
			// insert the new record
			if (count($errorList) == 0 && is_object($serviceObj = t3lib_div::makeInstanceService('rggmData',$table))) {
				// get fields from service
	      $latField		= $serviceObj->getTable('lat');
	      $lngField		= $serviceObj->getTable('lng');
	      $titleField	= $serviceObj->getTable('rggmtitle');
	      $catField		= $serviceObj->getTable('rggmcat');
				
				$insert = array();
				$insert['crdate']			= time();
				$insert['tstamp']			= time();
				$insert['cruser_id']	= $GLOBALS['BE_USER']->user['uid'];
				$insert[$latField]		= $lat;
				$insert[$lngField]  	= $lng;
				$insert[$titleField]  = $title;
				$insert[$catField]		= $cat;
				$insert['pid']				= $pid;
				
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $insert);
			}	
		}

		// response
		$GLOBALS['LANG']->includeLLFile('EXT:rggooglemap/locallang_flex.xml');
		$generalStyles = 'color:#fff;margin:5px 30px;width:400px;font-weight:bold;text-align:center;padding:5px 10px;';

		if (count($errorList) == 0) {
			$result = '<div style="'.$generalStyles.'background:green;">'.$GLOBALS['LANG']->getLL('usermap.response.success').'</div>';
			$ajaxObj->setContent(array('result' => $result));
		} else {
			$result = '';
			foreach ($errorList as $key) {
				$result.= $GLOBALS['LANG']->getLL('usermap.error.'.$key).'<br />';
			}
			
			$result = '<div style="'.$generalStyles.'background:#FF7F90;">'.$result.'</div>';			
			$ajaxObj->setContent(array('error' => $result));
		}

	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/lib/class.tx_rggooglemap_ajax.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/lib/class.tx_rggooglemap_ajax.php']);
}

?>