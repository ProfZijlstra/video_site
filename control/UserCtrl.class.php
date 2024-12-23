<?php

/**
 * User Controller Class
 *
 * @author mzijlstra 11/14/2014
 */

#[Controller]
class UserCtrl
{

    #[Inject('UserDao')]
    public $userDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    /**
     * Simple mapping to the login page
     */
    #[Get(uri: "^/.*login$", sec: "none")]
    public function getLogin()
    {
        global $VIEW_DATA;
        if ($_COOKIE['logout']) {
            $VIEW_DATA['error'] = $_COOKIE['logout'];
        }
        return "login.php";
    }

    #[Get(uri: "^/.*hasSession$", sec: "none")]
    public function hasSession()
    {
        $result = ["session" => true];
        if (!array_key_exists("user", $_SESSION)) {
            $result["session"] = false;
        }
        return $result;
    }

    #[Get(uri: "^/.*reAuth$", sec: "none")]
    public function reAuth()
    {
        global $MY_BASE;
        global $VIEW_DATA;
        $matches = [];
        preg_match("!.*{$MY_BASE}(.*)!", $_SERVER['HTTP_REFERER'], $matches);
        $_SESSION['login_to'] = $matches[1];
        $VIEW_DATA['error'] = "Please Authenticate";
        return "Location: login";
    }

    /**
     * Attempts to login to the application
     * @global type $MY_BASE base URI of our application
     * @return string appropriate redirect for success or failure
     * 
     */
    #[Post(uri: "^/.*login$", sec: "none")]
    public function login()
    {
        global $MY_BASE;
        global $VIEW_DATA;

        $email = filter_input(INPUT_POST, "email");
        $pass = filter_input(INPUT_POST, "pass");

        // check if this is a valid login
        $row = $this->userDao->checkLogin($email);

        if ($row && password_verify($pass, $row['password'])) {
            // prevent session fixation
            session_regenerate_id();

            // set current user details
            $_SESSION['user'] = array(
                "id" => $row['id'],
                "first" => $row['firstname'],
                "last" => $row['lastname'],
                "email" => $row['email'],
                "isAdmin" => $row['isAdmin'],
                "isFaculty" => $row['isFaculty'],
                "isRemembered" => false
            );

            // create a remember me cookie
            $reme = $row["id"] . ":" .
                $row['firstname'] . ":" .
                $row['lastname'] . ":" .
                $row['email'] . ":" .
                $row['isAdmin'] . ":" .
                $row['isFaculty'];
            $reme = password_hash($reme . SALT, PASSWORD_DEFAULT) . ":" . $reme;
            setcookie("ReMe", $reme, time() + 7 * 24 * 60 * 60, "/videos");

            // update the last accessed time
            $this->userDao->updateAccessed($row['id']);

            $redirect = "Location: $MY_BASE";
            if (isset($_SESSION['login_to'])) {
                // redirect to original requested URL
                $redirect .= $_SESSION['login_to'];
                unset($_SESSION['login_to']);
            }
            return $redirect;
        } else {
            $VIEW_DATA['error'] = "Error: try again";
            return "Location: login";
        }
    }

    /**
     * Logs someone out of the application
     * @return string redirect back to login page
     */
    #[Get(uri: "^/.*logout$", sec: "none")]
    public function logout()
    {
        session_destroy();
        setcookie("ReMe", "", time() - 10, "/videos");
        setcookie("logout", "Logged Out", time() + 3, "/videos");
        return "Location: login";
    }

    /**
     * Shows all the users
     * @global array $VIEW_DATA empty array that we populate with view data
     * @return string name of view file
     */
    #[Get(uri: "^/user$", sec: "admin")]
    public function all()
    {
        global $VIEW_DATA;
        $VIEW_DATA['users'] = $this->userDao->all();
        $VIEW_DATA['title'] = "Users";
        return "user/users.php";
    }

    /**
     * Show the create user page
     */
    #[Get(uri: "^/user/add$", sec: "admin")]
    public function addUser()
    {
        global $VIEW_DATA;
        $VIEW_DATA["title"] = "Add User";
        return "user/userAdd.php";
    }

    /**
     * Get faculty memebers
     */
    #[Get(uri: "^/user/faculty$", sec: "admin")]
    public function getFaculty()
    {
        return $this->userDao->faculty();
    }

    /**
     * Shows details for a user
     * @global array $URI_PARAMS as provided by framework based on request URI
     * @global array $VIEW_DATA empty array that we populate with view data
     * @return string name of view file
     */
    #[Get(uri: "^/user/(\d+)$", sec: "admin")]
    public function details()
    {
        global $VIEW_DATA;
        global $URI_PARAMS;

        $uid = $URI_PARAMS[1];
        $error = filter_input(INPUT_GET, "error", FILTER_UNSAFE_RAW);
        $user = $this->userDao->retrieve($uid);

        $VIEW_DATA['msg'] = $error;
        $VIEW_DATA['user'] = $user;
        $VIEW_DATA["title"] = "User Details";

        return "user/userDetails.php";
    }

