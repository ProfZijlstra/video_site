<?php

/**
 * Comment Controller Class
 * @author mzijlstra 09/24/2021
 */

#[Controller(path: "^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/(W\d+D\d+)")]
class CommentCtrl
{

    #[Inject('CommentDao')]
    public $commentDao;

    #[Inject('CommentVoteDao')]
    public $commentVoteDao;

    #[Inject('ReplyDao')]
    public $replyDao;

    #[Inject('ReplyVoteDao')]
    public $replyVoteDao;

    #[Inject('UserDao')]
    public $userDao;

    #[Inject('MarkdownHlpr')]
    public $markdownCtrl;

    #[Inject('MailHlpr')]
    public $mailHlpr;

    #[Inject('EnrollmentDao')]
    public $enrollmentDao;


    #[Post(uri: "/comment$", sec: "observer")]
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

        $comment = "";
        if ($shifted) {
            $comment = trim($this->markdownCtrl->ceasarShift($shifted));
        }
        if (!$comment) {
            return "Location: {$tab}#commentForm";
        }

        $ins = $this->enrollmentDao->getTopInstructorFor($course, $block);
        $to = [$ins['email'], $ins['teamsName']];
        $id = $this->commentDao->add($comment, $user_id, $video);
        $user = $this->userDao->retrieve($user_id);

        $message = $user["knownAs"] . " " . $user["lastname"] .
            " asks:\n\n$comment\n
See comment at: http://manalabs.org/videos/{$course}/{$block}/{$day}/{$tab}#r{$id}";

        $subject = "{$course} Question or Comment";
        $this->mailHlpr->mail($to, $subject, $message);

        return "Location: {$tab}#q{$id}";
    }


    #[Post(uri: "/delComment$", sec: "observer")]
    public function del()
    {
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        $comment = $this->commentDao->get($id);
        if ($_SESSION['user']['isAdmin'] == 1 || $comment['user_id'] == $_SESSION['user']['id']) {
            $this->commentDao->del($id);
        }
        return "Location: {$tab}#commentForm";
    }

    #[Get(uri: "/getComment$", sec: "observer")]
    public function get()
    {
        $id = filter_input(INPUT_GET, "id");
        return $this->commentDao->get($id);
    }


    #[Post(uri: "/updComment$", sec: "observer")]
    public function update()
    {
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        $shifted = filter_input(INPUT_POST, "text");

        $text = "";
        if ($shifted) {
            $text = $this->markdownCtrl->ceasarShift($shifted);
        }
        $comment = $this->commentDao->get($id);

        if ($_SESSION['user']['isAdmin'] == 1 || $comment['user_id'] == $_SESSION['user']['id']) {
            $this->commentDao->update($id, $text);
        }

        return "Location: {$tab}#q{$id}";
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/upvote$", sec: "observer")]
    public function upvote()
    {
        return array("vid" => $this->vote("q", 1), "type" => "up");
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/downvote$", sec: "observer")]
    public function downvote()
    {
        return array("vid" => $this->vote("q", -1), "type" => "down");
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/upreply$", sec: "observer")]
    public function upreply()
    {
        return array("vid" => $this->vote("r", 1), "type" => "up");
    }

    /**
     * Expects AJAX
     */
    #[Post(uri: "/downreply$", sec: "observer")]
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
        if ($_SESSION['user']['isAdmin'] == 1) {
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


    #[Post(uri: "/addReply$", sec: "observer")]
    public function addReply()
    {
        global $URI_PARAMS;
        $course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];
        $tab = filter_input(INPUT_POST, "tab");
        $shifted = filter_input(INPUT_POST, "text");
        $user_id = $_SESSION['user']['id'];
        $qid = filter_input(INPUT_POST, "id");

        $text = "";
        if ($shifted) {
            $text = trim($this->markdownCtrl->ceasarShift($shifted));
        }
        if (!$text) {
            return "Location: {$tab}#q{$qid}";
        }

        $user = $this->userDao->retrieve($user_id);
        $op_email = $this->commentDao->getUserEmail($qid);
        $id = $this->replyDao->add($text, $user_id, $qid);
        $ins = $this->enrollmentDao->getTopInstructorFor($course, $block);
        $ins_to = [$ins['email'], $ins['teamsName']];

        $message = $user["knownAs"] . " " . $user["lastname"] .
            " says:\n\n$text\n
See reply at: http://manalabs.org/videos/{$course}/{$block}/{$day}/{$tab}#r{$id}";

        $this->mailHlpr->mail($op_email, "{$course} Reply", $message);
        $this->mailHlpr->mail($ins_to, "{$course} Reply", $message);

        return "Location: {$tab}#r{$id}";
    }

    #[Get(uri: "/getReply$", sec: "observer")]
    public function getReply()
    {
        $id = filter_input(INPUT_GET, "id");
        return $this->replyDao->get($id);
    }


    #[Post(uri: "/updReply$", sec: "observer")]
    public function updateReply()
    {
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        $shifted = filter_input(INPUT_POST, "text");

        $text = "";
        if ($shifted) {
            $text = $this->markdownCtrl->ceasarShift($shifted);
        }
        $reply = $this->replyDao->get($id);
        if ($_SESSION['user']['isAdmin'] == 1 || $reply['user_id'] == $_SESSION['user']['id']) {
            $this->replyDao->update($id, $text);
        }

        return "Location: {$tab}#r{$id}";
    }


    #[Post(uri: "/delReply$", sec: "observer")]
    public function delReply()
    {
        $id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");

        $reply = $this->replyDao->get($id);
        if ($_SESSION['user']['isAdmin'] == 1 || $reply['user_id'] == $_SESSION['user']['id']) {
            $this->replyDao->del($id);
        }

        return "Location: {$tab}#q" . $reply['comment_id'];
    }
}
