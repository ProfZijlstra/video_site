<?php

/*
 * Michael Zijlstra 11/14/2014
 */

// helper function to check if user is logged in
function isLoggedIn() {
    if (isset($_SESSION['user'])) {
        return;
    }
    if (isset($_COOKIE['ReMe'])) {
        $data = explode(":", $_COOKIE['ReMe']);
        $pos = strpos($_COOKIE['ReMe'], ":");
        $reme = substr($_COOKIE["ReMe"], $pos + 1);

        // check that the hash matches 
        if (password_verify($reme . SALT, $data[0])) {
            $_SESSION['user'] = array(
                "id" => $data[1],
                "first" => $data[2],
                "last" => $data[3],
                "email" => $data[4],
                "isAdmin" => $data[5],
                "isFaculty" => $data[6],
                "isRemembered" => true
            );    
        }

    }
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

function reAuthRemembered() {
    if ($_SESSION['user']['isRemembered']) {
        global $MY_BASE;
        global $MY_URI;

        // the original url the user requested
        $_SESSION['login_to'] = $MY_URI;

        // Then show login page
        $_SESSION['error'] = "Authorization Check";
        header("Location: {$MY_BASE}/login");
        exit();
    }
}

// helper function to check if a user has the minimum requested authentication
function hasMinAuth($req_auth) {
    global $SEC_LVLS;
    global $URI_PARAMS;
    global $context;

    if ($_SESSION['user']['isAdmin']) {
        return true;
    } 

    $course = $URI_PARAMS[1];
    if (!$course) {
        return false;
    }
    $block = $URI_PARAMS[2];
    if (!$block) {
        $block = "none";
    }
    $user_id = $_SESSION['user']['id'];

    if (!$_SESSION[$course]) {
        $_SESSION[$course] = [];
    }
    if (!$_SESSION[$course][$block]) {
        $enrollmentDao = $context->get("EnrollmentDao");
        $enroll = $enrollmentDao->checkEnrollmentAuth($user_id, $course, $block);
        $_SESSION[$course][$block] = $enroll;
        if (!$enroll) {
            include("view/course/notEnrolled.php");
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

function isAuthorized($lvl) {
    global $MY_MAPPING;
    if (!hasMinAuth($lvl)) {
        var_dump($MY_MAPPING);
        require "view/error/403.php";
        exit();
    }
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
        isLoggedIn();
        isAuthorized($MY_MAPPING['sec']);
        break;
    case "assistant":
    case "instructor": 
    case "admin":
    default:
        isLoggedIn();
        reAuthRemembered();
        isAuthorized($MY_MAPPING['sec']);
        break;
}
