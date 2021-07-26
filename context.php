<?php
/******************************************************************************* 
 * DO NOT MODIFY THIS FILE, IT IS GENERATED 
 * 
 * When DEVELOPMENT=true this file is generated based on the settings in 
 * frontController.php and the annotations found in the class files in the 
 * control and model directories
 ******************************************************************************/
$mappings = array(
	"GET" => array(
		'|^/.*login$|' => 
			['sec' => 'none', 'route' => 'UserCtrl@getLogin'],
		'|^/.*logout$|' => 
			['sec' => 'none', 'route' => 'UserCtrl@logout'],
		'|^/user$|' => 
			['sec' => 'admin', 'route' => 'UserCtrl@all'],
		'|^/user/(\d+)$|' => 
			['sec' => 'admin', 'route' => 'UserCtrl@details'],
		'|^/user/add$|' => 
			['sec' => 'admin', 'route' => 'UserCtrl@addUser'],
		'|^/(cs\d{3})?/?$|' => 
			['sec' => 'user', 'route' => 'VideoCtrl@loggedIn'],
		'|^/(cs\d{3})/(20\d{2}-\d{2})/?$|' => 
			['sec' => 'user', 'route' => 'VideoCtrl@overview'],
		'|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/?$|' => 
			['sec' => 'user', 'route' => 'VideoCtrl@videos'],
		'|^/cs\d{3}/20\d{2}-\d{2}/(W[1-4]D[1-7]/)?start.*$|' => 
			['sec' => 'user', 'route' => 'VideoCtrl@start'],
	),
	"POST" => array(
		'|^/.*login$|' => 
			['sec' => 'none', 'route' => 'UserCtrl@login'],
		'|^/user$|' => 
			['sec' => 'admin', 'route' => 'UserCtrl@create'],
		'|^/user/(\d+)$|' => 
			['sec' => 'admin', 'route' => 'UserCtrl@update'],
		'|^/cs\d{3}/20\d{2}-\d{2}/(W[1-4]D[1-7]/)?stop$|' => 
			['sec' => 'user', 'route' => 'VideoCtrl@stop'],
	),
);
class Context {
    private $objects = array();
    
    public function __construct() {
        $db = new PDO("mysql:dbname=cs472;host=mysql.manalabs.org", "cs472dbuser", "WAP Passwd");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->objects["DB"] = $db;
    }

    public function get($id) {
        if ($id === "CourseDao" && !isset($this->objects["CourseDao"])) {
            $this->objects["CourseDao"] = new CourseDao();
            $this->objects["CourseDao"]->db = $this->get("DB");
        }
        if ($id === "DayDao" && !isset($this->objects["DayDao"])) {
            $this->objects["DayDao"] = new DayDao();
            $this->objects["DayDao"]->db = $this->get("DB");
        }
        if ($id === "EnrollmentDao" && !isset($this->objects["EnrollmentDao"])) {
            $this->objects["EnrollmentDao"] = new EnrollmentDao();
            $this->objects["EnrollmentDao"]->db = $this->get("DB");
        }
        if ($id === "OfferingDao" && !isset($this->objects["OfferingDao"])) {
            $this->objects["OfferingDao"] = new OfferingDao();
            $this->objects["OfferingDao"]->db = $this->get("DB");
        }
        if ($id === "UserDao" && !isset($this->objects["UserDao"])) {
            $this->objects["UserDao"] = new UserDao();
            $this->objects["UserDao"]->db = $this->get("DB");
        }
        if ($id === "ViewDao" && !isset($this->objects["ViewDao"])) {
            $this->objects["ViewDao"] = new ViewDao();
            $this->objects["ViewDao"]->db = $this->get("DB");
        }
        if ($id === "UserCtrl" && !isset($this->objects["UserCtrl"])) {
            $this->objects["UserCtrl"] = new UserCtrl();
            $this->objects["UserCtrl"]->userDao = $this->get("UserDao");
        }
        if ($id === "VideoCtrl" && !isset($this->objects["VideoCtrl"])) {
            $this->objects["VideoCtrl"] = new VideoCtrl();
            $this->objects["VideoCtrl"]->courseDao = $this->get("CourseDao");
            $this->objects["VideoCtrl"]->offeringDao = $this->get("OfferingDao");
            $this->objects["VideoCtrl"]->dayDao = $this->get("DayDao");
            $this->objects["VideoCtrl"]->enrollmentDao = $this->get("EnrollmentDao");
            $this->objects["VideoCtrl"]->viewDao = $this->get("ViewDao");
        }
        return $this->objects[$id];
    } // close get method
} // close Context class