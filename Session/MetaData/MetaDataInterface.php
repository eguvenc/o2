<?php

namespace Obullo\Session\MetaData;

use Obullo\Log\LoggerInterface;
use Obullo\Session\SessionInterface;
use Obullo\Http\Request\RequestInterface;

/**
 * MetaData Storage Interface
 * 
 * @category  Session
 * @package   MetaData
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/session
 */
interface MetaDataInterface
{
    /**
     * Constructor
     * 
     * @param object $session \Obullo\Session\SessionInterface
     * @param object $logger  \Obullo\Log\LogInterface
     * @param object $request \Obullo\Http\Request\RequestInterface
     * @param array  $params  service parameters
     */
    public function __construct(SessionInterface $session, LoggerInterface $logger, RequestInterface $request, array $params);

    /**
     * Compare meta data with user data if something went 
     * wrong destroy the session and say good bye to user.
     * 
     * @return boolean
     */
    public function isValid();

    /**
     * Stores meta data into $this->meta variable.
     * 
     * @return void
     */
    public function build();

    /**
     * Create meta data
     * 
     * @return void
     */
    public function create();

    /**
     * Update meta data
     * 
     * @return void
     */
    public function update();

    /**
     * Remove meta data
     * 
     * @return void
     */
    public function remove();

    /**
     * Read meta data
     * 
     * @return array
     */
    public function read();
}