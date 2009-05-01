<?php

function user_rggmCat($content,$conf) {
  // query for the categories
    $table = 'tx_rggooglemap_cat';
    $field = 'title';
    $where = 'deleted = 0 AND hidden = 0 AND uid='.$content;

    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where,$groupBy='',$orderBy,$limit); 
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);


return $row['title'];
}

?>
