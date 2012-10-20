<?php

class FarmersMarketLocations{

	private $url_head = 'www.vermontgrowersguide.com/results/?pid=';
	private $url_tail= '&searchcat=producers';

	private $user = 'hack_vt';
	private $dbName = 'hack_vt';
	private $host = 'localhost';
	private $tableName= 'new_farms';
	private $dbPass='opendata1920';
	private $dsn = null;
	private $con = null;

	public function __construct(){
		$this->dsn = "mysql:dbname=".$this->dbName.";host=".$this->host;
	}

	//grabs all farm ID's from http://www.vermontgrowersguide.com/ to use in later processing
	public function getAllFarmID(){
		$url = 'http://www.vermontgrowersguide.com/';
		//set up the curl
		$curl_site = curl_init();
		curl_setopt($curl_site, CURLOPT_URL, $url);
		curl_setopt($curl_site, CURLOPT_RETURNTRANSFER, 1); 
		$output= curl_exec($curl_site);

		//parse the needed data out
		$initial = explode('<select name="pid">', $output);
		$location_setup = explode('</select>', $initial[1], 2);
		$locations = explode('</option><option value=',$location_setup[0]);
		$first_location = trim($locations[0]);
		$locations[0] = ltrim($first_location, '<option value=');
		$last = count($locations);
		$last_location = trim($locations[$last-1]);
		$locations[$last-1]= rtrim($last_location, '</option>');
		$i=0;
		foreach($locations as $farm){
			$pid = explode('">', $farm);
			$farms[$i]= array(ltrim($pid[0],'"'),$pid[1]);
			$i=$i+1;
		}
		
		//insert these farm attributs into the database
		$this->dsn = "mysql:dbname=".$this->dbName.";host=".$this->host;
		$this->dbConnect();
		foreach($farms as $farm){
			if(!$this->farmExists($farm['id'])){
				$data = array(
					$farm['id'],
					$farm['name'],
					$farm['latitude'],
					$farm['longitude']);
				$db_query = 'INSERT INTO '.$this->tableName.' (id, name, latitude, longitude) VALUES (?,?,?,?)';
				$insertStmt = $this->con->prepare($db_query);
				$insertStmt->execute($data);
				if($insertStmt == false){
					echo 'Error inserting into table';
				}
			}
		}		
	}

	//connect to the db
	private function dbConnect(){
		try{
			$this->con = new PDO($this->dsn, $this->user, $this->dbPass);
			/*
			$this ->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			*/
		}catch(PDOException $err){$this->error = true;}
	}

	//check to see if a farm already has a table entry 
	private function farmExists($id){
		$exists= false;
		$existsQuery = 'SELECT * FROM '.$this->tableName.' WHERE id='.$id;
		$existsStatement = $this->con->prepare($existsQuery);
		$existsStatement ->execute();
		$tweet_id = $existsStatement ->fetchall();
		if(count($tweet_id)!=0){
			$exists = true;
		}
		return $exists;
	}


