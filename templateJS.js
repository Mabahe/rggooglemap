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
		var clusterer = new Clusterer(map);
		}



		map.setCenter(new GLatLng(###MAP_LAT###, ###MAP_LNG###), ###MAP_ZOOM###);

		###SETTINGS###
		//map.addControl(new GOverviewMapControl(new GSize(200,200)));
		//setTimeout("positionOverview(10,60)",10);


		if (###MAP_TYPE_MAPNIK### == 1) { loadMap_mapnik("###MAP_TYPE_MAPNIK_TITLE###"); }
		if (###MAP_TYPE_TAH### == 1)    { loadMap_tah("###MAP_TYPE_TAH_TITLE###"); }


		//###__MAKEMAP### 
		
		getXMLData(1);
		
		// create the clusterer
		cat =   document.getElementById("mapcatlist").innerHTML;
		
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

      if (clearOverlay !=123) {
	show('rggooglemapload');
	}
  // getCategories
  cat =   document.getElementById("mapcatlist").innerHTML;
  myXmlVar="###URL###&tx_rggooglemap_pi1[cat]="+cat+"&tx_rggooglemap_pi1[area]=" + getBound() + "&tx_rggooglemap_pi1[zoom]="+map.getZoom()+"&r=" + Math.random();

//	GLog.write('URL: '+myXmlVar,'blue');


        	var markersList= [];
  var request = GXmlHttp.create();
  request.open("GET", myXmlVar, true);
  request.onreadystatechange = function() {
    if (request.readyState == 4) {
      
      var xmlDoc = request.responseXML;
      
      // obtain the array of markers and loop through it
      var markers = xmlDoc.documentElement.getElementsByTagName("marker");



      if (clearOverlay ==12) {
        map.clearOverlays();gmarkers.length = 0;
        setTimeout('getXMLData(11)', 100000);
        
      }

        if(d("rggooglemap-recordsonmap")){tx_rggooglemap_pi1activeRecords(getBound(),cat); }
      
      
     var rggmbound =document.getElementById('rggmBound');
     if (rggmbound )  { rggmbound.value = getBound(); }
   
	   

       //GLog.write(markers.length+"marker neu vs gmarker array "+gmarkers.length, 'red');
       //GLog.write('begin: '+gmarkers.length,'blue');
       

       var count=0;
       var length = gmarkers.length;

			if (###MAP_CLUSTER###==2) {
  			gmarkers = [];
  			map.clearOverlays();
  		}
       
     //x  document.getElementById('coordinfo').innerHTML= markers.length + " ---- "+test+"<a href=\" "+myXmlVar+"\">XML<&#47;a>";
      for (var i = 0; i < markers.length; i++) {

        // obtain the attribues of each marker
        var lat = parseFloat(markers[i].getAttribute("lat"));
        var lng = parseFloat(markers[i].getAttribute("lng"));
        var point = new GLatLng(lat,lng);
        var title = [GXml.value(markers[i].getElementsByTagName("t")[0]) ];
        var id = parseFloat(markers[i].getAttribute("uid"));
        var table = markers[i].getAttribute("table");
        var img = markers[i].getAttribute("img");
        
        var id2 = id;


        
        	boundsgeneral.extend(point);

        marker = createMarker(point, id, img, title, table);

          markersList[i] = marker;
        if (!gmarkers[id2] ) {
        	count++
          gmarkers[id2] = 1;     
					//GLog.write('mid: '+title+'    '+count,'black');           
          //clusterer.AddMarker(marker,title);

          ###ADD_MARKER###
        } 
        
      }

			if (###MAP_CLUSTER###==2) {
				var markerCluster = new MarkerClusterer(map, markersList);
			}

			
			     
       //GLog.write('end: '+gmarkers.length,'red');      
        
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
//GLog.write('point: '+point+' id '+id+' img '+img+' title' +title+ ' table '+table,'blue');
//alert('point: '+point+' id '+id+' img '+img+' title' +title+ ' table '+table);

	
	if(searchIons==1) {
		icon = searchIcon;
		icon.image = "###URL_ICONS###"+img;
		//alert(icon.image);
	} else {
		var icon = gicons[img];
	}

//	
	
	// var marker = new GxMarker( point, icon, ""+ title, { "offset": new GSize(10, -20), "isStatic": false } );
	var marker = new GxMarker( point, icon, ""+title );
	//var marker = new GMarker (point);
	
	// var marker = new GxMarker( point, "http://www.rggooglemap.com/uploads/tx_rggooglemap/VirtError_02.gif", ""+title );
	var url = "###URL###&no_cache=1&tx_rggooglemap_pi1[detail]="+id+"&tx_rggooglemap_pi1[table]="+table;

	GEvent.addListener(marker, "click", function() {
		
		var req = GXmlHttp.create();
		req.open("GET", url, true );
		req.onreadystatechange = function() {
			if ( req.readyState == 4 ) {
				marker.openInfoWindowHtml( req.responseText );
			}
		};
		req.send(null);
		
	});
	return marker;
}  

 // This function picks up the click and opens the corresponding info window 
function myclick(i, lng, lat, table,showMarker) {
	
	var req = GXmlHttp.create();
	var url = "###URL###&type=500&no_cache=1&tx_rggooglemap_pi1[detail]="+i+"&tx_rggooglemap_pi1[table]="+table;
	
	req.open("GET", url, true);
	var t=this;
	req.onreadystatechange = function() {
		if ( req.readyState == 4 ) {
			t.map.openInfoWindowHtml( new GLatLng(lat, lng), req.responseText );
		}
	};
	req.send(null);
	

}; 


function clearCat() {
//	test = true;
	map.clearOverlays();
	gmarkers.length = 0;
	//getXMLData();
	setTimeout("getXMLData(12);",2000);
show('rggooglemapload');
}

/*]]>*/
<!-- ###ALL### -->
