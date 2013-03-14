var map2 = null;
    var geocoder2 = null;

function makeMap() {
  if (GBrowserIsCompatible()) {
    var map2 = new GMap2(document.getElementById("mapLoad"));
    

    var center = new GLatLng(43, 13);
    map2.setCenter(center, 3);
    geocoder2 = new GClientGeocoder();
    
    map2.addControl(new GSmallMapControl());
    //map.addControl(new GOverviewMapControl());
    
    var marker = new GMarker(center, {draggable: true});
    map2.enableDragging();

    GEvent.addListener(marker, "dragstart", function() {
      map2.closeInfoWindow();
    });

    GEvent.addListener(marker, "dragend", function() {
      document.getElementById("rggm_liad_lat").value = marker.getPoint().lat();
      document.getElementById("rggm_liad_lng").value = marker.getPoint().lng();
    });
    
    GEvent.addListener(map2, "moveend", function() {
      document.getElementById("rggm_liad_lat").value = marker.getPoint().lat();
      document.getElementById("rggm_liad_lng").value = marker.getPoint().lng();

    });

    GEvent.addListener(map2, "click", function(overlay, point) {
        marker.setPoint(point);
        document.getElementById("rggm_liad_lat").value = marker.getPoint().lat();
        document.getElementById("rggm_liad_lng").value = marker.getPoint().lng();  
    });  
    
            

    map2.addOverlay(marker);
    

  }
}
function findAddress(address) {
    var map2 = new GMap2(document.getElementById("mapLoad"));
      if (address !="") {
      if (geocoder2) {
        geocoder2.getLatLng(
          address,
          function(point) {
            if (!point) {
              alert(address + " not found");
            } else {
              map2.setCenter(point, 13);
                      document.getElementById("rggm_liad_lat").value = point.lat();
        document.getElementById("rggm_liad_lng").value = point.lng();  

              var marker = new GMarker(point);
              map2.addOverlay(marker);
              //marker.openInfoWindowHtml(address);
              


    GEvent.addListener(marker, "dragend", function() {
      document.getElementById("rggm_liad_lat").value = marker.getPoint().lat();
      document.getElementById("rggm_liad_lng").value = marker.getPoint().lng();
    });
    
    GEvent.addListener(map2, "moveend", function() {
      document.getElementById("rggm_liad_lat").value = marker.getPoint().lat();
      document.getElementById("rggm_liad_lng").value = marker.getPoint().lng();

    });

    GEvent.addListener(map2, "click", function(overlay, point) {
        marker.setPoint(point);
        document.getElementById("rggm_liad_lat").value = marker.getPoint().lat();
        document.getElementById("rggm_liad_lng").value = marker.getPoint().lng();  
    });  
            }
          }
        );
      }
      } else {
      loadPoint();
      }
}
