<?php

//include_once('codes/masterfunctions.php'); //for database access
define('MAX_VISITS_PER_IP', 50);
date_default_timezone_set('Asia/Kolkata');
$open_ips = array('122.167.68.68');

//first call on any page visit
function traceVisits()
{
	global $open_ips;

	//1. store details about the user	
	$ip = getIP();

	//2. If open access user, do not limit number of visits
	if(array_search($ip,$open_ips) === FALSE)
	{
		//3. if number of visits from IP address > MAX_VISITS - add user to negative list
		checkUser($ip);
	}
	else
	{
		//3. track visits from 'friendly' URLs anyhow
		$date = date("Y-m-d");
		if(isset($counter)){
		$counter = $counter+1;} else {$counter = 1;}
		//increment visit counter - asynchronous to prevent holdout
	 	do_async_post("http://www.medly.in/testenv/simplydoc/addCounter.php?ip=$ip&date=$date&counter=$counter",'');
		}
}

traceVisits();

function getIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

//check if user is in the negative list and redirect - must always be called first on page load
function checkUser($ip)
{
	$con 	= dbConnect();
	
	$date 	= date("Y-m-d"); //today
	$query 	= "SELECT numvisits from visitor_table WHERE `ip` = '$ip' and DATE_FORMAT(`date`,'%Y-%m-%d') = '$date'";
	$result = $con->query($query);
	if (!$result)
	{
		error_log('Error: '. $query . $con->error);
		return;
	}	
	$row 	= mysqli_fetch_row($result);
	if($row != NULL)
	{
		$counter = $row[0];
		if($counter > MAX_VISITS_PER_IP)
		{
			//redirect to exceeded maximum visits page
			header( 'Location: over_ip_limits.html ');
		}
	}

	//increment visit counter - asynchronous to prevent holdout
	if(isset($counter)){
	$counter = $counter+1;} else {$counter = 1;}
 	do_async_post("http://www.medly.in/testenv/simplydoc/addCounter.php?ip=$ip&date=$date&counter=$counter",'');
}

?>