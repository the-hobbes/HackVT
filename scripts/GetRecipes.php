<?php

class GetRecipes{

	private $url_base = 'http://www.recipepuppy.com/api/?';
	private $ingredients= '';
	private $dish = '';

	public function __construct(){
	}

	public function set_ingredients($ingredients){
		$this->ingredients='i='.implode(',',$ingredients);
	}

	public function set_dish($dish){
		$this->dish = 'q='.implode(',',$dish);
	}

	public function query_recipe(){
		$search_params = array($this->ingredients, $this->dish);
		$url = $this->url_base.implode('&', $search_params);
		$curl_site = curl_init();
		curl_setopt($curl_site, CURLOPT_URL, $url);
		curl_setopt($curl_site, CURLOPT_RETURNTRANSFER, 1); 
		$output= curl_exec($curl_site);
		return json_decode($output)->results;
	}
}


?>