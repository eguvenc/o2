<?php

use Obullo\Error\DebugOutput;

if (isset($fatalError)) {
    echo "\33[1;36mFatal Error\33[0m\n";
    // We could not load error libraries when error is fatal.
    echo "\33[0;36m".str_replace(array(APP, DATA, CLASSES, ROOT, OBULLO, PUBLIC_FOLDER), array('APP' . DS, 'DATA' . DS, 'CLASSES' . DS, 'ROOT' . DS, 'OBULLO' . DS, 'PUBLIC' . DS), $e->getMessage())."\n";
    echo str_replace(array(APP, DATA, CLASSES, ROOT, OBULLO, PUBLIC_FOLDER), array('APP' . DS, 'DATA' . DS, 'CLASSES' . DS, 'ROOT' . DS, 'OBULLO' . DS, 'PUBLIC' . DS), $e->getFile()) . ' Line : ' . $e->getLine()."\33[0m\n";
    exit;
}
echo "\33[1;36mException Error\n". DebugOutput::getSecurePath($e->getMessage())."\n";

if (isset($lastQuery) AND ! empty($lastQuery)) {
    echo 'SQL: ' . $lastQuery . "\n";
}
echo $e->getCode().' '.DebugOutput::getSecurePath($e->getFile()). ' Line : ' . $e->getLine() . "\n";

$eTrace = array();
$eTrace['file'] = $e->getFile();
$eTrace['line'] = $e->getLine();

echo "\33[0;36m".strip_tags(DebugOutput::debugFileSource($eTrace))."\33[0m";
echo "\33[1;36mDetails: \33[0m\n\33[0;36m";
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
        $output = '';
        $i = 0;
        foreach ($debugTraces as $key => $trace) {
            ++$i;
            if (isset($trace['file']) AND $i == 1) { // Just show the head class path
                $output = '';
                if (isset($trace['class']) AND isset($trace['function'])) {
                    $output.= $trace['class'] . '->' . $trace['function'];
                }
                if ( ! isset($trace['class']) AND isset($trace['function'])) {
                    $output.= $trace['function'];
                }
                $output.= (isset($trace['function'])) ? '()' : '';
                echo $output;
            }
            if ($unset == false) {
                ++$key;
            }
            if ($i == 1)  // Just show the head file
            echo "\n".DebugOutput::getSecurePath($trace['file']).' Line : ' . $trace['line'] . "\n";

        } // end foreach 

    }   // end if isset debug traces
    
}   // end if isset 

echo "\33[0m";