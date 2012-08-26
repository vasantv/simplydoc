<?php
	include_once("codes/masterfunctions.php");
		
	//read request lat & long
	$lat = $_POST["lat"];
	$long = $_POST["long"];
	$rad1 = $_POST["radius1"];
	$rad2 = $_POST["radius2"];
	$num = $_POST["num"];
	$offset = $_POST["offset"];
	
	//query database
	$geoPoint = array (
		"lat" => $lat,
		"long" => $long
		);
		
	$results = findNearbyDouble($geoPoint,$rad1,$rad2,$num,$offset,"docTable");
	
	if($results == -1) { echo -1; }
	else if ($results == NULL) { echo 0; }
	else {

		//construct html and echo out
//		foreach ($results as $gdPoint)
//		{
//			echo "<ul>";
//			echo "<li>".$gdPoint["Name"]."</li>";
//			echo "<li>".$gdPoint["Address"]."</li>";
//			echo "<li>".$gdPoint["Phone"]."</li>";
//			echo "<li id='lat'>".$gdPoint["geoPoint"]["lat"]."</li>";
//			echo "<li id='long'>".$gdPoint["geoPoint"]["long"]."</li>";
//			echo "<li>".$gdPoint["Distance"]."</li>";
//			echo "</ul>";
//		}
		echo json_encode($results);
	}
?>