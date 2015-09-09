<?php

namespace Obullo\Application\Middlewares;

use RuntimeException;

trait UnderMaintenanceTrait
{
    /**
     * Maintenance status : up / down
     * 
     * @var mixed
     */
    protected $maintenance;

    /**
     * Domain is down
     *
     * @param array $params route domain parameters
     * 
     * @return void
     */
    public function check(array $params)
    {   
        $maintenance = $this->config['maintenance'];  // Default loaded in config class.
        
        $maintenance['root']['regex'] = null; 
        $maintenance['root']['namespace'] = null;

        $domain    = (isset($params['domain'])) ? $params['domain'] : null;
        $namespace = (isset($params['namespace'])) ? $params['namespace'] : null;

        $this->parse($maintenance, $domain, $namespace);

        $this->checkRoot();
        $this->checkNodes();
    }

    /**
     * Parse maintenance configuration
     * 
     * @param array  $maintenance config
     * @param string $domain      mixed
     * @param string $namespace   mixed
     * 
     * @return void
     */
    public function parse($maintenance, $domain, $namespace)
    {
        foreach ($maintenance as $label) {
            if (! empty($label['regex']) && ! empty($label['namespace'])) {
                if ($label['regex'] == $domain && $label['namespace'] == $namespace) {
                    $this->maintenance = $label['maintenance'];
                }
            } elseif (! empty($label['regex']) && $label['regex'] == $domain) { // If route domain equal to domain.php regex config
                $this->maintenance = $label['maintenance'];
            } elseif (! empty($label['namespace']) && $label['namespace'] == $namespace) {
                $this->maintenance = $label['maintenance'];
            }
        }
    }

    /**
     * Check root domain is down
     * 
     * @return boolean
     */
    public function checkRoot()
    {
        if ($this->config['maintenance']['root']['maintenance'] == 'down') {  // First do filter for root domain
            $this->showMaintenance();
        }
    }

    /**
     * Check app nodes is down
     * 
     * @return void
     */
    public function checkNodes()
    {
        if (empty($this->maintenance)) {
            return;
        }
        if ($this->maintenance == 'down') {
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