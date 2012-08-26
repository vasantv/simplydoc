<?php 

/* functions available
	dbConnect - connect to the local database
	storeJD - store scrapped JD results into the database
	findNearby - find nearby locations in the database to a given point	
	findNearbyDouble - find nearby locations in the database to a given point, between two bounding radii
	findCount - find count of nearby locations for (<1 km, 1-5 kms, 5+ kms)
	do_async_post - asynchronous posting to another php file
*/

/* master variables */
$hostname = "localhost";
$dbUser = "medlyuser";
$dbPass = "medly123"; 
$dbName = "simplydoc";

/* Objective: connect to master database
	Inputs: None
	Returns: Connection to database */
function dbConnect()
{
	global $hostname,$dbUser,$dbPass,$dbName;

    $con = new mysqli($hostname,$dbUser,$dbPass, $dbName) or die ("Couldn't connect to database!");
    //mysql_select_db($dbName,$con) or die ("Couldn't select database $dbName!");

	return ($con);
}

/* Objective: Retrieve nearby items to an indicated geoPoint from the database, sorted by distance from geoPoint
	Inputs: geoPoint, boundingRadius, numItems (Optional=10), offset (Optional=0), tableName
			geoPoint ( "lat", "long" ) specified as degrees in decimals
	Returns: array of geoDataPoints (Name, Address, Phone, geoPoint, Distance), sorted by distance from input geoPoint; -1 otherwise
*/
function findNearby($geoPoint, $boundingRadius, $numItems = 10, $offsetItems = 0, $tableName = 'docDetails')
{

	/* credits for below code: http://www.movable-type.co.uk/scripts/latlong-db.html */

	//identifying base elements
	$lat = $geoPoint["lat"];
	$long = $geoPoint["long"];
	$radLat = deg2rad($lat);
	$radLong = deg2rad($long);
	
	$earthR = 6371; //Earth's radius in kms
	
	//first-cut bounding box construction (in degrees)
	$maxLat = $lat + rad2deg($boundingRadius/$earthR);
	$minLat = $lat - rad2deg($boundingRadius/$earthR);
	$maxLong = $long + rad2deg($boundingRadius/$earthR/cos(deg2rad($lat)));
	$minLong = $long - rad2deg($boundingRadius/$earthR/cos(deg2rad($lat)));
	
	//connect to database
    $con = dbConnect();
	
    /*construct query and run into database*/
    $firstQuery = "(SELECT *, acos(sin($radLat)*sin(radians(`doc_add_lat`)) + cos($radLat)*cos(radians(`doc_add_lat`))*cos(radians(`doc_add_long`)-$radLong))*$earthR AS Distance 
		FROM $tableName WHERE 
		`doc_add_lat` >= $minLat AND `doc_add_lat` <= $maxLat AND
		`doc_add_long` >= $minLong AND `doc_add_long` <= $maxLong) AS firstQuery";
	
	$totalQuery = "SELECT * FROM $firstQuery WHERE Distance < $boundingRadius
		ORDER BY Distance ASC
		LIMIT $numItems OFFSET $offsetItems";

	$result = $con->query($totalQuery);
	
	if (!$result)
	{
		die('Error:  '. $totalQuery . $con->error);
	}
	mysqli_close($con);
  
  	if(mysqli_num_rows($result)==0) //no matches
  	{	  
		return NULL;
  	}
  	elseif(mysqli_num_rows($result)>0) //matches exist
 	{

     //construct geoDataPoints array
	 while($row = mysqli_fetch_array($result))
	 {
		 //push into the array
		 	
		 $geoDataPoints[] = array (
			 "Name" => $row["doc_name"], 
			 "Address" => $row["doc_address"],
			 "geoPoint" => array ( 
			 	"lat" => $row["doc_add_lat"],
				"long" => $row["doc_add_long"] ),
			 "Distance" => $row["Distance"],
		 	);		 
	 }
	 return $geoDataPoints;
  }
}

