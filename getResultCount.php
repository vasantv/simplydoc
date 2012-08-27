<?php
	include_once("codes/masterfunctions.php");
	
	//read request lat & long
	$lat = $_POST["lat"];
	$long = $_POST["long"];
	
	//query database
	$geoPoint = array (
		"lat" => $lat,
		"long" => $long
		);
		
	$results = findCount($geoPoint,"docDetails");
	
	if($results == -1) { echo -1; }
	else if ($results == NULL) { echo 0; }
	else {
		echo json_encode($results);
	}
?>