  var geocoder;
  var map;
  
  function initialize(lat,lng,name) {
	
	/*
	var resLatLng = new google.maps.LatLng(lat, lng);

			var myOptions = {
			  zoom: 18,
			  center: resLatLng,
			  mapTypeId: google.maps.MapTypeId.ROADMAP
			}
			map = new google.maps.Map(document.getElementById("mapCanvas"), myOptions);
				
			//display associated results for the address
			map.setCenter(resLatLng);
			
			var iconImage = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter_withshadow&chld=D|52B552|000000';
			var marker = new google.maps.Marker({
				map: map, 
				position: resLatLng,
				icon: iconImage
			});
	*/

	/* Testing with leaflet instead */
	var map = L.map('mapCanvas');

	var cloudmadeUrl = 'http://{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png',
              subDomains = ['otile1','otile2','otile3','otile4'],
              cloudmadeAttrib = '<a href="http://open.mapquest.co.uk" target="_blank">MapQuest</a>, <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> and contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">CC-BY-SA</a>';
    var cloudmade = new L.TileLayer(cloudmadeUrl, {maxZoom: 18, attribution: cloudmadeAttrib, subdomains: subDomains});

    map.setView([lat,lng],18).addLayer(cloudmade);	

	var marker = L.marker([lat,lng]).addTo(map);
	marker.bindPopup("<b>"+name+"</b>").openPopup();

  }  
