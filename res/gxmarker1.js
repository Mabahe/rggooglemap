/*
 GxMarker version 1.2

 SYNOPSIS
    This version is compatible with Google Maps API Version 2

    A more full-featured marker that supports tooltips and hover events.  The
    first iteration just supports triggering of mouse over events, and tooltips.
   
    To setup a tooltip, pass in a third parameter (after the icon) to the
    GxMarker class:
        var marker = new GxMarker( new GPoint(lat,lng), icon, "My Tooltip" );
        map.addOverlay(marker);

    Or:
        var marker = new GxMarker( new GPoint(lat,lng) );
        marker.setTooltip("My Tooltip");
        map.addOverlay(marker);

    As of 1.1, changes to setTooltip() should work after the initial invocation

    Please refer to http://code.toeat.com/package/gxmarker for additional
    documentation.
    
    TESTED PLATFORMS:
        Linux: Firefox
        Windows: Firefox, IE6
        Mac OS X (Panther): Safari

    There is no warranty of functionality of this code, if you wish to use it
    and it does not work for you, I recommend you submit a patch.  This software
    is licensed under the GNU Lesser General Public License (LGPL):
    the full text at: http://opensource.org/licenses/lgpl-license.php
	
	Update: 04/07/06 - modified to load with API v2.44+ of the Google Maps API
	Modified by Robert Aspinall - raspinall (AT) gmail (dot) com
*/

