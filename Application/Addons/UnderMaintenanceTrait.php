<?php

namespace Obullo\Application\Addons;

use RuntimeException;

trait UnderMaintenanceTrait
{
    /**
     * Domain is down
     * 
     * @return void
     */
    public function domainIsDown()
    {
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
        if ($this->c['config']['domain']['root']['maintenance'] == 'down') {  // First do filter for root domain
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

        if ($this->params['domain'] == $this->c['config']['url']['webhost']) {
            $this->params['domain'] = array('regex' => $this->c['config']['url']['webhost']);
        }
        if ( ! is_array($this->params['domain']) OR ! isset($this->params['domain']['regex'])) {
            throw new RuntimeException(
                sprintf(
                    'Correct your routes.php domain value it must be like this <pre>%s</pre>', 
                    '$c[\'router\']->group( [\'domain\' => $c[\'config\'][\'domain\'][\'key\'], .., function () { .. }),.'
                )
            );
        }
        if (isset($this->params['domain']['maintenance']) 
            AND $this->params['domain']['maintenance'] == 'down'
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
        $this->c['response']->status(503)->append($this->c['view']->template('errors/maintenance'));
        $this->c['response']->flush();
        die;
    }

}

// END UnderMaintenanceTrait File
/* End of file UnderMaintenanceTrait.php

/* Location: .Obullo/Application/Addons/UnderMaintenanceTrait.php */