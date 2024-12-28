<?php

/**
 * Quiz Taking Controller Class
 *
 * @author mzijlstra 12/21/2022
 */
#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/quiz")]
class QuizTakingCtrl
{
    #[Inject('QuizDao')]
    public $quizDao;

    #[Inject('QuestionDao')]
    public $questionDao;

    #[Inject('AnswerDao')]
    public $answerDao;

    #[Inject('QuizEventDao')]
    public $quizEventDao;

    #[Inject('MarkdownHlpr')]
    public $markdownCtrl;

    #[Inject('ImageHlpr')]
    public $imageHlpr;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;

    #[Inject('OfferingDao')]
    public $offeringDao;

    /**
     * This function is really a 3 in one.
     * 1. If it is used before the start time it shows a countdown timer
     * 2. If it is used after the stop time it shows a status for each question
     * 3. If between start and stop the user can give answers
     */
    #[Get(uri: "/(\d+)(/(\d+))?$", sec: 'student')]
    public function viewQuiz()
    {
        global $URI_PARAMS;
        global $VIEW_DATA;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = $URI_PARAMS[3];
        $selected = $URI_PARAMS[5];

        $offering = $this->offeringDao->getOfferingByCourse($course, $block);
        $quiz = $this->quizDao->byId($quiz_id);
        $tz = new DateTimeZone(TIMEZONE);
        $now = new DateTimeImmutable('now', $tz);
        $start = new DateTimeImmutable($quiz['start'], $tz);
        $stop = new DateTimeImmutable($quiz['stop'], $tz);
        $startDiff = $now->diff($start);
        $stopDiff = $now->diff($stop);

        $fac_upd = false;
        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
            $fac_upd = true;
        }
        $VIEW_DATA['course'] = $course;
        $VIEW_DATA['block'] = $block;
        $VIEW_DATA['offering'] = $offering;
        $VIEW_DATA['quiz'] = $quiz;
        if ($startDiff->invert === 0) { // start is in the future
            // show countdown page
            $VIEW_DATA['title'] = 'Quiz Countdown';
            $VIEW_DATA['start'] = $startDiff;

            return 'quiz/countdown.php';
        }

        require_once 'lib/Parsedown.php';
        $parsedown = new Parsedown;
        $parsedown->setSafeMode(true);
        $VIEW_DATA['parsedown'] = $parsedown;
        $VIEW_DATA['questions'] = $this->questionDao->forQuiz($quiz_id);
        $VIEW_DATA['answers'] = $this->answerDao->forUser($user_id, $quiz_id);

        $auth = $this->enrollmentDao->checkEnrollmentAuth($user_id, $course, $block);

        // quiz is done / over, stop is in the past
        if (! $fac_upd && ($auth == 'observer' || $stopDiff->invert === 1)) {
            // check if there is no 'stop' quiz event and add it
            $hasStop = $this->quizEventDao->checkStop($quiz_id, $user_id);
            if (! $hasStop) {
                $this->quizEventDao->add($quiz_id, $user_id, 'stop');
            }

            // show quiz taken / results page
            $VIEW_DATA['title'] = 'Quiz Results: '.$quiz['name'];
            $VIEW_DATA['possible'] = $this->sumPoints($VIEW_DATA['questions']);
            $VIEW_DATA['received'] = $this->sumPoints($VIEW_DATA['answers']);

            return 'quiz/results.php';
        }

        // show the actual quiz page
        if ($fac_upd) {
            $VIEW_DATA['user_id'] = $student_user_id;
        }
        $this->quizEventDao->add($quiz_id, $user_id, 'start');
        $VIEW_DATA['title'] = 'Quiz: '.$quiz['name'];
        $VIEW_DATA['stop'] = $stopDiff;
        $VIEW_DATA['selected'] = $selected;

        return 'quiz/doQuiz.php';
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/(\d+)/question/(\d+)/text$", sec: 'student')]
    public function answerTextQuestion()
    {
        global $URI_PARAMS;

        // reject answers after quiz stop time
        $quiz_id = $URI_PARAMS[3];
        if (! $_SESSION['user']['isFaculty'] && $this->quizEnded($quiz_id, 30)) {
            return 'error/403.php';
        }

        $question_id = $URI_PARAMS[4];
        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
        }
        $answer_id = filter_input(INPUT_POST, 'answer_id', FILTER_VALIDATE_INT);
        $hasMarkdown = filter_input(INPUT_POST, 'hasMarkDown', FILTER_VALIDATE_INT);
        $shifted = filter_input(INPUT_POST, 'answer');

        $answer = $this->markdownCtrl->ceasarShift($shifted);

        if ($answer_id) {
            $this->answerDao->update($answer_id, $answer, $user_id, $hasMarkdown);
        } else {
            $answer_id = $this->answerDao->add($answer, $question_id, $user_id, $hasMarkdown);
        }

