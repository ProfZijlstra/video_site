<?php
/*
 * Michael Zijlstra 11/14/2014
 */
/* ******************************
 * Configuration variables
 * **************************** */
define("DEVELOPMENT", true);
define("DSN", "mysql:dbname=cs472;host=localhost");
define("DB_USER", "cs472dbuser");
define("DB_PASS", "WAP Passwd");
$SEC_LVLS = array("none", "user", "admin");
date_default_timezone_set("America/Chicago");
error_reporting(E_ALL ^ E_WARNING);

/* ******************************
 * Initialize Globals
 * **************************** */
$__self = $_SERVER["PHP_SELF"];
$matches = array();
preg_match("|(.*)/frontController.php|", $__self, $matches);               
$MY_BASE = $matches[1];

$the_uri = $_SERVER["REQUEST_URI"];
if (preg_match("|(.*)\?.*|", $the_uri, $matches)) {
    $the_uri = $matches[1]; // remove GET input params from URI   
}   
preg_match("|$MY_BASE(/.*)|", $the_uri, $matches);
$MY_URI = $matches[1];

$MY_METHOD = $_SERVER["REQUEST_METHOD"];
$MY_MAPPING = array(); // populated by the code below
$URI_PARAMS = array(); // populated with URI parameters on URI match in security
$VIEW_DATA = array(); // populated by controller, and used by view

/* *****************************
 * Include the (generated) application context
 * **************************** */
// Setup autoloading for control and model classes
// TODO move these into the AnnotationContext, so that it automatically adds
// an additional spl_autoload function for each directory it searches
spl_autoload_register(function ($class) {
    $file = 'control/' . $class . '.class.php';
    if (file_exists($file)) {
        include $file;
    }
});
spl_autoload_register(function ($class) {
    $file =  'model/' . $class . '.class.php';
    if (file_exists($file)) {
        include $file;
    }
});

if (DEVELOPMENT) {
    require 'AnnotationReader.class.php';
    $ac = new AnnotationReader();
    $ac->scan()->create_context();
    $ac->write("context.php");  # uncomment to generate file
    eval($ac->context);
} else {
    require 'context.php';
}

// find our mapping (first step for security and routing)
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

// always start the session context
session_start();

/* ****************************** 
 * Check Security based on context security array
 * **************************** */
require 'security.php';

/* ****************************** 
 * Do Routing based on context routing arrays
 * **************************** */
require 'routing.php';