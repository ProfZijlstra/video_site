<?php
require 'lib/guzzle/vendor/autoload.php';

use GuzzleHttp\Client;


/**
 * CAMS (MIU Attendance and Grade system) Helper
 * @author mzijlstra 2023-06-17
 */
class CamsHlpr
{
    // conenction related data
    private $client;
    private $accesKey;

    // course related data
    private $username;
    private $course_id;

    private $status = [
        "Absent" => 1049,
        "Late" => 1050,
        "Tardy" => 1051,
        "Excused" => 1052,
        "Left Early" => 1053,
        "Present" => 1054,
        "Other" => 1055
    ];


    function __construct($cams)
    {
        $this->client = new Client([
            'base_uri' => 'https://fac.miu.edu/',
            'cookies' => true,
        ]);

        $this->username = $cams['username'];
        $this->course_id = $cams['course_id'];
    }

    function login($password)
    {
        $this->client->request('GET', 'login.asp'); // gets cookies
        $this->client->request('POST', 'ceProcess.asp', [
            'form_params' => [
                'txtUsername' => $this->username,
                'txtPassword' => $password,
                'term' => '131', // spring 2023 (term is irrelevant)
                '' => '',
                'op' => 'login',
            ],
        ]);

        // get the access key
        $response = $this->client->request('GET', '/ceCourseList.asp');
        $htmlString = (string) $response->getBody();

        // suppress warnings 
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML($htmlString);
        $elem = $doc->getElementById("A2");
        $href = $elem->getAttribute("href");
        $url_components = parse_url($href);
        $params = [];
        parse_str($url_components['query'], $params);
        $this->accesKey = $params['ak'];
    }

    function logout()
    {
        $this->client->request('GET', '/logout.asp');
    }

    function getSessionTypes()
    {
        // select the course
        $form_params = [
            // "crs" => 57823,  // 57823 == CS4894B 2024-04
            "crs" => $this->course_id,  // 50709 == CS544 2023-06
            "accessKey" => $this->accesKey,
        ];
        $response = $this->client->request('POST', '/SetcmSessionObjects.asp', [
            'form_params' => $form_params,
        ]);

        // Get the attendance entry page that contains the types
        $response = $this->client->request('GET', '/cmFacultyAttendanceDateRange.asp');
        $htmlString = (string) $response->getBody();
        $doc = new DOMDocument();
        $doc->loadHTML($htmlString);

        // get the session types
        $select = $doc->getElementById("srofferSchedule");
        $options = $select->getElementsByTagName("option");
        $session_types = [];
        foreach ($options as $option) {
            $session_types[$option->textContent] = $option->getAttribute("value");
        }

        return $session_types;
    }

    function submitAttendance($students, $session_type, $date, $start, $stop)
    {
        // assumes session types id's are set in the constructor
        $prop = $session_type . "_id";
        $session_type_id = $this->$prop;


        $form_params = [
            "crs" => $this->course_id,  // 50709 == CS544 2023-06
            "accessKey" => $this->accesKey,
        ];

        // select the course
        $response = $this->client->request('POST', '/SetcmSessionObjects.asp', [
            'form_params' => $form_params,
        ]);

        $form_params = [
            "datefrom" => $date,
            "accessKey" => $this->accesKey,
            "srofferSchedule" => $session_type_id,  // "32754" =10am to 12am for CS544 2023-06
            "pageSize" => "0"
        ];

        // select the date and class session for attendance entry
        $response = $this->client->request('POST', "/cmFacultyAttendance.asp", [
            'form_params' => $form_params
        ]);

        // build the data that we need to submit
        $htmlString = (string) $response->getBody();
        $doc = new DOMDocument();
        $doc->loadHTML($htmlString);
        $roomId = $doc->getElementById("RoomID")->getAttribute("value");

        $data = [
            "RoomID" => $roomId,                // retrieved from HTML
            "HeaderTable" => "SROfferSchedule", // Always the same???
            "HeaderID" => $session_type_id,     // CAMS_AM_id
            "TimeFromDis" => $start,     // user specified start time
            "TimeToDis" => $stop,        // user specified stop time
            //"TimeFrom" => "10:00 AM",  // not needed, hidden field start time
            //"TimeTo" => "12:00 PM",    // not needed, hidden field stop time
            "classdate" => $date,
            "dateFrom" => $date,
            "newPage" => "",
            "currentPage" => "1",
            "pageSize" => "0",
            "totalPages" => "1",
            "IsPostBack" => "True",
            "hShowWithdrawn" => "False",
            "hShowPhoto" => "False",
            "accessKey" => $this->accesKey,
            "srofferSchedule" => $session_type_id,
            "op" => "SaveAttendancePaged",
        ];

        $table = $doc->getElementById("AttendanceEntry");
        $rows = $table->getElementsByTagName('tr');
        foreach ($rows as $row) {
            $tds = $row->getElementsByTagName("td");
            if (count($tds) == 0) {
                // the first TR doesn't contain a student (header)
                continue;
            }

            $CAMS_UID = $tds[0]->getElementsByTagName("input")[0]->getAttribute('value');
            $MIU_studentId = trim($tds[0]->textContent);
            $student = $students[$MIU_studentId];

            if (!is_numeric($CAMS_UID)) {
                // the last TR doesn't contain a student (footer)
                continue;
            }

            $data["StudentUID" . $CAMS_UID] = $CAMS_UID;
            $data["Stu" . $CAMS_UID] = $this->status[$student['status']];
            if ($student['comment']) {
                if ($student['inClass']) {
                    $data["inclass" . $CAMS_UID] = "INCLASS";
                    $data["C" . $CAMS_UID] = "[InClass] - " . $student['comment'];
                    $data["C_Edited" . $CAMS_UID] = $student['comment'];
                } else {
                    $data["C" . $CAMS_UID] = $student['comment'];
                    $data["C_Edited" . $CAMS_UID] = $student['comment'];
                }
            } else {
                if ($student['inClass']) {
                    $data["inclass" . $CAMS_UID] = "INCLASS";
                    $data["C" . $CAMS_UID] = "[InClass] - ";
                    $data["C_Edited" . $CAMS_UID] = "";
                } else {
                    $data["C" . $CAMS_UID] = "";
                    $data["C_Edited" . $CAMS_UID] = "";
                }
            }
        }
        $response = $this->client->request('POST', "/cmProcess.asp", [
            'form_params' => $data
        ]);
    }
}

