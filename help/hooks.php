<?php



// for hook

if (TYPO3_MODE == 'FE')    {
    require_once(t3lib_extMgm::extPath('rggooglemap').'class.tx_rggm_fe.php');
}


// register hooks for ve_guestbook
$TYPO3_CONF_VARS['EXTCONF']['ve_guestbook']['extraItemMarkerHook'][]   = 'tx_rggm_fe';
$TYPO3_CONF_VARS['EXTCONF']['ve_guestbook']['preEntryInsertHook'][]    = 'tx_rggm_fe';

$TYPO3_CONF_VARS['EXTCONF']['th_mailformplus']['extraItemMarkerHook'][]   = 'tx_rggm_fe';

$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][]   = 'tx_rggm_fe';
$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraGlobalMarkerHook'][]   = 'tx_rggm_fe';
$TYPO3_CONF_VARS['EXTCONF']['ve_guestbook']['postEntryInsertedHook'][] = 'tx_rggm_fe';



?>