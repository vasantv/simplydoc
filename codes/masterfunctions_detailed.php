<?php 

/* functions available
	dbConnect - connect to the local database
	getDocDetails - get details of given doct
	do_async_post - post to a remote php file in an asynchronous manner
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

/* 
	Objective: retrieve doctor details for a doctor with a certain doctor name
	Inputs: doctorName
	Returns: array of docDetails (Name, Address, Phone, GeoPoint) for the doctor
*/
function getDocDetails($docName)
{
	
	//connect to database
    $con = dbConnect();
	
    /*construct query and run into database*/	
	$query = "SELECT doc_id FROM docMaster WHERE doc_name = '$docName'";

	$result = $con->query($query);	
	if (!$result)
	{
		error_log('Error:  '. $query . $con->error);
		return NULL;
	}
	$row = mysqli_fetch_row($result);
	$docId = $row[0];	

	$query2 = "SELECT * FROM docDetails WHERE doc_id = '$docId'";
	$result = $con->query($query2);	
	if (!$result)
	{
		error_log('Error: '. $query2 . $con->error);
		return NULL;
	}

  	if(mysqli_num_rows($result)==0) //no matches
  	{	  
		return NULL;
  	}
  	elseif(mysqli_num_rows($result)>0) //matches exist
 	{
		//construct geoDataPoints array
		$row = mysqli_fetch_array($result);
		 
		//push into the array
		$geoDataPoint = array(
					 "Name" => $row["doc_name"], 
					 "Address" => str_replace("xA0","",$row["doc_address"]), //temporarily to remove Unicode errors
					 "City" => $row["doc_add_city"],
					 "State" => $row["doc_add_state"],
					 "Phone" => $row["doc_phone"],
					 "Specs" => $row["doc_spec"],
					 "Timings" => $row["doc_timings"],
					 "geoPoint" => array ( 
						"lat" => $row["doc_add_lat"],
						"long" => $row["doc_add_long"] )
		);		 
	 }
	mysqli_close($con);

	return $geoDataPoint;		
  
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