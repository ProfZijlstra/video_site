<?php

/*
 * Michael Zijlstra 11/14/2014
 */

// helper function to checks if user is logged in
function isLoggedIn() {
    if (!isset($_SESSION['user'])) {
        global $MY_BASE;
        global $MY_URI;

        // If the user requests a specific project store the request URL
        // That way we can go there after logging in
        if (preg_match("|project/(\d+)|", $MY_URI)) {
            $_SESSION['login_to'] = $MY_URI;
        }

        // Then show login page
        $_SESSION['error'] = "Please Login:";
        header("Location: ${MY_BASE}/login");
        exit();
    }
}

// apply the security policy
switch ($MY_MAPPING['sec']) {
    case "none":
        break;
    case "user":
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
