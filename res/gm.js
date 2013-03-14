var stopReload=false;
var map;
var gmarkers = [];
var tempmarkers = [];
//var clusterer;
var test = false;

var mc; // MarkerClusterer
var infoWindow = new google.maps.InfoWindow();
var oms; //OverlappingMarkerSpiderfier

// general help functions
function d(id){
    return document.getElementById(id);
}

// Show Info Box of POI-List (rgpopup)
function show(id) {
    obj = document.getElementById(id);
    obj.style.display = "block";
}


// Hide Info Box of POI-List (rgpopup)
function hide(id) {
    obj = document.getElementById(id);
    obj.style.display = "none";
}

function getBound() {
    var myXmlVar=String(map.getBounds());
    myXmlVar=myXmlVar.replace(/\(/g ,"");
    myXmlVar=myXmlVar.replace(/\)/g ,"");
    myXmlVar=escape(myXmlVar);
    //alert(myXmlVar);
    return myXmlVar;
}

//var baseIcon = new google.maps.MarkerImage({
//    url : "/typo3conf/ext/rggooglemap/res/dot.png",
//    origin : new google.maps.Point(8, 8),
//    size : new google.maps.Size(16, 16)
//});

// openPic from typo3 core function 
function openPic(url,winName,winParams) {
    var theWindow = window.open(url,winName,winParams);
    if (theWindow) {
        theWindow.focus();
    }
}

// geocoding for searchbox
function showAddress(address) {
    
    if (geocoder) {
        geocoder.geocode({'address' : address}, function(result, status) {
            if (status !== "OK") {
                alert(address + " not found");
            } else {
                map.setCenter(result[0].geometry.location);
                map.setZoom(13);
            }
        });
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
    for (var i = 0; i < tempmarkers.length; i++) {
//        map.removeOverlay(tempmarkers[i]);
        tempmarkers[i].setMap(null);
  
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
    tx_rggooglemap_pi1processCat(xajax.getFormValues('xajax_cat'));
    clearCat(); 
}

function uncheckTree () {
    var test = document.getElementById("treemenu1").getElementsByTagName("input");
    for (var i=0; i<test.length; i++) {
        test[i].checked = false;
    }
    tx_rggooglemap_pi1processCat(xajax.getFormValues('xajax_cat'));
    clearCat(); 
}


// directions
var gdir;
function getDirections(uid,table) {
    gdir = new google.maps.DirectionsRenderer(map, document.getElementById('getdirections'));
    var saddr = document.getElementById('saddr').value;
    var daddr = document.getElementById('daddr').value;
    gdir.load('from: '+saddr+' to: '+daddr);
 
}    

function updateSearch(intPageIdx) {
    
    var form = document.getElementById('rggmsearch');
    var elm = document.getElementById('rggmSearchPageIdx');
    
    if (intPageIdx >= 0) {
        elm.value = intPageIdx;
    }
    
    if (form.onsubmit) {
//        console.log("Der er en onSubmit, som bliver eksekveret nu.");
        form.onsubmit();
    }
}
