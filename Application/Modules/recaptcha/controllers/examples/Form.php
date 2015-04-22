<?php

namespace Recaptcha\Examples;

class Form extends \Controller
{
    /**
     * Loader
     * 
     * @return void
     */
    public function load()
    {
        $this->c['url'];
        $this->c['form'];
        $this->c['view'];
        $this->c['request'];
        $this->c['recaptcha'];
    }

    /**
     * Index
     * 
     * @return void
     */
    public function index()
    {
        if ($this->request->isPost()) {

            $this->c['validator']->setRules('username', 'Username', 'required|trim|max(100)');

            if ($this->c['validator']->isValid()) {
                $this->form->success('Form Validation Success.');
            }
        }
        $this->view->load(
            'form',
            [
                'title' => 'Hello Captcha !'
            ]
        );
    }
}