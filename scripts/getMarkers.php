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

	$array_in = $_POST["foodCategory"];

	/*
	foreach($array_in as $element)
	{
		echo $element." ";
	}
	*/
	foreach($array_in as $element)
	{
		switch ($element) {
		    case "Meat":
		        $meat = true;
		        break;
		    case "Vegetables":
		        $vegetables = true;
		        break;
		    case "Fruits":
		        $fruits = true;
		        break;
		    case "Eggs":
		        $eggs = true;
		        break;
		    case "Dairy":
		        $dairy = true;
		        break;
		}
	}
		

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

	// while($row = mysql_fetch_row($result))
	// {
	// 	$count = $row[0];
	//     echo $count;
	// }

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