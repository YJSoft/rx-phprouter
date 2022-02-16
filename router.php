<?php
if(php_sapi_name() !== 'cli-server') {
    die('Direct access not allowed');
}

require "router.utils.php";

$uri = $_SERVER["SCRIPT_NAME"];
$uri = ltrim($uri, '/');
$shouldHandle = FALSE;

// block request to router itself
if($uri == "router.php" || $uri == "router.utils.php") $uri = '/block/';

// except editor skin / style files
if(!preg_match('/modules\/editor\/(skins|styles)\//', $uri)) {
    // block request to html/xml
    if(preg_match('/^(addons|common\/tpl|files\/ruleset|(m\.)?layouts|modules|plugins|themes|widgets|widgetstyles)\/.+\.(html|xml)$/', $uri)) {
        $uri = '/block/';
    // block execution of attached script files
    } else if(preg_match('/^files\/(attach|config|cache\/store)\/.+\.(ph(p|t|ar)?[0-9]?|p?html?|cgi|pl|exe|[aj]spx?|inc|bak)$/', $uri)) {
        $uri = '/block/';
    // block access to env / member cache files
    } else if(preg_match('/^files\/(env|member_extra_info\/(new_message_flags|point))\//', $uri)) {
        $uri = '/block/';
    // block dotfile / etc file
    } else if(preg_match('/^(\.git|\.ht|\.travis|codeception\.|composer\.|Gruntfile\.js|package\.json|CONTRIBUTING|COPYRIGHT|LICENSE|README)/', $uri)) {
        $uri = '/block/';
    }
}

// if uri is not blocked
if($uri != '/block/') {
    // if file is not exists
    if(!file_exists($_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"])) {
        // handle /(mid)/~ case
        if(preg_match('/^(.+)\/(addons|files|layouts|m\.layouts|modules|widgets|widgetstyles)\/(.*)/', $uri, $m)) {
            $uri = $m[2] . "/" . $m[3];
            $shouldHandle = TRUE;
        }

        // handle .min.js or .min.css case
        if(preg_match('/^(.+)\.min\.(css|js)$/', $uri, $m)) {
            $uri = $m[1] . "." . $m[2];
            $shouldHandle = TRUE;
        }

        // if url should be handled by router
        if($shouldHandle) handlefile($_SERVER["DOCUMENT_ROOT"] . "/" . $uri);
        // all other nonexist file is handled by rhymix
        else require "index.php";
    } else {
        // all exist file is handled by php itself
        return false;
    }
} else {
    echo http_403();

    return true;
}
