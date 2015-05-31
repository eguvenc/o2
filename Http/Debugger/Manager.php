<?php

namespace Obullo\Http\Debugger;

use Obullo\Container\Container;

/**
 * Manager Class
 * 
 * @category  Debug
 * @package   Debugger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/debugger
 */
class Manager
{
    /**
     * Container
     * 
     * @var object
     */
    protected $c;

    /**
     * Debugger url
     * 
     * @var string
     */
    protected $debuggerUrl;

    /**
     * Web socket url
     * 
     * @var string
     */
    protected $websocketUrl;

    /**
     * Debug output
     * 
     * @var object
     */
    protected $debugOutput;

    /**
     * Constructor
     * 
     * @param object $c container
     */
    public function __construct(Container $c)
    {
        $this->c = $c;
        if (! $this->c['config']['http']['debugger']['enabled']) {
            $this->c['response']->status(300)->showError(
                'Debugger disabled from your application config file.', 
                'Debugger Disabled'
            );
        }
        // Disable logs sending by _debugger=1 params.
        $this->debuggerUrl  = $this->c['app']->uri->getBaseUrl(INDEX_PHP.'/debugger/console?o_debugger=1');
        $this->websocketUrl = $this->c['config']['http']['debugger']['socket'];
        $this->debugOutput  = new Output($this->c);
    }

    /**
     * Print debugger content
     * 
     * @return string
     */
    public function indexHtml()
    {
        return '<!DOCTYPE html>
        <html>
        <head>
        <meta charset="utf-8" />
        <script type="text/javascript">

            var ajax = {
                get : function(url, closure, params){
                    var xmlhttp;
                    if (window.XMLHttpRequest){
                        xmlhttp = new XMLHttpRequest(); // code for IE7+, Firefox, Chrome, Opera, Safari
                    }else{
                        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); // code for IE6, IE5
                    }
                    xmlhttp.onreadystatechange=function(){
                        if (xmlhttp.readyState==4 && xmlhttp.status==200){
                            if( typeof closure === "function"){
                                closure(xmlhttp.responseText);
                            }
                        }
                    }
                    xmlhttp.open("GET",url,true);
                    xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                    xmlhttp.send(params);
                }
            }
            function refreshDebugger(sock, env) {
                if (sock !=0 && refreshDebugger.socket == sock) {  // Just one time refresh the content for same socket id
                    return;
                }
                refreshDebugger.socket = sock;

                var f2 = document.getElementById("f2");
                f2 = f2.contentWindow.document;

                var f1 = document.getElementById("f1");
                f1 = f1.contentWindow.document;

                ajax.get("'.$this->debuggerUrl.'", function(html){
                        f2.body.innerHTML = html;
                        var ajaxDiv       = f2.getElementById("obulloDebugger-ajax-log");
                        var httpDiv       = f2.getElementById("obulloDebugger-http-log");
                        var consoleDiv    = f2.getElementById("obulloDebugger-console-log");
                        var wrapper       = f2.getElementById("obulloDebugger");
                        
                        ajaxDiv.scrollTop    = ajaxDiv.scrollHeight;
                        httpDiv.scrollTop    = httpDiv.scrollHeight;
                        consoleDiv.scrollTop = consoleDiv.scrollHeight;
                        wrapper.scrollTop    = wrapper.scrollHeight;

                        if (typeof env != "undefined") {
                            f2.getElementById("obulloDebugger-environment").innerHTML = decode64(env);
                        }
                    }
                );
            }
            function load(refresh){
                try
                {
                    var base64ActiveSrc = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAHCAYAAADnCQYGAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6Qzc1NDQwMzdFOThGMTFFNEI3RUY5QTE3OEY3NDI5QjMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6Qzc1NDQwMzhFOThGMTFFNEI3RUY5QTE3OEY3NDI5QjMiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpDNzU0NDAzNUU5OEYxMUU0QjdFRjlBMTc4Rjc0MjlCMyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpDNzU0NDAzNkU5OEYxMUU0QjdFRjlBMTc4Rjc0MjlCMyIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PvXgJSIAAACwSURBVHjaYvz//z8DPvDMTPM1kOIH4i4gLgPij1Knrovi08PCQBgIAjEzEKcCMSuUjxewAF0SC6TlofzvQCwBcg0SmwnNAUxAPd1A+gUQc0J9AWODwEOQwiwgtiDCxU+BWAiIGYG4BI+6EyBDFwPxMSSXgsLrCxI7FWqQNFQNKBJmA/FrqOt4kNggcJeRiIj6Aw3TV0AsBsR/gRGFNy6YiPD2eyD+DXXdbygfLwAIMADjmCy+s+pJrgAAAABJRU5ErkJggg==";
                    var base64DeactiveSrc = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAHCAYAAADnCQYGAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MTlBQzFFMTNFOThGMTFFNEI0NEU5NDA3Qzc3OUI4REYiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MTlBQzFFMTRFOThGMTFFNEI0NEU5NDA3Qzc3OUI4REYiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxOUFDMUUxMUU5OEYxMUU0QjQ0RTk0MDdDNzc5QjhERiIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoxOUFDMUUxMkU5OEYxMUU0QjQ0RTk0MDdDNzc5QjhERiIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pn2CUacAAAC+SURBVHjaYvz//z8DLhATE/MaSPEDcciSJUs2Afl+QPYaIP4I5Ivi0sfEgB8IAjErEKdB+WlQviA+TSxA22OBtDwQfwdiCZArkNgwS9nRaCagvm4g/QKIOaG+gbEfsgCJLCC2IODim0i0CxAzAnEJDrUnQIYuBuJjUNeBwukLEjsVaoA6VAOMBkXEbCB+DXUdDxL7LgswwKfhiahkIMUMxD+hQjD6H1BfOrkR9R6IfwPxLCh/FpT/Hp8mgAADAAPLMt1P18UbAAAAAElFTkSuQmCC";

                    var f2 = document.getElementById("f2");
                    f2 = f2.contentWindow.document;
                    var connectedImg = f2.getElementById("obulloDebuggerSocket");
                    connectedImg.src = base64DeactiveSrc;

                    var wsUri = "'.$this->websocketUrl.'";           // Create webSocket connection
                    var websocket =  new WebSocket(wsUri);

                    websocket.onopen = function(data) {        // Connection is open 
                        console.log("Debugger websocket connection established.");
                        connectedImg.src = base64ActiveSrc;
                    }
                    websocket.onmessage = function(response) { // Received messages from server
                        var msg = JSON.parse(response.data);   // Php sends Json data

                        if (msg.type == "system") {
                            if (msg.message == "HTTP_REQUEST") {
                                refreshDebugger(msg.socket, msg.env);
                            } else if (msg.message == "AJAX_REQUEST") {
                                refreshDebugger(msg.socket, msg.env);
                            } else if (msg.message == "CLI_REQUEST") {
                                refreshDebugger(msg.socket, msg.env);
                            }
                        }
                    };
                    websocket.onclose = function(data) {        // Connection is closed connect again ?
                        console.log("Debugger websocket connection closed.");
                        connectedImg.src = base64DeactiveSrc;
                    }
                    frame1.window.onbeforeunload = function() {
                        // websocket.close();  // Don not close the websocket connection we get disconnect errors on firefox browser.
                    };

                }
                catch(ex)
                { 
                    console.log("Debugger exception error:" + ex.message + " at line " + ex.lineNumber);
                }
            }

