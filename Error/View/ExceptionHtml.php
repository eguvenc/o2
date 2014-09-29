
<?php 
use Obullo\Error\DebugOutput;
?>

<div id="obullo_debug">
<style type="text/css">
    #exceptionContent {font-family:Arial,Verdana,Sans-serif;font-size:12px;width:99%;padding:5px;background-color: #F0F0F0;}
    #exceptionContent  h1 {font-size:18px;color:#069586;margin:0;}
    #exceptionContent  h2 {font-size:13px;color:#333;margin:0;margin-top:3px;}
    #exceptionContent .errorFile { line-height: 2.0em;}
    #exceptionContent .errorLine { font-weight: bold;color:#069586;}
    #exceptionContent pre.source { margin: 0px 0 0px 0;padding: 0; background: none; border: none;line-height: none;}
    #exceptionContent div.collapsed { display: none; }
    #exceptionContent div.arguments { }
    #exceptionContent div.arguments table { font-family: Verdana, Arial, Sans-serif;font-size: 12px; border-collapse: collapse;border-spacing: 0; background: #fff;}
    #exceptionContent div.arguments table td { text-align: left; padding: 0 4px 0 4px; border: 1px solid #ccc; }
    #exceptionContent div.arguments table td .object_name { color: blue; }
    #exceptionContent pre.source span.line { display: block; }
    #exceptionContent pre.source span.highlight { background: #DBDBDB; }
    #exceptionContent pre.source span.line span.number { color: none; }
    #exceptionContent pre.source span.line span.number { color: none; } a{color:#069586;} body{color:#666;}
    code,kbd{ background:#EEE;border:1px solid #DDD;border:1px solid #DDD;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:0 4px;color:#666;font-size:12px;}
    pre{ color:#069586; font-weight: normal; background:#fff;border:1px solid #DDD;border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;padding:5px 10px;color:#666;font-size:12px;}
    pre code{ border:none;padding:0; }
</style>
<script type="text/javascript">
    function ExceptionElement() {
        var elements = new Array();
        for (var i = 0; i < arguments.length; i++) {
            var element = arguments[i];
            if (typeof element == 'string')
                element = document.getElementById(element);
            if (arguments.length == 1)
                return element;
            elements.push(element);
        }
        return elements;
    }
    function ExceptionToggle(obj) {
        var el = ExceptionElement(obj);
        if (el == null) {
            return false;
        }
        el.className = (el.className != 'collapsed' ? 'collapsed' : '');
    }
</script>

<div id="exceptionContent">
<?php if (isset($fatalError)) :  ?>
    <h1>Fatal Error</h1>
    <h2><?php echo str_replace(array(APP, DATA, CLASSES, ROOT, OBULLO, PUBLIC_DIR), array('APP' . DS, 'DATA' . DS, 'CLASSES' . DS, 'ROOT' . DS, 'OBULLO' . DS, 'PUBLIC' . DS), $e->getMessage()) ?></h2>
    <div class="errorFile errorLine"><?php echo str_replace(array(APP, DATA, CLASSES, ROOT, OBULLO, PUBLIC_DIR), array('APP' . DS, 'DATA' . DS, 'CLASSES' . DS, 'ROOT' . DS, 'OBULLO' . DS, 'PUBLIC' . DS), $e->getFile()) . '  Line : ' . $e->getLine() ?>
    </div>
</div>
<?php
exit; // Shutdown error exit.
else: echo '<h1>Exception Error</h1>';
endif;
?>

<h2><?php echo DebugOutput::getSecurePath($e->getMessage()); ?></h2>
<?php
if (isset($lastQuery) AND ! empty($lastQuery)) {
    echo '<div class="errorFile"><pre>' . $lastQuery . '</pre></div>';
}
?>
<div class="errorFile errorLine"><?php
 echo $e->getCode() . ' '.DebugOutput::getSecurePath($e->getFile()). '  Line : ' . $e->getLine() . ' ' ?></div>
<?php

    $eTrace = array();
    $eTrace['file'] = $e->getFile();
    $eTrace['line'] = $e->getLine();

    echo DebugOutput::debugFileSource($eTrace);

    $fullTraces  = $e->getTrace();
    $debugTraces = array();

    foreach ($fullTraces as $key => $val) {
        if (isset($val['file']) AND isset($val['line'])) {
            $debugTraces[] = $val;
        }
    }
    if (isset($debugTraces[0]['file']) AND isset($debugTraces[0]['line'])) {

        if ($debugTraces[0]['file'] == $e->getFile() AND $debugTraces[0]['line'] == $e->getLine()) {
            unset($debugTraces[0]);
            $unset = true;
        } else {
            $unset = false;
        }
        if (isset($debugTraces[1]['file']) AND isset($debugTraces[1]['line'])) {
            
            $html = '';
            foreach ($debugTraces as $key => $trace) {
                $prefix = uniqid() . '_';
                if (isset($trace['file'])) {
                    $html = '';
                    if (isset($trace['class']) AND isset($trace['function'])) {
                        $html.= $trace['class'] . '->' . $trace['function'];
                    }
                    if (!isset($trace['class']) AND isset($trace['function'])) {
                        $html.= $trace['function'];
                    }
                    if (isset($trace['args'])) {
                        if (count($trace['args']) > 0) {
                            $html.= '(<a href="javascript:void(0);" ';
                            $html.= 'onclick="ExceptionToggle(\'arg_toggle_' . $prefix . $key . '\');">';
                            $html.= 'arg';
                            $html.= '</a>)';
                            $html.= '<div id="arg_toggle_' . $prefix . $key . '" class="collapsed">';
                            $html.= '<div class="arguments">';

                            $html.= '<table>';
                            foreach ($trace['args'] as $arg_key => $arg_val) {
                                $html.= '<tr>';
                                $html.= '<td>' . $arg_key . '</td>';

                                if ($trace['function'] == 'pdoConnect' AND ($arg_key == 2 OR $arg_key == 1)) { // hide database password for security.
                                    $html.= '<td>***********</td>';
                                } else {
                                    $html.= '<td>' . DebugOutput::dumpArgument($arg_val) . '</td>';
                                }
                                
                                $html.= '</tr>';
                            }
                            $html.= '</table>';

                            $html.= '</div>';
                            $html.= '</div>';
                        } else {
                            $html.= (isset($trace['function'])) ? '()' : '';
                        }
                    } else {
                        $html.= (isset($trace['function'])) ? '()' : '';
                    }
                    echo '<div class="errorFile" style="line-height: 2em;">' . $html . '</div>';
                }
                if ($unset == false) {
                    ++$key;
                }
                ?>
                <div class="errorFile" style="line-height: 1.8em;">
                    <a href="javascript:void(0);" onclick="ExceptionToggle('error_toggle_' + '<?php echo $prefix . $key ?>');"><?php echo addslashes(DebugOutput::getSecurePath($trace['file']));
                echo ' ( ' ?><?php echo ' Line : ' . $trace['line'] . ' ) '; ?></a>
                </div>

                <?php
                echo DebugOutput::debugFileSource($trace, $key, $prefix);
                ?>

        <?php } // end foreach  ?>

    <?php }   // end if isset ?>     
<?php }   // end if isset ?>
</div>
</div>