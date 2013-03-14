//+++++++++++++++++++++++++ Variablen ++++++++++++++++++++++++++++++++++++++++++++++++++ //     
		  xmlFile = "example.php";
		  var myMarkerList=new Array();
		 
		 	//+++++++++++++++++++++++++ End Variablen +++++++++++++++++++++++++++++++++++++++++++++ //
		 
		  // this variable will collect the html which will eventualkly be placed in the side_bar
      var side_bar_html = "";
    
      // arrays to hold copies of the markers and html used by the side_bar
      // because the function closure trick doesnt work there
      var gmarkers = [];
      var htmls = [] ;
      var i = 0;
			var count=0;
			var stopReload=false;


      // A function to create the marker and set up the event window
      function createMarker(point,name,html,icon,searchstring) {
        var marker = new GMarker(point,icon);
      
        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(html);
          stopReload=true;
        });
          // save the info we need to use later for the side_bar
        gmarkers[i] = marker;
        htmls[i] = html;
        // add a line to the side_bar html
        googleNeeds="/search?ie=UTF-8&oe=UTF-8&sourceid=navclient&gfns=1&q=";
        googleQuest=name
     //   mySearchstring=escape(googleNeeds+name+'+'+searchstring)
        mySearchstring=googleNeeds+name+'+'+searchstring
        side_bar_html += '<a href="javascript:myclick(' + i + ')">' + name +'</a>&nbsp;<a href="http://www.google.de'+mySearchstring+'" target="search">search</a><br>';
        i++;
        return marker;
      }


var map
var geocoder=null;
var baseIcon 
var car
 function pLoad() {    
    
    if (GBrowserIsCompatible()) {
    
      	  baseIcon = new GIcon();
          baseIcon.iconSize=new GSize(28,18);
          baseIcon.shadowSize=new GSize(28,18);
          baseIcon.iconAnchor=new GPoint(28,18);
          baseIcon.infoWindowAnchor=new GPoint(14,0);
          
          car = new GIcon(baseIcon, "gfx/camper_icon_small.jpg", null,null);
          // car = new GIcon(baseIcon, "http://maps.google.com/mapfiles/kml/pal4/icon54.png", null,null);
 // create the map
      map = new GMap2(document.getElementById("map"));
      map.addControl(new GLargeMapControl());
      map.addControl(new GMapTypeControl());
      map.setCenter(new GLatLng( 52.563, 13.57386), 10);
       geocoder = new GClientGeocoder();
      map.enableContinuousZoom();
      map.enableDoubleClickZoom()
    //############## mausrad-zoom-funktion
       //ENDE MAUSRAD ZOOM ########################################
     getXMLData(1);
		hookMouseWheelHandlers("map")
   		// getXMLData();
		GEvent.addListener(map, 'moveend', function() {
 							document.getElementById('info').innerHTML="<br>:::"+map.getZoom();
 							if(!stopReload) getXMLData(1);
 							stopReload=false;
});
		


    }

    else {
      alert("Sorry, the Google Maps API is not compatible with this browser");
    }
  }
  
  function getXMLData(clearOverlay) {
  		myFilter=getKatFilter();
   			// example.xml mit koordinationsdaten aufrufen (nur marker im ausschnitt aufrufen)
      if(clearOverlay)map.clearOverlays();
      side_bar_html="";
			var myXmlVar=String(map.getBounds());
			myXmlVar=myXmlVar.replace(/\(/g ,"");
      myXmlVar=myXmlVar.replace(/\)/g ,"");
      myXmlVar=escape(myXmlVar);
			myXmlVar=xmlFile + "?area=" + myXmlVar + "&zoom="+map.getZoom();
    	if(myFilter.length>0)myXmlVar+="&kat="+myFilter.join("~");
			document.getElementById('info').innerHTML=myXmlVar;
			var request = GXmlHttp.create();
      request.open("GET", myXmlVar, true);
      request.onreadystatechange = function() {
        if (request.readyState == 4) {
       
          var xmlDoc = request.responseXML;
          
          // obtain the array of markers and loop through it
          var markers = xmlDoc.documentElement.getElementsByTagName("marker");
					
					for (var i = 0; i < markers.length; i++) {
            // obtain the attribues of each marker
      			var lat = parseFloat(markers[i].getAttribute("lat"));
            var lng = parseFloat(markers[i].getAttribute("lng"));
            var point = new GLatLng(lat,lng);
            //gucken, ob marker schon gesetzt ist
						//if(!myMarkerList[point]) {
						var html="<h2 style='font-weight:bold;margin:2px 5px 5px 0;color:red;font-size:1.2em'>"+markers[i].getAttribute("label")+"</h2>";
            html+=markers[i].getAttribute("anschrift");
            if(markers[i].getAttribute("kategorie")!="")html+=markers[i].getAttribute("kategorie");
            html+=markers[i].getAttribute("versorgung");
						html+=markers[i].getAttribute("preis");
						html=html.replace(/~~~+/g ,"~~~");
						html=html.replace(/~~~/g ,"<br />");
						
						
						 // create the marker
						squestion= markers[i].getAttribute("anschrift")
					 	squestion=squestion.replace(/~~~/g ,"");
					  
						var marker = createMarker(point,markers[i].getAttribute("label"),html,car,squestion);
         		map.addOverlay(marker);
            //createLocationMenue();
         	 // myMarkerList[point]=1;
						//}
          }
          }
          
					// put the assembled side_bar_html contents into the side_bar div
          document.getElementById("side_bar").innerHTML = side_bar_html;
        }
      
      request.send(null);
    			 }
    			   // This function picks up the click and opens the corresponding info window
      function myclick(i) {
      	stopReload=true;
				myHtml=htmls[i];
      	
			  map.setCenter(gmarkers[i].getPoint())
				map.setZoom(14);
				gmarkers[i].openInfoWindowHtml(myHtml);
			  stopReload=false;
			}
    			 
    		//++++++++++ no use +++++++++++++++++	 
    	function createTabbedMarker(point,htmls,labels) {
        var marker = new GMarker(point);
        GEvent.addListener(marker, "click", function() {
          // adjust the width so that the info window is large enough for this many tabs
          if (htmls.length > 2) {
            htmls[0] = '<div style="width:'+htmls.length*808+'px;">' + htmls[0] + '</div>';
          }
          var tabs = [];
          for (var i=0; i<htmls.length; i++) {
            tabs.push(new GInfoWindowTab(labels[i],htmls[i]));
          }
          marker.openInfoWindowTabsHtml(tabs);
        });
        // save the info we need to use later for the side_bar
       	htmlsS[count] = new Array(label,html);
    	  gmarkers[count] = marker;
	     // add a line to the side_bar html
        side_bar_html += '<a href="javascript:myclick(' + count + ')">' + labels[0] +'</a><br>';
       	count++;
        
        return marker;
      }
      
       function showAddress(address) {
      if (geocoder) {
        geocoder.getLatLng(
          address,
          function(point) {
            if (!point) {
              alert(address + " not found");
            } else {
              map.setCenter(point, 13);
              var marker = new GMarker(point);
              map.addOverlay(marker);
              marker.openInfoWindowHtml(address);
             
            }
          }
        );
      
			}
			setTimeout("getXMLData(0)",500);
    }
    function getKatFilter() {
    	var filterOptions=[]
			for(i=0;i<document.forms.filter.kat.length;i++) {
				if(document.forms.filter.kat[i].checked==true) {
					filterOptions.push(document.forms.filter.kat[i].value)
				}
			}
			//wenn alles ausgewählt, dann keinen filter anwenden
		 if (filterOptions.length==i) filterOptions=[]
		 return filterOptions;
		 }
    
    
	 
	 function chgMapSize(step) {
	 		if(step!="full") {
		 	sizes=new Array('200x150','400x300','700x500','800x600')
	 		mDiv=document.getElementById("map");
	 		mySize=sizes[step].split("x");
	 		x=mySize[0]
	 		y=mySize[1]
	 		} else {
			x=document.getElementById("body").offsetWidth;
			y=document.getElementById("body").offsetHeight; 
			}
	 		mDiv.style.width=x+"px";
	 		mDiv.style.height=y+"px";
	 } 