            var keyStr = "ABCDEFGHIJKLMNOP" +
                           "QRSTUVWXYZabcdef" +
                           "ghijklmnopqrstuv" +
                           "wxyz0123456789+/" +
                           "=";

            function decode64(input) {
                 var output = "";
                 var chr1, chr2, chr3 = "";
                 var enc1, enc2, enc3, enc4 = "";
                 var i = 0;

                 // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
                 var base64test = /[^A-Za-z0-9\+\/\=]/g;
                 if (base64test.exec(input)) {
                    alert("There were invalid base64 characters in the input text.\n" +
                          "Valid base64 characters are A-Z, a-z, 0-9, \'+\', \'/\',and \'=\'\n" +
                          "Expect errors in decoding.");
                 }
                 input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

                 do {
                    enc1 = keyStr.indexOf(input.charAt(i++));
                    enc2 = keyStr.indexOf(input.charAt(i++));
                    enc3 = keyStr.indexOf(input.charAt(i++));
                    enc4 = keyStr.indexOf(input.charAt(i++));

                    chr1 = (enc1 << 2) | (enc2 >> 4);
                    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                    chr3 = ((enc3 & 3) << 6) | enc4;

                    output = output + String.fromCharCode(chr1);

                    if (enc3 != 64) {
                       output = output + String.fromCharCode(chr2);
                    }
                    if (enc4 != 64) {
                       output = output + String.fromCharCode(chr3);
                    }

                    chr1 = chr2 = chr3 = "";
                    enc1 = enc2 = enc3 = enc4 = "";

                 } while (i < input.length);

                 return unescape(output);
              }
        </script>
        </head>

        <frameset rows="60%,40%" frameborder="0">
             <frame id ="f1" name="frame1" onload="load();" src="'.$this->c['app']->uri->getBaseUrl(INDEX_PHP.'?o_debugger=1').'">
             <frame id ="f2" src="'.$this->debuggerUrl.'">
        </frameset>
        </html>';
    }

    /**
     * Print console html ( bottom frame )
     * 
     * @return string
     */
    public function printConsole()
    {
        return $this->debugOutput->printConsole();
    }

    /**
     * Close debug window
     * 
     * @return void
     */
    public function off()
    {   
        $redirectUrl = preg_replace('#\/debugger\/off$#', '', $this->c['uri']->getRequestUri());
        $redirectUrl = '/'.trim($redirectUrl, '/');
        echo '<html>';
        echo '<head>';
        echo '<script type="text/javascript">';
        echo 'window.top.location.href = "'.$redirectUrl.'";';
        echo '</script>';
        echo '</head>';
        echo '<body></body>';
        echo '</html>';
    }

    /**
     * Clear log data
     * 
     * @return void
     */
    public function clear()
    {
        $this->debugOutput->clear(); 
    }

}

// END Manager class
/* End of file Manager.php */

/* Location: .Obullo/Http/Debbuger/Manager.php */