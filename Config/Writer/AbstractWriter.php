<?php

namespace Obullo\Config\Writer;

use Traversable;
use RuntimeException;
use Obullo\Utils\ArrayUtils;

/**
 * Abstract Writer Class
 *
 * Borrowed from Zend Framework 
 * 
 * @category  Config
 * @package   Writer
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/config
 */
abstract class AbstractWriter
{
    /**
     * Defined by Writer interface.
     *
     * @param string $filename      filename
     * @param mixed  $config        config
     * @param bool   $exclusiveLock exclusive lock
     * 
     * @see    WriterInterface::toFile()
     * @throws Exception\RuntimeException
     * 
     * @return void
     */
    public function toFile($filename, $config, $exclusiveLock = true)
    {
        if (empty($filename)) {
            throw new RuntimeException('No file name specified');
        }
        if (! is_writable($filename)) {        // Check file is writable
            throw new RuntimeException(
                sprintf(
                    '%s file is not writable.', 
                    $this->file
                )
            );
        }
        $flags = 0;
        if ($exclusiveLock) {
            $flags |= LOCK_EX;
        }
        set_error_handler(
            function ($error, $message = '', $file = '', $line = 0) use ($filename) {
                $file = $line = null;
                throw new RuntimeException(
                    sprintf('Error writing to "%s": %s', $filename, $message),
                    $error
                );
            },
            E_WARNING
        );
        try {
            file_put_contents($filename, $this->toString($config), $flags);
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }
        restore_error_handler();
    }

    /**
     * Defined by Writer interface.
     *
     * @param mixed $config config
     * 
     * @see    WriterInterface::toString()
     * @throws Exception\InvalidArgumentException
     * 
     * @return string
     */
    public function toString($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        } elseif (! is_array($config)) {
            throw new RuntimeException(__METHOD__ . ' expects an array or Traversable config');
        }
        return $this->processConfig($config);
    }

    /**
     * Abstract process
     * 
     * @param array $config config
     * 
     * @return string
     */
    abstract protected function processConfig(array $config);
}