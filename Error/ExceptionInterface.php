<?php

namespace Obullo\Error;

/**
 * Interface Exception
 * 
 * @category  Database
 * @package   SQLLogger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/error
 */
interface ExceptionInterface
{
     /**
     * Display the exception view
     * 
     * @param object  $e          exception object
     * @param boolean $fatalError whether to fatal error
     * 
     * @return string view
     */
    public function show(\Exception $e, $fatalError = false);
}