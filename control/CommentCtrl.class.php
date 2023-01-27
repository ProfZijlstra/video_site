<?php

/**
 * Comment Controller Class
 * @author mzijlstra 09/24/2021
 *
 * @Controller
 */
class CommentCtrl
{

    /**
     * @Inject("CommentDao")
     */
    public $commentDao;

    /**
     * @Inject("CommentVoteDao")
     */
    public $commentVoteDao;

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
     * @Inject('MarkdownCtrl')
     */
    public $markdownCtrl;

    /**
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/comment$!", sec="applicant")
     */
    public function add()
    {
        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $user_id = $_SESSION['user']['id'];
        $video = filter_input(INPUT_POST, "video");
        $shifted = filter_input(INPUT_POST, "text");
        $tab = filter_input(INPUT_POST, "tab");

        $comment = $this->markdownCtrl->ceasarShift($shifted);
        $id = $this->commentDao->add($comment, $user_id, $video);
        $user = $this->userDao->retrieve($user_id);

        $message = $user["knownAs"] . " " . $user["lastname"] . 
            " asks:\n\n$comment\n
See comment at: http://manalabs.org/videos/{$course}/{$block}/{$day}/{$tab}#r{$id}";

        $headers = 'From: "Manalabs Video System" <videos@manalabs.org> \r\n';
        mail("mzijlstra@miu.edu", "{$course} Comment or Comment", $message, $headers);

        return "Location: {$tab}#q{$id}";
    }

    /**
     * @POST(uri="!^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/delComment$!", sec="applicant")
     */
    public function del()
    {
        $user_id = $_SESSION['user']['id'];
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        $comment = $this->commentDao->get($id);
        if ($_SESSION['user']['type'] === 'admin' || $comment['user_id'] == $_SESSION['user']['id']) {
            $this->commentDao->del($id);
        }
        return "Location: {$tab}#commentForm";
    }

    /**
     * @GET(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/getComment$!", sec="applicant")
     */
    public function get()
    {
        $id = filter_input(INPUT_GET, "id");
        return $this->commentDao->get($id);
    }

    /**
     * @POST(uri="!^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/updComment$!", sec="applicant")
     */
    public function update()
    {
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        $shifted = filter_input(INPUT_POST, "text");

        $text = $this->markdownCtrl->ceasarShift($shifted);
        $comment = $this->commentDao->get($id);

        if ($_SESSION['user']['type'] === 'admin' || $comment['user_id'] == $_SESSION['user']['id']) {
            $this->commentDao->update($id, $text);
        }

        return "Location: {$tab}#q{$id}";
    }

    /**
     * @POST(uri="!^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/upvote$!", sec="applicant")
     */
    public function upvote()
    {
        return array("vid" => $this->vote("q", 1), "type" => "up");
    }

    /**
     * @POST(uri="!^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/downvote$!", sec="applicant")
     */
    public function downvote()
    {
        return array("vid" => $this->vote("q", -1), "type" => "down");
    }

    /**
     * @POST(uri="!^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/upreply$!", sec="applicant")
     */
    public function upreply()
    {
        return array("vid" => $this->vote("r", 1), "type" => "up");
    }

    /**
     * @POST(uri="!^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/downreply$!", sec="applicant")
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
                $this->commentVoteDao->update($vid, $user_id, $value);
            } else {
                $this->replyVoteDao->update($vid, $user_id, $value);
            }

            return $vid;
        } else {
            // otherwise create
            if ($q_r == "q") {
                return $this->commentVoteDao->add($id, $user_id, $value);
            } else {
                return $this->replyVoteDao->add($id, $user_id, $value);
            }
        }
    }

    /**
     * @POST(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/addReply$!", sec="applicant")
     */
    public function addReply()
    {
        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];
        $tab = filter_input(INPUT_POST, "tab");
        $shifted = filter_input(INPUT_POST, "text");
        $user_id = $_SESSION['user']['id'];

        $text = $this->markdownCtrl->ceasarShift($shifted);
        $user = $this->userDao->retrieve($user_id);
        $qid = filter_input(INPUT_POST, "id");
        $op_email = $this->commentDao->getUserEmail($qid);
        $id = $this->replyDao->add($text, $user_id, $qid);

        $message = $user["knownAs"] . " " . $user["lastname"] . 
            " says:\n\n$text\n
See reply at: http://manalabs.org/videos/{$course}/{$block}/{$day}/{$tab}#r{$id}";

        $headers = 'From: "Manalabs Video System" <videos@manalabs.org> \r\n';
        mail($op_email, "$course Reply", $message, $headers);
        mail("mzijlstra@miu.edu", "{$course} Reply", $message, $headers);

        return "Location: {$tab}#r{$id}";
    }

    /**
     * @GET(uri="!^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/getReply$!", sec="applicant")
     */
    public function getReply()
    {
        $id = filter_input(INPUT_GET, "id");
        return $this->replyDao->get($id);
    }

    /**
     * @POST(uri="!^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/updReply$!", sec="applicant")
     */
    public function updateReply()
    {
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        $shifted =filter_input(INPUT_POST, "text");

        $text = $this->markdownCtrl->ceasarShift($shifted);
        $reply = $this->replyDao->get($id);
        if ($_SESSION['user']['type'] === 'admin' || $reply['user_id'] == $_SESSION['user']['id']) {
            $this->replyDao->update($id, $text);
        }
        
        return "Location: {$tab}#r{$id}";
    }

    /**
     * @POST(uri="!^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/delReply$!", sec="applicant")
     */
    public function delReply()
    {
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");

        $reply = $this->replyDao->get($id);
        if ($_SESSION['user']['type'] === 'admin' || $reply['user_id'] == $_SESSION['user']['id']) {
            $this->replyDao->del($id);
        }

        return "Location: {$tab}#q" . $reply['comment_id'];
    }

}
