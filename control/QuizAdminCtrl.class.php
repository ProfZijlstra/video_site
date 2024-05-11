<?php

/**
 * Quiz Admin Controller Class
 * @author mzijlstra 07/31/2022
 */

#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/quiz")]
class QuizAdminCtrl
{
    #[Inject('QuizDao')]
    public $quizDao;

    #[Inject('QuestionDao')]
    public $questionDao;

    #[Inject('OverviewHlpr')]
    public $overviewHlpr;

    #[Inject('MarkdownHlpr')]
    public $markdownCtrl;

    #[Inject('ImageHlpr')]
    public $imageHlpr;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('DayDao')]
    public $dayDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Get(uri: "$", sec: "observer")]
    public function courseOverview()
    {
        // We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->overviewHlpr->overview();

        global $VIEW_DATA;

        // get all quizzes for this offering
        $oid = $VIEW_DATA["offering_id"];
        if (
            $_SESSION['user']['isAdmin'] ||
            $_SESSION['user']['isFaculty']
        ) {
            $quizzes = $this->quizDao->allForOffering($oid);
            $grading = $this->quizDao->getInstructorGradingStatus($oid);
        } else {
            $quizzes = $this->quizDao->visibleForOffering($oid);
            $user_id = $_SESSION['user']['id'];
            $grading = $this->quizDao->getStudentGradingStatus($oid, $user_id);
        }

        $graded = [];
        foreach ($grading as $grade) {
            $graded[$grade['id']] = $grade;
        }

        // integrate the quizzes data into the days data
        foreach ($VIEW_DATA['days'] as $day) {
            $day['quizzes'] = array();
        }
        foreach ($quizzes as $quiz) {
            $VIEW_DATA['days'][$quiz['abbr']]['quizzes'][] = $quiz;
        }

        $VIEW_DATA['title'] = 'Quizzes';
        $VIEW_DATA['graded'] = $graded;
        $VIEW_DATA["isRemembered"] = $_SESSION['user']['isRemembered'];
        return "quiz/overview.php";
    }


    #[Post(uri: "$", sec: "instructor")]
    public function addQuiz()
    {
        $day_id = filter_input(INPUT_POST, "day_id", FILTER_SANITIZE_NUMBER_INT);
        $name = filter_input(INPUT_POST, "name");
        $startdate = filter_input(INPUT_POST, "startdate");
        $stopdate = filter_input(INPUT_POST, "stopdate");
        $starttime = filter_input(INPUT_POST, "starttime");
        $stoptime = filter_input(INPUT_POST, "stoptime");

        $start = "{$startdate} {$starttime}";
        $stop = "{$stopdate} {$stoptime}";
        $id = $this->quizDao->add($name, $day_id, $start, $stop);

        return "Location: quiz/{$id}/edit"; // edit quiz view
    }

    #[Get(uri: "/(\d+)/edit$", sec: "instructor")]
    public function editQuiz()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = $URI_PARAMS[3];

        $offering = $this->offeringDao->getOfferingByCourse($course_num, $block);
        $days = $this->dayDao->getDays($offering['id']);

        $VIEW_DATA['days'] = $days;
        $VIEW_DATA['course'] = $course_num;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['quiz'] = $this->quizDao->byId($quiz_id);
        $VIEW_DATA['questions'] = $this->questionDao->forQuiz($quiz_id);
        $VIEW_DATA['title'] = "Edit Quiz";

        return "quiz/edit.php";
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)$", sec: "instructor")]
    public function updateQuiz()
    {
        global $URI_PARAMS;

        $id = $URI_PARAMS[3];
        $day_id = filter_input(INPUT_POST, "day_id");
        $name = filter_input(INPUT_POST, "name");
        $startdate = filter_input(INPUT_POST, "startdate");
        $stopdate = filter_input(INPUT_POST, "stopdate");
        $starttime = filter_input(INPUT_POST, "starttime");
        $stoptime = filter_input(INPUT_POST, "stoptime");

        $start = "{$startdate} {$starttime}";
        $stop = "{$stopdate} {$stoptime}";

        $this->quizDao->update($id, $day_id, $name, $start, $stop);
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/status$", sec: "instructor")]
    public function setQuizStatus()
    {
        global $URI_PARAMS;
        $id = $URI_PARAMS[3];
        $visible = filter_input(INPUT_POST, "visible", FILTER_VALIDATE_INT);
        $this->quizDao->setStatus($id, $visible);
    }


    #[Post(uri: "/(\d+)/del$", sec: "instructor")]
    public function deleteQuiz()
    {
        global $URI_PARAMS;
        $id = $URI_PARAMS[3];
        $this->quizDao->delete($id);
        return "Location: ../../quiz";
    }


    #[Post(uri: "/(\d+)/question$", sec: "instructor")]
    public function addQuestion()
    {
        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $quiz_id = filter_input(INPUT_POST, "quiz_id", FILTER_SANITIZE_NUMBER_INT);
        $type = filter_input(INPUT_POST, "type");
        $qshifted = filter_input(INPUT_POST, "text");
        $points = filter_input(INPUT_POST, "points", FILTER_SANITIZE_NUMBER_INT);
        $seq = filter_input(INPUT_POST, "seq", FILTER_SANITIZE_NUMBER_INT);
        $text = $this->markdownCtrl->ceasarShift($qshifted);

        $model_answer = "";
        if ($type == "text") {
            $ashifted = filter_input(INPUT_POST, "model_answer");
            if ($ashifted) {
                $model_answer = $this->markdownCtrl->ceasarShift($ashifted);
            }
        }

        $question_id = $this->questionDao->add($quiz_id, $type, $text, $model_answer, $points, $seq);

        if ($type == "image" && $_FILES['image']['tmp_name']) {
            $path = "res/{$course}/{$block}/quiz/{$question_id}";
            $res = $this->imageHlpr->process("image", $path);
            if (isset($res['error'])) {
                return $res;
            }
            $this->questionDao->update($question_id, $text, $res['dst'], $points, $seq);
        }

        return "Location: ../{$quiz_id}/edit";
    }


    #[Post(uri: "/\d+/question/(\d+)$", sec: "instructor")]
    public function updateQuestion()
    {
        global $URI_PARAMS;

        $id = $URI_PARAMS[3];
        $type = filter_input(INPUT_POST, "type");
        $points = filter_input(INPUT_POST, "points", FILTER_SANITIZE_NUMBER_INT);
        $qshifted = filter_input(INPUT_POST, "text");
        $text = $this->markdownCtrl->ceasarShift($qshifted);
        $hasMarkdown = filter_input(INPUT_POST, "hasMarkDown", FILTER_VALIDATE_INT);
        $mdlAnsHasMd = filter_input(INPUT_POST, "mdlAnsHasMD", FILTER_VALIDATE_INT);
        if (!$hasMarkdown) {
            $hasMarkdown = 0;
        }
        if (!$mdlAnsHasMd) {
            $mdlAnsHasMd = 0;
        }

        $model_answer = "";
        if ($type == "text") {
            $ashifted = filter_input(INPUT_POST, "model_answer");
            if ($ashifted) {
                $model_answer = $this->markdownCtrl->ceasarShift($ashifted);
            }
        } else if ($type == "image") {
            $model_answer = filter_input(INPUT_POST, "model_answer");
        }

        $this->questionDao->update($id, $text, $model_answer, $points, $hasMarkdown, $mdlAnsHasMd);
    }


    #[Post(uri: "/(\d+)/question/(\d+)/modelAnswerImage$", sec: "instructor")]
    public function uploadReplacementModelImage()
    {
        global $URI_PARAMS;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $question_id = $URI_PARAMS[4];

        $path = "res/{$course}/{$block}/quiz/{$question_id}";
        $res = $this->imageHlpr->process("image", $path);
        $this->questionDao->updateModelAnswer($question_id, $res['dst'], 0);

        return $res;
    }


    #[Post(uri: "/(\d+)/question/(\d+)/del$", sec: "instructor")]
    public function delQuestion()
    {
        global $URI_PARAMS;
        $question_id = $URI_PARAMS[4];
        $this->questionDao->delete($question_id);
        return "Location: ../../edit";
    }

    #[Get(uri: "/report$", sec: "instructor")]
    public function resultsReport()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $enrolled = $this->enrollmentDao->getEnrollmentForOffering($offering['id']);

        // create data two dimensional array and initialize first 3 columns
        $data = [];
        foreach ($enrolled as $user) {
            $data[$user['id']] = [];
            $data[$user['id']][] = $user['studentID'];
            $data[$user['id']][] = $user['firstname'];
            $data[$user['id']][] = $user['lastname'];
        }

        $quizzes = $this->quizDao->allForOffering($offering['id']);

        // build CSV header and query for data fetching 
        $count = 1;
        $header = '"studentId","firstName","lastName",';
        foreach ($quizzes as $quiz) {
            // build CSV header line
            $header .= '"' . $quiz['abbr'] . '",';

            // build data column for this quiz
            $pts = $this->quizDao->getQuizTotalsForEnrolled($quiz['id'], $offering['id']);
            foreach ($pts as $pt) {
                $data[$pt['user_id']][] = $pt['points'];
            }
            $count++;
        }

        $VIEW_DATA['colCount'] = $count + 3; // 3 are sid, first, last
        $VIEW_DATA['header'] = $header;
        $VIEW_DATA['data'] = $data;

        return "quiz/csv.php";
    }
}
