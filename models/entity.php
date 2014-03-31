<?php


class Entity {
	const DC_DATASTREAM = "DC";

	private $DC = NULL;
	private $data = NULL;
	private $content = NULL;
	private $content_name = NULL;

	function __construct($json, $content_name) {
		$this -> data = $json;
		$this -> content_name = $content_name;

		// Check if the data streams exist
		foreach ($this->data->datastreams as $ds) {
			if ($ds -> dsid === self::DC_DATASTREAM) {
				$this -> DC = $ds;
			} else if ($ds -> dsid === $this -> content_name) {
				$this -> content = $ds;
			}
		}
	}

	/**
	 * Returns the PID of the current entity.
	 */
	function getPID() {
		return $this -> data -> pid;
	}
	
	function getLabel() {
		return $this -> data -> label;
	}

	/**
	 * Obtains the current text content of the entity.
	 */
	function getContent() {
		$url = cwrc_url() . "/islandora/rest/v1/object/" . urldecode($this -> data -> pid) . "/datastream/" . urldecode($this -> content_name) . "?content=true";
		$data = array("content" => "true");

		$header = array("Content-type: application/json");
		
		

		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}

		$options = array('http' => array('header' => $header, 'method' => 'GET', ), );

		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);

		return $result;
	}
	
	function setRelationship($uri, $predicate, $entityModel) {
		$url = cwrc_url() . "/islandora/rest/v1/object/" . $this -> data -> pid . "/relationship";
		$data = array('uri' => $uri, 'predicate' => $predicate, 'object' => $entityModel, 'type' => 'uri');
		$header = array();
		
		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}
		
		array_push($header, "Content-type: application/json");
		
		$options = array('http' => array('header' => $header, 'method' => 'POST', 'content' => json_encode($data), ), );
		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);
		
		if (strpos($http_response_header[0], "201")) {
			return null;
		}else {
			return $url . " - " . $http_response_header[0] . "\n" . $result;
		}
	}
	
	/*function setRelationship($uri, $predicate, $entiyyModel) {
		$url = cwrc_url() . "/islandora/rest/v1/object/" . $this -> data -> pid . "/relationship";
		$data = array('uri' => $uri, 'predicate' => $predicate, 'object' => $entiyyModel, 'literal' => 'false');
		$header = array();
		
		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}
		
		array_push($header, 'Content-Type: multipart/form-data; boundary=' . MULTIPART_BOUNDRY);
		
		
		$content = cwrc_createFormContent($entityModel, $data);
		$context = stream_context_create(array('http' => array('header' => $header, 'method' => 'POST', 'content' => $content)));
		$result = @file_get_contents($url, false, $context);
		
		if (strpos($http_response_header[0], "201")) {
			return null;
		}else {
			return $url . " - " . $http_response_header[0];
		}
	}*/
	
	protected function addWorkflowEntry($category, $stampType, $toolID){
		$url = cwrc_url() . "/islandora_workflow_rest/v1/add_workflow/?";
		$url .= 'PID=' . urlencode($this -> data -> pid);
		//$url .= '&toolID=' . $toolID;
		$url .= '&activity=' . urlencode(json_encode(array('category' => $category, 'stamp' => $stampType, 'status' => 'c')));
		$header = array();
		
		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}
		
		array_push($header, "Content-type: application/json");
		
		$options = array('http' => array('header' => $header, 'method' => 'GET', ), );
		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);
		
		if (strpos($http_response_header[0], "200")) {
			return null;
		}else {
			return $url . " - " . $http_response_header[0] . "\n" . $result;
		}
	}

	/**
	 * Updates the current content of the entity.
	 */
	function updateData($inputXml) {
		$header = array();
		$url = null;
		$data = null;
		$method = null;

		foreach (get_login_cookie() as $key => $val) {
			array_push($header, "Cookie: " . $key . "=" . $val);
		}

		array_push($header, 'Content-Type: multipart/form-data; boundary=' . MULTIPART_BOUNDRY);

		if ($this -> content == NULL) {
			$method = 'POST';
			$url = cwrc_url() . "/islandora/rest/v1/object/" . $this -> data -> pid . "/datastream";
			$data = array('dsid' => $this -> content_name, 'mimeType' => 'text/xml', 'label' => 'Entity Data', 'controlGroup' => 'M');
		} else {
			$method = 'POST';
			$url = cwrc_url() . "/islandora/rest/v1/object/" . $this -> data -> pid . "/datastream/" . $this -> content_name;
			$data = array('method' => 'PUT');
		}

		$content = cwrc_createFormContent($inputXml, $data);
		//array_push($header, 'Content-Length: ' . strlen($content));

		$context = stream_context_create(array('http' => array('header' => $header, 'method' => $method, 'content' => $content)));

		$result = file_get_contents($url, false, $context);
		

		if (strpos($http_response_header[0], "201")) {
			return $this->addWorkflowEntry('created', 'cwrc:cre', 'cwrc'); // Add the workflow information
		} else if (strpos($http_response_header[0], "200")) {
			return  $this->addWorkflowEntry('content_contribution', 'cwrc:mod', 'cwrc');// Add the workflow information
		}else {
			return $http_response_header[0];
		}
	}

}
