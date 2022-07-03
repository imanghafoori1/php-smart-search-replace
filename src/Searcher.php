<?php

namespace Imanghafoori\SearchReplace;

class Searcher
{
    public static function search($patterns, $tokens, $maxMatches = null)
    {
        return self::searchReplaceMultiplePatterns(PatternParser::parsePatterns($patterns), $tokens, $maxMatches);
    }

    public static function searchReplace($patterns, $tokens, $maxMatches = null)
    {
        [$tokens, $replacementLines] = self::search($patterns, $tokens, $maxMatches);

        return [Stringify::fromTokens($tokens), $replacementLines];
    }
    public static function searchFirst($patterns, $tokens)
    {
        return self::searchReplaceMultiplePatterns(PatternParser::parsePatterns($patterns), $tokens, 1);
    }

    public static function searchReplaceFirst($patterns, $tokens)
    {
        [$tokens, $replacementLines] = self::search($patterns, $tokens, 1);

        return [Stringify::fromTokens($tokens), $replacementLines];
    }

    public static function searchReplaceMultiplePatterns($parsedPatterns, $tokens, $maxMatches = null)
    {
        $replacementAllLines = [];

        foreach ($parsedPatterns as $pattern) {
            [$tokens, $replacementLines] = self::searchReplaceOnePattern($pattern, $tokens, $maxMatches);

            $replacementAllLines = array_merge($replacementAllLines, $replacementLines);
        }

        return [$tokens, $replacementAllLines];
    }

    public static function searchReplaceOnePattern($pattern, $tokens, $maxMatches = null)
    {
        $result = Finder::getMatches(
            $pattern['search'],
            $tokens,
            $pattern['predicate'],
            $pattern['mutator'],
            $pattern['named_patterns'],
            $pattern['filters'],
            1,
            $maxMatches
        );

        [
            $tokens,
            $replacementLines,
        ] = Replacer::applyAllMatches(
            $result,
            $pattern['replace'],
            $tokens,
            $pattern['named_patterns'],
            $pattern['avoid_syntax_errors']
        );

        isset($pattern['post_replace']) && [$tokens] = PostReplace::applyPostReplaces($pattern['post_replace'], $tokens);

        return [$tokens, $replacementLines];
    }

    /*private static function applyPatterns($patterns, $matches, $tokens)
    {
        $replacePatterns = array_values($patterns);

        $replacementLines = [];
        foreach ($matches as $pi => $patternMatch) {
            [$tokens, $replacementLines] = self::applyAllMatches($patternMatch, $replacePatterns[$pi]['replace'], $tokens, $replacementLines);
        }

        return [$tokens, $replacementLines];
    }*/

    public static function searchParsed($patterns, $tokens)
    {
        return self::searchReplaceMultiplePatterns(PatternParser::parsePatterns($patterns,false), $tokens);
    }
}
