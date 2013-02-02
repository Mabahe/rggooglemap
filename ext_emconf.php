<?php

########################################################################
# Extension Manager/Repository config file for ext: "rggooglemap"
#
# Auto generated 01-04-2009 07:48
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'A Google Maps extension',
	'description' => 'New Version with lots of new features, everything new! Still not finished, manual pending at http://wiki.typo3.org/index.php/EXT_rggooglemap',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '4.1.0-dev',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => 'uploads/tx_rggooglemap/',
	'modify_tables' => 'tt_address',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Georg Ringer',
	'author_email' => 'www.ringer.it',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'tt_address' => '',
			'xajax' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'constantsextended' => '',
		),
	),
	'_md5_values_when_last_written' => '',
);

?>