        return ['answer_id' => $answer_id];
    }

    /**
     * Expects AJAX
     **/
    #[Post(uri: "/(\d+)/question/(\d+)/image$", sec: 'student')]
    public function answerImageQuestion()
    {
        global $URI_PARAMS;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = $URI_PARAMS[3];
        $question_id = $URI_PARAMS[4];

        // reject answers after quiz stop time
        if (! $_SESSION['user']['isFaculty'] && $this->quizEnded($quiz_id, 30)) {
            return 'error/403.php';
        }

        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
        }
        $answer_id = filter_input(INPUT_POST, 'answer_id', FILTER_VALIDATE_INT);

        $quiz = $this->quizDao->byId($quiz_id);
        $qname = str_replace(' ', '_', $quiz['name']);
        $question = $this->questionDao->get($question_id);
        $qseq = $question['seq'];
        if (strlen($qseq) == 1) {
            $qseq = '0'.$qseq;
        }

        $path = "res/course/{$course}/{$block}/quiz/{$qname}/{$qseq}";
        $res = $this->imageHlpr->process('image', $path);

        if (isset($res['error'])) {
            return $res;
        } else {
            $dst = $res['dst'];
        }

        // create / update answer in the db
        if ($answer_id) {
            $img = $this->answerDao->byId($answer_id)['text'];
            $this->imageHlpr->delete($img);
            $this->answerDao->update($answer_id, $dst, $user_id, 0);
        } else {
            $answer_id = $this->answerDao->add($dst, $question_id, $user_id, 0);
        }

        return ['dst' => $dst, 'answer_id' => $answer_id];
    }

    /**
     * Expects AJAX
     **/
    #[Post(uri: "/(\d+)/question/(\d+)/picture$", sec: 'student')]
    public function takePicture()
    {
        global $URI_PARAMS;

        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $quiz_id = $URI_PARAMS[3];
        $question_id = $URI_PARAMS[4];

        // reject answers after quiz stop time
        if (! $_SESSION['user']['isFaculty'] && $this->quizEnded($quiz_id, 30)) {
            return 'error/403.php';
        }

        $student_user_id = filter_input(INPUT_GET, 'student', FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user']['id'];
        if ($student_user_id && $_SESSION['user']['isFaculty']) {
            $user_id = $student_user_id;
        }
        $answer_id = filter_input(INPUT_POST, 'answer_id', FILTER_VALIDATE_INT);

        $quiz = $this->quizDao->byId($quiz_id);
        $qname = str_replace(' ', '_', $quiz['name']);
        $question = $this->questionDao->get($question_id);
        $qseq = $question['seq'];
        if (strlen($qseq) == 1) {
            $qseq = '0'.$qseq;
        }

        $path = "res/course/{$course}/{$block}/quiz/{$qname}/{$qseq}";
        $img = filter_input(INPUT_POST, 'image');
        $dst = $this->imageHlpr->save($img, $path);

        // create / update answer in the db
        if ($answer_id) {
            $img = $this->answerDao->byId($answer_id)['text'];
            $this->imageHlpr->delete($img);
            $this->answerDao->update($answer_id, $dst, $user_id, 0);
        } else {
            $answer_id = $this->answerDao->add($dst, $question_id, $user_id, 0);
        }

        return ['dst' => $dst, 'answer_id' => $answer_id];
    }

    #[Post(uri: "/(\d+)/finish$", sec: 'student')]
    public function finishQuiz()
    {
        global $URI_PARAMS;

        $quiz_id = $URI_PARAMS[3];
        $user_id = $_SESSION['user']['id'];
        $this->quizEventDao->add($quiz_id, $user_id, 'stop');

        return 'Location: ../../quiz';
    }

    /**
     * Expects AJAX
     */
    #[Delete(uri: "/(\d+)/delivery/(\d+)$", sec: 'student')]
    public function deletePicture()
    {
        global $URI_PARAMS;

        $id = $URI_PARAMS[4];
        $user_id = $_SESSION['user']['id'];
        $answer = $this->answerDao->byId($id);
        if ($user_id != $answer['user_id']) {
            return ['error' => 'You are not the owner of this file'];
        }
        // remove the file from the filesystem
        if (str_starts_with($answer['text'], 'res/course/')) {
            unlink($answer['text']);
        }
        // remove the delivery from the database
        $this->answerDao->delete($id);

        return ['success' => true];
    }

    private function quizEnded($quiz_id, $leewaySecs = 0)
    {
        $quiz = $this->quizDao->byId($quiz_id);
        $tz = new DateTimeZone(TIMEZONE);
        $now = new DateTimeImmutable('now', $tz);
        $stop = new DateTimeImmutable($quiz['stop'], $tz);
        // give leeway second
        $stop = $stop->add(new DateInterval("PT{$leewaySecs}S"));
        $stopDiff = $now->diff($stop);

        return $stopDiff->invert == 1; // is it in the past?
    }

    private function sumPoints($array)
    {
        $result = 0;
        foreach ($array as $item) {
            $result += $item['points'];
        }

        return $result;
    }
}
