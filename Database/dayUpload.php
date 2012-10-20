<?php
//This is a terrible terrible script to take a terrible piece of data(located in data.php) and make it better and into an sql database


include('data.php');
include('mySQLDB.php');

$db = new mySQLDB();

//Function to upload the day table to the database

//Get the station
$pos = strpos($data,'STATION:');
$secpos = strpos($data,'M',$pos+15);
$station = explode(':',substr($data,$pos,$secpos-$pos));
$station = trim($station[1]);
$station = trim(preg_replace('/VT/', '', $station));


//Get the month
$pos = strpos($data, 'MONTH:');
$secpos = strpos($data,'Y',$pos);
$month = explode(':',substr($data,$pos,$secpos-$pos));
$month = trim($month[1]);

//Get the Year
$pos = strpos($data,'YEAR:');
$secpos = strpos($data,'L',$pos);
$year = explode(":",substr($data, $pos, $secpos-$pos));
$year = trim($year[1]);


//Find the beginning of the table:
$pos = strpos($data, 'DR');
$secpos = strpos($data, '1',$pos);
//Truncate to the end of table
$endpos = strpos($data, '=',$secpos);
$tableStart = $secpos-1;
$tableEnd = $endpos;

//Now trim and explode til we're dead!
$currentrow = $tableStart;
$numRows = 31; //SET THIS ONE YOURSELF


for($i = 0; $i < $numRows; $i++){
	$rowend = strpos($data,';',$currentrow);
	$row = substr($data, $currentrow,$rowend-$currentrow);
	$currentrow = $rowend+1;
	$rowArray = explode(" " ,(ltrim($row)));
	print_r($row);
	foreach ($rowArray as $key => $value) {
		$rowArray[$key] = trim($value);
	}
	if($rowArray[16] == ""){
		$rowArray[16] = 0;
	}
	
	$db->inputDay($station,$rowArray[2],$rowArray[4] ,$rowArray[6] ,$rowArray[0] ,$rowArray[16] ,$year ,$month);
	

}


?>