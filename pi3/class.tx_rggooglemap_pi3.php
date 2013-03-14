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

class tx_rggooglemap_pi3 extends tslib_pibase {
	var $prefixId = 'tx_rggooglemap_pi1';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_rggooglemap_pi2.php';	// Path to this script relative to the extension dir.
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
		
		$size =count($id)-1;
		$size2 = count($id)-2;
		
		if ($id[$size2] == 'hide') {
      $size -= 1;
      $function = 'hide';
    } else {
      $function = 'show';
    }
		for ($i=0;$i<$size;$i++ ) {
  	$cats.= $id[$i].'-';
    }
    $cats = substr($cats, 0,-1);

    // Debug
    #t3lib_div::debug($id);
    		
    $link = $this->pi_linkTP_keepPIvars($tag_content, Array($function=>$cats), 1,1,$conf['mapId']);
     return '<span class="maplink">'.$link.'</span>';
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/pi3/class.tx_rggooglemap_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/pi3/class.tx_rggooglemap_pi3.php']);
}

?>
