<?php

    /**
     * Github pages
     */

    namespace IdnoPlugins\Github\Pages {

        /**
         * Default class to serve Github-related account settings
         */
        class Account extends \Idno\Common\Page
        {

            function getContent()
            {
                $this->gatekeeper(); // Logged-in users only
                if ($github = \Idno\Core\site()->plugins()->get('Github')) {
                    if (!$github->hasGithub()) {
                        if ($githubAPI = $github->connect()) {
                            $login_url = $githubAPI->getAuthenticationUrl(
				\IdnoPlugins\Github\Main::$AUTHORIZATION_ENDPOINT,
				\IdnoPlugins\Github\Main::getRedirectUrl(),
				['response_type' => 'code', 'state' => \IdnoPlugins\Github\Main::getState(), 'scope' => 'repo,gist,public_repo'] 
                            );
			    
                        }
                    } else {
                        $login_url = '';
                    }
                }
                $t = \Idno\Core\site()->template();
                $body = $t->__(['login_url' => $login_url])->draw('account/github');
                $t->__(['title' => 'Github', 'body' => $body])->drawPage();
            }

            function postContent() {
                $this->gatekeeper(); // Logged-in users only
                if (($this->getInput('remove'))) {
                    $user = \Idno\Core\site()->session()->currentUser();
                    $user->github = [];
                    $user->save();
                    \Idno\Core\site()->session()->addMessage('Your Github settings have been removed from your account.');
                }
                $this->forward('/account/github/');
            }

        }

    }