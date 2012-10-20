<?php

//Scott MacEwan
//10/19/2012


//Class to get various farm locations around vermont
//All information parsed from http://www.vermontfresh.net/member-search/
class FarmLocations{

	//Farm attrubutes
	private $farmsVegetables = null;
	private $farmsFruits = null;
	private $farmsMeat = null;
	private $farmsEggs = null;
	private $farmsDairy = null;
	private $farmsAll = null;
	private $farmsPickYourOwn = null;


	//Database information
	private $user = 'hack_vt';
	private $dbName = 'hack_vt';
	private $host = 'localhost';
	private $tableName= 'farms';
	private $dbPass='opendata1920';
	private $dsn = null;
	private $con = null;


	public function __construct(){
	}


	//Get all farms that grow vegetables
	public function getFarmsVegetables(){
		$url = "http://www.vermontfresh.net/member-search/MemberSearchForm?Keywords=&ProductCategoryID=5&Categories%5B13%5D=13&RegionID=&action_doMemberSearch=Search";
		return $this->parse_data($url);
	}


	//Get all farms that grow fruits
	public function getFarmsFruits(){
		$url = 'http://www.vermontfresh.net/member-search/MemberSearchForm?Keywords=&ProductCategoryID=8&Categories%5B13%5D=13&RegionID=&action_doMemberSearch=Search';
		return $this->parse_data($url);
	}

	//Get all farms that raise animals for meat
	public function getFarmsMeat(){
		$url = 'http://www.vermontfresh.net/member-search/MemberSearchForm?Keywords=&ProductCategoryID=7&Categories%5B13%5D=13&RegionID=&action_doMemberSearch=Search';
		return $this->parse_data($url);
	}


	//Get all farms in vermont
	public function getFarms(){
		$url = 'http://www.vermontfresh.net/member-search/MemberSearchForm?Keywords=&ProductCategoryID=&Categories%5B13%5D=13&RegionID=&action_doMemberSearch=Search';
		return $this->parse_data($url);
	}

	//Get all farms that have dairy
	public function getFarmsDairy(){
		$url = 'http://www.vermontfresh.net/member-search/MemberSearchForm?Keywords=&ProductCategoryID=10&Categories%5B13%5D=13&RegionID=&action_doMemberSearch=Search';
		return $this->parse_data($url);
	}


	//Get all farms that have eggs
	public function getFarmsEggs(){
		$url = 'http://www.vermontfresh.net/member-search/MemberSearchForm?Keywords=&ProductCategoryID=9&Categories%5B13%5D=13&RegionID=&action_doMemberSearch=Search';
		return $this->parse_data($url);
	}

	public function getFarmsPickYourOwn(){
		$url = 'http://www.vermontfresh.net/member-search/MemberSearchForm?Keywords=&ProductCategoryID=&Categories%5B10%5D=10&RegionID=&action_doMemberSearch=Search';
		return $this->parse_data($url);
	}


	//parse the data from the webpage
	private function parse_data($url){

		//set up the curl and execute it
		$curl_site = curl_init();
		curl_setopt($curl_site, CURLOPT_URL, $url);
		curl_setopt($curl_site, CURLOPT_RETURNTRANSFER, 1); 
		$output= curl_exec($curl_site);

		//split up the page based on the string 'createMarker' 
		$areas = explode('createMarker', $output);
		$last = count($areas);

		//break up the last farm and take off all the unecessart html code
		$last_farm = explode(';', $areas[$last-1], 2);
		$areas[$last-1] = $last_farm[0];

		//unset the first two because they are useless
		unset($areas[0], $areas[1]);

		//break up the data, trim it and add it to a array
		$i=0;
		foreach($areas as $farm){
			$farm_parameters = explode('<br />', $farm);
			$holder = explode('\">', $farm_parameters[0]);
			$geo = explode(',', $holder[0],3);
			$locations[$i]= array(
				'name'=> rtrim($holder[2],'</a>'),
				'address'=>trim($farm_parameters[1]).' '.trim($farm_parameters[2]),
				'latitude'=>ltrim($geo[0],'('),
				'longitude'=>$geo[1]);
			$i = $i+1;
		}
		return $locations;
	}


