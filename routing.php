<?php

/*
 * Michael Zijlstra 11/15/2014
 */

/**
 * Helper function to redirect to a GET or display an HTML page
 * 
 * @global array $VIEW_DATA any data that the view may need in order to render
 * @param string $view the name of the view file to include before exiting, 
 * or alternately for redirects a location header string
 */
function htmlView($view)
{
    global $VIEW_DATA;
    if (preg_match("/^Location: /", $view)) {
        if ($VIEW_DATA) {
            $_SESSION['redirect'] = $view;
            $_SESSION['flash_data'] = $VIEW_DATA;
        }
        header($view);
    } else {
        // if logged in also add user_id (used in header.php)
        if ($_SESSION['user']) {
            $VIEW_DATA['_user_id'] = $_SESSION['user']['id'];
        }

        // make keys in VIEW_DATA available as regular variables
        foreach ($VIEW_DATA as $key => $value) {
            // do htmlspecialchars? (breaks flowcharts)
            $$key = $value;
        }
        require "view/$view";
    }
}

/**
 * Helper function to check what kind of view should be displayed
 * 
 * @param type $data either string for HTML view or data for JSON
 */
function view($data)
{
    if (is_string($data)) {
        htmlView($data);
    } else if ($data || is_array($data)) {
        print json_encode($data);
    }
    // some web service calls don't generate a view / data
}

// check for redirect flash attributes
if ($MY_METHOD === "GET" && isset($_SESSION['redirect'])) {

    foreach ($_SESSION['flash_data'] as $key => $val) {
        $VIEW_DATA[$key] = $val;
    }

    unset($_SESSION['redirect']);
    unset($_SESSION['flash_data']);
}

// populate $_PUT for PUT requests
// ideas from https://stackoverflow.com/questions/6805570/
// implementation from Copilot
if ($MY_METHOD === "PUT") {
    $_PUT = array();
    parse_str(file_get_contents("php://input"), $_PUT);
    foreach ($_PUT as $key => $value) {
        $_PUT[$key] = str_replace(' ', "+", urldecode($value));;
    }
}


// find our mapping (first step for routing and security)
$uris = $mappings[$MY_METHOD];
foreach ($uris as $pattern => $mapping) {
    if (preg_match($pattern, $MY_URI, $URI_PARAMS)) {
        $MY_MAPPING = $mapping;
        break;
    }
}

// If there was no mapping send out a 404
if ($MY_MAPPING === []) {
    if (DEVELOPMENT) {
        print("Mapping not found");
    }
    require "view/error/404.php";
    exit();
}

/* ****************************** 
 * Check Authorization based on the security level in the mapping 
 * **************************** */
require 'security.php';


// do the actual routing process
list($class, $method) = explode("@",  $MY_MAPPING['route']);
try {
    $db = $context->get("DB");
    $controler = $context->get($class);
    $output = "error/500.php";
    try {
        $db->beginTransaction();
        $db->exec("SET time_zone = '" . TIMEZONE . "'");
        $output = $controler->{$method}();
        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        error_log($e); // log the whole trace
    }
    view($output);
} catch (Exception $e) {
    // Perhaps have some user setting for debug mode
    error_log($e->getMessage()); // log only the message
    require "view/error/500.php";
}

// always exit after displaying the view
exit();
