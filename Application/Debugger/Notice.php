<?php

namespace Obullo\Application\Debugger;

/**
 * Debugger notice functions
 * 
 * @category  Log
 * @package   Debugger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/application
 */
class Notice
{
    /**
     * Turn off notice
     * 
     * @param string $output html body
     * 
     * @return string
     */
    public static function turnOff($output)
    {
        $closeDiv = '<div style="z-index:10000;position: fixed;top: 3px;left: 3px;background:#eaeaea;border:1px solid #ccc;height:23px;line-height:23px;font-size:12px;padding:0 6px;font-family: Arial;">
              <a href="/debugger/off" style="color: #E53528;text-decoration:none;">
                <button style="width:13px;height:13px;display:block;float:left;border:none;background: url(data:image/gif;base64,R0lGODlhEAAQAMQAAORHHOVSKudfOulrSOp3WOyDZu6QdvCchPGolfO0o/XBs/fNwfjZ0frl3/zy7////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAkAABAALAAAAAAQABAAAAVVICSOZGlCQAosJ6mu7fiyZeKqNKToQGDsM8hBADgUXoGAiqhSvp5QAnQKGIgUhwFUYLCVDFCrKUE1lBavAViFIDlTImbKC5Gm2hB0SlBCBMQiB0UjIQA7);background-size:100% 100%;margin-top:5px;margin-right:3px;"></button>
                <span style="float:left;">Turn Off Debugger</span><div style="clear:both;"></div>
              </a>
        </div>';
        return preg_replace('#<\s*\/\s*body\s*>#', "$closeDiv</body>", $output);
    }
}

// END Notice class
/* End of file Notice.php */

/* Location: .Obullo/Log/Debbuger/Notice.php */