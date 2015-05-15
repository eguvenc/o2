<?php

namespace Recaptcha\Examples;

class Ajax extends \Controller
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
        if ($this->request->isAjax()) {

            $this->c['validator']->setRules('username', 'Username', 'required|trim|max(100)');

            if ($this->validator->isValid()) {
                $this->form->success('Form Validation Success.');
            }
            $this->form->setErrors($this->validator);
            echo $this->c['response']->json($this->form->outputArray());
            return;
        }

        $this->view->load(
            'ajax',
            [
                'title' => 'Hello Captcha !'
            ]
        );
    }
}