function GxMarkerNamespace() {

    var n4=(document.layers);
    var n6=(document.getElementById&&!document.all);
    var ie=(document.all);
    var o6=(navigator.appName.indexOf("Opera") != -1);
    var safari=(navigator.userAgent.indexOf("Safari") != -1);
    var currentSpan = new google.maps.LatLngBounds();

    function setCursor( container, cursor ) {
        try {
            container.style.cursor = cursor;
        }
        catch ( c ) {
            if ( cursor == "pointer" )
                setCursor("hand");
        }
    };

    function GxMarker( a, b, tooltip ) {
    
        this.inheritFrom = google.maps.Marker;
        //    this.inheritFrom(a,b);

        this.inheritFrom({
            position : a,
            icon : b
        });

        //    if ( !currentSpan.minX || a.x < currentSpan.minX ) currentSpan.minX = a.x;
        //    if ( !currentSpan.maxX || a.x > currentSpan.maxX ) currentSpan.maxX = a.x;
        //    if ( !currentSpan.minY || a.y < currentSpan.minY ) currentSpan.minY = a.y;
        //    if ( !currentSpan.maxY || a.y > currentSpan.maxY ) currentSpan.maxY = a.y;
        if ( tooltip ) {
            this.setTooltip( tooltip );
        }
    }

    //GxMarker.prototype = new google.maps.Marker({ position : new google.maps.LatLng(1, 1)});
    //
    //GxMarker.prototype.setTooltip = function( string ) {
    //    this.removeTooltip();
    //    this.tooltip = {
    //        opacity     : 70,
    //        contents    : string
    //    };
    //};
    //
    //GxMarker.prototype.initialize = function( a ) {
    //    try {
    ////        google.maps.Marker.prototype.initialize.call(this, a);
    //        // Setup the mouse over/out events
    //		google.maps.event.bind(this, "mouseover", this, this.onMouseOver);
    //		google.maps.event.bind(this, "mouseout", this, this.onMouseOut);
    //    } catch(e) {
    //		alert(e);
    //    }
    //}
    //
    //GxMarker.prototype.setCursor = function( cursor ) {
    //    var c = this.iconImage;
    //    // Use the image map for Firefox/Mozilla browsers
    //    if ( n6 && this.icon.imageMap && !safari) {
    //        c = this.imageMap;
    //    }
    //    // If we have a transparent icon, use that instead of the main image
    //    else if ( this.transparentIcon && typeof this.transparentIcon != "undefined" ) {
    //        c = this.transparentIcon;
    //    }
    //}
    //
    //GxMarker.prototype.remove = function( a ) {
    //    google.maps.Marker.prototype.remove.call(this);
    //    this.removeTooltip();
    //}
    //
    //GxMarker.prototype.removeTooltip = function() {
    //    if ( this.tooltipObject ) {
    //       // this.map.div.removeChild(this.tooltipObject);
    //        this.tooltipObject = null;
    //    }
    //}
    //
    //GxMarker.prototype.onInfoWindowOpen = function() {
    //    this.hideTooltip();
    //    google.maps.Marker.prototype.onInfoWindowOpen.call(this);
    //}
    //
    //GxMarker.prototype.onMouseOver = function() {
    //    this.showTooltip();
    //  //  google.maps.event.trigger(this, "mouseover");
    //};
    //
    //GxMarker.prototype.onMouseOut = function() {
    //    this.hideTooltip();
    //  //  google.maps.event.trigger(this, "mouseout");
    //};
    //
    //GxMarker.prototype.showTooltip = function() {
    //    if ( this.tooltip ) {
    //        if ( !this.tooltipObject ) {
    //            var opacity = this.tooltip.opacity / 100;
    //            this.tooltipObject = document.createElement("div");
    //            this.tooltipObject.style.display    = "none";
    //            this.tooltipObject.style.position   = "absolute";
    //            this.tooltipObject.style.background = "#fff";
    //            this.tooltipObject.style.padding    = "0";
    //            this.tooltipObject.style.margin     = "0";
    //            this.tooltipObject.style.MozOpacity = opacity;
    //            this.tooltipObject.style.filter     = "alpha(opacity=" + this.tooltip.opacity + ")";
    //            this.tooltipObject.style.opacity    = opacity;
    //            this.tooltipObject.style.zIndex     = 50000;
    //            this.tooltipObject.innerHTML        = "<div class=\"markerTooltip\">" + this.tooltip.contents + "</div>";
    //            map.getPane(G_MAP_MARKER_PANE).appendChild(this.tooltipObject);
    //		}
    //
    //        var c = map.fromLatLngToDivPixel(new google.maps.LatLng(this.getPoint().y, this.getPoint().x));
    //		try {
    //        	this.tooltipObject.style.top  = c.y - ( this.getIcon().iconAnchor.y + 5 ) + "px";
    //        	this.tooltipObject.style.left = c.x + "px";
    //        	this.tooltipObject.style.display = "block";
    //		} catch(e) {
    //			alert(e);
    //		}
    //    }
    //}
    //
    //GxMarker.prototype.hideTooltip = function() {
    //    if ( this.tooltipObject ) {
    //        this.tooltipObject.style.display = "none";
    //    }
    //}
    //
    //google.maps.Map.prototype.flushOverlays = function() {
    //    currentSpan = new google.maps.LatLngBounds();
    //    this.clearOverlays();
    //}
    //
    //google.maps.Map.prototype.zoomToMarkers = function() {
    //    var span = new google.maps.Size( currentSpan.maxX - currentSpan.minX, currentSpan.maxY - currentSpan.minY );
    //    for ( var zoom = 0; zoom < this.spec.numZoomLevels; zoom++ ) {
    //        var ppd = this.spec.getPixelsPerDegree(zoom);
    //        var pixelSpan = new google.maps.Size(
    //            Math.round(span.width * ppd.x), Math.round(span.height * ppd.y));
    //        if ( pixelSpan.width  <= this.viewSize.width &&
    //             pixelSpan.height <= this.viewSize.height )
    //        { break; }
    //    }
    //    this.centerAndZoom( new google.maps.Point( currentSpan.minX + (span.width/2), currentSpan.minY + (span.height/2) ), zoom);
    //}

    function makeInterface(a) {
        var b = a || window;
        b.GxMarker = GxMarker;
    }

    makeInterface();
}

GxMarkerNamespace();
