<?php
	header('X-Frame-Options: GOFORIT'); 

	include("../../hackVTconf.php");
	//connect to server
	$link = mysql_connect($database, $username, $password);
	if (!$link) {
	    die('Not connected : ' . mysql_error());
	}

	//connect to database
	$db_selected = mysql_select_db('PVENDEVI_HackVT', $link);
	if (!$db_selected) {
	    die ('Can\'t use foo : ' . mysql_error());
	}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>HackVT: Collateral Damage - Recipe Farm</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<!-- Mobile viewport optimized: h5bp.com/viewport -->
		<meta name="viewport" content="width=device-width">
        <meta name="author" content="Dillan, Phelan, Garth, Scott, Ethan." />
        <meta name="description" content="" />
        <meta name="keywords" content="HackVT, Hackathon, Collateral Damage, Recipe Farm, etc." />

        <!--[if IE]>
			<style>
				#frame 
				{
	    			zoom: 0.2;
				}
			</style>
		<[endif]-->

		<link href='http://fonts.googleapis.com/css?family=Carrois+Gothic' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="css/style.css">
		<!--google maps api-->
		<script 
			type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyD6IjVeJmEWLaBdvZDNBbpj0WzbrWSxrp8&amp;sensor=true">
		</script><!-- end google api map key -->
		<script type="text/javascript">
	      function initialize() {
	        
	        //default map options
        	var myOptions = 
			{
	          center: new google.maps.LatLng(43.80599, -72.729492),
	          zoom: 7,
	          mapTypeId: google.maps.MapTypeId.ROADMAP
	        };

	        //make map
			var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

			//call downloadurl and get all marker elements
			downloadUrl("getMarkers.php", function(data) 
			{
				var markers = data.documentElement.getElementsByTagName("marker");
				
				//loop through the xml file and grab all the necessary information
	      		for (var i = 0; i < markers.length; i++) 
				{
	        		var latlng = new google.maps.LatLng(parseFloat(markers[i].getAttribute("latitude")),
			                                    parseFloat(markers[i].getAttribute("longitude")));
			
					var name = markers[i].getAttribute("name");
					//var address = markers[i].getAttribute("address");
			        var marker = new google.maps.Marker({position: latlng, map: map});
			
					var html = "<b>" + name + "</b> <br/>"; 	//+ address;
					var infowindow_1 = new google.maps.InfoWindow({content: html});
					
					//create the marker on the map
					createMarker(latlng, marker, infowindow_1);
			    }
 			});

			//load object lets you retrieve a file that resides on the same domain as the requesting webpage
			function downloadUrl(url,callback) 
			{
				var request = window.ActiveXObject ?
				new ActiveXObject('Microsoft.XMLHTTP') :
				new XMLHttpRequest;

				request.onreadystatechange = function() 
				{
					if (request.readyState == 4) 
					{
					request.onreadystatechange = doNothing;
					callback(request, request.status);
					}
			 	};

				request.open('GET', url, true);
				request.send(null);
			}
	      }
	    </script><!-- end google maps initilizer -->
	</head>

	<body onload="initialize()">
		<div class="container_12 shadow gridContent">  
		    <header>
			    <div class="grid_10" style="padding-bottom:0px;"><h1>LocalVoracious.</h1></div><!-- end header -->
			    <div class="clear"></div>  
			    <!--<div class="grid_12" style="background-color:green">
				    <nav>
						<ul>
							<li><a href="index.php">Home</a></li>
							<li><a href="link2.php">Link 2</a></li>
							<li>
								<a href="dropdown1.php">Drop Down 1</a>
								<ul class="submenu">
									<li>
										<a href="link4.php">Link 4</a>
										<a href="link5.php">Link 5</a>
									</li>
								</ul>
							</li>
							<li>
								<a href="dropdown2.php">Drop Down 2</a>
								<ul class="submenu">
									<li><a href="link6.php">Link 6</a></li>
								</ul>
							</li>
							<li><a href="link3.php">Link 3</a></li>
						</ul>
					</nav>--><!--end nav -->
			    <!--</div>--><!-- end navigation -->
		    </header>
		    <div class="grid_3 wingContent" style="height:480px">
		    	<div class="paddingFix">
						
						<div class="grid_3 alpha">
							<form name="input" action="scripts/getMarkers.php" method="post">
					    		<select id="categorySelector" name="foodCategory[]" multiple="multiple">
								    <option value="Meat">Meat</option>
								    <option value="Vegetables">Vegetables</option>
								    <option value="Fruits">Fruits</option>
								    <option value="Eggs">Eggs</option>
								    <option vaule="Dairy" selected="selected">Dairy</option>
								</select><!-- end foodCategory -->
					    		<input name="submit" type="submit">
							</form>

					    </div><!-- end food catagory selector div -->

					    <div class="grid_3 alpha">
					    	<select id="ingredientSelector" name="foodCategory" multiple="multiple"></select><!-- end foodCategory -->
					    </div>

		    	</div><!-- end padding-fix -->
			</div><!-- end left-content -->  

		    <div class="grid_6 wingContent">
			    	<div id="map_canvas" style="width:460px; height:480px; "></div><!-- Google Map Canvas -->
			</div><!-- end middle-content -->
		    
		    <div class="grid_3 wingContent" style="height:480px;">
		    	<div class="paddingFix">
			    	<div class="grid_3 alpha">
		    			<select id="recipeSelector" name="recipeSelected">
		    			</select>
		    		</div>
		    		<div class="grid_3 alpha">
		    			<iframe id="frame" src="http://www.google.com">
		    			</iframe>
		    		</div>
			    </div><!-- end padding-fix -->
			</div><!-- end right-content -->

		    <div class="clear"></div>

		    <footer class="footerstyle">
			    <div class="grid_12">
			    	<p>Dillan, Phelan, Garth, Ethan, Scott @ HackVT 2012</p>
			    </div>
			</footer>
		</div><!--end 12 column container -->

		<!-- JavaScript -->
			<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script><!--import jquery from google-->
			<script src="scripts/selectionTables.js"></script><!-- code to perform ingredient table selection -->
		<!-- end JavaScript -->
	</body>
</html>