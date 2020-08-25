  var geocoder;
  var map;
  var latlng; 
  var markers = [];
  var currentPage = 1;
  var localLocation = 1;
  var localLatLng;
  var rad1 = 0; 
  var rad2 = 1;
  
  function initialize(address) {
    geocoder = new google.maps.Geocoder();
	latlng = new google.maps.LatLng(12.971599,77.594563);
    var myOptions = {
      zoom: 13,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    map = new google.maps.Map(document.getElementById("mapCanvas"), myOptions);

	//showing default results
	localLatLng = latlng;
	localLocation = 1; 
	currentPage = 1;

	if(address!= "") { codeGivenAddress(address,1); }
	else{
		displayResult(latlng,1);	
		updateResultCount(latlng);
	}
  }

  function clearOverLays()
  {
        for (var i = 0; i < markers.length; i++) {
          markers[i].setMap(null);
        }	  
  }
  
  function addMarker(id, newLocation)
  {
	  	var iconImage = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter_withshadow&chld='+id+'|52B552|000000';
        var marker = new google.maps.Marker({
            map: map, 
            position: newLocation,
			icon: iconImage
        });
		markers.push(marker); 

		//add a listener event for marker clicks
	    google.maps.event.addListener(marker, 'click', function(event) {
	     	//placeMarker(event.latLng);
			removeHighlights();
			$("#result"+id).addClass("highResult");
	    });

  }
  function removeHighlights()
  {
	$("div[id^='result']").removeClass("highResult");  
  }
  
  function appendResult(id, provName, provAddress, provPhone, provDistance, provSpec)
  {
	  	provDistance = Math.round(provDistance*100)/100;
	  	addString = '<div class="results" itemscope itemtype="http://data-vocabulary.org/Organization" id="result'+id+'" >\
				  <span>'+id+'.</span>\
					<span itemprop="name"><strong><a href="http://www.simplydoc.in/doctor/'+provName.replace(/ /g,"-")+'/">'+provName+'</a></strong></span>\
				    <span itemprop="distance" class="greyText"> ('+provDistance+' kms)</span><br/>';
		if(provAddress != ''){		  
				  	addString = addString + '<address itemprop="address" itemscope itemtype="http://data-vocabulary.org/Address">';
					addString = addString + provAddress+'</address>';
				}
	    if(provSpec != '') {
	    	addString = addString + "<i class='icon-leaf'></i> " +provSpec;
	    }
		addString = addString+'</div>';
		$("#textResults").append(addString);			  
  }
  
  function geoLocError(err)
  {
		$("#myLocAlert").html('<button class="close" data-dismiss="alert">×</button>&nbsp; Location <strong>can\'t be found</strong>. Please type in address.');
		$("#myLocAlert").show("fast").delay(2000).hide("slow");
	  
  }
  
  function displayResult(resLatLng, pageNum) { 
		
		//clear off existing markers & results
		clearOverLays();
		$("#textResults").html('');
		
		//set a new marker for the current user search location - styled to show distinction
        map.setCenter(resLatLng);
		
		var styleIconClass = new StyledIcon(StyledIconTypes.CLASS,{color:"#ff00ff"});
		var iconImage = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter_withshadow&chld=home|FFFFFF';
	    var marker = new StyledMarker({
	    //var marker = new google.maps.Marker({
			styleIcon:new StyledIcon(StyledIconTypes.MARKER,{text:"Y"},styleIconClass),
			position:resLatLng,
			map:map,
			icon:iconImage
			});		
		markers.push(marker);

		var offPage = (pageNum-1)*5;	

		//post to PHP file to get information and display in HTML document below	
		$.post("getNearBy.php", { lat: resLatLng.lat(), long: resLatLng.lng(), radius1: rad1, radius2: rad2, num: 5, offset: offPage}, 
			function(data){				
				if(data == 0) { 				
					$("#textResults").html('<div style="color:red"> Damn! Ain\'t nothing more here folks. </div>');
				}
				
				//parse JSON results
				var json = $.parseJSON(data);
				var resultCount=(pageNum-1)*5; //starting each page counter appropriately
				var bounds = new google.maps.LatLngBounds(); //finding bounds of the map
				bounds.extend(resLatLng);

				$.each(json,function(i, result){

					resultCount++;
					//Create new marker
					var newLatLng = new google.maps.LatLng(result.geoPoint.lat, result.geoPoint.long);
					
					//adding marker and associated text result
					addMarker(resultCount, newLatLng);
					appendResult(resultCount, result.Name, result.Address, result.Phone, result.Distance, result.Spec);
					bounds.extend(newLatLng);
				});
				
				//if showing a broader/narrower range of results, zoom out or in appropriately
				//switch(rad2) { 
				//	case 5: map.setZoom(13); break;
				//	case 50: map.setZoom(11); break;
				//	case 1: map.setZoom(15);
				//}
				map.fitBounds(bounds); //fit map to bounds

				//Pagination logic							
				if(pageNum == 1 && resultCount == 5) { //first page - only append next
					$("#textResults").append('<div id="resultPages">\
							<ul class="pager">\
							  <li >\
								<a href="" id="nextPage" class="next">Next &rsaquo;&rsaquo;</a>\
							  </li>\
							</ul>\
						  </div>');
						  
					$("#nextPage").on("click",function(e) {
						e.preventDefault();	
						if(localLocation == 0)
						{	codeAddress(currentPage + 1); }
						else { displayResult(localLatLng, currentPage+1); }
						currentPage = currentPage + 1;
						return false;
					});	

				}//end if pageNum == 1
				else if(pageNum > 1 && (resultCount%5==0)){ //if pageNum = 2+ and next pages exist										
					$("#textResults").append('<div id="resultPages">\
							<ul class="pager">\
							  <li >\
								<a href="" id="prevPage" class="previous">&lsaquo;&lsaquo; Previous</a>\
							  </li>\
							  <li >\
								<a href="" id="nextPage" class="next">Next &rsaquo;&rsaquo;</a>\
							  </li>\
							</ul>\
						  </div>');						  					  
					//add paginator events
					$("#prevPage").on("click",function(e) {
						e.preventDefault();
						if(currentPage > 1)
						{	
							if(localLocation == 0) { codeAddress(currentPage - 1); }
							else { displayResult(localLatLng, currentPage-1); }
							currentPage = currentPage - 1;
							if (currentPage == 1)
							{ $(this).addClass("disabled");	}
							else 
							{ $(this).removeClass("disabled"); }
						}
						return false;
					});
					$("#nextPage").on("click",function(e) {
							e.preventDefault();	
							if(localLocation == 0) { codeAddress(currentPage + 1); }
							else { displayResult(localLatLng, currentPage+1); }
							currentPage = currentPage + 1;
							return false;
					});	  
				} //end else if
				else if(pageNum > 1){ //this is the last page and next pages don't exist- only show previous
					$("#textResults").append('<div id="resultPages">\
							<ul class="pager">\
							  <li >\
								<a href="" id="prevPage" class="previous">&lsaquo;&lsaquo; Previous</a>\
							  </li>\
							</ul>\
						  </div>');
					$("#prevPage").on("click",function(e) {
						e.preventDefault();
						if(currentPage > 1)
						{	
							if(localLocation == 0) { codeAddress(currentPage - 1); }
							else { displayResult(localLatLng, currentPage-1); }
							currentPage = currentPage - 1;
							if (currentPage == 1)
							{ $(this).addClass("disabled");	}
							else 
							{ $(this).removeClass("disabled"); }
						}
						return false;
					});
					
					if (rad2 != 50) //append a see more results link, if this is not the outermost set of results 
					{
						$("#textResults").append('<span style="display:inline-block;"><a href="" id="seeMore">See more results, further away?</a></span>');
						$("#seeMore").on("click",function(e) {
							e.preventDefault();	
							switch(rad2) { case 1: $("#res2").click(); break; case 5: $("#res3").click(); }  
							return false;
						});	
					}
				} //end else					   
		});	  
  }

  function codeAddress(pageNum) {
		
	  //find the value of the address
      var address = document.getElementById("myAddress").value;
      	  
	  //geocode address
      geocoder.geocode( { 'address': address }, function(results, status) {
		  if (status == google.maps.GeocoderStatus.OK) {
				
			//display associated results for the address
			displayResult(results[0].geometry.location, pageNum);
	
			//event tracking
			_gaq.push(['_trackEvent', 'Location', address]);
	
			if(pageNum ==1 ) {
				updateResultCount(results[0].geometry.location); //updateResultCount
			}
					
		  } else {
			  $("#myLocAlert").html('<button class="close" data-dismiss="alert">×</button>&nbsp; Location <strong>can\'t be found</strong>. Please type in address.');
			$("#myLocAlert").show("fast").delay(2000).hide("slow");
		  }
    });
  }

   //simple function to code with the given input addresses
  function codeGivenAddress(address,pageNum) {
	  //geocode address
      geocoder.geocode( { 'address': address }, function(results, status) {
		  if (status == google.maps.GeocoderStatus.OK) {
				
			//display associated results for the address
			displayResult(results[0].geometry.location, pageNum);
	
			//event tracking
			_gaq.push(['_trackEvent', 'Location', address]);
	
			if(pageNum ==1) {
				updateResultCount(results[0].geometry.location); //updateResultCount
			}					
		  } 
    });
  }
  
  function updateResultCount(latlng)
  {
	 $.post("getResultCount.php", { lat: latlng.lat(), long: latlng.lng() }, 
			function(data){
						
			//parse JSON results
			var json = $.parseJSON(data);
			
			//update resultCount based on json results
			$("#res1").html(json.first);$("#res2").html(json.second);$("#res3").html(json.third);
	});
  }
	
  $(document).ready(function() {

	//if text address is provided and search button is clicked
 	$("#searchForm").submit(function(){
			
			//make results visible
			$("#resultsHeader").css("visibility","visible");
			$("#resultsContainer").css("visibility","visible");
 			
			localLocation = 0;
			currentPage = 1;
			codeAddress(1); //show the first page of results
			return false; //prevent standard form submit							
	});

	//if another range of results is requested
	$("#res1").click(function(){
		rad1 = 0; rad2 = 1;
		currentPage = 1;
		if (localLocation == 0) //address has been provided
		{
			codeAddress(1);	
		}
		else //if localLatLng was calculated
		{
			displayResult(localLatLng,1);
		}
		$("#res1").addClass("largeFont");
		$("#res2").removeClass("largeFont");
		$("#res3").removeClass("largeFont");
		return false;
	});
	
	//if another range of results is requested
	$("#res2").click(function(){
		rad1 = 1; rad2 = 5;
		currentPage = 1;
		if (localLocation == 0) //address has been provided
		{
			codeAddress(1);	
		}
		else //if localLatLng was calculated
		{
			displayResult(localLatLng,1);
		}
		$("#res2").addClass("largeFont");
		$("#res1").removeClass("largeFont");
		$("#res3").removeClass("largeFont");
		return false;
	});

	//if another range of results is requested
	$("#res3").click(function(){
		rad1 = 5; rad2 = 50;
		currentPage = 1;
		if (localLocation == 0) //address has been provided
		{
			codeAddress(1);	
		}
		else //if localLatLng was calculated
		{
			displayResult(localLatLng,1);
		}
		$("#res3").addClass("largeFont");
		$("#res1").removeClass("largeFont");
		$("#res2").removeClass("largeFont");
		return false;
	});


  });