<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

global $TYPO3_CONF_VARS;

// class for displaying the category tree in BE forms.
include_once(t3lib_extMgm::extPath($_EXTKEY).'lib/class.tx_rggooglemap_treeview.php');

// get tables from EM settings
$tmp_confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);

// Default items for tt_address if tt_address is used
if (strpos($tmp_confArr['tables'], 'tt_address') !== false) {
	$tempColumns = array (
		"tx_rggooglemap_lng" => array (
			"exclude" => 1,		
			"label" => "LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_lng",
			"config" => array (
				"type" => "input",	
				"size" => "20",
			)
		),
		"tx_rggooglemap_lat" => array (
			"exclude" => 1,		
			"label" => "LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_lat",
			"config" => array (
				"type" => "input",	
				"size" => "20",
	
			)
		),
		"tx_rggooglemap_cat" => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_cat',
			'config' => array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_rggm_treeview->displayCategoryTree',
				'treeView' => 1,
				'treeName' => 'txchtreeviewexample',
				'foreign_table' => 'tx_rggooglemap_cat',
				'size' => 5,
				'autoSizeMax' => 10,
				'minitems' => 0,
				'maxitems' => 10,
				'wizards' => array(
				),
			)
		),
	);
	
	// fields for tt_address
	$value = 'tt_address';
	t3lib_div::loadTCA($value);
	t3lib_extMgm::addTCAcolumns($value,$tempColumns,1);
	t3lib_extMgm::addToAllTCAtypes($value,"tx_rggooglemap_lat;;;;1-1-1, tx_rggooglemap_lng, tx_rggooglemap_cat");
}

/*
* GENERIC PART
*/

$tables = t3lib_div::trimExplode(',', $tmp_confArr['tables']);
foreach ($tables as $key=>$singleTable) {
	$singleTable = trim($singleTable);
	
	$serviceChain='';
	while (is_object($serviceObj = t3lib_div::makeInstanceService('rggmData', $singleTable, $serviceChain))) {
		$serviceChain.=','.$serviceObj->getServiceKey();    
		if ($tempuser=$serviceObj->init()) {
		
			// user found, just stop to search for more
			if ($tmp_confArr['wizards'])	{
				$lat = $serviceObj->getTable('lat');
				$lng = $serviceObj->getTable('lng');
				$cat = $serviceObj->getTable('rggmcat');
				
				t3lib_div::loadTCA($singleTable);
				
				// wizard
				$TCA[$singleTable]['columns'][$lng]['config']['wizards'] = array(
					'_POSITION' => 'right',
					'googlemap' => array(
						'title' => 'LLL:EXT:rggooglemap/locallang_db.xml:wizard.title',
						'icon' => $BACK_PATH.t3lib_extMgm::extRelPath('rggooglemap').'mod1/moduleicon.gif',
						'type' => 'popup',
						'script' => 'EXT:rggooglemap/class.tx_rggooglemap_wizard.php',
						'JSopenParams' => 'height=630,width=800,status=0,menubar=0,scrollbars=0',
						'lat_field' => $lat,
						'lng_field' => $lng,
					),
				);
				
				// latitude
				$TCA[$singleTable]['columns'][$lat]['config']['wizards'] = array(
					'_POSITION' => 'right',
					'googlemap' => array(
						'title' => 'LLL:EXT:rggooglemap/locallang_db.xml:wizard.title',
						'icon' => $BACK_PATH.t3lib_extMgm::extRelPath('rggooglemap').'mod1/moduleicon.gif',
						'type' => 'popup',
						'script' => 'EXT:rggooglemap/class.tx_rggooglemap_wizard.php',
						'JSopenParams' => 'height=630,width=800,status=0,menubar=0,scrollbars=0',
						'lat_field' => $lat,
						'lng_field' => $lng,
					),
				);
				
				// longitude
				$TCA[$singleTable]['columns'][$cat]['config'] = array (
					'type' => 'select',
					'form_type' => 'user',
					'userFunc' => 'tx_rggm_treeview->displayCategoryTree',
					'treeView' => 1,
					'treeName' => 'txchtreeviewexample',
					'foreign_table' => 'tx_rggooglemap_cat',
					'size' => 5,
					'autoSizeMax' => 10,
					'minitems' => 0,
					'maxitems' => 10,
					'wizards' => array(
					),
				);
				
			}
			break;
		}
	}
	
}  




t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key, pages';


t3lib_extMgm::addPlugin(array('LLL:EXT:rggooglemap/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

// Static TS
t3lib_extMgm::addStaticFile($_EXTKEY,"static/","Google Maps");

// Flexforms
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue('rggooglemap_pi1', 'FILE:EXT:rggooglemap/flexform_ds.xml');


// Wizicon
if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_rggooglemap_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_rggooglemap_pi1_wizicon.php';


// Be Module
if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::addModule("web","txrggooglemapM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
}

// Category 
$TCA["tx_rggooglemap_cat"] = array (
    "ctrl" => array (
        'title' => 'LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_cat',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        "default_sortby" => "ORDER BY crdate",
        "delete" => "deleted",    
        'treeParentField' => 'parent_uid',
        "enablecolumns" => array (
            "disabled" => "hidden",
        ),
        "dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
        "iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_rggooglemap_cat.gif",
    ),
    "feInterface" => array (
        "fe_admin_fieldList" => "hidden, title, parent_uid",
    )
);

// categories on pages
t3lib_extMgm::allowTableOnStandardPages('tx_rggooglemap_cat');

// adds processing for extra "codes" that have been added to the "what to display" selector in the content element by other extensions
include_once(t3lib_extMgm::extPath($_EXTKEY).'lib/class.tx_rggooglemap_itemsProcFunc.php');

// add the ajax request
$TYPO3_CONF_VARS['BE']['AJAX']['tx_rggooglemap_ajax::getMap'] = t3lib_extMgm::extPath($_EXTKEY) . 'lib/class.tx_rggooglemap_ajax.php:tx_rggooglemap_ajax->getMap';

?>
