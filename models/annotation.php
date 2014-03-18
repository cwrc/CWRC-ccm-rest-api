<?php

class Annotation {
	const DC_DATASTREAM = "DC";
	const OA_DATASTREAM = "OA";

	private $DC = NULL;
	private $OA = NULL;
	private $data = NULL;

	function __construct($json) {
		$this -> data = $json;

		// Check if the data streams exist
		foreach ($this->data->datastreams as $ds) {
			if ($ds -> dsid === self::DC_DATASTREAM) {
				$this -> DC = $ds;
			} else if ($ds -> dsid === self::OA_DATASTREAM) {
				$this -> OA = $ds;
			}
		}
	}

	/**
	 * Returns the PID of the current entity.
	 **/
	function getPID() {
		return $this -> data -> pid;
	}

	/**
	 * Obtains the current xml content of the entity.
	 **/
	function getContent() {
		$url = cwrc_url() . "/islandora/rest/v1/object/" . $this -> data -> pid . "/datastream/OA?content=true";
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
	
	/**
	 * Set any special relationship for the annotation
	 */
	function setRelationship($url, $predicate, $model){
		$url = cwrc_url() . "/islandora/rest/v1/object/" . $this -> data -> pid . "/relationship";
		$data = array('uri' => $uri, 'predicate' => $predicate, 'object' => $model, 'type' => 'uri');
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
	
	/**
	 * Updates the current content of the annotation
	 **/
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
			$data = array('dsid' => self::OA_DATASTREAM, 'mimeType' => 'text/xml', 'label' => 'Annotation Data', 'controlGroup' => 'M');
		} else {
			$method = 'POST';
			$url = cwrc_url() . "/islandora/rest/v1/object/" . $this -> data -> pid . "/datastream/" . self::OA_DATASTREAM;
			$data = array('method' => 'PUT');
		}

		$content = cwrc_createFormContent($inputXml, $data);

		$context = stream_context_create(array('http' => array('header' => $header, 'method' => $method, 'content' => $content)));

		$result = file_get_contents($url, false, $context);

		if (strpos($http_response_header[0], "201")) {
			return null;
		} else if (strpos($http_response_header[0], "200")) {
			return null;
		}else {
			return $http_response_header[0];
		}
	}
}
?>