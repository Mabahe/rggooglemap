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
                var $prefixId = 'tx_rggooglemap_sv1';        // Same as class name
                var $scriptRelPath = 'sv1/class.tx_rggooglemap_sv1.php';    // Path to this script relative to the extension dir.
                var $extKey = 'rggooglemap';    // The extension key.	
				/**
	 
   
   * [Put your description here]
	 *
	 * @return	[type]		...
	 */
				function init()	{
					$available = parent::init();
	
					// Here you can initialize your class.
	
					// The class have to do a strict check if the service is available.
					// The needed external programs are already checked in the parent class.
	
					// If there's no reason for initialization you can remove this function.
					return $available;
				}
	
				/**
	 * [Put your description here]
	 * performs the service processing
	 *
	 * @param	string		Content which should be processed.
	 * @param	string		Content type
	 * @param	array		Configuration array
	 * @return	boolean
	 */

        function getTable($field='') {
            $tbl['lng'] = 'tx_rggooglemap_lng';
            $tbl['lat'] = 'tx_rggooglemap_lat';  
            $tbl['rggmcat'] = 'tx_rggooglemap_cat2';            
            $tbl['rggmtitle'] = 'name';
       
            if ($field) return ($tbl[$field]) ? $tbl[$field] : $field;
            else return   $tbl;
        }    
        
        function mergeFields($string) {
          $whereFields = $this->getTable();
          $whereOld = array_keys($whereFields);
          $whereNew = array_values($whereFields);
          return str_replace($whereOld, $whereNew, $string);
        }
        
        function addressFields() {
          $address = 'address,city,zip,country';

          return $address;
        }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggmsvttaddress/sv1/class.tx_rggmsvttaddress_sv1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggmsvttaddress/sv1/class.tx_rggmsvttaddress_sv1.php']);
}

?>
