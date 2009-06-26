var stopReload=false;
var map;
var gmarkers = [];
var rggmclustermarkers = [];
var searchresultmarkers = [];
var clusterer;
var test = false;
var boundsgeneral;
var count = 0;
var firstCall = 0;

// generall help functions
function d(id){return document.getElementById(id);}

// Show Info Box of POI-List (rgpopup)
function show(id) {
	obj = document.getElementById(id);
	obj.style.display = "block";
}


// Hide Info Box of POI-List (rgpopup)
function hide(id) {
	obj = document.getElementById(id);
	obj.style.display = "none";
	return false;
}

function getBound() {
	var myXmlVar=String(map.getBounds());
	myXmlVar=myXmlVar.replace(/\(/g ,"");
	myXmlVar=myXmlVar.replace(/\)/g ,"");
	myXmlVar=escape(myXmlVar);
	//alert(myXmlVar);
	return myXmlVar;
}

var baseIcon = new GIcon();
baseIcon.iconSize = new GSize(16, 16);
baseIcon.iconAnchor = new GPoint(8, 8);
baseIcon.infoWindowAnchor = new GPoint(8, 8);
baseIcon.transparent = "http://www.google.com/mapfiles/markerTransparent.png";

searchIcon = baseIcon;
searchIcon.iconSize = new GSize(20, 34);
searchIcon.iconAnchor = new GPoint(10, 34);
searchIcon.infoWindowAnchor = new GPoint(10, 17);


// openPic from typo3 core function 
function openPic(url,winName,winParams) {  
	var theWindow = window.open(url,winName,winParams); 
	if (theWindow) {theWindow.focus();} 
}

// geocoding for searchbox
function showAddress(address, zoom) {
	if (geocoder) {
		geocoder.getLatLng(
			address,
			function(point) {
				if (!point) {
					alert(address + " not found");
				} else {
					map.setCenter(point, parseInt(zoom));
				}
			}
		);
	}
}

// catTreemneu
function rggmTree(data) {
	tx_rggooglemap_pi1processCatTree(data);
	setTimeout("fdTableSort.init()", 1000);
}

// catTreemneu
function rggmSearchMenu(data) {
	tx_rggooglemap_pi1processSearchInMenu(data);
	setTimeout("fdTableSort.init()", 1000);
}

function deleteSearchResult() {
	document.getElementById("searchFormResult").innerHTML = '';
	for (var i = 0; i < searchresultmarkers.length; i++) {
		map.removeOverlay(searchresultmarkers[i]);
	}
}

function checkall(id) {
var contentDiv = document.getElementById(id);
  var test = document.getElementById(id).getElementsByTagName("input");
  var state = false;
  
  if (test[1].checked==false) {
    for (var i=0; i<test.length; i++) {
      test[i].checked = true;
    }
  } else {
    for (var i=0; i<test.length; i++) {
      test[i].checked = false;
    }
  }   
  tx_rggooglemap_pi1processCat(xajax.getFormValues('xajax_cat'));clearCat(); 
}

function uncheckTree () {
  var test = document.getElementById("treemenu1").getElementsByTagName("input");
      for (var i=0; i<test.length; i++) {
      test[i].checked = false;
    }
   tx_rggooglemap_pi1processCat(xajax.getFormValues('xajax_cat'));clearCat(); 
}


// directions
function getDirections() {
	var saddr = document.getElementById('saddr').value;
	var daddr = document.getElementById('daddr').value;
	gdir.load('from: '+saddr+' to: '+daddr);
	show('removedirections');
}    

function getDirectionsLong(to, FromCountry,FromAddr, showDir) {
	var daddr = FromAddr;
	if (FromCountry != '') {
		daddr = FromAddr+', '+ FromCountry;
	}
	gdir2.load('from: '+daddr+' to: '+to);
	show(showDir);
} 

function removeDirections() {
	gdir.clear();
	hide('removedirections');
}


function callSearchWithResults() {

}



// copyright information for the additonal layers
function getCopyright() {
		var copyright = new GCopyright(1, new GLatLngBounds(new GLatLng(-90,-180), new GLatLng(90,180)), 0, 
			'(<a rel="license" href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>)');
		var copyrightCollection = new GCopyrightCollection('&copy; 2009 <a href="http://www.openstreetmap.org/">OpenStreetMap</a> Contributors');
		copyrightCollection.addCopyright(copyright);

		return copyrightCollection;
}

// Mapnik map
function loadMap_mapnik(title) {
	copyrightCollection = getCopyright();
	
	var tilelayers_mapnik = new Array();
	tilelayers_mapnik[0] = new GTileLayer(copyrightCollection, 0, 18);
	tilelayers_mapnik[0].getTileUrl = loadMap_mapnik_url;
	tilelayers_mapnik[0].isPng = function () { return true; };
	tilelayers_mapnik[0].getOpacity = function () { return 1.0; };

	var mapnik_map = new GMapType(tilelayers_mapnik,
		new GMercatorProjection(19), title,
		{ urlArg: 'mapnik', linkColor: '#000000' }
	);
	map.addMapType(mapnik_map);
}

// Mapnik map URL
function loadMap_mapnik_url(a, z) { 
	return "http://tile.openstreetmap.org/" + z + "/" + a.x + "/" + a.y + ".png"; 
}

// T@H map
function loadMap_tah(title) {
	copyrightCollection = getCopyright();
	
	var tilelayers_tah = new Array();
	tilelayers_tah[0] = new GTileLayer(copyrightCollection, 0, 17);
	tilelayers_tah[0].getTileUrl = loadMap_tah_url;
	tilelayers_tah[0].isPng = function () { return true; };
	tilelayers_tah[0].getOpacity = function () { return 1.0; };
	
	var tah_map = new GMapType(tilelayers_tah,
		new GMercatorProjection(19), title,
		{ urlArg: 'tah', linkColor: '#000000' }
	);
	map.addMapType(tah_map);
}

// T@H map url
function loadMap_tah_url(a, z) { 
	return "http://tah.openstreetmap.org/Tiles/tile/" + z + "/" + a.x + "/" + a.y + ".png";
}


function userLocation(doIt, zoomLevel) {
	if (doIt==1 && navigator.geolocation) {   
		navigator.geolocation.getCurrentPosition(function(position) {
			callbackUserPos(position.coords.latitude, position.coords.longitude, zoomLevel);
		});
	}
}

function callbackUserPos(lat,lng, zoomLevel){
	if (zoomLevel==0) {
		zoomLevel = map.getZoom();
	}
	
	map.setCenter(new GLatLng(lat, lng), zoomLevel);
}