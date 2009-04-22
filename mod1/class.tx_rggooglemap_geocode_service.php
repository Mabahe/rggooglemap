<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Axel Jung <info@jung-newmedia.de>
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
class tx_rggooglemap_geocode_service {
	/**
	 * Get the Geo Codes from Google
	 * @param	string	Adress
	 * @param	string	Api Key
	 * @return	mixed	False if it fails or array
	 */
	function geoGetCoords($address,$api_key) {
		$_result = false;
		$_url = 'http://maps.google.com/maps/geo';
		$_url .= '?output=xml';
		$_url .= '&key=' . $api_key;
		$_url .= '&q=' . rawurlencode($address);
		$_coords = array();
		if ($_result = file_get_contents($_url)) {
			if (preg_match('/<coordinates>(-?\d+\.\d+),(-?\d+\.\d+),(-?\d+\.?\d*)<\/coordinates>/', $_result, $_match)) {
				$_coords['lon'] = $_match[1];
				$_coords['lat'] = $_match[2];
				$_result = true;
			}
		}
		if ($_result) {
			return $_coords;
		}
		return $_result;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/mod1/class.tx_rggooglemap_geocode_service.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/mod1/class.tx_rggooglemap_geocode_service.php']);
}
?>
