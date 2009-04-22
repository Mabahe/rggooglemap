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
 * Service to get the Geo Codes from Google
 */
class tx_rggooglemap_table_service {
	/**
	 * Get the right table information
	 * @param	string	Table
	 * @return	Array with table information
	 */
	function getCoords($table) {
    $data = Array();
    $data['disabled'] = 'hidden';
    
    if ($table == 'tt_address') {
      $data['lng'] = 'tx_rggooglemap_lng';
      $data['lat'] = 'tx_rggooglemap_lat';
      $data['title'] = 'name';
      $data['cat'] = 'tx_rggooglemap_cat2';
      $data['show'] = 'tx_rggooglemap_display';

      $data['address'] = 'address';
      $data['zip'] = 'zip';
      $data['city'] = 'city';
      $data['country'] = 'country';
    }

    if ($table == 'fe_users') {
      $data['lng'] = 'first_name';
      $data['lat'] = 'last_name';
      $data['title'] = 'username';
      $data['cat'] = 'usergroup';
      $data['disabled'] = 'disable';
      $data['show'] = 'tx_mapfeuser_mapdisplay';
    }
    
    
		return $data;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/mod1/class.tx_rggooglemap_geocode_service.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/mod1/class.tx_rggooglemap_geocode_service.php']);
}
?>
