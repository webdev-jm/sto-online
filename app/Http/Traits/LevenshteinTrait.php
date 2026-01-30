<?php

namespace App\Http\Traits;

trait LevenshteinTrait {
    public function checkSimilarity($str1, $str2)
    {
        if ($str1 === $str2) return 100;
        $str1 = strtoupper(str_replace(' ', '', $str1));
        $str2 = strtoupper(str_replace(' ', '', $str2));
        $max_length = max(strlen($str1), strlen($str2));
        if ($max_length == 0) return 100;
        return (1 - (levenshtein($str1, $str2) / $max_length)) * 100;
    }
}
