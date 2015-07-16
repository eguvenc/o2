<?php

namespace Obullo\Mail;

use Obullo\Container\ContainerInterface;
use Obullo\Service\ServiceProviderInterface;

/**
 * MailManager Class
 * 
 * @category  Mailer
 * @package   MailManager
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
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
    public function setConfiguration($params = array())
    {
        $this->params = array_merge($params, $this->c['config']->load('mailer'));
    }

    /**
     * Returns to mail manager instance
     * 
     * @param string $mailer name
     * 
     * @return object
     */
    public function getMailer($mailer)
    {
        /**
         * Create new instance if we haven't got the same
         * otherwise return to old instance
         */
        if (! isset($this->mailers[$mailer])) {
            if ($this->params['default']['enabled']) {
                $Class = '\Obullo\Mail\Provider\\'.ucfirst($mailer);
            } else {
                $Class = '\Obullo\Mail\Provider\\Null';
            }
            return $this->mailer = $this->mailers[$mailer] = new $Class($this->c, $this->params);
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