<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006,2007 Georg Ringer <typo3@ringerge.org>
*  (c) 2007 Bernhard Kraft  <kraftb@kraftb.at>
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
 * BE Wizard for the 'rggooglemap' extension.
 *
 * @author	Georg Ringer <typo3@ringerge.org>
 * @author	Bernhard Kraft <kraftb@kraftb.at>
 */

$BACK_PATH = '../../../typo3/';
define('TYPO3_MOD_PATH', '../typo3conf/ext/rggooglemap/');
$MCONF['name']='web_txrggooglemapM2';
$MCONF['access']='user,group';
$MCONF['script']='class.tx_rggooglemap_wizard.php';



require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$GLOBALS['LANG']->includeLLFile('EXT:rggooglemap/locallang.xml');
require_once (PATH_t3lib."class.t3lib_scbase.php");
require_once(PATH_t3lib.'class.t3lib_stdgraphic.php');
require (t3lib_extMgm::extPath('xajax').'class.tx_xajax.php');


class tx_rggooglemap_wizard	{
	var $content = '';
	var $doc = NULL;

	/**
	 * The init method getting called at startup
	 *
	 * @return	void
	 */
	function init ()	{
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType = 'xhtml_trans';
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->doc->bodyTagAdditions = 'onload="initAll();"';
	}

	/**
	 * Print module content
	 *
	 * @return	void
	 */
	function printContent()	{
		echo $this->content;
	}

	function main()	{
		global $BE_USER, $BACK_PATH, $LANG;

		$params = t3lib_div::_GP('P');
		t3lib_div::loadTCA($params['table']);
		$params['wConf'] = $GLOBALS['TCA'][$params['table']]['columns'][$params['field']]['config']['wizards']['googlemap'];
		$params['row'] = t3lib_BEfunc::getRecord($params['table'], $params['uid']);

		
		$this->content = '';
		
		$wiz = $this->renderWizard($params);
			
		$this->content .= $this->doc->startPage($LANG->getLL('mlang_tabs_tab'));
		$this->content .= $wiz;
	}

	function renderWizard($params)	{
		$item = '';

		$this->confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);
		$settings = '';
		if ($this->confArr['mapNavigation'] == 'large')	{
			$settings .= 'map.addControl(new GLargeMapControl());'; 
		} else	{
			$settings .= 'map.addControl(new GSmallMapControl());';
		}
		if ($this->confArr['mapType'] == 'on')	{
			$settings .= 'map.addControl(new GMapTypeControl());';
		}
		if ($this->confArr['mapOverview'] == 'on')	{
			$settings .= 'map.addControl(new GOverviewMapControl());';
		}

		$onload = 'window.onload = all_load;';
		
		$this->confArr['lat_field'] = $params['wConf']['lat_field'];
		if (doubleval($params['row'][$params['wConf']['lat_field']]))	{
			$this->confArr['startLat'] = $params['row'][$params['wConf']['lat_field']];
		}
		$this->confArr['lng_field'] = $params['wConf']['lng_field'];
		if (doubleval($params['row'][$params['wConf']['lng_field']]))	{
			$this->confArr['startLong'] = $params['row'][$params['wConf']['lng_field']];
		}
		$this->confArr['table'] = $params['table'];

		require_once(t3lib_extMgm::extPath('rggooglemap').'mod1/index.php');
		$this->modObj = t3lib_div::makeInstance('tx_rggooglemap_module1');
		$this->confArr['recordUid'] = $params['uid'];
		$this->modObj->mapIdx = $params['table'].'_'.$params['uid'].'_'.$params['wConf']['lat_field'];
		$this->modObj->doc = &$this->doc;
		$this->modObj->wizardMode = true;
		$this->modObj->confArr = $this->confArr;

		$this->modObj->xajax = new tx_xajax();
		$this->modObj->xajax->registerFunction(array("getPoi",&$this->modObj,"xajaxGetPoi"));
		$this->modObj->xajax->registerFunction(array("insertPoi",&$this->modObj,"xajaxInsertPoi"));
		$this->modObj->xajax->registerFunction(array("listRecords",&$this->modObj,"xajaxListRecords"));

		$item .= '';
		if (!$this->doc->JScodeArray['rggooglemap_wizard_loadfunc'])	{
			$this->doc->JScodeArray['rggooglemap_wizard_loadfunc'] = '
var loadMaps = Array();
function initAll()	{
	all_load();
}

if ((typeof latFields)=="undefined")	{
	var latFields = Array();
}
if ((typeof lngFields)=="undefined")	{
	var lngFields = Array();
}

function savePoint(map)	{

	var lat = document.getElementById("centerlatitude");
	var lng  = document.getElementById("centerlongitude");
	latFields[map][1].value = lat.value;
	latFields[map][2].value = lat.value;
	lngFields[map][1].value = lng.value;
	lngFields[map][2].value = lng.value;
}

function all_load()	{
//###MARK###

	for (var i in loadMaps)	{

		doload(loadMaps[i]);
	}
}
			';
		}

		$this->doc->JScodeArray['rggooglemap_wizard_jscode'] = $this->modObj->genJScode($settings, $onload, false);
		$this->doc->JScodeArray[] = '

loadMaps["'.$params['table'].'_'.$params['uid'].'_'.$params['wConf']['lat_field'].'"] = "map'.$params['table'].'_'.$params['uid'].'_'.$params['wConf']['lat_field'].'";

		';

		$this->doc->JScodeArray['rggooglemap_wizard_loadfunc'] = str_replace('//###MARK###', '
//###MARK###

		var frm = window.opener.document.editform;
    latFields["map'.$this->modObj->mapIdx.'"] = Array();
    latFields["map'.$this->modObj->mapIdx.'"][1] = frm["data['.$this->confArr['table'].']['.$this->confArr['recordUid'].']['.$this->confArr['lat_field'].']"];
    latFields["map'.$this->modObj->mapIdx.'"][2] = frm["data['.$this->confArr['table'].']['.$this->confArr['recordUid'].']['.$this->confArr['lat_field'].']_hr"];
    lngFields["map'.$this->modObj->mapIdx.'"] = Array();
    lngFields["map'.$this->modObj->mapIdx.'"][1] = frm["data['.$this->confArr['table'].']['.$this->confArr['recordUid'].']['.$this->confArr['lng_field'].']"];
    lngFields["map'.$this->modObj->mapIdx.'"][2] = frm["data['.$this->confArr['table'].']['.$this->confArr['recordUid'].']['.$this->confArr['lng_field'].']_hr"];
		', $this->doc->JScodeArray['rggooglemap_wizard_loadfunc']);

		$item .= $this->modObj->viewMap();

		$item .= $this->modObj->genPostJScode();


		return $item;
	}



}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/class.tx_rggooglemap_wizard.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rggooglemap/class.tx_rggogglemap_wizard.php']);
}



$SOBE = t3lib_div::makeInstance('tx_rggooglemap_wizard');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();

?>
