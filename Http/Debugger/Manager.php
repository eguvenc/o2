<?php

namespace Obullo\Http\Debugger;

use RuntimeException;
use Obullo\Container\ContainerInterface;

/**
 * Manager Class
 * 
 * @category  Debug
 * @package   Manager
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/debugger
 */
class Manager
{
    /**
     * Container class
     * 
     * @var object
     */
    protected $c;

    /**
     * Logger class
     * 
     * @var object
     */
    protected $logger;

    /**
     * Config
     * 
     * @var array
     */
    protected $config;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
        $this->logger = $c['logger'];
        $this->config = $c['config']->load('logger');
    }

    /**
     * Display logs
     * 
     * @return string echo the log output
     */
    public function printConsole()
    {
        $envtab = new EnvTab($this->c);
        $envHtml = $envtab->printHtml();

        $websocketUrl = $this->c['config']['http']['debugger']['socket'];
        $debuggerUrl = $this->c['app']->uri->getBaseUrl(INDEX_PHP.'/debugger/index?o_debugger=1');

        ob_start();
        include_once 'Views/Debugger.php';
        $view = ob_get_clean();

        $body = '';
        $body = $this->getLogHtml('http', $view);
        $body = $this->getLogHtml('ajax', $body);
        $body = $this->getLogHtml('cli', $body);
        unset($envHtml);

        $patterns = array(
            '#<p>(.*(Uri Class Initialized\b).*)<\/p>#',
            '#<p>(.*(system.error\b).*)<\/p>#',
            '#<p>(.*(system.warning\b).*)<\/p>#',
            '#<p>(.*(system.notice\b).*)<\/p>#',
            '#<p>(.*(system.emergency\b).*)<\/p>#',
            '#<p>(.*(system.critical\b).*)<\/p>#',
        );
        $replace = array(
            '<p class="title">$1</p>',
            '<p class="error">$1</p>',
            '<p class="error">$1</p>',
            '<p class="error">$1</p>',
            '<p class="error">$1</p>',
        );
        $body = preg_replace($patterns, $replace, $body);
        if (empty($body)) {
            $body = $view;
        }
        return $body;
    }

    /**
     * Get log path
     * 
     * @param string $request log request type
     * 
     * @return string
     */
    protected function getLogPath($request = 'http')
    {
        $path = str_replace('resources/', RESOURCES, ltrim($this->config['file']['path'][$request], '/'));
        return str_replace('/', DS, $path);
    }

    /**
     * Get log html
     * 
     * @param string $request type
     * @param string $view    layout
     * 
     * @return void
     */
    protected function getLogHtml($request, $view)
    {   
        $VARS = array(
            'http' => '{{LOGS}}',
            'ajax' => '{{AJAX:LOGS}}',
            'cli' => '{{CONSOLE:LOGS}}',
        );
        $file = $this->getLogPath($request);
        if ( ! file_exists($file)) {
            return str_replace($VARS[$request], '', $view);
        }
        $data = file_get_contents($file);
        $patterns = array(
            '#\[([0-9\-:\s]+)\]#',  // date
            '#([\w]+\.[\w]+):#',    // channnel.level
            '#-->(.*)#',            // --> message & context data
        );
        $replace = array(
            '<p><span class="date">$1</span>',
            '<span class="info">$1</span>',
            ' --> $1</p>'
        );
        return str_replace($VARS[$request], preg_replace($patterns, $replace, $data), $view);
    }

    /**
     * Clear all log data from log folder
     *
     * Also removes queue data
     * 
     * @return void
     */
    public function clear()
    {
        $files = array(
            trim($this->c['config']['logger']['file']['path']['http'], '/'),
            trim($this->c['config']['logger']['file']['path']['ajax'], '/'),
            trim($this->c['config']['logger']['file']['path']['cli'], '/'),
        );
        foreach ($files as $file) {
            $file = ROOT. str_replace('/', DS, $file);
            $exp = explode(DS, $file);
            $filename = array_pop($exp);
            $path = implode(DS, $exp). DS;

            if (is_file($path.$filename)) {
                unlink($path.$filename);
            }
        }
        if ($this->c->has('queue')) {
            $this->c['queue']->deleteQueue($this->c['config']['logger']['queue']['route']); // Clear queue data
        }
    }

}

// END Manager class
/* End of file Manager.php */

/* Location: .Obullo/Http/Debugger/Manager.php */
