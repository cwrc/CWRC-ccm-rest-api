<?php
include_once ('./models/entity.php');

abstract class EntityController {
	abstract public static function search($solrString);
	abstract public static function view($id);
	abstract public static function createNew($data);
	abstract public static function update($id);
	abstract public static function delete($id);

	public static function create($id = '') {
		// Checks if this call has a 'message' parameter. I it does, we need to pass it to the appropriate method.
		if (isset($_POST["method"])) {
			switch (strtolower($_POST["method"])) {
				case 'put' :
					static::update($id);
					return;

				case 'delete' :
					static::delete($id);
					return;
			}
		}

		static::createNew($_POST);
	}

	protected static function getEntity($pid, $content_name) {
		$url = cwrc_url() . "/islandora/rest/v1/object/" . $pid;
		
		$header = array("Content-type: application/json");

		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}

		$options = array('http' => array('header' => $header, 'method' => 'GET', ), );
		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);

		if (strpos($http_response_header[0], "200")) {
			// Item created.
			$entity = new Entity(json_decode($result), $content_name);
			return $entity;
		} else {
			return $http_response_header[0];
		}
	}
	
	protected static function modifyEntity($content_name, $pid, $data){
		$result = self::getEntity($pid, $content_name);
		
		if(get_class($result) == "Entity"){
			$result->updateData($data);
		}
		
		return $result;
	}

	protected static function uploadNewEntity($namespace, $content_name, $entityData) {
		$url = cwrc_url() . "/islandora/rest/v1/object";
		$data = array('namespace' => $namespace);

		$header = array("Content-type: application/json");

		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}

		$options = array('http' => array('header' => $header, 'method' => 'POST', 'content' => json_encode($data), ), );
		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);

		if (strpos($http_response_header[0], "201")) {
			// Item created.
			$entity = new Entity(json_decode($result), $content_name);
			$successful = $entity -> updateData($entityData);
			
			if($successful == null){
				return $entity;
			}else{
				return $successful;
			}
		} else {
			return $http_response_header[0];
		}
	}

}
