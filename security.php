<?php

/*
 * Michael Zijlstra 11/14/2014
 */

// helper function to check if user is logged in
function isLoggedIn() {
    if (!isset($_SESSION['user'])) {
        global $MY_BASE;
        global $MY_URI;

        // the original url the user requested
        $_SESSION['login_to'] = $MY_URI;

        // Then show login page
        $_SESSION['error'] = "Please Login:";
        header("Location: ${MY_BASE}/login");
        exit();
    }
}

// helper function to check if logged in applicant / student is enrolled
function allowEnrolledAt($url_pattern) {
    global $MY_URI;

    $matches = array();
    // only check for course urls
    if (preg_match($url_pattern, $MY_URI, $match)) {
        $enrolled = false;
        foreach ($_SESSION['user']['enrolled'] as $off) {
            if ($off['number'] === $match[1] && $off['block'] === $match[2]) {
                $enrolled = true;
            }
        }
        if (!$enrolled) {
            require "view/error/403.php";
            exit();    
        }
    }
}

// apply the security policy
switch ($MY_MAPPING['sec']) {
    case "none":
        break;
    case "applicant":
        isLoggedIn();
        // has to be enrolled to see anything in an offering
        if ($_SESSION['user']['type'] === 'applicant') {
            allowEnrolledAt("!^/(cs\d{3})/(20\d{2}-\d{2})/.*!"); 
        }
        // has to be enrolled to see quiz or lab for an offering
        // if ($_SESSION['user']['type'] === 'student') {
        //    allowEnrolledAt("!^/(cs\d{3})/(20\d{2}-\d{2})/(quiz|lab)/.*!");
        // }
        break;
    case "student":
    case "instructor":        
        isLoggedIn();
        break;
    case "admin":
    default:
        isLoggedIn();
        if ($_SESSION['user']['type'] !== 'admin') {
            require "view/error/403.php";
            exit();
        }
}
