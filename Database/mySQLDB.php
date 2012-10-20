<?php
//Ethan Eldridge
//Database access stuff going to have to put semi sensistive info in, so we'll include that a conf file
require_once('../../hackConf.php');

class mySQLDB{
	private $con = null;
	private $uniStats = null;

	public function __construct(){
		$this->con =  new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME,DATABASE_USER,DATABASE_PASS);
		$this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//$results = $this->con->exec("create view stats as select station_name,avg(max_tmp),avg(min_tmp),avg(avg_tmp) from daydata group by station_name;");
		$this->uniStats = $this->getUniversalStats();
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
		
	}

	public function findClosestStation($lat,$lon){
		//Returns the name of station
		$result = $this->con->prepare('select * from station;');
		$result->execute();

		$best = null;
		$distance = 1000000000;
		$arrayS = $result->fetchAll(PDO::FETCH_ASSOC);
		foreach ($arrayS as $station) {
			$thisDistance = (sqrt(pow($lat - strval($station['lat]'])),2) + pow(($lon - strval($station['lon'])),2));
			if($thisDistance < $distance){
				$distance = $thisDistance;
				$best = $station['name'];
			}
		}
		var_dump($best);
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
		$results = $this->con->prepare("SELECT * FROM stats;");
		$results->execute();
		return $results->fetchAll(PDO::FETCH_ASSOC);
	}
	public function getUniversalStats(){
		$results = $this->con->prepare("SELECT AVG(  `avg(max_tmp)` ) , AVG(  `avg(min_tmp)` ) , AVG(  `avg(avg_tmp)` ) FROM  `stats`");
		$results->execute();
		return $results->fetch(PDO::FETCH_ASSOC);
	}

	public function cropQuality($lat,$lon){
		$station = $this->findClosestStation($lat,$lon);
		var_dump($station);
		$avgRain = $this->getStationAvgRainfall($station);
		$stats = $this->getStats();
		var_dump($stats);
		foreach ($stats as $statToCompare) {
			
			if($statToCompare['station_name']==$station){
				
				//Use the ranges of the statistics to figure out the quality of the crops
				//Between averages is ok, outside of max or min is bad, and close to the average is best
				if($statToCompare['max_tmp'] > $this->uniStats['AVG( `avg(max_tmp)` )'] || $statToCompare['min_tmp'] < $this->uniStats["AVG( `avg(min_tmp)` )"]){
					return "#402C12";
				}else{
					//We're in an ok zone. If we're more than 3 away from the average
					if($statToCompare['avg(avg_tmp)'] > $this->uniStats['AVG( `avg(avg_tmp)` )']+3 || $statToCompare['avg(avg_tmp)'] < $this->uniStats['AVG( `avg(avg_tmp)` )']-3){
						//They're alright:
						return "#F2EE39";
					}else{
						//We are now in the sweet spot of precipitation!
						return "#375903";
					}
				}
			}
		}
	}
}



$w = new mySQLDB();
$w->findClosestStation(50,36.3);
echo 'heeey hyeey</br >';

var_dump($w->cropQuality(45.00,79.4));


?>
