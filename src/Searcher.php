<?php

namespace Imanghafoori\SearchReplace;

class Searcher
{
    public static function search($patterns, $tokens)
    {
        return self::searchReplaceMultiplePatterns(PatternParser::parsePatterns($patterns), $tokens);
    }

    public static function searchReplace($patterns, $tokens)
    {
        [$tokens, $replacementLines] = self::search($patterns, $tokens);

        return [Stringify::fromTokens($tokens), $replacementLines];
    }

    public static function searchReplaceMultiplePatterns($parsedPatterns, $tokens)
    {
        $replacementAllLines = [];

        foreach ($parsedPatterns as $pattern) {
            [$tokens, $replacementLines] = self::searchReplaceOnePattern($pattern, $tokens);

            $replacementAllLines = array_merge($replacementAllLines, $replacementLines);
        }

        return [$tokens, $replacementAllLines];
    }

    public static function searchReplaceOnePattern($pattern, $tokens)
    {
        $result = Finder::getMatches(
            $pattern['search'],
            $tokens,
            $pattern['predicate'],
            $pattern['mutator'],
            $pattern['named_patterns'],
            $pattern['filters']
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
