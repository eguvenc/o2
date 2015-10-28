<?php

namespace Obullo\Error;

/**
 * Exception interface
 * 
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
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