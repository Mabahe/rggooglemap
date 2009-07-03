<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


	# pi1 > plugin
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_rggooglemap_pi1 = < plugin.tx_rggooglemap_pi1.CSS_editor
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_rggooglemap_pi1.php','_pi1','list_type',0);


	# pi2 > set up userdefined tag <MAP>
t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.text.20.parseFunc.tags.map = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi2
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_rggooglemap_pi2.php','_pi2','',1);


	# pi3 > set up userdefined tag <MAPCAT>
t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.text.20.parseFunc.tags.mapcat = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi3
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_rggooglemap_pi3.php','_pi3','',1);



// add the service
t3lib_extMgm::addService($_EXTKEY, 'rggmData', 'tx_rggooglemap_sv1',
	array(
		'title' => 'tt_address for rggooglemap ',
		'description' => 'Gets the needed data out of tt_address',
		'subtype' => 'tt_address',
		
		'available' => true,
		'priority' => 50,
		'quality' => 50,
		
		'os' => '',
		'exec' => '',
		
		'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_rggooglemap_sv1.php',
		'className' => 'tx_rggooglemap_sv1',
	)
);

// settings of extension manager
$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);

// automatic lng+lat if activated
if ($confArr['autoGeocode'] == 1) {
	$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:rggooglemap/lib/class.tx_rggooglemap_tcemainprocdm.php:tx_rggooglemap_tcemainprocdm';
}

// hide category records in page module if not allowed in EM settings
if ($confArr['hideInPageModule']!=1) {
	$TYPO3_CONF_VARS['EXTCONF']['cms']['db_layout']['addTables']['tx_rggooglemap_cat'][0] = array(
		'fList' => 'title,image,tabprefix',
		'icon' => false
	);
}

// linkhandler
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler']['rggm'] = 'EXT:rggooglemap/lib/class.tx_rggooglemap_linkhandler.php:&tx_rggooglemap_linkhandler';

?>
