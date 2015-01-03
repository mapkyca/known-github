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
	    }, ['note', 'article']);


	    $reply_func = function(\Idno\Core\Event $event) {
		$object = $event->data()['object'];
		if ($this->hasGithub()) {
		    if ($githubAPI = $this->connect()) {
			$githubAPI->setAccessToken(\Idno\Core\site()->session()->currentUser()->github['access_token']);
			$message = $object->getDescription();

			$in_reply_to = \Idno\Core\site()->currentPage()->getInput('inreplyto');
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

	    $htmlUrl = preg_replace('#https:\/\/(www\.)?github\.com\/#', 'https://api.github.com/repos/', $htmlUrl);

	    if (preg_match('#issues\/[0-9]+#', $htmlUrl))
		    $htmlUrl = trim($htmlUrl, '/') . '/comments';
	    
	    return $htmlUrl;
	}
	
	/** 
	 * Get an issue URL.
	 * 
	 * If in_replyto set
	 */
	function getIssueUrl() {
	    
	}

    }

}
