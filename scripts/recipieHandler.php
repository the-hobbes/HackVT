<?php
	require_once("../GetRecipies.php");

	$recipie-> new GetRecipes();
	$array_in = $_REQUEST['selectedOptions'];

	$recipie->set_ingredients($array_in);
	$result_recipie = $recipie->query_recipe();

	header("Content-type: text/xml");
	// Start XML file, echo parent node
	echo '<recipies>';
	//$datab = new mySQLDB($link);
	// Iterate through the rows, printing XML nodes for each
	foreach($result_recipie as $row){
	  // ADD TO XML DOCUMENT NODE
	  echo '<recipie ';
	  echo 'title="' . parseToXML($row['title']) . '" ';
	  echo 'href="' . $row['href'] . '" ';
	  echo 'ingredients="' . $row['ingredients'] . '" ';
	  echo 'thumbnail="' . $row['thumbnail'] . '" ';
	  echo '/>';
	}

	// End XML file
	echo '</recipies>';
?>