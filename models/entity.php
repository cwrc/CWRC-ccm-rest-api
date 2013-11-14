<?php
define('MULTIPART_BOUNDRY', md5(time()));

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

	function getPID() {
		return $this -> data -> pid;
	}

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

	function createFormContent($inputXml, $data) {

		$content = "--" . MULTIPART_BOUNDRY . "\r\n";

		foreach ($data as $key => $val) {
			$content .= "Content-Disposition: form-data; name=\"" . $key . "\"\r\n\r\n";
			$content .= $val . "\r\n";
			$content .= "--" . MULTIPART_BOUNDRY . "\r\n";
		}

		$content .= "Content-Disposition: form-data; name=\"entity\"; filename=\"entity_data\"\r\n";
		$content .= "Content-type: text/xml\r\n\r\n";
		$content .= $inputXml . "\r\n";

		$content .= "--" . MULTIPART_BOUNDRY . "--";

		return $content;
	}

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
			$method = 'PUT';
			$url = cwrc_url() . "/islandora/rest/v1/object/" . $this -> data -> pid . "/datastream/" . $this -> content_name;
			$data = array('versionable' => 'true');
		}

		$content = $this -> createFormContent($inputXml, $data);
		//array_push($header, 'Content-Length: ' . strlen($content));

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
