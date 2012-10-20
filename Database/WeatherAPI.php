<?php
//Ethan Eldridge 
//2012 October 19
//Hack VT Database Weather Aggregation.
/*
Weather API a8be19e4c204a4b0
This query will get us all of VT
http://api.wunderground.com/api/a8be19e4c204a4b0/history_20100101/q/VT.json
*/

class weatherAPIInterface{
	private $key = 'a8be19e4c204a4b0';
	private $state = 'VT';
	private $cities = null;

	public function __construct(){
		$this->getCities();
	}

	public function getCities(){
		//Start getting ready to ask for data (Refactor this to go through dates)
		$ch = curl_init('http://api.wunderground.com/api/'.$this->key.'/history_20100101/q/'.$this->state.'.json');
		//Set options, I want to fail silently if I get a 404 page because I can't parse that
		//             I want to follow anything I need to, and get the data as a string
		curl_setopt($ch,CURLOPT_FAILONERROR,true);
		curl_setopt($ch,CURLOPT_AUTOREFERER,true);
//		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$output = json_decode(curl_exec($ch));
		//free up resources
		curl_close($ch);

		$this->cities = array();

		foreach ($output->response->results as $key) {
			$this->cities[] = ($key->city);
		}
	}

}


$wi = new weatherAPIInterface();

?>