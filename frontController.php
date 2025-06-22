<?php

/*
 * Michael Zijlstra 11/14/2014
 */
/* ******************************
 * Configuration variables
 * **************************** */
require 'settings.php';
date_default_timezone_set(TIMEZONE);
$SEC_LVLS = ['none', 'login', 'observer', 'student', 'assistant', 'instructor', 'admin'];
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

/* ******************************
 * Initialize Globals
 * **************************** */
$__self = $_SERVER['PHP_SELF'];
$matches = [];
preg_match('|(.*)/frontController.php|', $__self, $matches);
$MY_BASE = $matches[1];

$the_uri = $_SERVER['REQUEST_URI'];
if (preg_match("|(.*)\?.*|", $the_uri, $matches)) {
    $the_uri = $matches[1]; // remove GET input params from URI
}
preg_match("|$MY_BASE(/.*)|", $the_uri, $matches);
$MY_URI = $matches[1];

$MY_METHOD = $_SERVER['REQUEST_METHOD'];
$MY_MAPPING = []; // populated when looking for a route
$URI_PARAMS = []; // populated with URI parameters on URI match in routing
$VIEW_DATA = []; // populated by controller, and used by view

/* *****************************
 * Include the (generated) application context
 * **************************** */
// Setup autoloading for control and model classes
// may be good to move these into the AnnotationContext, so that it
// automatically adds an additional spl_autoload function for each directory
// that it searches
spl_autoload_register(function ($class) {
    $file = 'control/'.$class.'.class.php';
    if (file_exists($file)) {
        include $file;
    }
});
spl_autoload_register(function ($class) {
    $file = 'model/'.$class.'.class.php';
    if (file_exists($file)) {
        include $file;
    }
});

// create the context
if (DEVELOPMENT) {
    require 'AnnotationReader.class.php';
    $ac = new AnnotationReader;
    $ac->scan()->create_context();
    $ac->write('context.php');  // uncomment to generate file
    eval($ac->context);
} else {
    require 'context.php';
}
$context = new Context;

// always start the session
session_start();

/* ******************************
 * Do Routing based on the routing arrays found in the context
 *
 * Once we have a route mapping it will check with security.php
 * to check the indicated authorization level for that route
 * **************************** */
require 'routing.php';
