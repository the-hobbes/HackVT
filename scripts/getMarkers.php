<?php
	include("../../../hackVTconf.php");
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

	//$array_in = $_POST["selectedOptions"];

	$array_in = $_REQUEST['selectedOptions'];

	//lowercase all variables
	for($i=0; $i<count($array_in); $i++)
	{
		$array_in[$i] = strtolower($array_in[$i]);
		echo $array_in[$i];
	}

	$join = " JOIN ";= 
	//build basic query
	$query = "SELECT DISTINCT name,latitude,longitude FROM `new_farms`";
	// ON (`new_farms`.id = `raspberries`.farm_id 

	foreach($array_in as $element)
	{
		$query = $query . $join . $element;
	}

	$query = $query . " ON ( ";

	foreach($array_in as $element)
	{
		$query = $query . "`new_farms`.id = ". $element .".farm_id" ." AND ";
	}
	//remove trailing AND
	$query = substr($query, 0, -5);

	for($array_in as $element)
	{
		$query = $query . ;
	}

	// foreach($array_in as $element)
	// {
	// 	switch ($element) {
	// 	    case "Meat":
	// 	        $meat = true;
	// 	        break;
	// 	    case "Vegetables":
	// 	        $vegetables = true;
	// 	        break;
	// 	    case "Fruits":
	// 	        $fruits = true;
	// 	        break;
	// 	    case "Eggs":
	// 	        $eggs = true;
	// 	        break;
	// 	    case "Dairy":
	// 	        $dairy = true;
	// 	        break;
	// 	}
	// }


	function parseToXML($htmlStr) 
	{ 
		$xmlStr=str_replace('<','&lt;',$htmlStr); 
		$xmlStr=str_replace('>','&gt;',$xmlStr); 
		$xmlStr=str_replace('"','&quot;',$xmlStr); 
		$xmlStr=str_replace("'",'&#39;',$xmlStr); 
		$xmlStr=str_replace("&",'&amp;',$xmlStr); 
		return $xmlStr; 
	} 

	// Select all the rows in the farms table
	$query = "SELECT * FROM farms WHERE ";
	$categories;

	if($meat)
		$categories .= "`meat`=1";
	if($vegetables)
	{
		if($categories != "")
			$categories .= " AND ";
		$categories .= "`vegetables`=1";
	}
	if($fruits)
	{
		if($categories != "")
			$categories .= " AND ";
		$categories .= "`fruits`=1";
	}
	if($eggs)
	{
		if($categories != "")
			$categories .= " AND ";
		$categories .= "`eggs`=1";
	}
	if($dairy)
	{
		if($categories != "")
			$categories .= " AND ";
		$categories .= "`dairy`=1";
	}

	$query .= $categories;
	// echo $query;
	$result = mysql_query($query);

	if (!$result) 
	{
	  die('Invalid query: ' . mysql_error());
	}

	header("Content-type: text/xml");

	// Start XML file, echo parent node
	echo '<markers>';

	// Iterate through the rows, printing XML nodes for each
	while ($row = @mysql_fetch_assoc($result)){
	  // ADD TO XML DOCUMENT NODE
	  echo '<marker ';
	  echo 'name="' . parseToXML($row['name']) . '" ';
	  echo 'address="' . parseToXML($row['address']) . '" ';
	  echo 'lat="' . $row['latitude'] . '" ';
	  echo 'lng="' . $row['longitude'] . '" ';
	  echo '/>';
	}

	// End XML file
	echo '</markers>';


?>