/* Objective: Retrieve nearby items to an indicated geoPoint from the database, sorted by distance from geoPoint, between the two bouding Radii
	Inputs: geoPoint, boundingRadius1 - inner, boundingRadius2 - outer, numItems (Optional=10), offset (Optional=0), tableName
			geoPoint ( "lat", "long" ) specified as degrees in decimals
	Returns: array of geoDataPoints (Name, Address, Phone, geoPoint, Distance), sorted by distance from input geoPoint; -1 otherwise
*/
function findNearbyDouble($geoPoint, $boundingRadius1, $boundingRadius2, $numItems = 10, $offsetItems = 0, $tableName)
{

	/* credits for below code: http://www.movable-type.co.uk/scripts/latlong-db.html */

	//identifying base elements
	$lat = $geoPoint["lat"];
	$long = $geoPoint["long"];
	$radLat = deg2rad($lat);
	$radLong = deg2rad($long);
	
	$earthR = 6371; //Earth's radius in kms
	
	//first-cut outer bounding box construction (in degrees)
	$maxLat = $lat + rad2deg($boundingRadius2/$earthR);
	$minLat = $lat - rad2deg($boundingRadius2/$earthR);
	$maxLong = $long + rad2deg($boundingRadius2/$earthR/cos(deg2rad($lat)));
	$minLong = $long - rad2deg($boundingRadius2/$earthR/cos(deg2rad($lat)));
	
	//connect to database
    $con = dbConnect();
	
    /*construct query and run into database*/
    $firstQuery = "(SELECT *, acos(sin($radLat)*sin(radians(`doc_add_lat`)) + cos($radLat)*cos(radians(`doc_add_lat`))*cos(radians(`doc_add_long`)-$radLong))*$earthR AS Distance 
		FROM $tableName WHERE 
		`doc_add_lat` >= $minLat AND `doc_add_lat` <= $maxLat AND
		`doc_add_long` >= $minLong AND `doc_add_long` <= $maxLong) AS firstQuery";
	
	$totalQuery = "SELECT * FROM $firstQuery WHERE Distance < $boundingRadius2 AND Distance >= $boundingRadius1
		ORDER BY Distance ASC
		LIMIT $numItems OFFSET $offsetItems";

	$result = $con->query($totalQuery);
	
	if (!$result)
	{
		die('Error:  '. $totalQuery . $con->error);
	}
	mysqli_close($con);
  
  	if(mysqli_num_rows($result)==0) //no matches
  	{	  
		return NULL;
  	}
  	elseif(mysqli_num_rows($result)>0) //matches exist
 	{

     //construct geoDataPoints array
	 while($row = mysqli_fetch_array($result))
	 {
		 //push into the array
		 	
		 $geoDataPoints[] = array (
			 "Name" => $row["doc_name"], 
			 "Address" => $row["doc_address"],
			 "geoPoint" => array ( 
			 	"lat" => $row["doc_add_lat"],
				"long" => $row["doc_add_long"] ),
			 "Distance" => $row["Distance"],
		 );		 
	 }
	 return $geoDataPoints;
  }
}

