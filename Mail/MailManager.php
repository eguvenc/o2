<?php

namespace Obullo\Mail;

use RuntimeException;
use Obullo\Container\ContainerInterface;

/**
 * MailManager Class
 * 
 * @category  Mailer
 * @package   MailManager
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/mailer
 */
class MailManager
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Mailer instance
     * 
     * @var object
     */
    protected $mailer;

    /**
     * Service parameters
     * 
     * @var array
     */
    protected $params = array();

    /**
     * Instances
     * 
     * @var array
     */
    protected $mailers = array();

    /**
     * Create classes
     * 
     * @param object $c container
     * 
     * @return object
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    /**
     * Set mailer service parameters
     *
     * @param array $params mailer parameters
     * 
     * @return void
     */
    public function setParameters($params = array())
    {
        $this->params = array_merge($params, $this->c['config']->load('mailer'));
    }

    /**
     * Returns to configuration parameters
     * 
     * @return array
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * Returns to mail manager instance
     * 
     * @param string $mailer name
     * 
     * @return object
     */
    public function setProvider($mailer)
    {
        $Class = '\Obullo\Mail\Provider\\Null';
        /**
         * Create new instance if we haven't got the same
         * otherwise return to old instance
         */
        if (! isset($this->mailers[$mailer])) {
            
            if ($this->params['default']['enabled']) {

                if (empty($this->params['provider'][$mailer])) {
                    throw new RuntimeException(
                        sprintf("Mail provider %s is not configured in mailer service.", $mailer)
                    );
                }
                $Class = $this->params['provider'][$mailer]['class'];
            }
            return $this->mailer = $this->mailers[$mailer] = new $Class(
                $this->c,
                $this->c['translator'],
                $this->c['logger'],
                $this->getParameters()
            );
        }
        $this->mailer = $this->mailers[$mailer];
        return $this;
    }

    /**
     * Call mailer methods
     * 
     * @param string $method    method name
     * @param array  $arguments parameters
     * 
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->mailer, $method), $arguments);
    }
}