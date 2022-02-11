<?php

/*
 *  VTFarsi Team
 *   http:www.vtfarsi.ir/
 *   Copyright (C) 2020  VTFarsi.ir Team
 *   All rights reserved
 */

include_once dirname(__FILE__).'/RestClient.php';

/**
 * Provides API to work with HTTP Connection.
 * @package vtlib
 */
class ParsVT_NetClient {

	protected $client;
	protected $url;
	protected $response;
	protected $headers = array();

	/**
	 * Constructor
	 * @param String URL of the site
	 * Example: 
	 * $client = new Vtiger_New_Client('http://demo.vtiger.ir');
	 */
	function __construct($url, $username=false, $accesskey=false) {
        $url = rtrim($url, "/");
        $url = $url.'/modules/ParsVT/ws/API/V2/vtiger/extended/';
        $this->url = $url;
		$this->client = new ParsVT_RestClient();
		$this->response = false;
		$this->setDefaultHeaders();
		if ($username && $accesskey)
		    $this->setAuthorization($username, $accesskey);
	}

	function setDefaultHeaders() {
		$headers = array();
		if (isset($_SERVER)) {
		    $site_URL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https': 'http')."://".$_SERVER['HTTP_HOST'].(dirname($_SERVER['PHP_SELF']) != '/' && dirname($_SERVER['PHP_SELF']) != '\\' ? str_replace('\\','/',dirname($_SERVER['PHP_SELF'])) : '').'/';;
            $headers['referer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ($site_URL."?noreferer");
			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$headers['user-agent'] = $_SERVER['HTTP_USER_AGENT'];
			}
		}
		$this->headers = $headers;
	}

	function setAuthorization($username, $password) {
		$this->client->setBasicAuthentication($username, $password);
	}

	/**
	 * Set custom HTTP Headers
	 * @param Map HTTP Header and Value Pairs
	 */
	function setHeaders($headers) {
		$this->client->buildCurlOptions($headers, array());
	}

	/**
	 * Perform a GET request
	 * @param Map key-value pair or false
	 * @param Integer timeout value
	 */
	function doGet($method, $params = false) {
		$response = $this->client->get($this->url.$method, $params, $this->headers);
		return $response;
	}

	/**
	 * Perform a POST request
	 * @param Map key-value pair or false
	 * @param Integer timeout value
	 */
	function doPost($method, $params = false) {
		$response = $this->client->post($this->url.$method, $params, $this->headers);
		return $response;
	}

	/**
	 * Perform a PUT request
	 * @param Map key-value pair or false
	 * @param Integer timeout value
	 */
	function doPut($method, $params = false) {
		$response = $this->client->put($this->url.$method, $params, $this->headers);
		return $response;
	}

    public function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

?>