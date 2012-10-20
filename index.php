<?php
include("../../hackVTconf.php");
session_start();

//connect to server
$link = mysql_connect($database, $username, $password);
if (!$link) {
    die('Not connected : ' . mysql_error());
}

//connect to database
$db_selected = mysql_select_db('PVENDEVI_HackVT', $link);
if (!$db_selected) {
    die ('Can\'t use foo : ' . mysql_error());

include("scripts/GetRecipes.php");
$recipes = new GetRecipes();
$recipes->set_ingredients($_SESSION['receipes']);
$qRecipe = $recipes->query_recipe();

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

		<link href='http://fonts.googleapis.com/css?family=Carrois+Gothic' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" href="css/style.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script><!--import jquery from google-->
		<!--google maps api-->
		<script 
			type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyD6IjVeJmEWLaBdvZDNBbpj0WzbrWSxrp8&amp;sensor=true">
		</script><!-- end google api map key -->
		<script src="scripts/selectionTables.js"></script>
		<script type="text/javascript">
	      function initialize() {
	        
	        //default map options
        	myOptions = 
			{
	          center: new google.maps.LatLng(43.80599, -72.729492),
	          zoom: 7,
	          mapTypeId: google.maps.MapTypeId.ROADMAP
	        };

	        //make map
			map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	      }
	    </script><!-- end google maps initilizer -->
	</head>

	<body onload="initialize()">
		<div class="container_12 shadow gridContent">  
		    <header>
			    <div class="grid_10" style="padding-bottom:0px;"><h1>GreenBean</h1></div><!-- end header -->
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
							<form name="input" >
					    		<select id="categorySelector" name="foodCategory" multiple="multiple">
								    <option value="Meat">Meat</option>
								    <option value="Vegetables">Vegetables</option>
								    <option value="Fruits">Fruits</option>
								    <option value="Eggs">Eggs</option>
								    <option vaule="Dairy" selected="selected">Dairy</option>
								</select><!-- end foodCategory -->
				    		<!--<input id="submit" name="submit" type="submit">-->
							</form>
					    </div><!-- end food catagory selector div -->

					    <div class="grid_3 alpha">
					    	<select id="ingredientSelector" name="ingredientCategory" multiple="multiple"></select><!-- end foodCategory -->
					    </div>
					    <button type="button" onclick="collectResult()">Submit</button>
					    <button type="button" onclick="collectRecipie()">Show Recipie</button>
		    	</div><!-- end padding-fix -->
			</div><!-- end left-content -->  

		    <div class="grid_6 wingContent">
			    	<div id="map_canvas" style="width:460px; height:480px; "></div><!-- Google Map Canvas -->
			</div><!-- end middle-content -->
		    
		    <div class="grid_3 wingContent" style="height:480px;">
		    	<div class="paddingFix">
			    	<div class="grid_3 alpha">
                        <select id="recipeSelector" name="recipeSelected" size="3">
                        	<option value="Meat">Recipe 1</option>
                        	<option value="Vegetables">Recipe 2</option>
                        	<option value="Fruits">Recipe 3</option>
                        	<option value="Eggs">Recipe 4</option>
                        	<option vaule="Dairy" selected="selected">Recipe 5</option>
                        </select>
                    </div>
                    <div class="grid_3 alpha" style="width: 100%;">
                            <iframe id="frame" src="http://www.uvm.edu"></iframe>
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
		<div id="hiddenStuff">

		</div>
		<!-- JavaScript -->
			<script src="scripts/selectionTables.js"></script><!-- code to perform ingredient table selection -->
			<script>

			function collectRecipie()
				{
					var x=document.getElementById("ingredientSelector");
					selectedOptions = new Array();

					for (i=0;i<x.length;i++)
					{
						if (x.options[i].selected)
							selectedOptions.push(x.options[i].text);
					}

					//alert(selectedOptions);
					passToPhpRecipie(selectedOptions);
				}

				var passToPhpRecipie = function(selectedOptions) {
		           jQuery.post("scripts/recipieHandler.php", {selectedOptions : selectedOptions}, 
					function(data)
					{
						var recipies = data.getElementsByTagName("recipies");
						for (var i = 0; i < recipies.length; i++) 
						{
							var title =  recipies[i].getAttribute("title");
							var href = recipies[i].getAttribute("href");
							var ingredients =recipies[i].getAttribute("ingredients");
							var thumbnail = recipies[i].getAttribute("thumbnail");

							document.getElementById("recipie_here").innerHTML = "<p><h3>"+title+"</h3>"+"<br />"+href+"<br />"+ingredients+"<br /></p>";
						}
					})
			    };


				var passToPhp = function(selectedOptions) {
		           jQuery.post("scripts/getMarkers.php", {selectedOptions : selectedOptions}, 
					function(data)
					{
						initialize();
						//alert(data);

						//success function
						var markers = data.getElementsByTagName("marker");
						
						//alert(markers);

						//loop through the xml file and grab all the necessary information
			      		for (var i = 0; i < markers.length; i++) 
						{
			        		var latlng = new google.maps.LatLng(parseFloat(markers[i].getAttribute("lat")),
                            parseFloat(markers[i].getAttribute("lng")));
							//alert(latlng);
							var name = markers[i].getAttribute("name");
							//var address = markers[i].getAttribute("address");
							var color = markers[i].getAttribute("fillColor");
					        var marker = new google.maps.Marker({position: latlng, map: map });

							var html = "<b>" + name + "</b> <br/>"; 	//+ address;
							var infowindow_1 = new google.maps.InfoWindow({content: (name+" "+color)});
							//alert(infowindow_1);
							//create the marker on the map

							createMarker(latlng, marker, infowindow_1);
					    }
					})
			    };

			    var previousBool = false;
			    var previousMarker = "";

				function collectResult()
				{
					var x=document.getElementById("ingredientSelector");
					selectedOptions = new Array();

					for (i=0;i<x.length;i++)
					{
						if (x.options[i].selected)
							selectedOptions.push(x.options[i].text);
					}

					//alert(selectedOptions);
					passToPhp(selectedOptions);
				}

				//create marker
				function createMarker(latlng, marker, infowindow_1)
				{
					//add the click event listener
					google.maps.event.addListener(marker, 'click', function() 
					{	
						//logic to keep only one infomation window open at a time and center on the marker clicked
						if(previousBool == true)
						{
							previousMarker.close();
							previousMarker = infowindow_1;
							infowindow_1.open(map,marker);
							previousBool = true;
							map.setCenter(latlng);
						}
						else
						{
							infowindow_1.open(map,marker);
							previousMarker = infowindow_1;
							previousBool = true;
							map.setCenter(latlng);
						}
						
					});
				}
			</script>
		<!-- end JavaScript -->
	</body>
</html>