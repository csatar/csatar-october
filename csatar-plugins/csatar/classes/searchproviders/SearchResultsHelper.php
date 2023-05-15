<?php

namespace Csatar\Csatar\Classes\SearchProviders;

class SearchResultsHelper
{

    public static function getMatchForQuery($query, $title, $content)
    {
        // check if the query is in the title
        if (stripos($title, $query) !== false) {
            return $title;
        }

        $content = strip_tags($content);
        // check if the query is in the content return content with query and 2 words before and after
        if (preg_match_all("/(?:\w+\W+){0,2}\b$query\b(?:\W+\w+){0,2}/u", $content, $matches)) {
            $result = implode(' ', $matches[0]);

            if (strpos($content, $query) !== 0) {
                $result = "..." . $result;
            }
            if (strpos($content, $query) !== (strlen($content) - strlen($content))) {
                $result = $result . "...";
            }

            return $result;
        }
    }

}
