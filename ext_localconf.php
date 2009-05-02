<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rggooglemap']);

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_rggooglemap_pi1 = < plugin.tx_rggooglemap_pi1.CSS_editor
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_rggooglemap_pi1.php','_pi1','list_type',0);


  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.text.20.parseFunc.tags.map = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi2
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_rggooglemap_pi2.php','_pi2','',1);



  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.text.20.parseFunc.tags.mapcat = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi3
',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_rggooglemap_pi3.php','_pi3','',1);


$PATH_rggm = t3lib_extMgm::extPath('rggooglemap');
// for hook
/*
if (TYPO3_MODE == 'FE')    {
    require_once($PATH_rggm.'class.tx_rggm_fe.php');
}


// register hooks for ve_guestbook
$TYPO3_CONF_VARS['EXTCONF']['ve_guestbook']['extraItemMarkerHook'][]   = 'tx_rggm_fe';
$TYPO3_CONF_VARS['EXTCONF']['ve_guestbook']['preEntryInsertHook'][]    = 'tx_rggm_fe';

$TYPO3_CONF_VARS['EXTCONF']['th_mailformplus']['extraItemMarkerHook'][]   = 'tx_rggm_fe';

$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][]   = 'tx_rggm_fe';
$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraGlobalMarkerHook'][]   = 'tx_rggm_fe';
$TYPO3_CONF_VARS['EXTCONF']['ve_guestbook']['postEntryInsertedHook'][] = 'tx_rggm_fe';

*/

#$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:myextension/class.myextension_tcemainprocdm.php:tx_myextension_tcemainprocdm';


t3lib_extMgm::addService($_EXTKEY,  'rggmData' /* sv type */,  'tx_rggooglemap_sv1' /* sv key */,
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

# automatic lng+lat if activated
 $GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:rggooglemap/res/class.tx_rggooglemap_tcemainprocdm.php:tx_rggooglemap_tcemainprocdm';


if ($confArr['hideInPageModule']!=1) {
	$TYPO3_CONF_VARS['EXTCONF']['cms']['db_layout']['addTables']['tx_rggooglemap_cat'][0] = array(
		'fList' => 'title,image,tabprefix',
		'icon' => false
	);
}


    
?>
