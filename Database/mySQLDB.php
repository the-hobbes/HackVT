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
		$result = $this->con->prepare("INSERT INTO weather (date, maxHum,minHum,rain,snow,hail,precip,maxTemp,minTemp,avgTemp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
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
		$result->execute();

	}

	public function inputDay($station_name,$max_tmp,$min_tmp,$avg_tmp,$num,$rainfall,$year,$month){
		if(is_null($this->con)){return;}

		$result = $this->con->prepare("INSERT INTO daydata VALUES ( ?,?,?,?,?,?,?,?);");
		$result->execute(array($station_name,$max_tmp,$min_tmp,$avg_tmp,$num,$rainfall,$year,$month ));
		$results = $this->con->exec("create view stats as select station_name,avg(max_tmp),avg(min_tmp),avg(avg_tmp) from daydata group by station_name;");
	}

	public function findClosestStation($lat,$lon){
		//Returns the name of station
		$result = $this->con->prepare('select * from station;');
		$result->execute();

		$best = null;
		$distance = 1000000000;
		$arrayS = $result->fetchAll(PDO::FETCH_ASSOC);
		foreach ($arrayS as $station) {
			$thisDistance = (sqrt(pow($lat - $station['lat]']),2) + pow(($lon - $station['lon']),2));
			if($thisDistance < $distance){
				$distance = $thisDistance;
				$best = $station['name'];
			}
		}
		return $best;
	}

	public function getStationAvgRainfall($station){ 
		$query = $this->con->prepare('select avg(rainfall) from daydata where station_name = ? ');
		$query->bindValue(1,$station,PDO::PARAM_STR);
		$query->execute();
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	// below .05 is iffy, above is a ok
	public function getStats(){
		$results = $this->con->exec("SELECT * FROM stats;");
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}

	public function cropQuality($lat,$lon){
		$station = $this->findClosestStation($lat,$lon);
		$avgRain = $this->getStationAvgRainfall($station);
		
	}
}



$w = new mySQLDB();
$w->findClosestStation(50,36.3);
var_dump($w->getStationAvgRainfall("BURLINGTON"));
echo 'heeey hyeey';

?>
