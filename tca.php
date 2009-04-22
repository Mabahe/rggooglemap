<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TCA["tx_rggooglemap_cat"] = Array (
    "ctrl" => $TCA["tx_rggooglemap_cat"]["ctrl"],
    "interface" => Array (
        "showRecordFieldList" => "hidden,title,parent_uid"
    ),
    "feInterface" => $TCA["tx_rggooglemap_cat"]["feInterface"],
    "columns" => Array (
        "hidden" => Array (        
            "exclude" => 1,
            "label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
            "config" => Array (
                "type" => "check",
                "default" => "0"
            )
        ),
       "title" => Array (        
           "exclude" => 1,        
            "label" => "LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_cat.title",        
            "config" => Array (
                "type" => "input",
		         	"size" => "30",
		         	"required" => "1",

           )
        ),
       
       "descr" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_cat.descr",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
        
       "tabprefix" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_cat.tabprefix",        
            "config" => Array (
                "type" => "input",
		         	"size" => "30",
            )
        ),
/*
        "color" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_cat.color",        
            "config" => Array (
                "type" => "input",    
                "size" => "10",    
                "wizards" => Array(
                    "_PADDING" => 2,
                    "color" => Array(
                        "title" => "Color:",
                        "type" => "colorbox",
                        "dim" => "12x12",
                        "tableStyle" => "border:solid 1px black;",
                        "script" => "wizard_colorpicker.php",
                        "JSopenParams" => "height=300,width=350,status=0,menubar=0,scrollbars=1",
                    ),
                ),
            )
        ),
*/
		"parent_uid" => Array (		
			'exclude' => 1,
			'label' => 'LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_cat',	
			'config' => Array (
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

		'image' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:rggooglemap/locallang_db.xml:tt_address.tx_rggooglemap_image',
			'config' => Array (
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
    "types" => Array (
        "0" => Array("showitem" => "hidden;;1;;1-1-1, title, descr, parent_uid, image,tabprefix")
    ),
    "palettes" => Array (
        "1" => Array("showitem" => "")
    )
);

/*
$TCA["tx_rggooglemap_generic"] = array (
    "ctrl" => $TCA["tx_rggooglemap_generic"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "hidden,tablename,mapping"
    ),
    "feInterface" => $TCA["tx_rggooglemap_generic"]["feInterface"],
    "columns" => array (
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        "tablename" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_generic.tablename",        
            "config" => Array (
                "type" => "input",    
                "size" => "30",    
                "eval" => "lower,nospace,unique",
            )
        ),
        "mapping" => Array (        
            "exclude" => 1,        
            "label" => "LLL:EXT:rggooglemap/locallang_db.xml:tx_rggooglemap_generic.mapping",        
            "config" => Array (
                "type" => "text",
                "cols" => "30",    
                "rows" => "5",
            )
        ),
    ),
    "types" => array (
        "0" => array("showitem" => "hidden;;1;;1-1-1, tablename, mapping")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);
*/
?>