	//select  all farm ids from the table
	private function selectFarmId(){
		if(is_null($this->con)){
			$this->dbconnect();
		}
		$selectQuery = 'SELECT Id FROM '.$this->tableName;
		$selectStatement = $this->con->prepare($selectQuery);
		$selectStatement->execute();
		$farm_id = $selectStatement->fetchall();
		return $farm_id;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////////////////
	//All these functions contain very specific html parsing for the farm pages on www.vermontgrowersguide.com/

	//parse out the vegetables each farm carries
	//all the code is very specific for the current page
	//it could easily change in the future and everything could fall apart
	private function getFarmVegetables($farmId){
		$url=$this->url_head.$farmId.$this->url_tail;
		$curl_site = curl_init();
		curl_setopt($curl_site, CURLOPT_URL, $url);
		curl_setopt($curl_site, CURLOPT_RETURNTRANSFER, 1);
		$output= curl_exec($curl_site);
		$initial=explode('What They Sell', $output, 2);
		$split = explode('<h2>Where They Sell</h2>', $initial[1],2);
		$foods = explode('</li><li>', $split[0]);
		$first_food = explode('<li>',$foods[0]);
		$foods[0] = $first_food[1];
		$count = count($foods);
		$last_food = explode('</li>', $foods[$count-1]);
		$foods[$count-1]= $last_food[0];
		$i=0;
		foreach($foods as $food){
			/*$holder = explode(' ', $food,3);
			if(count($holder)==1){
				$foods[$i]=$holder[0];
			}elseif(count($holder)==2){
				$foods[$i]=$holder[1];
			}elseif(count($holder)==3 && $holder[0]=='Organic'){
				$foods[$i]= $holder[1].' '.$holder[2];
			}else{
				$foods[$i]=$holder[2];
			}
			$i=$i+1;*/
			$holder = str_replace('Certified ', '', $food);
			$holder2 = str_replace('Organic ', '', $holder);
			$foods[$i] = trim($holder2);
			$i=$i+1;
		}
		return($foods);
	}

	//parse out the geo code for use with google maps api
	private function getFarmGeo($farmId){
		$url=$this->url_head.$farmId.$this->url_tail;
		$curl_site = curl_init();
		curl_setopt($curl_site, CURLOPT_URL, $url);
		curl_setopt($curl_site, CURLOPT_RETURNTRANSFER, 1); 
		$output= curl_exec($curl_site);
		//huge out amount of temp variables......my b
		$split = explode('ll=', $output);
		if(count($split)>1){
			$geo=explode('&spn=', $split[1],3);
			$geo_info = explode(',', $geo[0]);
			$longitude=explode('&',$geo_info[1]);
			$complete_geo =array($geo_info[0],$longitude[0]);
		}else{
			$complete_geo = array(0,0);
		}
		return $complete_geo;
	}

	//Not needed but I'll leave it in anyway
	private function getFarmAddress($farmId){
		$url=$this->url_head.$farmId.$this->url_tail;
		$curl_site = curl_init();
		curl_setopt($curl_site, CURLOPT_URL, $url);
		curl_setopt($curl_site, CURLOPT_RETURNTRANSFER, 1); 
		$output= curl_exec($curl_site);
		$split = explode('</strong>', $output);
		$addresses = explode('</p>', $split[2]);
		$address = str_replace('<br/>',' ',trim($addresses[0]));
		var_dump($address);
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////


	//construct all of the produce tables, curl will usually time out after many accesses, I don't 
	//think that www.vermontgrowersguide.com/results likes me very much
	public function insertVegetables(){
		if(is_null($this->con)){
			$this->dbconnect();
		}
		$farm_id = $this->selectFarmId();

		//had to limit these loops so that timeouts wouldn't occur,
		//would also be nice to check that the entry doesn't exist in the table
		//before it is attempted to be inserted, otherwise duplicates occur
		for($i=220;$i<239;$i++){
			var_dump($farm_id[$i]['Id']);
			$vegetables = $this->getFarmVegetables($farm_id[$i]['Id']);
			var_dump($vegetables);
			foreach ($vegetables as $vegetable) {
				//case statements to put all the produce in the correct table
				switch ($vegetable) {
					case 'Chicken':
						$db_query = 'INSERT INTO chicken (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Beef':
						$db_query = 'INSERT INTO beef (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Lamb':
						$db_query = 'INSERT INTO lamb (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Veal':
						$db_query = 'INSERT INTO veal (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Apples':
						$db_query = 'INSERT INTO apples (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Raspberries':
						$db_query = 'INSERT INTO raspberries (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Blueberries':
						$db_query = 'INSERT INTO blueberries (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Strawberries':
						$db_query = 'INSERT INTO strawberries (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Corn (fresh)':
						$db_query = 'INSERT INTO corn (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Tomatoes':
						$db_query = 'INSERT INTO tomatoes (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Carrots':
						$db_query = 'INSERT INTO carrots (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Cabbage':
						$db_query = 'INSERT INTO cabbage (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Lettuce':
						$db_query = 'INSERT INTO lettuce (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Kale':
						$db_query = 'INSERT INTO kale (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Eggs (chicken)':
						$db_query = 'INSERT INTO eggs (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Milk (cow)':
						$db_query = 'INSERT INTO milk (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Milk (other)':
						$db_query = 'INSERT INTO milk (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Cheese (cow)':
						$db_query = 'INSERT INTO cheese (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Cheese (goat)':
						$db_query = 'INSERT INTO cheese (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Cheese (sheep)':
						$db_query = 'INSERT INTO cheese (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;
					case 'Yogurt':
						$db_query = 'INSERT INTO yogurt (farm_id) VALUES (?)';
						$insertStmt = $this->con->prepare($db_query);
						$insertStmt->execute(array($farm_id[$i]['Id']));
						break;					
					default:
						break;
				}
			}
		}
	}
}


?>