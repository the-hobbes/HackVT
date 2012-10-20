<?php
//Ethan Eldridge
//Database access stuff going to have to put semi sensistive info in, so we'll include that a conf file
require_once('../../hackConf.php');

class mySQLDB{
	private $con = null;

	public function __construct(){
		$this->con =  new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME,DATABASE_USER,DATABASE_PASS);
		$this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function inputWeather($wArray){
		if(is_null($this->con)){return;}

		//Run the query
		$result = $this->con->prepare('INSERT INTO weather ("date", "maxHum","minHum","rain","snow","hail","precip","maxTemp","minTemp","avgTemp") VALUES (?, ?, ?, ?,?,?,?,?,?,?);');
		$result->bindValue(1,$wArray['date'],PDO::PARAM_STR);
		$result->bindValue(2,$wArray['maxHum'],PDO::PARAM_STR);
		$result->bindValue(3,$wArray['minHum'],PDO::PARAM_STR);
		$result->bindValue(4,$wArray['rain'],PDO::PARAM_INT);
		$result->bindValue(5,$wArray['snow'],PDO::PARAM_INT);
		$result->bindValue(6,$wArray['hail'],PDO::PARAM_INT);
		$result->bindValue(7,$wArray['precip'],PDO::PARAM_STR);
		$result->bindValue(8,$wArray['maxTemp'],PDO::PARAM_STR);
		$result->bindValue(9,$wArray['minTemp'],PDO::PARAM_STR);
		$result->bindValue(10,$wArray['avgTemp'],PDO::PARAM_STR);
		var_dump($result->execute());

	}
}

new mySQLDB();
?>