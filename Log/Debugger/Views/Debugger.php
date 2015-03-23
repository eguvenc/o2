<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="@ASSETS/css/reset.css" />
<link rel="stylesheet" type="text/css" href="@ASSETS/css/obullo-debugger.css" />
<script type="text/javascript" src="@ASSETS/js/obullo-debugger.js"></script>
</head>

<body>
<div class="obulloDebugger-wrapper" id="obulloDebugger">
    <nav class="obulloDebugger-nav">
        <ul>
            <li class="favicon">
                <img src="@ASSETS/images/favicon.ico" alt="">
            </li>
            <li class="obulloDebugger-activeTab" onclick="debuggerShowTab(this,'obulloDebugger-logs');">
                <a href="#">Logs</a>
            </li>
            <!--<li data-targeT="obulloDebugger-layers" onclick="debuggerShowTab(this,'obulloDebugger-layers')">
                <a href="#">Layers</a>
            </li>-->
            <li onclick="debuggerShowTab(this,'obulloDebugger-environment')">
                <a href="#">Environment</a>
            </li>
            <!--<li onclick="debuggerShowTab(this,'obulloDebugger-ajax')">
                <a href="#">Ajax</a>
            </li>-->
            <li class="closeBtn" onclick="hideDebugger();">
                <a href="#">x</a>
            </li>
        </ul>
    </nav>

    <div class="obulloDebugger-container" id="obulloDebugger-logs">{{LOGS}}</div>

    <div class="obulloDebugger-container hiddenContainer" id="obulloDebugger-environment">
        <?php
            $ENVIRONMENTS = ['SERVER' => isset($_SERVER) ? $_SERVER : [], 
                    'SESSION' => isset($_SESSION) ? $_SESSION : [], 
                    'COOKIE' => isset($_COOKIE) ? $_COOKIE : [], 
                    'HTTP_REQUEST' => isset($_HTTP_REQUEST) ? $_HTTP_REQUEST : [], 
                    'HTTP_RESPONSE' => isset($_HTTP_RESPONSE) ? $_HTTP_RESPONSE : []
            ];
        ?>

        <?php foreach($ENVIRONMENTS as $key => $value):?>
        <a href="#" onclick="fireMiniTab(this)" data_target="<?php echo strtolower($key)?>" class="fireMiniTab">$_<?php echo $key ?></a>
        <div id="<?php echo strtolower($key) ?>">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">$_<?php echo $key ?> Result Table</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($value as $k => $v):?>
                <tr>
                    <th><?php echo $k ?></th>
                    <td>
                        <pre><span>"<?php echo $v?>"</span></pre>
                    </td>
                </tr>
                <?php endforeach ?>
            </tbody>
        </table>
        </div>
        <?php endforeach ?>
    </div>
</div>

</body>
</html>