	//Set all the parameters to the individual farms then add them to the database, checking to see if they exist in there first
	public function insert_db(){

		//function no longer needed see FarmersMarketLocations
		if(is_null($this->farmsAll)){
			$this->farmsAll = $this->getFarms();
		}		
		if(is_null($this->farmsVegetables)){
			$this->farmsVegetables = $this->getFarmsVegetables();
		}
		if(is_null($this->farmsFruits)){
			$this->farmsFruits = $this->getFarmsFruits();
		}
		if(is_null($this->farmsEggs)){
			$this->farmsEggs = $this->getFarmsEggs();
		}
		if(is_null($this->farmsDairy)){
			$this->farmsDairy = $this->getFarmsDairy();
		}
		if(is_null($this->farmsMeat)){
			$this->farmsMeat = $this->getFarmsMeat();
		}
		if(is_null($this->farmsPickYourOwn)){
			$this->farmsPickYourOwn = $this->getFarmsPickYourOwn();
		}

		$i=0;
		foreach ($this->farmsAll as $farm) {
			$name = $farm['name'];
			$this->farmsAll[$i]['vegetables']= false;
			$this->farmsAll[$i]['fruits']= false;
			$this->farmsAll[$i]['meat']= false;
			$this->farmsAll[$i]['dairy']= false;
			$this->farmsAll[$i]['eggs']= false;
			$this->farmsAll[$i]['pickYourOwn']= false;
			foreach($this->farmsVegetables as $vege){
				if(in_array($name, $vege)){
					$this->farmsAll[$i]['vegetables'] = true;
				}
			}
			foreach($this->farmsFruits as $fruit){
				if(in_array($name, $fruit)){
					$this->farmsAll[$i]['fruits'] = true;
				}
			}
			foreach($this->farmsMeat as $meat){
				if(in_array($name, $meat)){
					$this->farmsAll[$i]['meat'] = true;
				}
			}
			foreach($this->farmsDairy as $dairy){
				if(in_array($name, $dairy)){
					$this->farmsAll[$i]['dairy'] = true;
				}
			}
			foreach($this->farmsEggs as $egg){
				if(in_array($name, $egg)){
					$this->farmsAll[$i]['eggs'] = true;
				}
			}
			foreach($this->farmsPickYourOwn as $pick){
				if(in_array($name, $pick)){
					$this->farmsAll[$i]['pickYourOwn'] = true;
				}
			}
		$i=$i+1;
		}
		$this->dsn = "mysql:dbname=".$this->dbName.";host=".$this->host;
		$this->dbConnect();
		foreach($this->farmsAll as $farm){
			if(!$this->farmExists($farm['name'])){
				$data = array(
					$farm['name'],
					$farm['address'],
					$farm['latitude'],
					$farm['longitude'],
					$farm['vegetables'],
					$farm['fruits'],
					$farm['meat'],
					$farm['dairy'],
					$farm['eggs'],
					$farm['pickYourOwn']);
				$db_query = 'INSERT INTO '.$this->tableName.' (name, address, latitude, longitude, vegetables, fruits, meat, dairy, eggs, pickYourOwn) VALUES (?,?,?,?,?,?,?,?,?,?)';
				$insertStmt = $this->con->prepare($db_query);
				$insertStmt->execute($data);
				if($insertStmt == false){
					echo 'Error inserting into table';
				}
			}
		}
	}

	function dbConnect(){
		try{
			$this->con = new PDO($this->dsn, $this->user, $this->dbPass);
			/*
			$this ->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			*/
		}catch(PDOException $err){$this->error = true;}
	}

	function farmExists($name){
		$exists= false;
		$existsQuery = 'SELECT * FROM '.$this->tableName.' WHERE name='.$name;
		$existsStatement = $this->con->prepare($existsQuery);
		$existsStatement ->execute();
		$tweet_id = $existsStatement ->fetchall();
		if(count($tweet_id)!=0){
			$exists = true;
		}
		return $exists;
	}
}
?>