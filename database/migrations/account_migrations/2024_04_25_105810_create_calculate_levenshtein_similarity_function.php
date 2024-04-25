<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCalculateLevenshteinSimilarityFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE FUNCTION CalculateLevenshteinSimilarity(str1 VARCHAR(255), str2 VARCHAR(255)) RETURNS decimal(5,2)
            BEGIN
                DECLARE len1 INT;
                DECLARE len2 INT;
                DECLARE maxLen INT;
                DECLARE distance INT;
                DECLARE similarity DECIMAL(5,2);
            
                -- Remove spaces from the strings
                SET str1 = REPLACE(str1, " ", "");
                SET str2 = REPLACE(str2, " ", "");
            
                -- Get the lengths of the strings
                SET len1 = CHAR_LENGTH(str1);
                SET len2 = CHAR_LENGTH(str2);
            
                -- Determine the maximum length
                SET maxLen = GREATEST(len1, len2);
            
                -- Calculate Levenshtein distance
                SET distance = LevenshteinDistance(str1, str2);
            
                -- Calculate similarity percentage
                SET similarity = 100 * (1 - distance / maxLen);
            
                RETURN similarity;
            END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROM FUNCTION IF EXISTS CalculateLevenshteinSimilarity');
    }
}
