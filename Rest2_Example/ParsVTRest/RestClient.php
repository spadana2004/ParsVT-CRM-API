<?php

/*
 *  VTFarsi Team
 *   http:www.vtfarsi.ir/
 *   Copyright (C) 2020  VTFarsi.ir Team
 *   All rights reserved
 */


class ParsVT_RestClient {

	protected static $name = 'ParsVTRestClient';
	protected static $version = '1.0';
	protected $defaultHeaders = array();
	protected $defaultOptions = array();
    protected $http_status_codes = array(100 => "Continue", 101 => "Switching Protocols", 102 => "Processing", 200 => "OK", 201 => "Created", 202 => "Accepted", 203 => "Non-Authoritative Information", 204 => "No Content", 205 => "Reset Content", 206 => "Partial Content", 207 => "Multi-Status", 300 => "Multiple Choices", 301 => "Moved Permanently", 302 => "Found", 303 => "See Other", 304 => "Not Modified", 305 => "Use Proxy", 306 => "(Unused)", 307 => "Temporary Redirect", 308 => "Permanent Redirect", 400 => "Bad Request", 401 => "Unauthorized", 402 => "Payment Required", 403 => "Forbidden", 404 => "Not Found", 405 => "Method Not Allowed", 406 => "Not Acceptable", 407 => "Proxy Authentication Required", 408 => "Request Timeout", 409 => "Conflict", 410 => "Gone", 411 => "Length Required", 412 => "Precondition Failed", 413 => "Request Entity Too Large", 414 => "Request-URI Too Long", 415 => "Unsupported Media Type", 416 => "Requested Range Not Satisfiable", 417 => "Expectation Failed", 418 => "I'm a teapot", 419 => "Authentication Timeout", 420 => "Enhance Your Calm", 422 => "Unprocessable Entity", 423 => "Locked", 424 => "Failed Dependency", 424 => "Method Failure", 425 => "Unordered Collection", 426 => "Upgrade Required", 428 => "Precondition Required", 429 => "Too Many Requests", 431 => "Request Header Fields Too Large", 444 => "No Response", 449 => "Retry With", 450 => "Blocked by Windows Parental Controls", 451 => "Unavailable For Legal Reasons", 494 => "Request Header Too Large", 495 => "Cert Error", 496 => "No Cert", 497 => "HTTP to HTTPS", 499 => "Client Closed Request", 500 => "Internal Server Error", 501 => "Not Implemented", 502 => "Bad Gateway", 503 => "Service Unavailable", 504 => "Gateway Timeout", 505 => "HTTP Version Not Supported", 506 => "Variant Also Negotiates", 507 => "Insufficient Storage", 508 => "Loop Detected", 509 => "Bandwidth Limit Exceeded", 510 => "Not Extended", 511 => "Network Authentication Required", 598 => "Network read timeout error", 599 => "Network connect timeout error");

    public function __construct() {
        $site_URL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https': 'http')."://".$_SERVER['HTTP_HOST'].(dirname($_SERVER['PHP_SELF']) != '/' && dirname($_SERVER['PHP_SELF']) != '\\' ? str_replace('\\','/',dirname($_SERVER['PHP_SELF'])) : '').'/';;
		$this->defaultOptions[CURLOPT_REFERER] = $site_URL;
		$this->defaultOptions[CURLOPT_USERAGENT] = self::$name.'/'.self::$version.'(CRM '.self::$version.')';
		$this->defaultOptions[CURLOPT_RETURNTRANSFER] = true;
		$this->defaultOptions[CURLOPT_HEADER] = true;
		$this->defaultOptions[CURLOPT_FOLLOWLOCATION] = true;
		$this->defaultOptions[CURLOPT_MAXREDIRS] = 5;
		$this->defaultOptions[CURLOPT_SSL_VERIFYPEER] = 0;
		$this->defaultOptions[CURLOPT_SSL_VERIFYHOST] = 0;
		$this->defaultOptions[CURLOPT_TIMEOUT] = 30;

		$this->defaultHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
		$this->defaultHeaders['Cache-Control'] = 'no-cache';
	}

