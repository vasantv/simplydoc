<?php
	
	include_once("codes/masterfunctions.php");
	include_once("codes/GoogleMap.php");
	include_once("codes/JSMin.php");
		
	//Note: Used to display an initial default page of results  before internal search
	function defaultPrinter($location)
	{	
		if(!isset($location)){ 
			$location = "Bangalore"; //by default location is Bangalore
		}
		
		//initializing the PHP-GoogleMap APIs
		$mapObject = new GoogleMapAPI();
		$mapObject->_minify_js = TRUE;
		$geoCodes = $mapObject->getGeocode($location);
			
		//read request lat & long
		$lat = $geoCodes["lat"];
		$long = $geoCodes["lon"];
				
		//initializing defualt variables
		$rad1 = 0;
		$rad2 = 1;
		$num = 10;
		$offset = 0;
		
		//query database
		$geoPoint = array (
			"lat" => $lat,
			"long" => $long
			);
			
		$results = findNearbyDouble($geoPoint,$rad1,$rad2,$num,$offset);
		
			$resultCount = 1;
			//construct html and echo out
			foreach ($results as $gdPoint)
			{				
				echo '<div itemscope itemtype="http://data-vocabulary.org/Organization" id="result'.$resultCount.'" >
					  <span>'.$resultCount.'.</span>
						<span itemprop="name"><strong><a href="http://www.simplydoc.in/doctor/'.str_replace(" ","-",$gdPoint["Name"]).'/">'.$gdPoint["Name"].'</a></strong></span>
						<span itemprop="distance" class="greyText"> ('.round($gdPoint["Distance"],2).' kms)</span><br/>';
				if($gdPoint['Address'] != "")
				{
					  echo 	'<address itemprop="address" itemscope itemtype="http://data-vocabulary.org/Address">
						'.$gdPoint["Address"].'</address>';
				}
				if($gdPoint['Spec'] != "")
				{
					  echo 	'<br/><i class="icon-leaf"></i> '.$gdPoint["Spec"];
				}
				echo '</div>';
				$resultCount++;
			}
			echo '<div id="resultPages">
					<ul class="pager">
					  <li>
						<a href="" id="nextPage" class="next">Next &rsaquo;&rsaquo;</a>
					  </li>
					</ul>
				  </div>';
	}

?>