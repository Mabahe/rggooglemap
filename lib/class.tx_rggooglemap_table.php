<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Georg Ringer <typo3 et ringer ge dot org>
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
 * TODO: Add description.
 *
 * @category    Library
 * @package     TYPO3
 * @subpackage  tx_rggooglemap
 * @author      Georg Ringer (just2b) <http://www.ringer.it>
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class tx_rggooglemap_table {
	var $prefixId 		= 'tx_rggooglemap_table';		// Same as class name
	var $scriptRelPath 	= 'res/class.tx_rggooglemap_table.php';	// Path to this script relative to the extension dir.
	var $extKey 		= 'rggooglemap';	// The extension key.
	var $myService;

	/**
	* Initialization of the table object through serice extensions
	* @param	string	Table
	*/  
	function init($table) {
		// use 'auth' service to find the user
		// first found user will be used
		
		$serviceChain='';
		while (is_object($serviceObj = t3lib_div::makeInstanceService('rggmData', $table, $serviceChain))) {
			$serviceChain.=','.$serviceObj->getServiceKey();
			if ($tempuser=$serviceObj->init()) {
				// service found, just stop to search for more
				$this->myService =  $serviceObj;
				
				break;
			}
		}  
		return '';
	}
		
		
		
	/*
	* selectquery
	*
	*/    	
	function exec_SELECTquery($select,$table,$where,$groupBy='',$orderBy='',$offset='',$debug=0) {
		global $TYPO3_DB;
		$out = array();
		$test = array(); 
		$debugOut = array();
		$out2 = '';
		$count = 0;
		
		// split tables
		$tables = explode(',',$table);
		$tableCount = count($tables);
		
		// query for each table
		foreach ($tables as $key=>$singleTable) {
			// table
			$singleTable = trim($singleTable);
			
			// get the object of the table
			$this->init($singleTable);
			
			// select
			$queryFields = ($select!='*') ? $this->myService->mergeFields($select) : '*';
			$debugOut[$singleTable]['fields'] = $queryFields;
			// where
			$whereFields = $this->myService->mergeFields($where);
			$debugOut[$singleTable]['where'] = $whereFields;
			
			
			if ($debug==0) {			
				// allfields
				$allFields = $this->myService->getTable();
				
				// the real query finally
				if ($tableCount==1) {
					$orderBy = $this->myService->mergeFields($orderBy);
					// if there is just 1 table, use the offset & orderBy without further code
					$res = $TYPO3_DB->exec_SELECTquery($queryFields,$singleTable,$whereFields,$groupBy,$orderBy,$offset);      
				} else {
					// if more tables, offset & orderBy has to be handeled myself
					$res = $TYPO3_DB->exec_SELECTquery($queryFields,$singleTable,$whereFields);
				}
				while($row = $TYPO3_DB->sql_fetch_assoc($res)) {	
					// get the correct offset. time consuming? we will see
					$out[$singleTable.'#'.$row['uid']] = $row;
					
					// mapping
					foreach ($allFields as $key=>$value) {
						if (strpos($queryFields, $key) || $queryFields == '*') {
							$out[$singleTable.'#'.$row['uid']][$key] = $row[$value];
						}  	
							$out[$singleTable.'#'.$row['uid']][$key] = $row[$value];
					}     
					
					// additional information
					$out[$singleTable.'#'.$row['uid']] ['table'] = $singleTable;
					
					// count for the offset needed
					$count++;     		
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($res);
			}
		}
		
		
		// sorting if multiple tables
		if ($tableCount>1) {
			// ASC || DESC
			$direction = stristr ($orderBy, ' DESC');
			// order field
			$orderField =  explode(' ',$orderBy);
			
			// if no orderfield, no sorting is needed
			if ($orderField!='') {
				$sortArray = array();
				
				// building the sort array
				foreach($out as $key => $array) {
					$sortArray[$key] = $array[$orderField{0}]; # sorting criteria
				} 
				
				// sorting process
				if ($direction) {
					array_multisort($sortArray, SORT_DESC, SORT_REGULAR, $out); # unsorted > sorted
				} else {
					array_multisort($sortArray, SORT_ASC, SORT_REGULAR, $out); # unsorted > sorted
				}
			}
			
			// offset
			if ($offset!='') {
				$split = t3lib_div::trimExplode(',',$offset);
				if (count($split) == 1) {
					$out = array_slice($out, 0, $split[0]);
				} else {
					$out = array_slice($out, $split[0], $split[1]);
				}
			}
		
		} # end tableCount
		
		if ($debug==1) {
			return $debugOut;
		}
		
		return $out; 
		
	}
		
	function exec_COUNTquery($table,$where) {
		global $TYPO3_DB;
		
		$tables = explode(',',$table);
		$tableCount = count($tables);
		$count = 0;
		
		// query for each table
		foreach ($tables as $key=>$singleTable) {
			// table
			$singleTable = trim($singleTable);
			$this->init($singleTable);
			// where
			# $whereFields = $this->mergeFields($singleTable,$where);
			$whereFields = $this->myService->mergeFields($where);
			
			// the real query finally
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('count(*)',$singleTable,$whereFields); 
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
			
			$count+=$row[0];   
			
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		
		return $count;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/lib/class.tx_rggooglemap_table.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/lib/class.tx_rggooglemap_table.php']);
}

?>