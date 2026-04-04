jQuery(function($) { 'use strict';

	var marker = [], infowindow = [], map;

    var stylesArray = {
    	'style-1' : {
   		'style': [{"featureType":"administrative.land_parcel","elementType":"geometry.stroke","stylers":[{"visibility":"on"},{"saturation":"-100"},{"weight":"1.22"}]},{"featureType":"administrative.land_parcel","elementType":"labels.text.stroke","stylers":[{"visibility":"off"}]},{"featureType":"administrative.land_parcel","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":"-100"},{"lightness":"100"}]},{"featureType":"road","elementType":"labels.text","stylers":[{"visibility":"on"},{"gamma":"1.00"},{"lightness":"-100"}]},{"featureType":"road","elementType":"labels.text.fill","stylers":[{"visibility":"on"}]},{"featureType":"road","elementType":"labels.text.stroke","stylers":[{"visibility":"off"},{"saturation":"0"}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#98adb6"},{"visibility":"on"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"saturation":"-13"},{"lightness":"18"}]}],
    		'marker' : 'img/marker.png'}
    };


	function addMarker(location,name,contentstr){
        marker[name] = new google.maps.Marker({
            position: location,
            map: map,
			icon: stylesArray[$('#map-canvas').attr("data-style")]['marker']
        });
        marker[name].setMap(map);
		
		infowindow[name] = new google.maps.InfoWindow({
			content:contentstr
		});
		
		google.maps.event.addListener(marker[name], 'click', function() {
			infowindow[name].open(map,marker[name]);
		});
    }
	
	function initialize() {

		var lat = $('#map-canvas').attr("data-lat");
		var lng = $('#map-canvas').attr("data-lng");

		var myLatlng = new google.maps.LatLng(lat,lng);

		var setZoom = parseInt($('#map-canvas').attr("data-zoom"));

		var styles = stylesArray[$('#map-canvas').attr("data-style")]['style'];
		var styledMap = new google.maps.StyledMapType(styles,{name: "Styled Map"});

		var mapOptions = {
			zoom: setZoom,
            gestureHandling: 'cooperative',
			panControl: false,
			panControlOptions: {
				position: google.maps.ControlPosition.LEFT_BOTTOM
			},
			zoomControl: true,
			zoomControlOptions: {
				position: google.maps.ControlPosition.LEFT_BOTTOM
			},
			streetViewControl: true,
			streetViewControlOptions: {
				position: google.maps.ControlPosition.LEFT_BOTTOM
			},
			
			center: myLatlng,
			mapTypeControlOptions: {
			  mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
			}
		
		};
		map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
		
		map.mapTypes.set('map_style', styledMap);
  		map.setMapTypeId('map_style');
		

		$('.addresses-block a').each(function(){
			var mark_lat = $(this).attr('data-lat');
			var mark_lng = $(this).attr('data-lng');
			var this_index = $('.addresses-block a').index(this);
			var mark_name = 'template_marker_'+this_index;
			var mark_locat = new google.maps.LatLng(mark_lat, mark_lng);
			var mark_str = $(this).attr('data-string');
			addMarker(mark_locat,mark_name,mark_str);	
		});
		
	}

	$(window).on('load', function(){
      if ($('#map-canvas').length) {
          setTimeout(function(){initialize();}, 500); 
      }    
		
	});

});
