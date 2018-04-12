<?php

namespace IdnoPlugins\Github {

    class Main extends \Idno\Common\Plugin {

	public static $AUTHORIZATION_ENDPOINT = 'https://github.com/login/oauth/authorize';
	public static $TOKEN_ENDPOINT = 'https://github.com/login/oauth/access_token';

	public static function getRedirectUrl() {
	    return \Idno\Core\site()->config()->url . 'github/callback';
	}

	public static function getState() {
	    return md5(\Idno\Core\site()->config()->site_secret . \Idno\Core\site()->config()->url . dirname(__FILE__));
	}

	function registerPages() {
	    // Register the callback URL
	    \Idno\Core\site()->addPageHandler('github/callback', '\IdnoPlugins\Github\Pages\Callback');
	    // Register admin settings
	    \Idno\Core\site()->addPageHandler('admin/github', '\IdnoPlugins\Github\Pages\Admin');
	    // Register settings page
	    \Idno\Core\site()->addPageHandler('account/github', '\IdnoPlugins\Github\Pages\Account');

	    /** Template extensions */
	    // Add menu items to account & administration screens
	    \Idno\Core\site()->template()->extendTemplate('admin/menu/items', 'admin/github/menu');
	    \Idno\Core\site()->template()->extendTemplate('account/menu/items', 'account/github/menu');
	}

	function registerEventHooks() {
	    
	    // Register syndication services
	    \Idno\Core\site()->syndication()->registerService('github', function() {
		return $this->hasGithub();
	    }, ['note']);

	    \Idno\Core\Idno::site()->addEventHook('user/auth/success', function (\Idno\Core\Event $event) {
	    	 if ($this->hasGithub()) {
			if (is_array(\Idno\Core\site()->session()->currentUser()->github)) { 
			    foreach (\Idno\Core\site()->session()->currentUser()-> github as $id => $details) {
				if ($id = 'access_token') {
				    \Idno\Core\site()->syndication()->registerServiceAccount('github', $id, 'github');
				}
			    }	
			}
		}
 	    });	

	    // Activate syndication automatically, if replying to github
	    \Idno\Core\site()->addEventHook('syndication/selected/github', function (\Idno\Core\Event $event) {
		$eventdata = $event->data();

		if (!empty($eventdata['reply-to'])) {
		    $replyto = $eventdata['reply-to'];
		    if (!is_array($replyto))
			$replyto = [$replyto];

		    foreach ($replyto as $url) {
			if (strpos(parse_url($url)['host'], 'github.com')!==false)
				$event->setResponse(true);
		    }
		}
	    });
	    
	    $reply_func = function(\Idno\Core\Event $event) {
		$object = $event->data()['object'];
		if ($this->hasGithub()) {
		    if ($githubAPI = $this->connect()) {
			$githubAPI->setAccessToken(\Idno\Core\site()->session()->currentUser()->github['access_token']);
			$message = $object->getDescription();

			$in_reply_to = array_merge((array) $object->inreplyto, (array) $object->syndicatedto); //\Idno\Core\site()->currentPage()->getInput('inreplyto');
			if (!is_array($in_reply_to))
			    $in_reply_to = [$in_reply_to];

			foreach ($in_reply_to as $url) {

			    $url = $this->normaliseCommentUrl($url);
			    
			    try {
				if (!$githubAPI->access_token) throw new \Exception("Access token is missing.");
				
				$result = \Idno\Core\Webservice::post($url, json_encode([
				    'title' => $object->getTitle(),
				    'body' => $message,
				]), ['Content-Type: application/json', 'Authorization: token '. $githubAPI->access_token]);

				$content = json_decode($result['content']);

				if ($result['response'] == 201) {

				    // Success
				    $link = $content->html_url;

				    $object->setPosseLink('github', $link);
				    $object->save();
				} else {
				    \Idno\Core\site()->logging->log("Github Syndication failed with " . print_r($result, true), LOGLEVEL_ERROR);

				    if (!empty($content->error_description))
					throw new \Exception($content->error_description);
				    
				    if (!empty($content->message))
					throw new \Exception("\"{$content->message}\" response code {$result['response']}");

				    throw new \Exception("Error code {$result['response']}");
				}
			    } catch (\Exception $e) {
				\Idno\Core\site()->session()->addErrorMessage('There was a problem posting reply to ' . $url . ': ' . $e->getMessage());
			    }
			}
		    }
		}
	    };

	    // Reply to a comment
	    \Idno\Core\site()->addEventHook('post/note/github', $reply_func);
	    \Idno\Core\site()->addEventHook('post/article/github', $reply_func);
	   
	}

	/**
	 * Connect to Github
	 * @return bool|\IdnoPlugins\Github\Client
	 */
	function connect() {
	    if (!empty(\Idno\Core\site()->config()->github)) {
		$api = new Client(
			\Idno\Core\site()->config()->github['appId'], \Idno\Core\site()->config()->github['secret']
		);
		return $api;
	    }
	    return false;
	}

	/**
	 * Can the current user use Github?
	 * @return bool
	 */
	function hasGithub() {
	    if (\Idno\Core\site()->session()->currentUser()->github) {
		return true;
	    }
	    return false;
	}

	/**
	 * When replying to a comment, we need to turn the HTML url to a correct post URL.
	 * @param type $htmlUrl
	 */
	function normaliseCommentUrl($htmlUrl) {

	    // Map github to api endpoint
	    $htmlUrl = preg_replace('#https:\/\/(www\.)?github\.com\/#', 'https://api.github.com/repos/', $htmlUrl);
	    
	    // Strip fragment
	    $htmlUrl = $this->stripFragment($htmlUrl);

	    // Comment on issue
	    if ($this->isIssueUrl($htmlUrl))
		    $htmlUrl = trim($htmlUrl, '/') . '/comments';
	    
	    // Comment on pull
	    //if (preg_match('#pull\/[0-9]+#', $htmlUrl))
	//	    $htmlUrl = trim(str_replace('pull','pulls', $htmlUrl), '/') . '/comments';
	    
	    return $htmlUrl;
	}
	
	/**
	 * Is this an issue URL (posting to create a new issue)
	 * @param type $url
	 */
	protected function isIssueUrl($url) {
	    if (preg_match('#issues\/[0-9]+#', $url))
		    return true;
	    
	    return false;
	}
	
	/**
	 * Strip fragments from a url
	 * @param type $url
	 */
	protected function stripFragment($url) {
	    
	    $bits = parse_url($url);
	    unset($bits['fragment']);
	    
	    return \Idno\Common\Page::buildUrl($bits);
	}

    }

}
