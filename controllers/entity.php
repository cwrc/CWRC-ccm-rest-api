<?php
include_once ('./models/entity.php');

/**
 * Used to handle all entity based functions.
 */
abstract class EntityController {
	const ENTITY = "Entity";
	const API_NAMESPACE = "cwrc";
	
	abstract public static function search();
	abstract public static function view($id);
	abstract public static function createNew($data);
	abstract public static function update($id);
	abstract public static function delete($id);

	/**
	 * Called when the user performs a post command to the entity. If the mthod variable is specified, then the appropriate action is called.
	 * @param id The id of the entity
	 */
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
	
	/**
	 * Helper function used to delete entitties.
	 * @param pid The id of the entity.
	 */
	protected static function deleteEntity($pid){
		$url = cwrc_url() . "/islandora/rest/v1/object/" . $pid;
		
		$header = array("Content-type: application/json");

		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}
		
		$options = array('http' => array('header' => $header, 'method' => 'DELETE', ), );
		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);

		if (strpos($http_response_header[0], "200")) {
			return null;
		} else {
			return $http_response_header[0];
		}
	}

	/**
	 * Obtains an entity from the server.
	 * @param pid The identifier of the entity.
	 * @param content_name The specified name for the content holder of the entity.
	 */
	public static function getEntity($pid, $content_name) {
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
	
	/**
	 * Modifies a currently existing entities content or creates a new content stream for an entity if it does not exist.
	 * @param content_name The specified name for the content holder of the entity.
	 * @param pid The identifier of the entity.
	 * @param data The content being placed
	 */
	protected static function modifyEntity($content_name, $pid, $data){
		$result = self::getEntity($pid, $content_name);
		
		if(get_class($result) == self::ENTITY){
			$successful = $result -> updateData($data);
			
			if($successful == null){
				return $result;
			}else{
				return $successful;
			}
		}
		
		return $result;
	}

	/**
	 * Adds a new entity to the server.
	 * @param namespace The namespace used for the entity.
	 * @param content_name The specified name for the content holder of the entity.
	 * @param entityData The content being placed
	 */
	protected static function uploadNewEntity($namespace, $content_name, $entityData, $label) {
		$url = cwrc_url() . "/islandora/rest/v1/object";
		$data = array('namespace' => $namespace, 'label' => $label);

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
	
	protected static function buildQueryString($searchString){
		if(preg_match('/^(["\']).*\1$/m', $searchString)){
			return urlencode($searchString);
		}
		
		$returnString = "";
		$explosion = explode("*", $searchString);
		
		foreach($explosion as $value){
			$returnString .= urlencode($value) . "*";
		}
		
		return strtolower($returnString);
	}
	
	protected static function searchEntities($content_name, $searchString, $limit, $page){
		$queryString = self::buildQueryString($searchString);
		$url = cwrc_url() . "/islandora/rest/v1/solr/fgs.label:" . $queryString . "?wt=json&limit=" . $limit . "&page=" . $page . "&f[]=" . urlencode("hasDatastream:" . $content_name);// . '&sort="fgs.label"+asc';
		$data = array();

		$header = array("Content-type: application/json");

		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}

		$options = array('http' => array('header' => $header, 'method' => 'GET', 'content' => json_encode($data), ), );
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		
		return $result;
	}

}

?>
