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

	if (isset($_POST["submit"])){
		
		echo "success!";
	}
	else
		echo "no success!";

	

?>