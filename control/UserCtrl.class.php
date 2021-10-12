<?php

/**
 * User Controller Class
 *
 * @author mzijlstra 11/14/2014
 * 
 * @Controller
 */
class UserCtrl {

    /**
     *
     * @var type 
     * @Inject("UserDao")
     */
    public $userDao;

    /**
     * Simple mapping to the login page
     * @GET(uri="|^/.*login$|", sec="none")
     */
    public function getLogin() {
        return "login.php";
    }

    /**
     * Simple mapping to get the add user page
     * @GET(uri="|^/user/add$|", sec="admin")
     */
    public function getAddUser() {
        return "userDetails.php";
    }

    /**
     * Attempts to login to the application
     * @global type $MY_BASE base URI of our application
     * @return string appropriate redirect for success or failure
     * 
	 * @POST(uri="|^/.*login$|", sec="none")
     */
    public function login() {
        global $MY_BASE;
        // start session, and clean any login errors 
        unset($_SESSION['error']);

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
                "type" => $row['type'],
                "autoplay" => "off"
            );

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
            $_SESSION['error'] = "Error: try again";
            return "Location: login";
        }
    }

    /**
     * Logs someone out of the application
     * @return string redirect back to login page
     * 
     * @GET(uri="|^/.*logout$|", sec="none")
     */
    public function logout() {
        session_destroy();
        $_SESSION['error'] = "Logged Out";
        return "Location: login";
    }

    /**
     * Shows all the users
     * @global array $VIEW_DATA empty array that we populate with view data
     * @return string name of view file
     * 
     * @GET(uri="|^/user$|", sec="admin")
     */
    public function all() {
        global $VIEW_DATA;
        $VIEW_DATA['users'] = $this->userDao->all();
        $VIEW_DATA['title'] = "Users";
        return "users.php";
    }

    /**
     * Shows details for a user
     * @global array $URI_PARAMS as provided by framework based on request URI
     * @global array $VIEW_DATA empty array that we populate with view data
     * @return string name of view file
     * 
     * @GET(uri="|^/user/(\d+)$|", sec="admin")
     */
    public function details() {
        global $VIEW_DATA;
        global $URI_PARAMS;
        $uid = $URI_PARAMS[1];

        $user = $this->userDao->retrieve($uid);
        $VIEW_DATA['user'] = $user;
        
        return "userDetails.php";
    }

    /**
     * Show the create user page
     * 
     * @GET(uri="|^/user/add$|", sec="admin")
     */
    public function addUser() {
        return "userDetails.php";
    }

    /**
     * Creates a user
     * @return strng redirect URI
     * @throws PDOException
     * 
     * @POST(uri="|^/user$|", sec="admin")
     */
    public function create() {
        global $VIEW_DATA;

        $first = filter_input(INPUT_POST, "first", FILTER_SANITIZE_STRING);
        $last = filter_input(INPUT_POST, "last", FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_STRING);
        $pass = filter_input(INPUT_POST, "pass");
        $type = filter_input(INPUT_POST, "type");
        $active = filter_input(INPUT_POST, "active");

        $error = [];
        if (!$first) {
            $error[] = "first name";
        }
        if (!last) {
            $error[] = "last name";
        }
        if (!$email) {
            $error[] = "email address";
        }
        if (!$pass) {
            $error[] = "password";
        }
        if ($error) {
            $VIEW_DATA["msg"] = "Missing: " . json_encode($error);
            return "Location: user/add";
        }
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $actv = 1;
        if (!$active) {
            $actv = 0;
        }

        try {
            $uid = $this->userDao->insert($first, $last, $email, $hash, $type, $actv);
        } catch (Exception $e) {
            $error = true;
        }

        if ($error) {
            $VIEW_DATA["msg"] = "Error: email address already in db";
            return "Location: user/add";
        } else {
            return "Location: user";            
        }
    }

    /**
     * Updates a user 
     * @global array $URI_PARAMS as provided by framework based on request URI
     * @return string redirect URI
     * 
     * @POST(uri="|^/user/(\d+)$|", sec="admin")
     */
    public function update() {
        global $URI_PARAMS;
        $uid = $URI_PARAMS[1];
        $first = filter_input(INPUT_POST, "first", FILTER_SANITIZE_STRING);
        $last = filter_input(INPUT_POST, "last", FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_STRING);
        $type = filter_input(INPUT_POST, "type");
        $active = filter_input(INPUT_POST, "active");
        $pass = filter_input(INPUT_POST, "pass");

        $error = "";
        if (!$first) {
            $error .= "first ";
        }
        if (!last) {
            $error .= "last ";
        }
        if (!$email) {
            $error .= "email ";
        }
        if ($error) {
            return "Location: $uid?error=" . urlencode("Incorrect $error");
        }

        $actv = 1;
        if (!$active) {
            $actv = 0;
        }
        $this->userDao->update($first, $last, $email, $type, $actv, $uid, $pass);

        return "Location: $uid";
    }

}
