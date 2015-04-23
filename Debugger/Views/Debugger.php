<!DOCTYPE html>
<html>
<head>
<style type="text/css">
/* Reset CSS */

html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed, 
figure, figcaption, footer, header, hgroup, 
menu, nav, output, ruby, section, summary,
time, mark, audio, video
{
    margin: 0;
    padding: 0;
    border: 0;
    /*font-size: 100%;*/
    /*font: inherit;*/
    vertical-align: baseline;
}
/* HTML5 display-role reset for older browsers */
article, aside, details, figcaption, figure, 
footer, header, hgroup, menu, nav, section
{
    display: block;
}
body {
    line-height: 1;
}
ol, ul
{
    list-style: none;
}
blockquote, q
{
    quotes: none;
}
blockquote:before, blockquote:after,
q:before, q:after
{
    content: '';
    content: none;
}
table
{
    border-collapse: collapse;
    border-spacing: 0;
}

/* Reset CSS */

html,body {
    height:100%;
}
body
{
    font:11px 'Arial';
    background: #ddd;
    overflow-y:auto;
    overflow-x:hidden;
}
.clearfix
{
    clear: both;
}
.obulloDebugger-wrapper
{
    width:100%;
    background: #fff;
    display: block;
    height:100%;
    overflow-y:auto;
    overflow-x:hidden;
    position:relative;
}
.obulloDebugger-wrapper > .obulloDebugger-nav
{
    padding:0 5px;
    background: #eaeaea;
    border:1px solid #ccc;
    display: block;
    position: fixed;
    top:0;
    width:100%;
    z-index:99;
    border-width: 0 1px 1px 1px;
}
.obulloDebugger-wrapper > .obulloDebugger-nav > ul
{
    list-style-type:none;
    height:100%;
    display: block;
    height:22px;
}
.obulloDebugger-wrapper > .obulloDebugger-nav > ul:after
{
    content: '';
    display: block;
    clear: both;
}
.obulloDebugger-wrapper > .obulloDebugger-nav ul > li
{
    float: left;
    display: block;
}
.obulloDebugger-wrapper > .obulloDebugger-nav ul > li > a
{
    padding:0 12px;
    text-decoration:none;
    color:#5A5A5F;
    border-right:1px solid #ccc;
    display: block;
    height:22px;
    line-height:22px;
    transition:.2s;
    -webkit-transition:.2s;
    -moz-transition:.2s;
    outline:none;
}
.obulloDebugger-wrapper > .obulloDebugger-nav ul > li > a:hover
{
    background: #ddd;
}
.obulloDebugger-wrapper > .obulloDebugger-nav ul > li:nth-child(2) > a
{
    border-left:1px solid #ccc;
}
.obulloDebugger-wrapper > .obulloDebugger-nav > ul > li.obulloDebugger-activeTab > a
{
    color: #E53528;
    background: #ddd;
}
.obulloDebugger-wrapper > .obulloDebugger-nav > ul > li.closeBtn
{
    float: right;
    margin-right:7px;
}
.obulloDebugger-wrapper > .obulloDebugger-nav > ul > li.closeBtn > a
{
    border:none;
    font-weight:bold;
    font-size:13px;
    color:#B8A4A4;
}
.obulloDebugger-wrapper > .obulloDebugger-nav > ul > li.closeBtn > a:hover
{
    color:#AC8282;
}
.obulloDebugger-wrapper > .obulloDebugger-container
{
    padding:12px;
    margin:25px 5px 2px 5px;
    color:#A09D9D;  /* 5A5A5F */
    height:calc(100% - 51px);
    height:-webkit-calc(100% - 51px);
    height:-moz-calc(100% - 51px);
    height:-o-calc(100% - 51px);
    height:-ms-calc(100% - 51px);
    overflow-y:auto;
    overflow-x:hidden;
}
.obulloDebugger-wrapper > .obulloDebugger-nav > ul > li.favicon
{
    margin-right:10px;
}
.obulloDebugger-wrapper > .obulloDebugger-nav > ul > li.favicon img
{
    margin-top:3px;
    margin-left:3px;
    display: block;
}
.obulloDebugger-wrapper > .obulloDebugger-container  p
{
    padding: 1px 0;
    background:white;
    cursor:pointer;
    position: relative;
}
.obulloDebugger-wrapper > .obulloDebugger-container  p:hover
{
    background:rgb(234, 234, 234);
    color:#000;

}
.obulloDebugger-wrapper .obulloDebugger-container p:hover  span.date
{
    color:#000;
}
.obulloDebugger-wrapper .obulloDebugger-container  p  span.date
{
    /*font-weight:bold;*/
    padding-right:3px;
    border-right:1px solid #ccc;
    color: #e53528;
}
.obulloDebugger-wrapper .obulloDebugger-container  img.icon
{
    width:12px;
    height:12px;
    position: absolute;
    left:5px;
}
.obulloDebugger-wrapper > .obulloDebugger-container p span.info
{
    padding-left:3px;
}
.hiddenContainer
{
    display: none;
}
#obulloDebugger-ajax > p > span.date
{
    color:#0070FF;
}
.fireMiniTab
{
    color:#333;
    text-decoration:none;
    display: block;
    padding:8px;
    border-radius:2px;
    border:1px dotted #ddd;
    margin:5px;
}
#obulloDebugger-environment div
{
    display: none;
    margin:5px;
}
.activeMiniTab
{
    font-weight:bold;
    color:#333;
}
#obulloDebugger-environment div table
{
    width:100%;
    display: table;
    margin:0 auto;
}
#obulloDebugger-environment div table thead tr th
{
    background: rgba(236, 236, 236, 0.51);
    padding: 8px 4px;
    font-weight: bold;
    text-align:center;
}
#obulloDebugger-environment div table td,#obulloDebugger-environment div table th
{
    padding:6px 4px;
    background: rgba(236, 66, 66, 0.04);
    text-align:left;
}
#obulloDebugger-environment div table tr
{
    border:1px solid rgba(182, 182, 182, 0.18);
}
#obulloDebugger-environment > div > table > tbody > tr > th
{
    border-right: 2px solid #ddd;
    padding-left: 10px;
    background: #ECECEC;
    font-weight: normal;
    color: #000;
}
#obulloDebugger-environment > div > table > tbody > tr > td
{
    width: 85%;
    padding-left:10px;
}
.title { color: #5A5A5F; font-weight: bold; margin-top: 5px; margin-bottom: 3px; }
.error { color: red; }
</style>

<?php 
$getDebuggerURl = function ($method = 'console') {
    return $this->c['app']->uri->getBaseUrl(INDEX_PHP.'/debugger/'.$method.'?o_debugger=1');
};
?>
<script type="text/javascript">
/**
 * Obullo debbugger js.
 * 
 * @category  Debugger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2015 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/debugger
 */
var ajax = {
    post : function(url, closure, params){
        var xmlhttp;
        xmlhttp = new XMLHttpRequest(); // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                if( typeof closure === "function"){
                    closure(xmlhttp.responseText);
                }
            }
        }
        xmlhttp.open("POST",url,true);
        xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xmlhttp.send(params);
    }
}
function debuggerShowTab(elem,target) {
    var containers = document.getElementsByClassName('obulloDebugger-container');
        for (var i=0; i < containers.length;i+=1){
            containers[i].style.display = 'none';
        };

    var activeTabLinks = document.getElementsByClassName('obulloDebugger-activeTab');
        for (var i=0; i < activeTabLinks.length;i+=1){
            activeTabLinks[i].classList.remove("obulloDebugger-activeTab");
        };

    var targetContainer = document.getElementById(target);
        targetContainer.style.display = "block";
        elem.className = 'obulloDebugger-activeTab';

    <?php echo 'var cookieName = "o_debugger_active_tab";' ?>;

        setCookie(cookieName, target); // set active tab to cookie
};
function hideDebugger() {
    var obulloDebugger = document.getElementById('obulloDebugger');
    obulloDebugger.style.display = "none";
}
document.onkeydown = function(key){
    var press = key.keyCode;
    if (press == 120){
        var obulloDebugger = document.getElementById('obulloDebugger');
            obulloDebugger.style.display = (obulloDebugger.style.display == 'none') ? 'block' : 'none';
    };
};
function fireMiniTab(elem){
    var target  = elem.getAttribute('data_target');
    var element = document.getElementById(target);
    if(elem.classList.contains('activeMiniTab') == true) {
        elem.classList.remove('activeMiniTab')
        element.style.display = ''; 
    } else {
        elem.className =  elem.className + ' activeMiniTab';
        element.style.display = 'block'; 
    }
};
function clearConsole() {
    ajax.post(<?php echo "'".$getDebuggerURl('clear')."'" ?>, function(html){
            document.body.innerHTML = html;
        }
    );
}
 function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
}
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}
</script>
</head>
<body>
<div class="obulloDebugger-wrapper" id="obulloDebugger">
    <nav class="obulloDebugger-nav">
        <ul>
            <li class="favicon">
                <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/wD/AP+gvaeTAAAAcElEQVQ4y2NgGGjAiMx5aqrxnxhN0qdvMGIYQKxmdENY0CXWnLuJV2OIkToDAwMDw0Rmxv/5f/8zsmBTlP/3PyNOAxgYUFzKRGkgjhpABQMwojHESB0jqvABspLymnM34WkFI8FMZGYkaAi+hEYyAAAveB/cnCrFwQAAAABJRU5ErkJggg=="/>
            </li>
            <?php $activeTab = isset($_COOKIE['o_debugger_active_tab']) ? $_COOKIE['o_debugger_active_tab'] : 'obulloDebugger-http-log'; ?>

            <li <?php echo ($activeTab == 'obulloDebugger-http-log') ? 'class="obulloDebugger-activeTab"' : '' ?> onclick="debuggerShowTab(this,'obulloDebugger-http-log');">
                <a href="javascript:void(0);">Http Log</a>
            </li>
            <li <?php echo ($activeTab == 'obulloDebugger-ajax-log') ? 'class="obulloDebugger-activeTab"' : '' ?> onclick="debuggerShowTab(this,'obulloDebugger-ajax-log')">
                <a href="javascript:void(0);">Ajax Log</a>
            </li>
            <li <?php echo ($activeTab == 'obulloDebugger-console-log') ? 'class="obulloDebugger-activeTab"' : '' ?> onclick="debuggerShowTab(this,'obulloDebugger-console-log')">
                <a href="javascript:void(0);">Cli Log</a>
            </li>
            <li <?php echo ($activeTab == 'obulloDebugger-environment') ? 'class="obulloDebugger-activeTab"' : '' ?> onclick="debuggerShowTab(this,'obulloDebugger-environment')">
                <a href="javascript:void(0);">Environments</a>
            </li>
            <li>
                <a href="javascript:void(0);" onclick="clearConsole();">Clear</a>
            </li>
            <li class="closeBtn">
                <a href="/debugger/off">x</a>
            </li>
        </ul>
    </nav>
    <div class="obulloDebugger-container <?php echo ($activeTab != 'obulloDebugger-environment') ? 'hiddenContainer'  : '' ?>" id="obulloDebugger-environment">
        <?php echo $envHtml ?>
    </div>
    <div class="obulloDebugger-container <?php echo ($activeTab != 'obulloDebugger-console-log') ? 'hiddenContainer'  : '' ?>" id="obulloDebugger-console-log">{{CONSOLE:LOGS}}</div>
    <div class="obulloDebugger-container <?php echo ($activeTab != 'obulloDebugger-ajax-log') ? 'hiddenContainer'  : '' ?>" id="obulloDebugger-ajax-log">{{AJAX:LOGS}}</div>
    <div class="obulloDebugger-container <?php echo ($activeTab != 'obulloDebugger-http-log') ? 'hiddenContainer'  : '' ?>" id="obulloDebugger-http-log">{{LOGS}}</div>
</div>
</body>
</html>