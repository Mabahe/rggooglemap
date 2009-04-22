function Markers2(show, hide) {
  var cats = show.split(",");
  for (var c=0; c<cats.length; c++) {
    cat = cats[c]; 

    for (var i=0;i<gmarkers.length;i++) {
       if (gmarkers[i].type==cat)  {
          map.removeOverlay(gmarkers[i]);  
          map.addOverlay(gmarkers[i]);
       }
    }
  }
 
  var cats = hide.split(",");
  for (var c=0; c<cats.length; c++) {
    cat = cats[c];
      
    for (var i=0;i<gmarkers.length;i++) {
       if (gmarkers[i].type==cat)  {
          map.removeOverlay(gmarkers[i]);  
       }
    }
  }  
}

var gmarkers=[];
var map;
var request;
var bounds = new GBounds(Number.MAX_VALUE, Number.MAX_VALUE, -Number.MAX_VALUE, -Number.MAX_VALUE); 
