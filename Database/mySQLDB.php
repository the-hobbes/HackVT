<?php
//Ethan Eldridge
//Database access stuff going to have to put semi sensistive info in, so we'll include that a conf file
require_once('../../hackConf.php');

//Class to provide functionality to the weather API's database. That we've migrated into an sql one for ease of use.
class mySQLDB{
	//Connection to the database
	private $con = null;

	//Universal Stats created by the database.
	private $uniStats = null;

	public function __construct(){
		//Connect adn set error reporting
		$this->con =  new PDO('mysql:host='.DATABASE_HOST.';dbname='.DATABASE_NAME,DATABASE_USER,DATABASE_PASS);
		$this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		//Remake the statistics when we connect and set up the universal stats for us to use later
		$results = $this->con->exec("Drop view stats; create view stats as select station_name,avg(max_tmp),avg(min_tmp),avg(avg_tmp) from daydata group by station_name;");
		$this->uniStats = $this->getUniversalStats();
	}

	//When I was using hte old Waether API I was using this to find some interesting data,but the rate limiting of the API caused me so much grief that 
	//I moved to a different, but more time consuming task of manually snagging data from the text files from the weather one I did use!
	//http://www.nws.noaa.gov/climate/index.php?wfo=btv  (The preliminary monthly climate data reports for each state.)
	public function inputWeather($wArray){
		if(is_null($this->con)){return;}

		//Run the query to insert the data
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

	//This function takes a day row that has been parsed into more meaningful data and places it in the database
	public function inputDay($station_name,$max_tmp,$min_tmp,$avg_tmp,$num,$rainfall,$year,$month){
		if(is_null($this->con)){return;}

		$result = $this->con->prepare("INSERT INTO daydata VALUES ( ?,?,?,?,?,?,?,?);");
		$result->execute(array($station_name,$max_tmp,$min_tmp,$avg_tmp,$num,$rainfall,$year,$month ));
		
	}

	//Finds the closest station to the longitude and latitude we're passed in. This is for figuring out
	//which weather station predicts the stats for the farm.
	public function findClosestStation($lat,$lon){
		//Returns the name of station
		$result = $this->con->prepare('select * from station;');
		$result->execute();

		$best = null;
		//Set distance to stupid so we'll easily overide it.
		$distance = 1000000000;
		$arrayS = $result->fetchAll(PDO::FETCH_ASSOC);
		//Loop through the stations and find the best one fitting the farm via euclidan distance
		foreach ($arrayS as $station) {
			$thisDistance = (sqrt(pow($lat - strval($station['lat]'])),2) + pow(($lon - strval($station['lon'])),2));
			if($thisDistance < $distance){
				$distance = $thisDistance;
				$best = $station['name'];
			}
		}
		return $best;

	}

	//Function to possibly display some nice data about the average rainfall at a station, and therefore a farm
	public function getStationAvgRainfall($station){ 
		$query = $this->con->prepare('select avg(rainfall) from daydata where station_name = ? ');
		$query->bindValue(1,$station,PDO::PARAM_STR);
		$query->execute();
		return $query->fetch(PDO::FETCH_ASSOC);
	}

	//Grab the statistics, which are mainly composed of averages of precipitations and std. devs. of them.
	public function getStats(){
		$results = $this->con->prepare("SELECT * FROM stats;");
		$results->execute();
		return $results->fetchAll(PDO::FETCH_ASSOC);
	}
	//Getting what I suppose is a variance, since it's an average of an average.
	public function getUniversalStats(){
		$results = $this->con->prepare("SELECT AVG(  `avg(max_tmp)` ) , AVG(  `avg(min_tmp)` ) , AVG(  `avg(avg_tmp)` ) FROM  `stats`");
		$results->execute();
		return $results->fetch(PDO::FETCH_ASSOC);
	}

	//Function to return a hex color to color a pin according to how good the quality of the farms food is.
	//Not a very precise metric, but's alright.
	public function cropQuality($lat,$lon){
		//We might return avgRain if we get ambitious to put them up into the bubbles near the pins.
		$station = $this->findClosestStation($lat,$lon);
		$avgRain = $this->getStationAvgRainfall($station);
		$stats = $this->getStats();
		//Figure out which station we should we checking out
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
						//We are now in the sweet spot of precipitation! (by our crude metric)
						return "#375903";
					}
				}
			}
		}
	}
}


?>
