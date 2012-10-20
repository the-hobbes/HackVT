<?php
//Ethan Eldridge
//Database access stuff going to have to put semi sensistive info in, so we'll include that a conf file
require_once('../../hackConf.php');

class mySQLDB{
	private $con = null;

	public function __construct(){
		$this->con =  @mysql_connect( DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD ) OR die ( 'Could not connect to MySQL: ' . mysql_error() );
		@mysql_select_db( DATABASE_NAME ) OR die( 'Could not select the database: ' . mysql_error() );
	}

	public function close(){
		mysql_close($this->con);
	}

	public function inputWeather($wArray){
		if(!is_null($this->con)){return;}

		//Run the query
		$result = mysql_query('INSERT INTO weather ("date", "maxHum","minHum","rain","snow","hail","precip","maxTemp","minTemp","avgTemp") '
					.'VALUES ('."'". $wArray['date']. "'," 
					. "'". $wArray['maxHum'] ."',"
					. "'". $wArray['maxHum'] ."',"
					. "'". $wArray['minHum'] ."',"
					. "'". $wArray['rain'] ."'," 
					. "'". $wArray['snow'] ."',"
					. "'". $wArray['hail'] ."',"
					. "'". $wArray['precip']."',"
					. "'". $wArray['maxTemp']."',"
					. "'". $wArray['minTemp']."',"
					. "'". $wArray['avgTemp']."');");
		

	}
}

new mySQLDB();

?>