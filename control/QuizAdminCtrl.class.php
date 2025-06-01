<?php

/**
 * Quiz Admin Controller Class
 *
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

    #[Inject('AnswerDao')]
    public $answerDao;

    #[Get(uri: '$', sec: 'student')]
    public function courseOverview()
    {
        // We're building on top of  overview -- run it first
        // this populates $VIEW_DATA with the overview related data
        $this->overviewHlpr->overview();

        global $VIEW_DATA;

        // get all quizzes for this offering
        $oid = $VIEW_DATA['offering_id'];
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
            $day['quizzes'] = [];
        }
        foreach ($quizzes as $quiz) {
            $VIEW_DATA['days'][$quiz['abbr']]['quizzes'][] = $quiz;
        }

        $VIEW_DATA['title'] = 'Quizzes';
        $VIEW_DATA['area'] = 'quiz';
        $VIEW_DATA['graded'] = $graded;
        $VIEW_DATA['isRemembered'] = $_SESSION['user']['isRemembered'];

        return 'quiz/overview.php';
    }

    #[Post(uri: '$', sec: 'instructor')]
    public function addQuiz()
    {
        $day_id = filter_input(INPUT_POST, 'day_id', FILTER_SANITIZE_NUMBER_INT);
        $name = filter_input(INPUT_POST, 'name');
        $startdate = filter_input(INPUT_POST, 'startdate');
        $stopdate = filter_input(INPUT_POST, 'stopdate');
        $starttime = filter_input(INPUT_POST, 'starttime');
        $stoptime = filter_input(INPUT_POST, 'stoptime');

        $start = "{$startdate} {$starttime}";
        $stop = "{$stopdate} {$stoptime}";
        $id = $this->quizDao->add($name, $day_id, $start, $stop);

        return "Location: quiz/{$id}/edit"; // edit quiz view
    }

    #[Get(uri: "/(\d+)/edit$", sec: 'instructor')]
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
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['quiz'] = $this->quizDao->byId($quiz_id);
        $VIEW_DATA['questions'] = $this->questionDao->forQuiz($quiz_id);
        $VIEW_DATA['title'] = 'Edit Quiz';

        return 'quiz/edit.php';
    }

    #[Get(uri: '/preview$', sec: 'instructor')]
    public function previewQuiz()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_NUMBER_INT);
        $user_id = $_SESSION['user']['id'];

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $quiz = $this->quizDao->byId($quiz_id);
        $stopDiff = new DateInterval('PT1H'); // 1 hour

        require_once 'lib/Parsedown.php';
        $parsedown = new Parsedown;
        $parsedown->setSafeMode(true);

        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['quiz'] = $quiz;
        $VIEW_DATA['parsedown'] = $parsedown;
        $VIEW_DATA['questions'] = $this->questionDao->forQuiz($quiz_id);
        $VIEW_DATA['answers'] = $this->answerDao->forUser($user_id, $quiz_id);
        $VIEW_DATA['title'] = 'Quiz: '.$quiz['name'];
        $VIEW_DATA['stop'] = $stopDiff;

        return 'quiz/doQuiz.php';
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)$", sec: 'instructor')]
    public function updateQuiz()
    {
        global $URI_PARAMS;

        $id = $URI_PARAMS[3];
        $day_id = filter_input(INPUT_POST, 'day_id');
        $name = filter_input(INPUT_POST, 'name');
        $startdate = filter_input(INPUT_POST, 'startdate');
        $stopdate = filter_input(INPUT_POST, 'stopdate');
        $starttime = filter_input(INPUT_POST, 'starttime');
        $stoptime = filter_input(INPUT_POST, 'stoptime');

        $start = "{$startdate} {$starttime}";
        $stop = "{$stopdate} {$stoptime}";

        $this->quizDao->update($id, $day_id, $name, $start, $stop);
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/status$", sec: 'instructor')]
    public function setQuizStatus()
    {
        global $URI_PARAMS;
        $id = $URI_PARAMS[3];
        $visible = filter_input(INPUT_POST, 'visible', FILTER_VALIDATE_INT);
        $this->quizDao->setStatus($id, $visible);
    }

    #[Post(uri: "/(\d+)/del$", sec: 'instructor')]
    public function deleteQuiz()
    {
        global $URI_PARAMS;
        $id = $URI_PARAMS[3];
        $this->quizDao->delete($id);

        return 'Location: ../../quiz';
    }

    #[Post(uri: "/(\d+)/question$", sec: 'instructor')]
    public function addQuestion()
    {
        global $VIEW_DATA;

        $quiz_id = filter_input(INPUT_POST, 'quiz_id', FILTER_SANITIZE_NUMBER_INT);
        $given_type = filter_input(INPUT_POST, 'type');
        $seq = filter_input(INPUT_POST, 'seq', FILTER_SANITIZE_NUMBER_INT);

        $text = '';
        $model_answer = '';
        $points = 0;
        $type = 'text';
        if ($given_type == 'image') {
            $type = $given_type;
        }

        $question_id = $this->questionDao->add($quiz_id, $type, $text, $model_answer, $points, $seq);
        $VIEW_DATA['question'] = $this->questionDao->get($question_id);

        return 'quiz/question.php';
    }

    #[Post(uri: "/\d+/question/(\d+)$", sec: 'instructor')]
    public function updateQuestion()
    {
        global $URI_PARAMS;

        $id = $URI_PARAMS[3];
        $type = filter_input(INPUT_POST, 'type');
        $points = filter_input(INPUT_POST, 'points', FILTER_SANITIZE_NUMBER_INT);
        $qshifted = filter_input(INPUT_POST, 'text');
        $text = $this->markdownCtrl->ceasarShift($qshifted);
        $hasMarkdown = filter_input(INPUT_POST, 'hasMarkDown', FILTER_VALIDATE_INT);
        $mdlAnsHasMd = filter_input(INPUT_POST, 'mdlAnsHasMD', FILTER_VALIDATE_INT);
        if (! $hasMarkdown) {
            $hasMarkdown = 0;
        }
        if (! $mdlAnsHasMd) {
            $mdlAnsHasMd = 0;
        }

        $model_answer = '';
        if ($type == 'text') {
            $ashifted = filter_input(INPUT_POST, 'model_answer');
            if ($ashifted) {
                $model_answer = $this->markdownCtrl->ceasarShift($ashifted);
            }
        } elseif ($type == 'image') {
            $model_answer = filter_input(INPUT_POST, 'model_answer');
        }

        $this->questionDao->update($id, $text, $model_answer, $points, $hasMarkdown, $mdlAnsHasMd);
    }

    #[Post(uri: "/(\d+)/question/(\d+)/modelAnswerImage$", sec: 'instructor')]
    public function uploadReplacementModelImage(): array
    {
        global $URI_PARAMS;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = $URI_PARAMS[3];
        $question_id = $URI_PARAMS[4];

        $quiz = $this->quizDao->byId($quiz_id);
        $qname = str_replace(' ', '_', $quiz['name']);
        $question = $this->questionDao->get($question_id);
        $qseq = $question['seq'];
        if (strlen($qseq) == 1) {
            $qseq = '0'.$qseq;
        }
        $path = "res/course/{$course}/{$block}/quiz/{$qname}/{$qseq}/model";
        $res = $this->imageHlpr->process('image', $path);
        $this->questionDao->updateModelAnswer($question_id, $res['dst'], 0);

        return $res;
    }

    // Pictures are taken with camera, images are uploaded from the FS
    #[Post(uri: "/(\d+)/question/modelAns/(\d+)/picture", sec: 'instructor')]
    public function uploadModelPicture(): array
    {
        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = $URI_PARAMS[3];
        $question_id = $URI_PARAMS[4];

        $img = filter_input(INPUT_POST, 'image');

        $quiz = $this->quizDao->byId($quiz_id);
        $question = $this->questionDao->get($question_id);
        $qseq = $question['seq'];
        if (strlen($qseq) == 1) {
            $qseq = '0'.$qseq;
        }
        $path = "res/course/{$course}/{$block}/quiz/{$quiz_id}/{$qseq}/model";
        $dst = $this->imageHlpr->save($img, $path);
        $this->questionDao->updateModelAnswer($question_id, $dst, 0);

        return ['dst' => $dst];
    }

    #[Post(uri: "/(\d+)/question/(\d+)/del$", sec: 'instructor')]
    public function delQuestion()
    {
        global $URI_PARAMS;
        $question_id = $URI_PARAMS[4];
        $this->questionDao->delete($question_id);

        return 'Location: ../../edit';
    }

    #[Get(uri: '/report$', sec: 'student')]
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
        if ($_SESSION['user']['isFaculty']) {
            foreach ($enrolled as $user) {
                if ($user['auth'] == 'instructor' || $user['auth'] == 'observer') {
                    continue;
                }
                $data[$user['id']] = [];
                $data[$user['id']][] = $user['studentID'];
                $data[$user['id']][] = $user['firstname'];
                $data[$user['id']][] = $user['lastname'];
            }
        } else {
            // only give them their own data
            foreach ($enrolled as $user) {
                if ($user['id'] == $_SESSION['user']['id']) {
                    $data[$user['id']] = [];
                    $data[$user['id']][] = $user['studentID'];
                    $data[$user['id']][] = $user['firstname'];
                    $data[$user['id']][] = $user['lastname'];
                }
            }
        }

        $quizzes = $this->quizDao->allForOffering($offering['id']);

        // build CSV header and query for data fetching
        $count = 1;
        $header = '"studentId","firstName","lastName",';
        foreach ($quizzes as $quiz) {
            // build CSV header line
            $header .= '"'.$quiz['abbr'].'",';

            // build data column for this quiz
            $pts = $this->quizDao->getQuizTotalsForEnrolled($quiz['id'], $offering['id']);
            foreach ($pts as $pt) {
                if (isset($data[$pt['user_id']])) {
                    $data[$pt['user_id']][] = number_format($pt['points'], 2);
                }
            }
            $count++;
        }

        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['type'] = 'quiz';
        $VIEW_DATA['colCount'] = $count + 3; // 3 are sid, first, last
        $VIEW_DATA['header'] = $header;
        $VIEW_DATA['data'] = $data;

        return 'csv.php';
    }
}
