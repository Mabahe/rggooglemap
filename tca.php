<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TCA["tx_rggooglemap_cat"] = array (
		"ctrl" => $TCA["tx_rggooglemap_cat"]["ctrl"],
		"interface" => array (
				"showRecordFieldList" => "hidden,title,parent_uid"
		),
		"feInterface" => $TCA["tx_rggooglemap_cat"]["feInterface"],
		"columns" => array (
				"hidden" => array (
						"exclude" => 1,
						"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
						"config" => array (
								"type" => "check",
								"default" => "0"
						)
				),
			 "title" => array (
					 "exclude" => 1,
						"label" => "LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_cat.title",
						"config" => array (
								"type" => "input",
							"size" => "30",
							"required" => "1",
						 	"eval" => "trim, required",
					 ),
				),

			 "descr" => array (
						"exclude" => 1,
						"label" => "LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_cat.descr",
						"config" => array (
								"type" => "text",
								"cols" => "30",
								"rows" => "5",
						)
				),

			 "tabprefix" => array (
						"exclude" => 1,
						"label" => "LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_cat.tabprefix",
						"config" => array (
								"type" => "input",
						 	"size" => "30",
						 	"eval" => "trim",
						)
				),


			"parent_uid" => array (
				'exclude' => 1,
				'label' => 'LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_cat',
				'config' => array (
					'type' => 'select',
					'form_type' => 'user',
					'userFunc' => 'tx_rggm_treeview->displayCategoryTree',
					'treeView' => 1,
					'treeName' => 'txchtreeviewexample',
					'foreign_table' => 'tx_chtreeview_example',
					'foreign_table' => 'tx_rggooglemap_cat',
					'size' => 5,
					'autoSizeMax' => 10,
					'minitems' => 0,
					'maxitems' => 5,

				)
			),

			'image' => array (
				'exclude' => 1,
				'label' => 'LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_image',
				'config' => array (
					'type' => 'group',
					'internal_type' => 'file',
					'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
					'max_size' => '30000',
					'uploadfolder' => 'uploads/tx_rggooglemap/',
					'show_thumbs' => '1',
					'size' => 1,
					'minitems' => 0,
					'maxitems' => 1
				)
			),

		),
		"types" => array (
				"0" => array("showitem" => "hidden;;1;;1-1-1, title, descr, parent_uid, image,tabprefix")
		),
		"palettes" => array (
				"1" => array("showitem" => "")
		)
);


?>