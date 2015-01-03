<?php

/**
 * Github pages
 */

namespace IdnoPlugins\Github\Pages {

    /**
     * Default class to serve the Github callback
     */
    class Callback extends \Idno\Common\Page {

	function getContent() {
	    $this->gatekeeper(); // Logged-in users only

	    try {
		if ($github = \Idno\Core\site()->plugins()->get('Github')) {
		    if ($githubAPI = $github->connect()) {

			if ($response = $githubAPI->getAccessToken(\IdnoPlugins\Github\Main::$TOKEN_ENDPOINT, 'authorization_code', [
			    'code' => $this->getInput('code'), 
			    'redirect_uri' => \IdnoPlugins\Github\Main::getRedirectUrl(), 
			    'state' => \IdnoPlugins\Github\Main::getState()])) {

			    $response = json_decode($response['content']); 
		
			    if ($response->error) throw new \Exception($response->error_description);
			    
			    $user = \Idno\Core\site()->session()->currentUser();
			    $user->github = ['access_token' => $response->access_token, 'scope' => $response->scope];

			    $user->save();
			    \Idno\Core\site()->session()->addMessage('Your Github account was connected with the following scope ('.$response->scope.').');
			}
		    }
		}
	    } catch (\Exception $e) {
		\Idno\Core\site()->session()->addErrorMessage($e->getMessage());
	    }
	    
	    $this->forward('/account/github/');
	}

    }

}