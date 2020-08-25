<?php

include_once("/home/ec2-user/webroot/medly/testenv/simplydoc/codes/masterfunctions_detailed.php");

//add user counter
$ip = $_GET['ip'];
$date = $_GET['date'];
$counter = $_GET['counter'];

$con = dbConnect();

$query = "DELETE FROM visitor_table WHERE ip= '$ip' and DATE_FORMAT(`date`,'%Y-%m-%d') = '$date'";
$result = $con->query($query);

$query 	= "INSERT INTO visitor_table (ip,date,numvisits) VALUES ('$ip','$date','$counter') ";
$result = $con->query($query);
if (!$result) 
{
	//error_log("Error: $ip: $date: $counter" . $con->error);
   	error_log('Error: $ip: $date: $counter' . $con->error);
}
mysqli_close($con);

?>