/*****************************************************/
function exO(a){return Math.round(a)+"px"}

GMap2.prototype.applyZoom = function(a)
{
        var b = this;
        var c = Math.floor(Math.log(b.viewSize.width) * Math.LOG2E - 2);
        var d = b.zoomLevel - a;
        if (d > c)
        {
                d = c;
        }
        else if (d < -c)
        {
                d = -c;
        }

        var e = Math.pow(2, d);
        b.div.style.zoom = e;
        var f = b.viewSize.width * b.centerScreen.x;
        var h = b.viewSize.height * b.centerScreen.y;
        b.div.style.left = exO((this._savedOffset.x - f) * e + f);
        b.div.style.top = exO((this._savedOffset.y - h) * e + h);

}
GMap2.prototype.smoothZoomTo = function(zoom_in) {
        var a = this;
				if (!a._zoomInterval) a._targetZoom = a.getZoom();
        a._targetZoom = clamp(a._targetZoom + (zoom_in ? 1 : -1), 0, 17);
				// it's not easy to do the nice zoom on browsers that don't support zoom
			  if (!a.div||a.div.style.zoom == undefined) {
       		   		a.setZoom(a._targetZoom);
                return;
        }

        if (a._zoomInterval) return;

        a._currentZoom = parseInt(a.getZoom());
        a._savedOffset={"x" : a.div.offsetLeft, "y" : a.div.offsetTop};
        a.hideOverlays();

        this._zoomInterval = setInterval(function() {
                a._currentZoom += 0.3 * (a._targetZoom - a._currentZoom);
                if (Math.abs(a._targetZoom - a._currentZoom) < 0.05)
                {
                        if (a._savedOffset)
                        {
                                a.div.style.left=exO(a._savedOffset.x);
                                a.div.style.top=exO(a._savedOffset.y);
                        }
                        a.div.style.zoom = 1;
                        a.showOverlays();
                        a.zoomTo(a._targetZoom);
                        a._savedOffset = null;
                        window.clearInterval(a._zoomInterval);
                        a._zoomInterval = null;
                }
                else
                {
                        a.applyZoom(a._currentZoom);
                }
        }, 50);

}
function zoom(oEvent, scr)
{

        var zoom_in = true;
        if (scr <0) zoom_in = false;
        map.smoothZoomTo(zoom_in);
        
        if (oEvent.preventDefault)
                oEvent.preventDefault();

}


function clamp(i,a,b){
	tmp=i<=a?a:i>=b?b:i
	return tmp;
	}
// Hook the mouse wheel to zoom the map on Mozilla and Internet Explorer 6.0 browsers
function hookMouseWheelHandlers(id) {
  var d = document.getElementById(id);
    if (d) { 
      try {
        if (document.body.addEventListener) {
						d.addEventListener('DOMMouseScroll', function(oEvent) {
										zoom(oEvent, oEvent.detail * -40); }, false)
							} else {
								d.onmousewheel = function() { zoom(event, event.wheelDelta); return false; }
							}
      } catch (ex) { }
    }

} 
