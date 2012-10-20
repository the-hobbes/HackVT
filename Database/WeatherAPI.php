<?php
//Ethan Eldridge 
//2012 October 19
//Hack VT Database Weather Aggregation.
/*
Weather API a8be19e4c204a4b0
This query will get us all of VT
http://api.wunderground.com/api/a8be19e4c204a4b0/history_20100101/q/VT.json
*/

include_once('mySQLDB.php');

class weatherAPIInterface{
	private $db = null;
	private $key = 'a8be19e4c204a4b0';
	private $state = 'VT';
	private $cities = null;

	public function __construct(){
		$this->db = new mySQLDB();
		//$this->getCities();
		//$this->getWeather();
	}

	public function curlOn($url){
		$ch = curl_init($url);
		//Set options, I want to fail silently if I get a 404 page because I can't parse that
		//             I want to follow anything I need to, and get the data as a string
		curl_setopt($ch,CURLOPT_FAILONERROR,true);
		curl_setopt($ch,CURLOPT_AUTOREFERER,true);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$out = curl_exec($ch);
		//free up resources
		curl_close($ch);
		return $out;
	}

	public function getCities(){
		//Start getting ready to ask for data (Refactor this to go through dates)
		$output = json_decode($this->curlOn('http://api.wunderground.com/api/'.$this->key.'/history_20100101/q/'.$this->state.'.json'));
		
		//Grab cities out of the data
		$this->cities = array();
		foreach ($output->response->results as $key) {
			$this->cities[] = ($key->city);
		}
	}

	public function getWeather(){
		//Initilization check.
		$weather = array();
		if(is_null($this->cities)){return;}
		foreach ($this->cities as $city) {
			//increment the date starting at the start date
			set_time_limit(60);
			$weather['date'] = "2012-01-01"; //Will be date to query on later on
			$data = json_decode($this->curlOn('http://api.wunderground.com/api/'.$this->key.'/history_20100101/q/'.$this->state.'/'.urlencode($city).'.json'));
			$dayData = $data->history->dailysummary; $dayData = $dayData[0];
			//Get the important things out of the query:
			$weather['maxHum'] = $dayData->maxhumidity;
			$weather['minHum'] = $dayData->minhumidity;
			$weather['rain']   = $dayData->rain;
			$weather['snow']   = $dayData->snow;
			$weather['hail']   = $dayData->hail;
			$weather['precip'] = $dayData->precipi;
			$weather['maxtemp']= $dayData->maxtempi;
			$weather['minTemp']= $dayData->mintempi;
			$weather['avgTemp']= $dayData->meantempi;
			
			print_r($weather);

			$this->db->inputWeather($weather);
			echo '<br /><br />';
			die('i died');
		}
	}

}


$wi = new weatherAPIInterface();

?>