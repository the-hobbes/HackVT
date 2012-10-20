<?php
//Scott MacEwan
//10/19/2012
require_once('farmLocations.php');
$farmLocations = new FarmLocations();
$locations = $farmLocations->insert_db();

var_dump($locations);

?>