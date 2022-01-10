<?php

/**
 * Question Controller Class
 * @author mzijlstra 09/24/2021
 *
 * @Controller
 */
class QuestionCtrl
{

    /**
     * @Inject("QuestionDao")
     */
    public $questionDao;

    /**
     * @Inject("QuestionVoteDao")
     */
    public $questionVoteDao;

    /**
     * @Inject("ReplyDao")
     */
    public $replyDao;

    /**
     * @Inject("ReplyVoteDao")
     */
    public $replyVoteDao;

    /**
     * @Inject("UserDao")
     */
    public $userDao;

    /**
     * @POST(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/question$|", sec="user")
     */
    public function add()
    {
        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $user_id = $_SESSION['user']['id'];
        $video = filter_input(INPUT_POST, "video");
        // using the capabilities of filter_input changes all html entities,
        // breaking markdown syntax -- so we fall back to htmlspecialchars()
        $question = htmlspecialchars(filter_input(INPUT_POST, "question"), ENT_NOQUOTES);
        $tab = filter_input(INPUT_POST, "tab");
        $id = $this->questionDao->add($question, $user_id, $video);
        $user = $this->userDao->retrieve($user_id);

        $message = $user["knownAs"] . " " . $user["lastname"] . 
            "asks:\n\n$question\n
See question at: http://manalabs.org/videos/${course}/${block}/${day}/${tab}#r${id}";

        $headers = 'FROM: "Manalabs Video System" <videos@manalabs.org>';
        mail("mzijlstra@miu.edu", "${course} Question or Comment", $message, $headers);

        return "Location: ${tab}#q${id}";
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/delQuestion$|", sec="user")
     */
    public function del()
    {
        $user_id = $_SESSION['user']['id'];
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        $question = $this->questionDao->get($id);
        if ($_SESSION['user']['type'] === 'admin' || $question['user_id'] == $_SESSION['user']['id']) {
            $this->questionDao->del($id);
        }
        return "Location: ${tab}#questionForm";
    }

    /**
     * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/getQuestion$|", sec="user")
     */
    public function get()
    {
        $id = filter_input(INPUT_GET, "id");
        return $this->questionDao->get($id);
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/updQuestion$|", sec="user")
     */
    public function update()
    {
        $user_id = $_SESSION['user']['id'];
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        // see comment inside add method about why htmlspecialchars()
        $text = htmlspecialchars(filter_input(INPUT_POST, "text"), ENT_NOQUOTES);
        $question = $this->questionDao->get($id);
        if ($_SESSION['user']['type'] === 'admin' || $question['user_id'] == $_SESSION['user']['id']) {
            $this->questionDao->update($id, $text);
        }
        return "Location: ${tab}#q${id}";
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/upvote$|", sec="user")
     */
    public function upvote()
    {
        return array("vid" => $this->vote("q", 1), "type" => "up");
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/downvote$|", sec="user")
     */
    public function downvote()
    {
        return array("vid" => $this->vote("q", -1), "type" => "down");
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/upreply$|", sec="user")
     */
    public function upreply()
    {
        return array("vid" => $this->vote("r", 1), "type" => "up");
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/downreply$|", sec="user")
     */
    public function downreply()
    {
        return array("vid" => $this->vote("r", -1), "type" => "down");
    }

    private function vote($q_r, $up_down)
    {
        $user_id = $_SESSION['user']['id'];
        $id = filter_input(INPUT_POST, "id");
        $vid = filter_input(INPUT_POST, "vid");
        $type = filter_input(INPUT_POST, "type");
        $value = $up_down;

        if ($vid == "undefined") {
            $vid = false;
        }
        if ($type == "undefined") {
            $type = false;
        }

        // if there is a previous vote like this, we're now undoing it
        if (($type == "up" && $up_down > 0) || ($type == "down" && $up_down < 0)) {
            $value = 0;
        }
        // admin votes weigh a lot more
        if ($_SESSION['user']['type'] === 'admin') {
            $value *= 10;
        }

        if ($vid) {
            // update if it exists
            if ($q_r == "q") {
                $this->questionVoteDao->update($vid, $user_id, $value);
            } else {
                $this->replyVoteDao->update($vid, $user_id, $value);
            }

            return $vid;
        } else {
            // otherwise create
            if ($q_r == "q") {
                return $this->questionVoteDao->add($id, $user_id, $value);
            } else {
                return $this->replyVoteDao->add($id, $user_id, $value);
            }
        }
    }

    /**
     * @POST(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/addReply$|", sec="user")
     */
    public function addReply()
    {
        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];
        $tab = filter_input(INPUT_POST, "tab");

        // see comment inside add method about why htmlspecialchars()
        $text = htmlspecialchars(filter_input(INPUT_POST, "text"), ENT_NOQUOTES);
        $user_id = $_SESSION['user']['id'];
        $user = $this->userDao->retrieve($user_id);
        $qid = filter_input(INPUT_POST, "id");
        $id = $this->replyDao->add($text, $user_id, $qid);

        $message = $user["knownAs"] . " " . $user["lastname"] . 
            "asks:\n\n$text\n
See reply at: http://manalabs.org/videos/${course}/${block}/${day}/${tab}#r${id}";

        $headers = 'FROM: "Manalabs Video System" <videos@manalabs.org>';
        mail("mzijlstra@miu.edu", "${course} Reply", $message, $headers);

        return "Location: ${tab}#r${id}";
    }

    /**
     * @GET(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/getReply$|", sec="user")
     */
    public function getReply()
    {
        $id = filter_input(INPUT_GET, "id");
        return $this->replyDao->get($id);
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/updReply$|", sec="user")
     */
    public function updateReply()
    {
        $user_id = $_SESSION['user']['id'];
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        // see comment inside add method about why htmlspecialchars()
        $text = htmlspecialchars(filter_input(INPUT_POST, "text"), ENT_NOQUOTES);
        $reply = $this->replyDao->get($id);
        if ($_SESSION['user']['type'] === 'admin' || $reply['user_id'] == $_SESSION['user']['id']) {
            $this->replyDao->update($id, $text);
        }
        return "Location: ${tab}#r${id}";
    }

    /**
     * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/delReply$|", sec="user")
     */
    public function delReply()
    {
        $user_id = $_SESSION['user']['id'];
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        $reply = $this->replyDao->get($id);
        if ($_SESSION['user']['type'] === 'admin' || $reply['user_id'] == $_SESSION['user']['id']) {
            $this->replyDao->del($id);
        }
        return "Location: ${tab}#q" . $reply['question_id'];
    }

}
