<?php

/**
 * Quiz Admin Controller Class
 * @author mzijlstra 07/31/2022
 * 
 * @Controller
 */
class QuizAdminCtrl {
    /**
     * @Inject('QuizDao')
     */
    public $quizDao;

    /**
     * @Inject('QuestionDao')
     */
    public $questionDao;

    /**
     * @Inject("OverviewCtrl")
     */
    public $overviewCtrl;

    /**
     * @Inject('MarkdownCtrl')
     */
    public $markdownCtrl;

    /**
     * @Inject('ImageCtrl')
     */
    public $imageCtrl;


    /**
     * @GET(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/quiz$!", sec="applicant")
     */
    public function courseOverview() {
        // We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->overviewCtrl->overview();

        global $VIEW_DATA;

        // get all quizzes for this offering
        $oid = $VIEW_DATA["offering_id"];
        if ($_SESSION['user']['type'] == "instructor"  ||
            $_SESSION['user']['type'] == "admin") {
            $quizzes = $this->quizDao->allForOffering($oid);
        } else {
            $quizzes = $this->quizDao->visibleForOffering($oid);
        }

        // integrate the quizzes data into the days data
        foreach ($VIEW_DATA['days'] as $day) {
            $day['quizzes'] = array();
        }
        foreach ($quizzes as $quiz) {
            $VIEW_DATA['days'][$quiz['abbr']]['quizzes'][] = $quiz;
        }

        $VIEW_DATA['title'] = 'Quizzes';
        return "quiz/overview.php";
    }

    /**
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/quiz$!", sec="instructor")
     */
    public function addQuiz() {
        $day_id = filter_input(INPUT_POST, "day_id", FILTER_SANITIZE_NUMBER_INT);
        $name = filter_input(INPUT_POST, "name");
        $startdate = filter_input(INPUT_POST, "startdate");
        $stopdate = filter_input(INPUT_POST, "stopdate");
        $starttime = filter_input(INPUT_POST, "starttime");
        $stoptime = filter_input(INPUT_POST, "stoptime");

        $start = "${startdate} ${starttime}";
        $stop = "${stopdate} ${stoptime}";
        $id = $this->quizDao->add($name, $day_id, $start, $stop);
    
        return "Location: quiz/${id}/edit"; // edit quiz view
    }

    /**
     * @GET(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/quiz/(\d+)/edit$!", sec="instructor")
     */
    public function editQuiz() {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course_num = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = $URI_PARAMS[3];

        $VIEW_DATA['course'] = $course_num;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['quiz'] = $this->quizDao->byId($quiz_id);
        $VIEW_DATA['questions'] = $this->questionDao->forQuiz($quiz_id);
        $VIEW_DATA['title'] = "Edit Quiz";

        return "quiz/edit.php";
    }

    /**
     * Expects AJAX
     *  
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/quiz/(\d+)$!", sec="instructor")
     */
    public function updateQuiz() {
        global $URI_PARAMS;

        $id = $URI_PARAMS[3];
        $name = filter_input(INPUT_POST, "name");
        $startdate = filter_input(INPUT_POST, "startdate");
        $stopdate = filter_input(INPUT_POST, "stopdate");
        $starttime = filter_input(INPUT_POST, "starttime");
        $stoptime = filter_input(INPUT_POST, "stoptime");

        $start = "${startdate} ${starttime}";
        $stop = "${stopdate} ${stoptime}";

        $this->quizDao->update($id, $name, $start, $stop);
    }

    /**
     * Expects AJAX
     * 
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/quiz/(\d+)/status$!", sec="instructor")
     */
    public function setQuizStatus() {
        global $URI_PARAMS;
        $id = $URI_PARAMS[3];
        $visible = filter_input(INPUT_POST, "visible", FILTER_VALIDATE_INT);
        $this->quizDao->setStatus($id, $visible);
    }

    /**
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/quiz/(\d+)/del$!", sec="instructor")
     */
    public function deleteQuiz() {
        global $URI_PARAMS;
        $id = $URI_PARAMS[3];
        $this->quizDao->delete($id);
        return "Location: ../../quiz";
    }

    /**
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/quiz/(\d+)/question$!", sec="instructor")
     */
    public function addQuestion() {
        $quiz_id = filter_input(INPUT_POST, "quiz_id", FILTER_SANITIZE_NUMBER_INT);
        $type = filter_input(INPUT_POST, "type");
        $qshifted = filter_input(INPUT_POST, "text");
        $points = filter_input(INPUT_POST, "points", FILTER_SANITIZE_NUMBER_INT);
        $seq = filter_input(INPUT_POST, "seq", FILTER_SANITIZE_NUMBER_INT);
        $text = $this->markdownCtrl->ceasarShift($qshifted);

        $model_answer = "";
        if ($type == "markdown") {
            $ashifted = filter_input(INPUT_POST, "model_answer");
            if ($ashifted) {
                $model_answer = $this->markdownCtrl->ceasarShift($ashifted);    
            }            
        } 

        $question_id = $this->questionDao->
            add($quiz_id, $type, $text, $model_answer, $points, $seq);

        if ($type == "image" && $_FILES['image']['tmp_name']) {
            $user_id = $_SESSION['user']['id'];
            $res = $this->imageCtrl->process("image", $question_id, $user_id);
            if (isset($res['error'])) {
                return $res;
            } 
            $this->questionDao->
                update($question_id, $text, $res['dst'], $points, $seq);
        }

        return "Location: ../${quiz_id}/edit";
    }

    /**
     * @POST(uri="!^/cs\d{3}/20\d{2}-\d{2}/quiz/\d+/question/(\d+)$!", sec="instructor")
     */
    public function updateQuestion() {
        global $URI_PARAMS;

        $id = $URI_PARAMS[1];
        $type = filter_input(INPUT_POST, "type");
        $points = filter_input(INPUT_POST, "points", FILTER_SANITIZE_NUMBER_INT);
        $qshifted = filter_input(INPUT_POST, "text");
        $text = $this->markdownCtrl->ceasarShift($qshifted);

        $model_answer = "";
        if ($type == "markdown") {
            $ashifted = filter_input(INPUT_POST, "model_answer");
            $model_answer = $this->markdownCtrl->ceasarShift($ashifted);
        } else if ($type == "image") {
            $model_answer = filter_input(INPUT_POST, "model_answer");
        }

        $this->questionDao->update($id, $text, $model_answer, $points);
    }

    /**
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/quiz/(\d+)/question/(\d+)/modelAnswerImage$!", sec="instructor")
     */
    public function uploadReplacementModelImage() {
        global $URI_PARAMS;

        $question_id = $URI_PARAMS[4];
        $user_id = $_SESSION['user']['id'];

        $res = $this->imageCtrl->process("image", $question_id, $user_id);
        $this->questionDao->updateModelAnswer($question_id, $res['dst']);

        return $res;
    }

    /**
     * @POST(uri="!^/cs\d{3}/20\d{2}-\d{2}/quiz/(\d+)/question/(\d+)/del$!", sec="instructor")
     */
    public function delQuestion() {
        global $URI_PARAMS;
        $quiz_id = $URI_PARAMS[1];
        $question_id = $URI_PARAMS[2];
        $this->questionDao->delete($question_id);
        return "Location: ../../edit";
    }
}

?>