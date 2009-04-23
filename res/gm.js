var stopReload=false;
var map;
var gmarkers = [];
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

//baseIcon.infoShadowAnchor = new GPoint(80, 25);
//baseIcon.shadow = "http://www.google.com/mapfiles/shadow50.png";
//baseIcon.shadowSize = new GSize(37, 34);
//baseIcon.iconAnchor = new GPoint(5, 8);


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

function getDirectionsLong(to, FromCountry,FromAddr, dirobj, showDir) {
	var daddr = FromCountry +', '+ FromAddr;
	dirobj.load('from: '+daddr+' to: '+to);
	show(showDir);
} 

function removeDirections() {
	gdir.clear();
	hide('removedirections');
}


function callSearchWithResults() {

}