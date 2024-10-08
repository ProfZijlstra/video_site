<?php

/**
 * Quiz Grading Controller Class
 * @author mzijlstra 12/21/2022
 */

#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/quiz")]
class QuizGradingCtrl
{
    #[Inject('QuizDao')]
    public $quizDao;

    #[Inject('QuestionDao')]
    public $questionDao;

    #[Inject('AnswerDao')]
    public $answerDao;

    #[Inject('QuizEventDao')]
    public $quizEventDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Inject('UserDao')]
    public $userDao;

    #[Inject('MarkdownHlpr')]
    public $markdownCtrl;

    #[Get(uri: "/(\d+)/grade$", sec: "assistant")]
    public function gradeQuiz()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = $URI_PARAMS[3];

        $offering_detail = $this->offeringDao->getOfferingByCourse($course, $block);
        $offering_id = $offering_detail['id'];
        $enrollment = $this->enrollmentDao->getEnrollmentForOffering($offering_id);

        // create categories based on enrollment
        $absent = [];
        $taken = [];
        $extra = [];
        foreach ($enrollment as $student) {
            if ($student['auth'] == 'observer') {
                continue;
            }
            $absent[$student["id"]] = $student; // all start as absent
        }

        $answers = $this->answerDao->overview($quiz_id);
        foreach ($answers as $answer) {
            if ($absent[$answer['id']]) { // is student enrolled?
                unset($absent[$answer['id']]); // no longer absent
                $taken[$answer['id']] = $answer;
            } else {
                $extra[$answer['id']] = $answer;
            }
        }

        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['title'] = "Grade Overview";
        $VIEW_DATA['offering'] = $offering_detail;
        $VIEW_DATA['quiz'] = $this->quizDao->byId($quiz_id);
        $VIEW_DATA['questions'] = $this->questionDao->forQuiz($quiz_id);
        $VIEW_DATA['absent'] = $absent;
        $VIEW_DATA['taken'] = $taken;
        $VIEW_DATA['extra'] = $extra;
        $VIEW_DATA['starts'] = $this->quizEventDao->getStartTimes($quiz_id);
        $VIEW_DATA['stops'] = $this->quizEventDao->getStopTimes($quiz_id);
        return "quiz/gradeOverview.php";
    }

    #[Get(uri: "/(\d+)/question/(\d+)$", sec: "assistant")]
    public function gradeQuestion()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = $URI_PARAMS[3];
        $question_id = $URI_PARAMS[4];

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $questions = $this->questionDao->forQuiz($quiz_id);
        $next_id = null;
        $prev_id = null;
        for ($i = 0; $i < count($questions); $i++) {
            $question = $questions[$i];
            if ($question['id'] == $question_id) {
                $prev_id = $questions[$i - 1]['id'];
                $next_id = $questions[$i + 1]['id'];
            }
        }

        require_once("lib/Parsedown.php");
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);
        $VIEW_DATA["parsedown"] = $parsedown;
        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['title'] = "Grade Question";
        $VIEW_DATA['prev_id'] = $prev_id;
        $VIEW_DATA['next_id'] = $next_id;
        $VIEW_DATA['quiz'] = $this->quizDao->byId($quiz_id);
        $VIEW_DATA['question'] = $this->questionDao->get($question_id);
        $VIEW_DATA['answers'] = $this->answerDao->forQuestion($question_id);

        return "quiz/gradeQuestion.php";
    }

    #[Get(uri: "/(\d+)/user/(\d+)$", sec: "assistant")]
    public function gradeUser()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = $URI_PARAMS[3];
        $user_id = $URI_PARAMS[4];

        $idx = filter_input(INPUT_GET, "idx", FILTER_VALIDATE_INT);
        $ids = array_column($this->userDao->idsForQuiz($quiz_id), 'user_id');
        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        if ($idx == "") {
            $idx = array_search($user_id, $ids);
        }

        require_once("lib/Parsedown.php");
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);
        $VIEW_DATA["parsedown"] = $parsedown;
        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['title'] = "Grade Quiz by Student";
        $VIEW_DATA['quiz_id'] = $quiz_id;
        $VIEW_DATA['user'] = $this->userDao->retrieve($user_id);
        $VIEW_DATA['events'] = $this->quizEventDao->forUser($quiz_id, $user_id);
        $VIEW_DATA['questions'] = $this->questionDao->forQuiz($quiz_id);
        $VIEW_DATA['answers'] = $this->answerDao->forUser($user_id, $quiz_id);
        $VIEW_DATA['idx'] = $idx;
        $VIEW_DATA['ids'] = $ids;

        return "quiz/gradeUser.php";
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/question/grade$", sec: "assistant")]
    public function grade()
    {
        $answer_ids = filter_input(INPUT_POST, "answer_ids");
        $points = filter_input(INPUT_POST, "points", FILTER_VALIDATE_FLOAT);
        $shifted = filter_input(INPUT_POST, "comment");
        $cmntHasMD = filter_input(INPUT_POST, "cmntHasMD", FILTER_VALIDATE_INT);
        if (!$cmntHasMD) {
            $cmntHasMD = 0;
        }

        $comment = "";
        if ($shifted) {
            $comment = $this->markdownCtrl->ceasarShift($shifted);
        }

        $this->answerDao->grade($answer_ids, $points, $comment, $cmntHasMD);
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/user/grade$", sec: "assistant")]
    public function gradeByUser()
    {
        $answer_id = filter_input(INPUT_POST, "answer_id", FILTER_VALIDATE_INT);
        $user_id = filter_input(INPUT_POST, "user_id", FILTER_VALIDATE_INT);
        $question_id = filter_input(INPUT_POST, "question_id", FILTER_VALIDATE_INT);
        $points = filter_input(INPUT_POST, "points", FILTER_VALIDATE_FLOAT);
        $shifted = filter_input(INPUT_POST, "comment");
        $cmntHasMD = filter_input(INPUT_POST, "cmntHasMD", FILTER_VALIDATE_INT);
        if (!$cmntHasMD) {
            $cmntHasMD = 0;
        }

        $comment = "";
        if ($shifted) {
            $comment = $this->markdownCtrl->ceasarShift($shifted);
        }

        if ($answer_id) {
            $this->answerDao->grade($answer_id, $points, $comment, $cmntHasMD);
        } else {
            $answer_id = $this->answerDao->gradeUnanswered($user_id, $question_id, $comment, $points, $cmntHasMD);
        }

        return ["answer_id" => $answer_id];
    }
}
