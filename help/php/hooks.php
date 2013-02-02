<?
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Georg Ringer <typo3 et ringerge dot org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

    /*
    *   YOU NEED AN EXTRA PHP-FILE TO INCLUDE THIS HOOKS
    *    THIS FILE IS JUST FOR SHOWING SAMPLES OF THE
    *                  FUNCTIONS ITSELF
    *
    *   IF YOU NEED AN ADDITIONAL HOOK IN THE CODE
    *             PLEASE DROP ME A NOTE
    */




	/**
	 * A hook for an extra search. It adds some parts to the query
	 *
	 * @param	string		$where: the existing where-part
	 * @param	array		$$data: Array holding the postvars
	 * @param	obj		$lconf: $this->config of the plugin
	 * @param	obj		$pobj: $this of the plugin
	 * @return	the additional where clause
	 */
  function extraSearchProcessor($where, $data, $lConf, &$pObj) {

    if ($data['type'] > 0) {
      $where.= ' AND tx_rgboulder_type = '.$data['type'];
    }
    if ($data['start'] > 0) {
      $where.= ' AND tx_rgboulder_start = '.$data['start'];
    }
		return $where;
	}

	/**
	 * A hook to modifiy the js part of the extension,
	 * e.g. loading an extra custom layer
	 *
	 * @param	string		$js: the existing js
	 * @param	array		$$data: Array holding the config of the js
	 * @param	obj		$lconf: $this->config of the plugin
	 * @param	obj		$pobj: $this of the plugin
	 * @return	the full js part, so you should know what you do!
	 */
	function extraGetJsProcessor($js, $data, $lConf, &$pObj) {
	  $postvar = t3lib_div::_GP('custom');
	  if ($postvar) {
      $additionalJS = 'function CustomGetTileUrl(a,b) {
                              if (b==17 && a.x>=70461 && a.x<=70465 && a.y>=45785 && a.y<= 45790) {
                                return "http://p28123.typo3server.info/fileadmin/dev/map/tiles/Tile_"+(a.x)+"_"+(a.y)+"_"+b+".jpg";
                              } else if (b==16 && a.x>=35230 && a.x<=35232 && a.y>=22892 && a.y<= 22895) {
                                return "http://p28123.typo3server.info/fileadmin/dev/map/tiles/Tile_"+(a.x)+"_"+(a.y)+"_"+b+".jpg";
                              } else {
                                return G_NORMAL_MAP.getTileLayers()[0].getTileUrl(a,b);
                              }
                      	   }';
      $additionalMap = 'var copyright = new GCopyright(1,new GLatLngBounds(new GLatLng(37.584580682182, 3.5339760780334), new GLatLng(57.584580682182, 23.533976078033)), 0, "Digitales Oberösterreichisches Raum-Informations-System");
      	var copyrightCollection = new GCopyrightCollection(\'Custom Layer\');
  		copyrightCollection.addCopyright(copyright);

  		var tilelayers = [new GTileLayer(copyrightCollection , 16, 17)];
  		tilelayers[0].getTileUrl = CustomGetTileUrl;

  		var custommap = new GMapType(tilelayers, G_SATELLITE_MAP.getProjection(), "Custom Layer", {errorMessage:"No chart data available"});
  		map.addMapType(custommap);';

  		$js = str_replace('//###MAKEMAP###',$additionalMap,$js);
  	}

    $all =  $js.$additionalJS;

    return $all;




	}

?>