<?php

namespace Obullo\Application\Middlewares;

use RuntimeException;

trait UnderMaintenanceTrait
{
    /**
     * Detected domain
     * 
     * @var array
     */
    protected $currentDomain;

    /**
     * Maintenance status : up / down
     * 
     * @var string
     */
    protected $currentDomainMaintenance;

    /**
     * Domain is down
     *
     * @param array $params route domain parameters
     * 
     * @return void
     */
    public function domainIsDown(array $params)
    {
        foreach ($this->config['domain'] as $domain) {
            if ($domain['regex'] == $params['domain']) {  // If route domain equal to domain.php regex config
                $this->currentDomain = $params['domain'];
                $this->currentDomainMaintenance = $domain['maintenance'];
            }
        }
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
        if (! is_string($this->currentDomain)) {
            throw new RuntimeException(
                sprintf(
                    'Routes.php domain value must be string. <pre>%s</pre>', 
                    '$c[\'router\']->group( [\'domain\' => \'example.com\', .., function () { .. }),.'
                )
            );
        }
        if ($this->currentDomainMaintenance == 'down') {
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