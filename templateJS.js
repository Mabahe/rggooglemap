<!-- ###ALL### -->

/*<![CDATA[*/ 

var gicons=[];
###GICONS###

 
function makeMap() {
	if (GBrowserIsCompatible()) {

		###SELECTED_CAT###
		
		//map = new GMap2(document.getElementById("###MAP_DIV###"));
		map = new GMap2(document.getElementById("map") ###MAP_TYPES###);
		gdir=new GDirections(map, document.getElementById('getdirections'));
		gdir2=new GDirections(map, document.getElementById('getdirections2'));
		geocoder = new GClientGeocoder();
		new GKeyboardHandler(map);
		boundsgeneral = new GLatLngBounds();

		if (###MAP_CLUSTER###==1) {
			clusterer = new Clusterer(map);
		}

		map.setCenter(new GLatLng(###MAP_LAT###, ###MAP_LNG###), ###MAP_ZOOM###);
		userLocation("###USE_USER_LOCATION###", ###USE_USER_LOCATION_ZOOMLEVEL###);
		###SETTINGS###

		if (###MAP_TYPE_MAPNIK### == 1) { loadMap_mapnik("###MAP_TYPE_MAPNIK_TITLE###"); }
		if (###MAP_TYPE_TAH### == 1)  { loadMap_tah("###MAP_TYPE_TAH_TITLE###"); }

		//###__MAKEMAP### 
		
		getXMLData(1);

		cat = document.getElementById("mapcatlist").innerHTML;
		
		GEvent.addListener(map, 'moveend', function() {
			myXmlVar="###URL###&tx_rggooglemap_pi1[cat]="+cat+"&tx_rggooglemap_pi1[area]=" + getBound() + "&tx_rggooglemap_pi1[zoom]="+map.getZoom()+"&r=" + Math.random();
					
			if(!stopReload) {
				getXMLData(123);
			}
			stopReload=false;
		});
		
		###HIDECONTROLSMOUSEOUT###
				
		###POI_ON_START###
		
	}
}

function getXMLData(clearOverlay) {

	if (clearOverlay != 123) {
		show('rggooglemapload');
	}
	
	// getCategories
	cat = document.getElementById("mapcatlist").innerHTML;
	myXmlVar="###URL###&tx_rggooglemap_pi1[cat]="+cat+"&tx_rggooglemap_pi1[area]=" + getBound() + "&tx_rggooglemap_pi1[zoom]="+map.getZoom()+"&r=" + Math.random();

	###DEBUG###GLog.writeUrl(myXmlVar);
	
	// clear clusters
	for (var i = 0; i < rggmclustermarkers.length; i++) {
		map.removeOverlay(rggmclustermarkers[i]); 
	}
	//rggmclustermarkers = [];

	var markersList = [];
	var request = GXmlHttp.create();
	request.open("GET", myXmlVar, true);
	request.onreadystatechange = function() {
		if (request.readyState == 4) {
			
			// obtain the array of markers and loop through it
			var xmlDoc = request.responseXML;
			var markers = xmlDoc.documentElement.getElementsByTagName("marker");
			
			if (clearOverlay == 12) {
				map.clearOverlays();gmarkers.length = 0;
				setTimeout('getXMLData(11)', 100000);
			}
			
			if (d("rggooglemap-recordsonmap")){tx_rggooglemap_pi1activeRecords(getBound(),cat); }

			var count=0;
			var length = gmarkers.length;

			if (###MAP_CLUSTER###==2) {
				gmarkers = [];
				map.clearOverlays();
			}

			var rggmclustermarkerscount = 0;
			
			###DEBUG###GLog.write('POIs from request : ' + markers.length);
			for (var i = 0; i < markers.length; i++) {
				
				// obtain the attribues of each marker
				var lat = parseFloat(markers[i].getAttribute("lat"));
				var lng = parseFloat(markers[i].getAttribute("lng"));
				var point = new GLatLng(lat,lng);
				var title = [GXml.value(markers[i].getElementsByTagName("t")[0]) ];
				var id = parseFloat(markers[i].getAttribute("uid"));
				var table = markers[i].getAttribute("table");
				var img = markers[i].getAttribute("img");
				var id2 = parseFloat(markers[i].getAttribute("uid2"));
				
				boundsgeneral.extend(point);
				
				marker = createMarker(point, id, img, title, table);
				
				markersList[i] = marker;
				
				if (###MAP_CLUSTER###!=2) {
					if (table == 'rggmcluster') {
						rggmclustermarkers[rggmclustermarkerscount] = marker;
						rggmclustermarkerscount++;
						###ADD_MARKER###					
						
					} else {
						if (gmarkers[id2]!=1) {
							//GLog.write('mid: '+title+'    '+count,'black');
							//clusterer.AddMarker(marker,title);
							
							count++
							gmarkers[id2] = 1;
							
							###ADD_MARKER###
						} 
					}
				}
				
			}
			
			###DEBUG###GLog.write('POIs added : '+count);
			
			if (###MAP_CLUSTER###==2) {
				var markerCluster = new MarkerClusterer(map, markersList, {maxZoom:12});
			}

			###DEBUG###GLog.write('Processing of additional datasets');
			###PROCESS_DATASETS###

			hide('rggooglemapload');
			
			// general bound for the 1st call only
			if (firstCall==0 && ###BOUNDS###==1) {
				var zoom=map.getBoundsZoomLevel(boundsgeneral);
				var centerLat = (boundsgeneral.getNorthEast().lat() + boundsgeneral.getSouthWest().lat()) /2;
				var centerLng = (boundsgeneral.getNorthEast().lng() + boundsgeneral.getSouthWest().lng()) /2;
				map.setCenter(new GLatLng(centerLat,centerLng),zoom);
				
				firstCall = 1;
			}
			
		}
	}
	request.send(null);
}



function createMarker(point, id, img, title,table, searchIons) { 
	
	if (searchIons==1) {
		icon = searchIcon;
		icon.image = "###URL_ICONS###"+img;
		//alert(icon.image);
	} else {
		var icon = gicons[img];
	}

	//
	// var marker = new GxMarker( point, icon, ""+ title, { "offset": new GSize(10, -20), "isStatic": false } );
	//
	if (table == 'rggmcluster') {
		var marker = new GMarker (point, icon);
	} else {
		var marker = new GxMarker( point, icon, ""+title );
	}
	
	// var marker = new GxMarker( point, "http://www.rggooglemap.com/uploads/tx_rggooglemap/VirtError_02.gif", ""+title );
	var url = "###URL###&no_cache=1&tx_rggooglemap_pi1[detail]="+id+"&tx_rggooglemap_pi1[table]="+table;

	GEvent.addListener(marker, "click", function() {
		if (table == 'rggmcluster') {
			map.zoomIn();
		} else {
			var req = GXmlHttp.create();
			req.open("GET", url, true );
			req.onreadystatechange = function() {
				if ( req.readyState == 4 ) {
					marker.openInfoWindowHtml( req.responseText );
				}
			};
			req.send(null);
		}
	});

	return marker;
}

// open the info bubble 
function myclick(i, lng, lat, table,showMarker) {
	var req = GXmlHttp.create();
	var url = "###URL###&type=500&no_cache=1&tx_rggooglemap_pi1[detail]="+i+"&tx_rggooglemap_pi1[table]="+table;
	
	req.open("GET", url, true);
	var t=this;
	req.onreadystatechange = function() {
		if (req.readyState == 4) {
			t.map.openInfoWindowHtml( new GLatLng(lat, lng), req.responseText );
		}
	};
	req.send(null);
};


function clearCat() {
	map.clearOverlays();
	gmarkers.length = 0;
	
	setTimeout("getXMLData(12);",2000);
	show('rggooglemapload');
}

/*]]>*/
<!-- ###ALL### -->
