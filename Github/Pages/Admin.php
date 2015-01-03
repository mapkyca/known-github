<?php

    /**
     * Github pages
     */

    namespace IdnoPlugins\Github\Pages {

        /**
         * Default class to serve Github settings in administration
         */
        class Admin extends \Idno\Common\Page
        {

            function getContent()
            {
                $this->adminGatekeeper(); // Admins only
                $t = \Idno\Core\site()->template();
                $body = $t->draw('admin/github');
                $t->__(['title' => 'Github', 'body' => $body])->drawPage();
            }

            function postContent() {
                $this->adminGatekeeper(); // Admins only
                $appId = $this->getInput('appId');
                $secret = $this->getInput('secret');
                \Idno\Core\site()->config->config['github'] = [
                    'appId' => $appId,
                    'secret' => $secret
                ];
                \Idno\Core\site()->config()->save();
                \Idno\Core\site()->session()->addMessage('Your Github application details were saved.');
                $this->forward('/admin/github/');
            }

        }

    }