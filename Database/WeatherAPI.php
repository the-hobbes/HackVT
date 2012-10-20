<?php
//Ethan Eldridge 
//2012 October 19
//Hack VT Database Weather Aggregation.
/*
Weather API a8be19e4c204a4b0
This query will get us all of VT
http://api.wunderground.com/api/a8be19e4c204a4b0/history_20100101/q/VT.json
*/

//We need SQL to store the weather elements
include_once('mySQLDB.php');

class weatherAPIInterface{
	//database connection
	private $db = null;
	//API Key
	private $key = '025e610d25bff853';
	private $state = 'VT';
	private $cities = null;
	//Number of api requests
	public $apiRequests = 0;

	public function __construct(){
		$this->db = new mySQLDB();
		$this->getCities();
		$this->getWeather();
	}

	//Nice and modular for us, easy to keep track of apiRequests too since we're limited to 500 a day
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
		$this->apiRequests = $this->apiRequests + 1;
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
		//SET THIS TO START DATA OF YOUR CHOOSING YYYY-MM-DD
		$weather['date'] = "2012-01-03"; 
		if(is_null($this->cities)){return;}
		for($i =0; $i < 71; $i++){
			foreach ($this->cities as $city) {
				//increment the date starting at the start date
				//Will be date to query on later on
				$data = json_decode($this->curlOn('http://api.wunderground.com/api/'.$this->key.'/history_'.implode(explode('-',$weather['date'])).'/q/'.$this->state.'/'.urlencode($city).'.json'));
				$dayData = $data->history->dailysummary; $dayData = $dayData[0];
				//Get the important things out of the query:
				$weather['maxHum'] = $dayData->maxhumidity;
				$weather['minHum'] = $dayData->minhumidity;
				$weather['rain']   = $dayData->rain;
				$weather['snow']   = $dayData->snow;
				$weather['hail']   = $dayData->hail;
				$weather['precip'] = $dayData->precipi;
				$weather['maxTemp']= $dayData->maxtempi;
				$weather['minTemp']= $dayData->mintempi;
				$weather['avgTemp']= $dayData->meantempi;
				
				$this->db->inputWeather($weather);

				if($this->apiRequests % 10){
					set_time_limit(80);
					sleep(60);
					if($this->apiRequests >= 500){
						die('CANT ASK FOR MORE CAPTAIN');
					}
				}

			}
			$weather['date'] = date("Y-m-d",strtotime("+1 day", strtotime($weather['date'])));
			
			}
	}

}

//testing
$wi = new weatherAPIInterface();

?>