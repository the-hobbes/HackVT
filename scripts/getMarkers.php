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
		//echo $array_in[$i];
	}

	$join = " JOIN "; 
	//build basic query
	$query = "SELECT DISTINCT name,latitude,longitude FROM `new_farms`";
	// ON (`new_farms`.id = `raspberries`.farm_id 

	foreach($array_in as $element)
	{
		$query = $query . $join . $element;
	}

	//need to put closing ) at the end!!!
	$query = $query . " ON ( ";

	//match all individual selections with the farm that has them
	foreach($array_in as $element)
	{
		$query = $query . "`new_farms`.id = ". $element .".farm_id" ." AND ";
	}
	//remove trailing AND
	$query = substr($query, 0, -5);

	//get all combinations of selections, then keep only the pair combos
	function depth_picker($arr, $temp_string, &$collect) {
	    if ($temp_string != "") 
	        $collect []= $temp_string;

	    for ($i=0; $i<sizeof($arr);$i++) {
	        $arrcopy = $arr;
	        $elem = array_splice($arrcopy, $i, 1); // removes and returns the i'th element
	        if (sizeof($arrcopy) > 0) {
	            depth_picker($arrcopy, $temp_string ." " . $elem[0], $collect);
	        } else {
	            $collect []= $temp_string. " " . $elem[0];
	        }   
	    }   
	}

	$pairs = array();
	$collect = array();
	depth_picker($array_in, "", $collect);
	#we took care of comparing farm_id to each selection, so now generate the pairs
	foreach ($collect as $value) {
		$temp = explode(" ", trim($value));

		if (count($temp)==2) {
			#keep the pair!
			$pairs[] = $temp;
		}
	}
	# now pairs has every pair of the selections
	# now match each selectionI.farm_id = selectionJ.farm_id
	foreach ($pairs as $item) {
		$query = $query . " AND " . $item[0] . '.farm_id = ' . $item[1] . '.farm_id';
	}
	#closing ) !!!
	$query .= ')';

	//echo $query;		

	function parseToXML($htmlStr) 
	{ 
		$xmlStr=str_replace('<','&lt;',$htmlStr); 
		$xmlStr=str_replace('>','&gt;',$xmlStr); 
		$xmlStr=str_replace('"','&quot;',$xmlStr); 
		$xmlStr=str_replace("'",'&#39;',$xmlStr); 
		$xmlStr=str_replace("&",'&amp;',$xmlStr); 
		return $xmlStr; 
	} 

	// echo $query;
	$result = mysql_query($query);

	if (!$result) 
	{
	  die('Invalid query: ' . mysql_error());
	}
	//var_dump($result);

	header("Content-type: text/xml");

	// Start XML file, echo parent node
	echo '<markers>';

	// Iterate through the rows, printing XML nodes for each
	while ($row = @mysql_fetch_assoc($result)){
	  // ADD TO XML DOCUMENT NODE
	  echo '<marker ';
	  echo 'name="' . parseToXML($row['name']) . '" ';
	  echo 'lat="' . $row['latitude'] . '" ';
	  echo 'lng="' . $row['longitude'] . '" ';
	  echo '/>';
	}

	// End XML file
	echo '</markers>';	    
?>