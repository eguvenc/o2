<?php

namespace Obullo\Debugger;

use RuntimeException;
use Obullo\Container\ContainerInterface;

/**
 * Debugger Environment Tab Builder
 * 
 * @category  Debug
 * @package   Debugger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/debugger
 */
class EnvTab
{
    /**
     * Application output
     * 
     * @var string
     */
    protected $output;

    /**
     * Request
     * 
     * @var object
     */
    protected $request;

    /**
     * Constructor
     * 
     * @param object $c      container
     * @param string $output output
     */
    public function __construct(ContainerInterface $c, $output = null)
    {
        $this->request = $c['request'];
        $this->output = $output;
    }

    /**
     * Build super globals
     * 
     * @return array
     */
    protected static function buildSuperGlobals()
    {
        $ENVIRONMENTS = array();
        $ENVIRONMENTS['POST'] = $_POST;
        $ENVIRONMENTS['GET'] = $_GET;
        $ENVIRONMENTS['COOKIE'] = isset($_COOKIE) ? $_COOKIE : [];
        $ENVIRONMENTS['SESSION'] = isset($_SESSION) ? $_SESSION : [];
        $ENVIRONMENTS['SERVER'] = isset($_SERVER) ? $_SERVER : [];

        return $ENVIRONMENTS;
    }

    /**
     * Build environments
     * 
     * @return string
     */
    public function printHtml()
    {
        $ENVIRONMENTS = static::buildSuperGlobals();

        $ENVIRONMENTS['HTTP_REQUEST']  = $this->request->headers->all();
        $ENVIRONMENTS['HTTP_HEADERS']  = headers_list();
        $ENVIRONMENTS['HTTP_RESPONSE'] = [htmlentities($this->output)];

        $method = $this->request->method();

        $output = '';
        foreach ($ENVIRONMENTS as $key => $value) {
            $label = (strpos($key, 'HTTP_') === 0) ? $key : '$_'.$key;
            $output.= '<a href="javascript:void(0);" onclick="fireMiniTab(this)" data_target="'.strtolower($key).'" class="fireMiniTab">'.$label.'</a>'."\n";

            $style = static::getDefaultTab($method, $key);

            if ($key == 'HTTP_RESPONSE') {
                $style = 'style="display:block;"';
            }
            $output.= '<div id="'.strtolower($key).'"'.$style.'>'."\n";
            $output.= "<table>\n";
            $output.= "<tbody>\n";

            if (empty($value)) {
                $output.= "<tr>\n";
                $output.= "<th><pre>\"\"</pre></th>\n";
                $output.= "</tr>\n";
            } else {
                foreach ($value as $k => $v) {
                    $output.= "<tr>\n";
                    $output.= "<th><pre>$k</pre></th>\n";
                    $output.= "<td>\n";
                    if (is_array($v)) {
                        $output.= "<pre><span>".var_export($v, true)."</span></pre>\n";
                    } else {
                        $output.= "<pre><span>\"$v\"</span></pre>\n";
                    }
                    $output.= "</td>\n";
                    $output.= "</tr>\n";
                }
            }

            $output.= "</tbody>\n";
            $output.= "</table>\n";
            $output.= "</div>\n";
        }
        return $output;
    }

    /**
     * Get selected env tab style
     * 
     * @param string $method current http method
     * @param string $env    env key
     * 
     * @return string
     */
    protected static function getDefaultTab($method, $env)
    {
        if ($method == 'POST' && $env == 'POST') {
            $style = 'style="display:block;"';
        } elseif ($method == 'GET' && $env == 'GET') {
            $style = 'style="display:block;"';
        } else {
            $style = 'style="display:none;"';
        }
        return $style;
    }
}

// END EnvTab.php File
/* End of file EnvTab.php

/* Location: .Obullo/Debugger/EnvTab.php */