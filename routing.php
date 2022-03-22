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
function htmlView($view) {
    global $VIEW_DATA;
    if (preg_match("/^Location: /", $view)) {
        if ($VIEW_DATA) {
            $_SESSION['redirect'] = $view;
            $_SESSION['flash_data'] = $VIEW_DATA;
        }
        header($view);
    } else {
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

function view($data) {
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

// start the routing process
list($class, $method) = explode("@",  $MY_MAPPING['route']);
try {
    $context = new Context();
    $controler = $context->get($class);
    view($controler->{$method}());
} catch (Exception $e) {
    // Perhaps have some user setting for debug mode
    error_log($e->getMessage());
    require "view/error/500.php";
}

// always exit after displaying the view
exit();
