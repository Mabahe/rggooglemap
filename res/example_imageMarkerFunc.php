<?php

function user_itemMarkerArrayFunc($paramArray,$conf){
echo 'xx';
	$markerArray = $paramArray[0];
	$lConf = $paramArray[1];
    $pObj = &$conf['parentObj']; // make a reference to the parent-object
	$row = $pObj->local_cObj->data;

	$imageNum = isset($lConf['imageCount']) ? $lConf['imageCount']:1;
	$imageNum = t3lib_div::intInRange($imageNum, 0, 100);
	$theImgCode = '';
	$imgs = t3lib_div::trimExplode(',', $row['image'], 1);
	$imgsCaptions = explode(chr(10), $row['imagecaption']);
	reset($imgs);
	$cc = 0;

	while (list(, $val) = each($imgs)) {
		if ($cc == $imageNum) break;
		if ($val) {
		 	$lConf['image.']['altText'] = ''; // reset altText
			$lConf['image.']['altText'] = $lConf['image.']['altText']; // set altText to value from TS
			$lConf['image.']['file'] = 'uploads/pics/'.$val;
			switch($lConf['imgAltTextField']) {
				case 'image':
					$lConf['image.']['altText'] .= $val;
				break;
				case 'imagecaption':
					$lConf['image.']['altText'] .= $imgsCaptions[$cc];
				break;
				default:
					$lConf['image.']['altText'] .= $row[$lConf['imgAltTextField']];
			}
		}
		$theImgCode .= $pObj->local_cObj->wrap($pObj->local_cObj->IMAGE($lConf['image.']).$pObj->local_cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']),$lConf['imageWrapIfAny_'.$cc]);
		$cc++;
	}
	$markerArray['###NEWS_IMAGE###'] = '';
	if ($cc) {
		$markerArray['###NEWS_IMAGE###'] = $pObj->local_cObj->wrap(trim($theImgCode), $lConf['imageWrapIfAny']);

	}
	
	$markerArray['###TEST###'] = '';
		return $markerArray;

}

?>