/* Objective: Retrieve count nearby items to an indicated geoPoint from the database
	Inputs: geoPoint, tableName
			geoPoint ( "lat", "long" ) specified as degrees in decimals
	Returns: array of resultCounts for ("<1","1-5","5+") distances
*/
function findCount($geoPoint, $tableName)
{

	/* credits for below code: http://www.movable-type.co.uk/scripts/latlong-db.html */

	//identifying base elements
	$lat = $geoPoint["lat"];
	$long = $geoPoint["long"];
	$radLat = deg2rad($lat);
	$radLong = deg2rad($long);
	
	$earthR = 6371; //Earth's radius in kms
	
	//connect to database
    $con = dbConnect();
	
	
	//first-cut bounding box construction (in degrees) - for inner-most circle
	$boundingRadius = 1;
	$maxLat = $lat + rad2deg($boundingRadius/$earthR);
	$minLat = $lat - rad2deg($boundingRadius/$earthR);
	$maxLong = $long + rad2deg($boundingRadius/$earthR/cos(deg2rad($lat)));
	$minLong = $long - rad2deg($boundingRadius/$earthR/cos(deg2rad($lat)));

    /*construct query and run into database*/
    $firstQuery = "(SELECT *, acos(sin($radLat)*sin(radians(`doc_add_lat`)) + cos($radLat)*cos(radians(`doc_add_lat`))*cos(radians(`doc_add_long`)-$radLong))*$earthR AS Distance 
		FROM $tableName WHERE 
		`doc_add_lat` >= $minLat AND `doc_add_lat` <= $maxLat AND
		`doc_add_long` >= $minLong AND `doc_add_long` <= $maxLong) AS firstQuery";
		
	$totalQuery1 = "SELECT COUNT(*) FROM $firstQuery WHERE Distance < $boundingRadius";
	$result = mysqli_query($totalQuery1,$con);
	$row = mysqli_fetch_row($result);
	$counters["first"] = $row[0];

	//first-cut bounding box construction (in degrees) - for second circle	
	$boundingRadius = 5;
	$maxLat = $lat + rad2deg($boundingRadius/$earthR);
	$minLat = $lat - rad2deg($boundingRadius/$earthR);
	$maxLong = $long + rad2deg($boundingRadius/$earthR/cos(deg2rad($lat)));
	$minLong = $long - rad2deg($boundingRadius/$earthR/cos(deg2rad($lat)));

	/*construct query and run into database*/
    $firstQuery = "(SELECT *, acos(sin($radLat)*sin(radians(`doc_add_lat`)) + cos($radLat)*cos(radians(`doc_add_lat`))*cos(radians(`doc_add_long`)-$radLong))*$earthR AS Distance 
		FROM $tableName WHERE 
		`doc_add_lat` >= $minLat AND `doc_add_lat` <= $maxLat AND
		`doc_add_long` >= $minLong AND `doc_add_long` <= $maxLong) AS firstQuery";
				
	$totalQuery2 = "SELECT COUNT(*) FROM $firstQuery WHERE Distance >= 1 AND Distance < 5";
	$result = $con->query($totalQuery2);
	$row = mysqli_fetch_row($result);
	$counters["second"] = $row[0];

	//first-cut bounding box construction (in degrees) - for rest of the circle
	$boundingRadius = 50;
	$maxLat = $lat + rad2deg($boundingRadius/$earthR);
	$minLat = $lat - rad2deg($boundingRadius/$earthR);
	$maxLong = $long + rad2deg($boundingRadius/$earthR/cos(deg2rad($lat)));
	$minLong = $long - rad2deg($boundingRadius/$earthR/cos(deg2rad($lat)));
	
	/*construct query and run into database*/	
	$firstQuery = "(SELECT *, acos(sin($radLat)*sin(radians(`doc_add_lat`)) + cos($radLat)*cos(radians(`doc_add_lat`))*cos(radians(`doc_add_long`)-$radLong))*$earthR AS Distance 
		FROM $tableName WHERE 
		`doc_add_lat` >= $minLat AND `doc_add_lat` <= $maxLat AND
		`doc_add_long` >= $minLong AND `doc_add_long` <= $maxLong) AS firstQuery";
			
	$totalQuery3 = "SELECT COUNT(*) FROM $firstQuery WHERE Distance >= 5 AND Distance < 50";
	$result = $con->query($totalQuery3);
	$row = mysqli_fetch_row($result);
	$counters["third"] = $row[0];

	if (!$result)
	{
		die('Error:  ' . mysql_error());
	}
	mysql_close($con);
  
	return $counters;
}

/* 
	Objective: retrieve doctor details for a doctor with a certain doctor name
	Inputs: url, data to send, optional headers, get response (if sync required)
	Returns: TRUE- if Ok, FALSE - Else
*/
function do_async_post($url, $data, $optional_headers = null,$getresponse = false) {
      $params = array('http' => array(
                   'method' => 'POST',
                   'content' => $data
                ));
      if ($optional_headers !== null) {
         $params['http']['header'] = $optional_headers;
      }
      $ctx = stream_context_create($params);
      $fp = @fopen($url, 'rb', false, $ctx);
      if (!$fp) {
        return false;
      }
      if ($getresponse){
        $response = stream_get_contents($fp);
        return $response;
      }
    return true;
}