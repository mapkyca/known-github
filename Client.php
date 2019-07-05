<?php

namespace IdnoPlugins\Github {
    
    class Client {
	
	private $key;
	private $secret;
	
	public $access_token;
	
	function __construct($apikey, $secret) {
	    $this->key = $apikey;
	    $this->secret = $secret;
	}
	
	public function getAuthenticationUrl($baseURL, $redirectURL, $parameters = []) {
	    
	    $parameters['redirect_uri'] = $redirectURL;
	    
	    $url = $baseURL . "?client_id={$this->key}&" . http_build_query($parameters);
	    //foreach ($parameters as $key => $value)
		//$url .= '&' . urlencode($key) . '=' . urlencode($value);
	    
	    return $url;
	}
	
	public function getAccessToken($endpointUrl, $grant_type = 'authorization_code', array $parameters) {
	    
	    if ($parameters['state'] != \Idno\Core\site()->plugins()->get('Github')->getState())
		throw new \Exception('State value not correct, possible CSRF attempt.');
		
	    unset($parameters['state']);
	    
	    
	    $parameters['client_id'] = $this->key;
	    $parameters['client_secret'] = $this->secret;
	    $parameters['grant_type'] = $grant_type;
	    	    
	    return \Idno\Core\Webservice::post(\IdnoPlugins\Github\Main::$TOKEN_ENDPOINT, $parameters, ['Accept: application/json']);
	    
	}
	
	public function setAccessToken($token) {
	    $this->access_token = $token;
	}
	
    }
}