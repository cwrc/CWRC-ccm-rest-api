<?php

function cwrc_url(){
	return "http://cwrc-dev-01.srv.ualberta.ca";
}

function initialize_cookie(){
	
}

function initialize_user(){
	// Should this be encrypted
	cwrc_login($_POST['username'], $_POST['password']);
}

function cwrc_login($username, $password){
	$url = cwrc_url() . "/rest/user/login";
	$data = array('username' => $username, 'password' => $password);
	$options = array(
			'http' => array(
				'header'  => "Content-type: application/json\r\n",
				'method'  => 'POST',
				'content' => json_encode($data),
			),
		);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	
	$cookies = '';
	foreach($http_response_header as $s){
		if(preg_match('/^Set-Cookie:\s*([^;]+)/', $s, $parts)){
			$cookies = $cookies . $parts[1] . ';';
		}
	}
	

	setcookie('cwrc-api', $cookies, 0, '/');
	//$_SERVER['HTTP_COOKIE'] = $result;
}

function get_login_cookie(){
	$cookies = array();
	$eachCookie = explode(';', $_COOKIE['cwrc-api']);
	
	foreach($eachCookie as $val){
		if(strlen($val) > 0){
			$parts = explode('=', $val);
			$cookies[$parts[0]] = $parts[1];
		}
	}
	
	return $cookies;
}

function cwrc_logout(){	
	setcookie('cwrc-api', '', 1, '/');
}