	public function setDefaultOption($option, $value) {
		$this->defaultOptions[$option] = $value;
		return $this;
	}

	public function setDefaultHeader($header, $value) {
		$this->defaultHeaders[$header] = $value;
		return $this;
	}

	public function setBasicAuthentication($username, $password) {
		$this->defaultHeaders['Authorization'] = 'Basic '.base64_encode($username.':'.$password);
	}

	protected function exec($curlopts) {
		$curl = curl_init();
		foreach ($curlopts as $option => $value) {
			if ($option) {
				curl_setopt($curl, $option, $value);
			}
		}

		// To be secure - we don't want user to override this
		// and open doors for hackers.
		$cookiefile = tempnam(sys_get_temp_dir(), ".".uniqid()."co");
		$cookiefp = fopen($cookiefile, "w");

		curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiefile);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiefile);

		// Now execute
		$response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_info = curl_getinfo($curl);

        $header_size = $curl_info['header_size'];
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);

		$responseData = array( 'status' => $status, 'response' => $body);
		if (curl_errno($curl)) {
			$errorMessage = curl_error($curl);
			$responseData['errorMessage'] = $errorMessage;
		}
		if ($status != 200 && empty($responseData['errorMessage'])) {
            $responseData['errorMessage'] = $this->http_status_codes[$status];
            $responseInfo = $this->parseResponse($header, $status);
            $responseData['response'] = $responseInfo;
        }

		curl_close($curl);

		fclose($cookiefp);
		unlink($cookiefile);

		return $responseData;
	}

	public function buildCurlOptions(array $headers, array $options) {
		foreach ($this->defaultOptions as $option => $value) {
			switch ($option) {
				// Stop overrides on some keys.
				case CURLOPT_REFERER:
				case CURLOPT_USERAGENT:
					$options[$option] = $value;
					break;
				default:
					// Pickup the overriding value
					if (!isset($options[$option])) {
						$options[$option] = $value;
					}
					break;
			}
		}

		$headeropts = array();
		foreach ($this->defaultHeaders as $key => $value) {
			// Respect the overriding value
			if ($headers && isset($headers[$key]))
				continue;
			$headeropts[] = ($key.': '.$value);
		}
		foreach ($headers as $key => $value)
			$headeropts[] = ($key.': '.$value);
		$options[CURLOPT_HTTPHEADER] = $headeropts;

		return $options;
	}

	public function get($url, $params = array(), $headers = array(), $options = array()) {
		$curlopts = $this->buildCurlOptions($headers, $options);


		$curlopts[CURLOPT_HTTPGET] = true;

		if (!empty($params)) {
			if (stripos($url, '?') === false)
				$url .= '?';
			else
				$url .= '&';
			$url .= http_build_query($params, '', '&');
		}

		$curlopts[CURLOPT_URL] = $url;
		return $this->exec($curlopts);
	}

	public function post($url, $params = array(), $headers = array(), $options = array()) {
		$curlopts = $this->buildCurlOptions($headers, $options);

		$curlopts[CURLOPT_POST] = true;
		if ($params) {
			$curlopts[CURLOPT_POSTFIELDS] = http_build_query($params, '', '&');
		}

		$curlopts[CURLOPT_URL] = $url;
		return $this->exec($curlopts);
	}

	public function put($url, $params = array(), $headers = array(), $options = array()) {
		$curlopts = $this->buildCurlOptions($headers, $options);

		$curlopts[CURLOPT_CUSTOMREQUEST] = 'PUT';
		if ($params) {
			$curlopts[CURLOPT_POSTFIELDS] = http_build_query($params, '', '&');
		}

		$curlopts[CURLOPT_URL] = $url;
		return $this->exec($curlopts);
	}

	protected function parseResponse($response, $status){
        $output = rtrim($response);
        $data = explode("\n",$output);
        $data[0] =  str_replace("HTTP/1.0 ".$status." ","",$data[0]);
        return str_replace("HTTP/1.1 ".$status." ","",$data[0]);
    }


}
