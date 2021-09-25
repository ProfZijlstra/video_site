<?php

/**
 * Question Controller Class
 * @author mzijlstra 09/24/2021
 *
 * @Controller
 */
class QuestionCtrl {

	/**
	 * @Inject("QuestionDao")
	 */
	public $questionDao;

   	/**
	 * @POST(uri="|^/(cs\d{3})/(20\d{2}-\d{2})/(W[1-4]D[1-7])/question$|", sec="user")
	 */
	public function add() {
        global $URI_PARAMS;
		$course = $URI_PARAMS[1];
        $block = $URI_PARAMS[2];
        $day = $URI_PARAMS[3];

        $user_id = $_SESSION['user']['id'];
		$video = filter_input(INPUT_POST, "video");
        // using the capabilities of filter_input changes all html entities, 
        // breaking markdown syntax -- so we fall back to htmlspecialchars()
        $question = htmlspecialchars(filter_input(INPUT_POST, "question"));
        $tab = filter_input(INPUT_POST, "tab");
        $id = $this->questionDao->add($question, $user_id, $video);

        $message = "See question at: http://manalabs.org/${course}/${block}/${tab}#${id}";
        mail("mzijlstra@miu.edu", "${course} Question or Comment", $message);

        return "Location: ${tab}#q${id}";
	}

    /**
	 * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/delQuestion$|", sec="user")
	 */
	public function del() {
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
    public function get() {
        $id = filter_input(INPUT_GET, "qid");
        return $this->questionDao->get($id);
    }

    /**
	 * @POST(uri="|^/cs\d{3}/20\d{2}-\d{2}/W[1-4]D[1-7]/updQuestion$|", sec="user")
	 */
    public function update() {
        $user_id = $_SESSION['user']['id'];
		$id = filter_input(INPUT_POST, "id");
        $tab = filter_input(INPUT_POST, "tab");
        // see comment inside add method about why htmlspecialchars()
        $text = htmlspecialchars(filter_input(INPUT_POST, "text"));
        $question = $this->questionDao->get($id);
        if ($_SESSION['user']['type'] === 'admin' || $question['user_id'] == $_SESSION['user']['id']) {
            $this->questionDao->update($id, $text);
        }
        return "Location: ${tab}#q${id}";
    }
}

?>