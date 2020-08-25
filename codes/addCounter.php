<?php

/* user counter updation - to be done as an Ajax query */
include_once("codes/masterfunctions.php");

//Add counter for a given IP address
$ip = $_POST['ip'];
$date = date('Y-m-d');

$conn = dbConnect();

$query = "UPDATE counter_table SET `counter` = `counter` + 1 WHERE `ip` = $ip and DATE_FORMAT(`date`,'%Y-%m-%d') = $date";
mysql_query($query,$conn);

//update access counter
$now = date('Y-m-d:H:i');
$query = "INSERT INTO access_table (ip,date) VALUES ('$ip.','$now')";
mysql_query($query,$conn);

?>