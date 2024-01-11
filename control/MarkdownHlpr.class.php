<?php

/**
 * Markdown Controller Class
 * @author mzijlstra 12/30/2022
 */

#[Controller]
class MarkdownHlpr
{
    /**
     * AJAX call to get a markdown preview
     */
    #[Get(uri: "!^/([a-z]{2,3}\d{3,4})/(20\d{2}-\d{2}[^/]*)/markdown$!", sec: "observer")]
    public function markdownPreview()
    {
        require_once("lib/Parsedown.php");
        global $VIEW_DATA;

        $shifted = filter_input(INPUT_GET, "markdown");
        $markdown = $this->ceasarShift($shifted);

        $VIEW_DATA["parsedown"] = new Parsedown();
        $VIEW_DATA['markdown'] = $markdown;
        return "markdown.php";
    }

    public function ceasarShift($text, $amount = -1)
    {
        $result = "";
        $chars = str_split($text);
        foreach ($chars as $char) {
            $code = ord($char) + $amount;
            $result .= chr($code);
        }
        return $result;
    }
}

