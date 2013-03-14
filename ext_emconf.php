<?php

########################################################################
# Extension Manager/Repository config file for ext "rggooglemap".
#
# Auto generated 13-05-2011 11:04
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'A google map extension',
	'description' => 'New Version with lots of new features, everything new! Still not finished, manual pending at http://wiki.typo3.org/index.php/EXT_rggooglemap',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '3.0.3',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => 'uploads/tx_rggooglemap/',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Georg Ringer',
	'author_email' => 'typo3@ringerge.org',
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
		),
	),
	'_md5_values_when_last_written' => 'a:94:{s:9:"ChangeLog";s:4:"f67c";s:10:"README.txt";s:4:"ee2d";s:20:"class.tx_rggm_fe.php";s:4:"4b8d";s:31:"class.tx_rggooglemap_wizard.php";s:4:"a5af";s:40:"class.tx_rggooglemap_wizardGeocoding.php";s:4:"d41d";s:21:"ext_conf_template.txt";s:4:"f727";s:12:"ext_icon.gif";s:4:"a875";s:17:"ext_localconf.php";s:4:"64fd";s:14:"ext_tables.php";s:4:"1684";s:14:"ext_tables.sql";s:4:"b248";s:24:"ext_typoscript_setup.txt";s:4:"2834";s:15:"flexform_ds.xml";s:4:"dc49";s:14:"googleShort.js";s:4:"65eb";s:11:"gxmarker.js";s:4:"41fb";s:27:"icon_tx_rggooglemap_cat.gif";s:4:"d55d";s:13:"locallang.xml";s:4:"4013";s:16:"locallang_db.xml";s:4:"e159";s:7:"tca.php";s:4:"7224";s:13:"template.html";s:4:"26ab";s:19:"templateSimple.html";s:4:"9a6c";s:14:"doc/manual.sxw";s:4:"52d4";s:19:"doc/wizard_form.dat";s:4:"785b";s:20:"doc/wizard_form.html";s:4:"0752";s:17:"mod1/backup-index";s:4:"c664";s:45:"mod1/class.tx_rggooglemap_geocode_service.php";s:4:"c327";s:43:"mod1/class.tx_rggooglemap_table_service.php";s:4:"4c6a";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"860c";s:14:"mod1/index.php";s:4:"0d34";s:19:"mod1/moduleicon.gif";s:4:"5838";s:12:"mod1/sort.js";s:4:"955a";s:14:"pi1/ce_wiz.gif";s:4:"a0fa";s:14:"pi1/ce_wiz.png";s:4:"22d5";s:33:"pi1/class.tx_rggooglemap_load.php";s:4:"b282";s:32:"pi1/class.tx_rggooglemap_pi1.php";s:4:"f660";s:40:"pi1/class.tx_rggooglemap_pi1_wizicon.php";s:4:"66a5";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"9e96";s:24:"pi1/static/editorcfg.txt";s:4:"1842";s:20:"pi1/static/setup.txt";s:4:"6d7e";s:32:"pi2/class.tx_rggooglemap_pi2.php";s:4:"1fb3";s:32:"pi3/class.tx_rggooglemap_pi3.php";s:4:"8279";s:19:"res/ajax-loader.gif";s:4:"cf46";s:20:"res/checkbox-off.png";s:4:"7efb";s:19:"res/checkbox-on.png";s:4:"d8b9";s:17:"res/checktree.css";s:4:"ee0d";s:18:"res/checktree.html";s:4:"9882";s:16:"res/checktree.js";s:4:"ad86";s:26:"res/checktree_commented.js";s:4:"1d55";s:30:"res/class.tx_rggm_treeview.php";s:4:"3b10";s:34:"res/class.tx_rggooglemap_table.php";s:4:"eb74";s:42:"res/class.tx_rggooglemap_tcemainprocdm.php";s:4:"dcbf";s:13:"res/close.gif";s:4:"47bc";s:14:"res/close2.gif";s:4:"faad";s:16:"res/clusterer.js";s:4:"2282";s:12:"res/db-0.gif";s:4:"6565";s:12:"res/db-1.gif";s:4:"7c3b";s:11:"res/dot.png";s:4:"482a";s:31:"res/example_imageMarkerFunc.php";s:4:"77a1";s:9:"res/gm.js";s:4:"276c";s:10:"res/gm2.js";s:4:"3b35";s:15:"res/gxmarker.js";s:4:"3aac";s:16:"res/gxmarker1.js";s:4:"6c90";s:19:"res/map_ttnews.html";s:4:"6965";s:20:"res/map_ttnews2.html";s:4:"4ebb";s:24:"res/map_veguestbook.html";s:4:"e546";s:25:"res/markerTransparent.png";s:4:"daf2";s:13:"res/minus.gif";s:4:"8165";s:11:"res/pde.css";s:4:"be26";s:10:"res/pde.js";s:4:"7e0f";s:15:"res/pdmarker.js";s:4:"f09c";s:12:"res/plus.gif";s:4:"4d48";s:12:"res/popup.js";s:4:"d328";s:12:"res/rggm.css";s:4:"5fce";s:18:"res/rggm_import.js";s:4:"e487";s:27:"res/simpletereemenu.js_orig";s:4:"abf9";s:21:"res/simpletreemenu.js";s:4:"ead2";s:14:"res/square.gif";s:4:"fb71";s:12:"res/tree.php";s:4:"4029";s:18:"res/help/hooks.php";s:4:"41c3";s:25:"res/help/rggmUserFunc.php";s:4:"7ba2";s:25:"res/help/ve_guestbook.txt";s:4:"14c7";s:53:"res/help/templateSamples/www.einkaufsstrassen.at.html";s:4:"8b35";s:43:"res/help/templateSamples/www.gosau.org.html";s:4:"5e9c";s:50:"res/help/templateSamples/www.hotel-koller.com.html";s:4:"d8f6";s:25:"res/icons/icon_delete.gif";s:4:"6384";s:23:"res/icons/icon_save.png";s:4:"83a6";s:22:"res/img/minus-last.gif";s:4:"5545";s:17:"res/img/minus.gif";s:4:"0c5b";s:21:"res/img/node-last.gif";s:4:"857c";s:16:"res/img/node.gif";s:4:"e2f3";s:21:"res/img/plus-last.gif";s:4:"13b8";s:16:"res/img/plus.gif";s:4:"f6b0";s:32:"sv1/class.tx_rggooglemap_sv1.php";s:4:"8883";}',
);

?>