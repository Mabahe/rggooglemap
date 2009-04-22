<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Georg Ringer <typo3 et ringerge dot org>
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

class tx_rggooglemap_load extends tslib_pibase {
	var $prefixId = 'tx_rggooglemap_load';
	var $scriptRelPath = 'pi1/class.tx_rggooglemap_load.php';  // Path to this script relative to the extension dir.
	var $extKey = 'rggooglemap';		// The extension key.
	var $conf = array();
	
	function makeCaptcha() {
		$this->tslib_pibase();
		
			//Make sure that labels in locallang.php may be overridden
		$this->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.'][$this->prefixId.'.'];
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;  // Disable caching
		
					$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);
		
    $GLOBALS['TSFE']->additionalHeaderData['b121211'] = '<script src="http://maps.google.com/maps?file=api&amp;v=2.61&amp;key='.$this->confArr['googleKey'].'" type="text/javascript"></script>'; 
    $GLOBALS['TSFE']->additionalHeaderData['b121212'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('rggooglemap').'res/rggm_load.js"></script>';
    		
		$L = t3lib_div::_GP('L');
		
		$markerArray = array();
		$markerArray['###MAP###'] = '<div style="height:350px;width:400px" id="mapLoad"></div>';
		$markerArray['###'. strtoupper($this->extKey) . '_NOTICE###'] = $this->pi_getLL('notice') . ' ' . $this->pi_getLL('explain');
		$markerArray['###'. strtoupper($this->extKey) . '_CANT_READ###'] = '<span ' . $this->pi_classParam('cant-read') . '>' . $this->pi_getLL('cant_read1');
		$markerArray['###'. strtoupper($this->extKey) . '_CANT_READ###'] .= ' <a href="#" onclick="this.blur();newFreeCap();return false;">' . $this->pi_getLL('click_here') . '</a>';
		$markerArray['###'. strtoupper($this->extKey) . '_CANT_READ###'] .= $this->pi_getLL('cant_read2') . '</span>';
		return $markerArray;
	}
	
	function checkWord($word) {
		session_start();
		if (!empty($_SESSION[$this->extKey . '_word_hash']) && !empty($word)) {
			// all freeCap words are lowercase.
			// font #4 looks uppercase, but trust me, it's not...
			if ($_SESSION[$this->extKey . '_hash_func'] == 'md5') {
				if (md5(strtolower($word)) == $_SESSION[$this->extKey . '_word_hash']) {
					// reset freeCap session vars
					// cannot stress enough how important it is to do this
					// defeats re-use of known image with spoofed session id
					$_SESSION[$this->extKey . '_attempts'] = 0;
					$_SESSION[$this->extKey . '_word_hash'] = false;
					return true;
				}
			}
		}
		return false;
	}


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sr_freecap/pi2/class.tx_srfreecap_pi2.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sr_freecap/pi2/class.tx_srfreecap_pi2.php']);
}

?>
