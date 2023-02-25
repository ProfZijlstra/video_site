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
        header("Location: {$MY_BASE}/login");
        exit();
    }
}

// helper function to check if a user has the minimum requested authentication
function hasMinAuth($req_auth) {
    global $MY_BASE;
    global $SEC_LVLS;
    global $URI_PARAMS;
    global $context;

    if ($_SESSION['user']['isAdmin']) {
        return true;
    } 

    if (count($URI_PARAMS) < 3) {
        return false;
    }

    $course = $URI_PARAMS[1];
    $block = $URI_PARAMS[2];
    $user_id = $_SESSION['user']['id'];
    if (!preg_match('/[a-z]{2,3}\d{3,4}/', $course) || 
        !preg_match('!20\d{2}-\d{2}[^/]*!', $block)) {
        return false;
    }

    if (!$_SESSION[$course]) {
        $_SESSION[$course] = [];
    }
    if (!$_SESSION[$course][$block]) {
        $enrollmentDao = $context->get("EnrollmentDao");
        $enroll = $enrollmentDao->checkEnrollmentAuth($user_id, $course, $block);
        $_SESSION[$course][$block] = $enroll;
        if (!$enroll) {
            include("view/notEnrolled.php");
            return false;
        }
    }

    $has_auth  = $_SESSION[$course][$block]['auth'];
    for ($i = 0; $i < count($SEC_LVLS); $i++) {
        $lvl = $SEC_LVLS[$i];
        if ($req_auth == $lvl) {
            $req_auth_lvl = $i;
        }
        if ($has_auth == $lvl) {
            $has_auth_lvl = $i;
        }
    }
    if ($req_auth_lvl > $has_auth_lvl) {
        return false;
    }

    return true;
}

// apply the security policy
switch ($MY_MAPPING['sec']) {
    case "none":
        break;
    case "login":
        isLoggedIn();
        break;
    case "observer":
    case "student":
    case "assistant":
    case "instructor":        
        isLoggedIn();
        if (!hasMinAuth($MY_MAPPING['sec'])) {
            require "view/error/403.php";
            exit();
        }
        break;
    case "admin":
    default:
        isLoggedIn();
        if (!$_SESSION['user']['isAdmin']) {
            require "view/error/403.php";
            exit();
        }
}