    #[Get(uri: "^/user/(\D.*)$", sec: "admin")]
    public function teamsName()
    {
        global $URI_PARAMS;

        $teamsName = urldecode($URI_PARAMS[1]);
        $uid = $this->userDao->byTeamsName($teamsName);
        if (!$uid) {
            $uid = [$teamsName, "not found"];
        }
        return "Location: $uid";
    }

    /**
     * Creates a user
     * @return strng redirect URI
     * @throws PDOException
     * 
     */
    #[Post(uri: "^/user$", sec: "admin")]
    public function create()
    {
        global $VIEW_DATA;

        $first = filter_input(INPUT_POST, "first", FILTER_UNSAFE_RAW);
        $last = filter_input(INPUT_POST, "last", FILTER_UNSAFE_RAW);
        $knownAs = filter_input(INPUT_POST, "knownAs", FILTER_UNSAFE_RAW);
        $email = filter_input(INPUT_POST, "email", FILTER_UNSAFE_RAW);
        $studentID = filter_input(INPUT_POST, "studentID", FILTER_SANITIZE_NUMBER_INT);
        $teamsName = filter_input(INPUT_POST, "teamsName", FILTER_UNSAFE_RAW);
        $pass = filter_input(INPUT_POST, "pass");
        $isAdmin = filter_input(INPUT_POST, "isAdmin", FILTER_SANITIZE_NUMBER_INT);
        $isFaculty = filter_input(INPUT_POST, "isFaculty", FILTER_SANITIZE_NUMBER_INT);
        $active = filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_INT);

        $error = [];
        if (!$first) {
            $error[] = "first name";
        }
        if (!$last) {
            $error[] = "last name";
        }
        if (!$email) {
            $error[] = "email address";
        }
        if ($studentID == "" || !is_numeric($studentID)) {
            $studentID = 0;
        }
        if (!$pass) {
            $error[] = "password";
        }
        if ($error) {
            $VIEW_DATA["msg"] = "Missing: " . json_encode($error);
            return "Location: user/add";
        }
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        try {
            $this->userDao->insert(
                $first,
                $last,
                $knownAs,
                $email,
                $studentID,
                $teamsName,
                $hash,
                $active,
                $isAdmin,
                $isFaculty
            );
        } catch (Exception) {
            $error = true;
        }

        if ($error) {
            $VIEW_DATA["msg"] = "DB Error: email address already in db?";
            return "Location: user/add";
        } else {
            return "Location: user";
        }
    }

    /**
     * Expects AJAX
     * 
     * Updates a user 
     * @global array $URI_PARAMS as provided by framework based on request URI
     * @return string redirect URI
     * 
     */
    #[Post(uri: "^/user/(\d+)$", sec: "admin")]
    public function update()
    {
        global $URI_PARAMS;

        $uid = $URI_PARAMS[1];
        $first = filter_input(INPUT_POST, "first", FILTER_UNSAFE_RAW);
        $last = filter_input(INPUT_POST, "last", FILTER_UNSAFE_RAW);
        $knownAs = filter_input(INPUT_POST, "knownAs", FILTER_UNSAFE_RAW);
        $email = filter_input(INPUT_POST, "email", FILTER_UNSAFE_RAW);
        $studentID = filter_input(INPUT_POST, "studentID", FILTER_SANITIZE_NUMBER_INT);
        $teamsName = filter_input(INPUT_POST, "teamsName", FILTER_UNSAFE_RAW);
        $active = filter_input(INPUT_POST, "active", FILTER_SANITIZE_NUMBER_INT);
        $isAdmin = filter_input(INPUT_POST, "isAdmin", FILTER_SANITIZE_NUMBER_INT);
        $isFaculty = filter_input(INPUT_POST, "isFaculty", FILTER_SANITIZE_NUMBER_INT);

        $error = "";
        if (!$first) {
            $error .= "first ";
        }
        if (!$last) {
            $error .= "last ";
        }
        if (!$email) {
            $error .= "email ";
        }
        if ($error) {
            return ["msg" => "Missing: " . json_encode($error)];
        }

        // if given an empty password it does not update password
        $this->userDao->update(
            $uid,
            $first,
            $last,
            $knownAs,
            $email,
            $studentID,
            $teamsName,
            $active,
            $isAdmin,
            $isFaculty
        );
    }

    /**
     * Expects AJAX
     * 
     * Updates Password
     * 
     */
    #[Post(uri: "^/user/(\d+)/pass$", sec: "admin")]
    public function updatePass()
    {
        global $URI_PARAMS;

        $uid = $URI_PARAMS[1];
        $pass = filter_input(INPUT_POST, "pass");
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $this->userDao->updatePass($uid, $hash);
    }


    #[Post(uri: "^/user/registerBadge$", sec: "admin")]
    public function updateAttendance()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $this->userDao->setBadge($data["studentID"], $data["badge"]);
    }

    /* This is not a good location for this function
     * TODO: put this in a better location
     */
    #[Get(uri: "^/remap$", sec: "admin")]
    public function remap()
    {
        require 'AnnotationReader.class.php';
        $ac = new AnnotationReader();
        $ac->scan()->create_context();
        $ac->write("context.php");
        return "Location: ../videos/";
    }
}
