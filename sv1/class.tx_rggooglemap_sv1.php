<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Georg Ringer <typo3 et ringerge dot org>
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

require_once(PATH_t3lib.'class.t3lib_svbase.php');


/**
 * Service "tt_address for rggooglemap " for the "rggooglemap" extension.
 *
 * @author    Georg Ringer <typo3@ringerge.org>
 * @package    TYPO3
 * @subpackage    tx_rggooglemap
 */
class tx_rggooglemap_sv1 extends t3lib_svbase {
	var $prefixId = 'tx_rggooglemap_sv1'; // Same as class name
	var $scriptRelPath = 'sv1/class.tx_rggooglemap_sv1.php'; // Path to this script relative to the extension dir.
	var $extKey = 'rggooglemap'; // The extension key.	
	
	/**
	* Initialization of the class, not needed in this case
	*
	*/
	function init()	{
		$available = parent::init();
		return $available;
	}
	
	
	/**
	 * Get the translated fields. This is needed to perform queries without knowing
	 * the exact field names. "Translate" every field by setting the key to the 
	 * name you want and the field to the original field name		
	 *
	 * @param	string		$field: Give a fieldname to get the translated one
	 * @return	mixed	With a given field return the translated fieldname, otherwise
	 * 	all translated fields as a table	
	 */
	function getTable($field='') {
		$tbl['lng'] = 'tx_rggooglemap_lng';
		$tbl['lat'] = 'tx_rggooglemap_lat';  
		$tbl['rggmcat'] = 'tx_rggooglemap_cat';            
		$tbl['rggmtitle'] = 'name';
		
		if ($field) {
			return ($tbl[$field]) ? $tbl[$field] : $field;
		} else {
			return $tbl;
		}
	}    


	/**
	 * Replace original fields with the new ones
	 *
	 * @param	string		$string: A lies of fields
	 * @return	string translated fields
	 */	
	function mergeFields($string) {
		$whereFields = $this->getTable();
		$whereOld = array_keys($whereFields);
		$whereNew = array_values($whereFields);
		return str_replace($whereOld, $whereNew, $string);
	}


	/**
	 * Set the addressFields of a table, this is needed for automatic geocoding 
	 * of the table. Order is important, needs to be like Steet,City,Country	
	 * 
	 * If the table doesn't represent an address (e.g. mountains), return ''!
	 * 	 	
	 *
	 * @return	string List of fields which represent an address
	 */	
	function addressFields() {
		$address = 'address,city,zip,country';
		
		return $address;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/sv1/class.tx_rggooglemap_sv1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/sv1/class.tx_rggooglemap_sv1.php']);
}

?>