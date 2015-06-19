<?php

namespace Obullo\Application\Middlewares;

use RuntimeException;

trait UnderMaintenanceTrait
{
    /**
     * Route domain parameters
     * 
     * @var array
     */
    public $params = array();

    /**
     * Domain is down
     *
     * @param array $params route domain parameters
     * 
     * @return void
     */
    public function domainIsDown(array $params)
    {
        $this->params = $params;

        $this->rootDomainIsDown();
        $this->subDomainIsDown();
    }

    /**
     * Check root domain is down
     * 
     * @return boolean
     */
    public function rootDomainIsDown()
    {
        if ($this->config['domain']['root']['maintenance'] == 'down') {  // First do filter for root domain
            $this->showMaintenance();
        }
    }

    /**
     * Check valid sub domain is down
     * 
     * @return void
     */
    public function subDomainIsDown()
    {
        // We inject parameters into $this->params variable in app->middleware() method.

        if ($this->params['domain'] == $this->config['url']['webhost']) {
            $this->params['domain'] = array('regex' => $this->c['config']['url']['webhost']);
        }
        if (! is_array($this->params['domain']) || ! isset($this->params['domain']['regex'])) {
            throw new RuntimeException(
                sprintf(
                    'Correct your routes.php domain value it must be like this <pre>%s</pre>', 
                    '$c[\'router\']->group( [\'domain\' => $c[\'config\'][\'domain\'][\'key\'], .., function () { .. }),.'
                )
            );
        }
        if (isset($this->params['domain']['maintenance']) 
            && $this->params['domain']['maintenance'] == 'down'
        ) {
            $this->showMaintenance();
        }
    }

    /**
     * Show maintenance view and die application
     * 
     * @return void
     */
    public function showMaintenance()
    {
        $this->response->status(503)->append($this->view->template('errors/maintenance'));
        $this->response->flush();
        die;
    }

}

// END UnderMaintenanceTrait File
/* End of file UnderMaintenanceTrait.php

/* Location: .Obullo/Application/Middlewares/UnderMaintenanceTrait.php */