<?php
getRoute()->get('/annotation/(.+)', array('AnnotationController', 'view'));
getRoute()->post('/annotation', array('AnnotationController', 'create'));
getRoute()->post('/annotation/(.+)', array('AnnotationController', 'create'));
getRoute()->put('/annotation/(.+)', array('AnnotationController', 'update'));
getRoute()->delete('/annotation/(.+)', array('AnnotationController', 'delete'));
include_once ('./models/annotation.php');

/** 
 * Used to handle all annotation based functions
 */
 class AnnotationController {
 	const ANNOTATION = "ANNOTATION";
	const API_NAMESPACE = "cwrc";
	const FEDORA_MODEL_URI = "info:fedora/fedora-system:def/model#";
	
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
	
	public static function getAnnotation($pid){
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
			$entity = new Annotation(json_decode($result));
			return $entity;
		} else {
			return $http_response_header[0];
		}
	}
	
	public static function delete($pid){
		$object = array();
		$url = cwrc_url() . "/islandora/rest/v1/object/" . $pid;
		$header - array("Content-type: application/json");
		
		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}
		
		$options = array('http' => array('header' => $header, 'method' => 'DELETE', ), );
		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);
		
		if (strpos($http_response_header[0], "200")) {
			$object['isDeleted'] = true;
		} else {
			$object['isDeleted'] = true;
			$object['error'] = $http_response_header[0];
		}
		
		echo json_encode($object);
	}

	public static function update($pid){
		$object = array();
		$result = self::getAnnotation($pid);
		
		if(get_class($result) == self::ANNOTATION){
			$successful = $result -> $_POST['data'];
			
			if($successful == null){
				// Successful update contains no error message.
			}else{
				$object["error"] = $successful;
			}
			
		}else{
			$object["error"] = $result;
		}
		
		$object["pid"] = $pid;
		
		echo json_encode($object);
	}
	
	public static function view($id){
		$result = self::getAnnotation($id);
		
		if(get_class($result) == self::ANNOTATION){
			echo $result->getContent();
		}else{
			echo $result;
		}
	}

	protected static function createNew($annotationData){
		$object = array();
		$url = cwrc_url() . "/islandora/rest/v1/object";
		$data = array('namespace' => self::API_NAMESPACE, 'label' => $label);
		
		$header = array("Content-type: application/json");

		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}

		$options = array('http' => array('header' => $header, 'method' => 'POST', 'content' => json_encode($data), ), );
		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);
		
		if (strpos($http_response_header[0], "201")) {
			// Item created.
			$annotation = new Annotation(json_decode($result));
			$successful = $annotation -> updateData($annotationData);
			$successful = $successful == null ? $annotation -> setRelationship(self::FEDORA_MODEL_URI, "hasModel", 'cwrc:OACModel') : $successful;
			
			if($successful == null){
				$object["pid"] = $annotation->getPID();
			}else{
				$object["error"] = $successful;
			}
		} else {
			$object["error"] = $http_response_header[0];
		}
		
		echo json_encode($object);
	}
 }
?>