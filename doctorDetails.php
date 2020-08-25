<?php

include_once("codes/masterfunctions_detailed.php");

//trackuser to check acces permissions
include_once("trackUsers.php");
traceVisits();

//create user page
if(isset($_GET["doctor"]))
{
	$docName = str_replace("-"," ",$_GET["doctor"]);	
	$docDetails = getDocDetails($docName); //needs to be updated
}
else
{
	header("Location:index.php");
}

?>
<!DOCTYPE html>
<head>
<title>Medly | Profile of <?php echo $docName; ?></title>
<meta name="description" content="Medly | Profile of <?php echo $docName; ?>" />
<meta name="keywords" content="health, healthcare, doctors, doc, hospitals, medly, medical, clinics, physicians, search, find, find a doctor, near, nearby, closest, bangalore, karnataka, mumbai, gurgaon, delhi"/>  
<meta name="viewport" content="initial-scale=1.0, width=device-width"/>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

<!-- stylesheets -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/bootstrap-responsive.min.css" rel="stylesheet">
<link href="css/simplyCSS.css" rel="stylesheet">
<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.css" />
<link rel="stylesheet" href="css/select2.css" />
     <!--[if lte IE 8]>
         <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.ie.css" />
     <![endif]-->

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    
<!-- javascripts -->
<!--
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="codes/StyledMarker.js"></script>-->
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" src="http://twitter.github.com/bootstrap/assets/js/bootstrap-typeahead.js"></script>
<script type="text/javascript" src="codes/myCodesDetails.js"></script>
<script type="text/javascript" src="codes/GA.js"></script>
<script src="http://cdn.leafletjs.com/leaflet-0.4/leaflet.js"></script>
<script src="codes/select2.min.js"></script>
<!--<script type="text/javascript" src="codes/kiss.js"></script>-->
<!--<script type="text/javascript" src="codes/zopim.js"></script>-->

<!-- facebook tags -->
<meta property="og:title" content="Medly" />
<meta property="og:type" content="website" />
<meta property="og:description" content="Find doctors and healthcare facilities nearby" />
<meta property="og:url" content='<?php echo "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]."?doctor=".$docName;?>' />
<meta property="og:site_name" content="Medly" />
<meta property="fb:admins" content="500073300" />
<meta property="og:image" content="" />

</head>
<body onload="initialize('<?php echo $docDetails["geoPoint"]["lat"]."','".$docDetails["geoPoint"]["long"]."','".$docName."'";?>)">
<div class="container-fluid">
<div id="header" class="row-fluid">
    <div class="span3">
        <div id="logo" class="row-fluid">
            <h1 class="logo_class"><a href="index.php">Medly<img src="images/medly_logo.png" alt="Medly"/></a></h1>
        </div>
      <!--<div id="whatsThis" class="row-fluid">find doctors nearby</div>-->
    </div>
    <div id="searchBar" class="span9">
            <form id="searchForm" class="form-search">
                <div class="input-append" class="span7">
                    <input type="text" id="myAddress" placeholder="Type location to find doctors and healthcare facilities nearby" title="Type location here" pattern=".{3,}" data-provide="typeahead" data-source='["Bangalore","Chennai","Delhi","Mumbai"]' data-items="4" required/>
                    <?php include_once('specs_list.php'); ?>
                    <button id="searchBtn" type="submit" class="btn btn-success"><i class="icon-search icon-white"></i>&nbsp;Find</button>
                <!--
                <label>&nbsp;OR&nbsp;</label>
                <input type="button" id="myLocation" class="btn btn-success" value="Search near my Location"/>
                -->
                </div>      
          <div id="myLocAlert" class="alert alert-error hide"></div>
        </form>
    </div>
</div>
<div id="content" class="row-fluid">
    <div id="resultsHeader" class="span12">
        <!--<span class="largeText">Doctor details:</span>-->
        <span class="back_link"><a href="#" id="back_link">&lsaquo;&lsaquo; Back to search</a></span>  
        <ul class="breadcrumb" id="nav_link">
          <li><a href="index.php">Home</a> <span class="divider">/</span></li>
          <li><a href="#"><?php echo $docDetails['State']; ?></a> <span class="divider">/</span></li>
          <li><a href="#"><?php echo $docDetails['City']; ?></a> <span class="divider">/</span></li>
          <li class="active">Doctor</li>
        </ul>
	   <hr class="blueline"/>
    </div>
	<div id="resultsContainer" class="span12">
        <div class="row-fluid">
          <div id="mapResults" class="span9">
            <div id="mapCanvas" style="min-height:500px;height:100%;width:100%;"></div>        
          </div>
          <div id="textResults" class="span3">
          <h3><?php echo $docName; ?></h3> 
    		  <?php 
    				echo '<div itemscope itemtype="http://data-vocabulary.org/Organization" id="result">
    							<address itemprop="address" itemscope itemtype="http://data-vocabulary.org/Address"/>';
    				if($docDetails['Address'] !== "") { echo $docDetails["Address"].'<br/><br/>'; }
                    if($docDetails['Specs'] !== "") { echo '<abbr title="Specializations" itemprop="specializations"><b>Specializations:</b> '.$docDetails["Specs"].'</abbr><br/>'; }
                    if($docDetails['Timings'] !== "") { echo '<abbr title="Timings" itemprop="timings"><b>Timings:</b> '.$docDetails["Timings"].'</abbr><br/>'; }
                    if($docDetails['Phone'] !== "") { echo '<abbr title="Phone" itemprop="tel"><b>Phone#:</b> '.$docDetails["Phone"].'</abbr><br/>'; }
    				echo '</address>
    				</div>'; 	  
    		  ?>
            <?php include_once("facebook_like.php"); ?>
          </div>
        </div>
    </div>    
</div>
<div id="footer" class="row-fluid">
    <?php include_once('footer.php'); ?>
</div>
</div><!--for the container-->
</body>
</html>