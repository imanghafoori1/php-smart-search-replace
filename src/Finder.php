<?php

namespace Imanghafoori\SearchReplace;

use Imanghafoori\SearchReplace\Keywords;
use Imanghafoori\TokenAnalyzer\Str;

class Finder
{
    public static $keywords = [
        Keywords\FullClassRef::class,
        Keywords\ClassRef::class,
        Keywords\Statement::class,
        Keywords\RepeatingPattern::class,
        Keywords\GlobalFunctionCall::class,
        Keywords\Until::class,
        Keywords\InBetween::class,
        Keywords\Any::class,
        Keywords\Variable::class,
        Keywords\Number::class,
        Keywords\Integer::class,
        Keywords\FloatNum::class,
        Keywords\DocBlock::class,
        Keywords\WhiteSpace::class,
        Keywords\Comment::class,
        Keywords\Keyword::class,
    ];

    private static $placeHolders = [
        T_CONSTANT_ENCAPSED_STRING,
        T_VARIABLE,
        T_LNUMBER,
        T_STRING,
    ];

    private static $ignored = [
        T_WHITESPACE => T_WHITESPACE,
        T_COMMENT => T_COMMENT,
        //',' => ',',
    ];

    public static function compareTokens($pattern, $tokens, $startFrom, $namedPatterns = [])
    {
        $pi = $j = 0;
        $tCount = count($tokens);
        $pCount = count($pattern);
        $repeating = $placeholderValues = [];

        $pToken = $pattern[$j];

        while ($startFrom < $tCount && $j < $pCount) {
            foreach (self::$keywords as $class_token) {
                if ($class_token::is($pToken, $namedPatterns)) {
                    if ($class_token::getValue($tokens, $startFrom, $placeholderValues, $pToken, $pattern, $pi, $j, $namedPatterns, $repeating) === false) {
                        return false;
                    } else {
                        break;
                    }
                }
            }

            [$pToken, $j] = self::getNextToken($pattern, $j);

            $pi = $startFrom;
            [, $startFrom] = self::forwardToNextToken($pToken, $tokens, $startFrom);
        }

        if ($pCount === $j) {
            return [$pi, $placeholderValues, $repeating,];
        }

        return false;
    }

    private static function compareOptionalTokens($patternTokens, $tokens, $startFrom)
    {
        $pCount = count($patternTokens);
        $j = $pCount - 1;
        $placeholderValues = [];

        $tToken = $tokens[$startFrom];
        $pToken = $patternTokens[$j];

        while ($tToken && $j !== -1) {
            if (self::is($pToken, '<any>')) {
                $placeholderValues[] = $tToken;
                $startFrom--;
            } elseif (self::is($pToken, ['<bool>', '<boolean>'])) {
                if (self::isBooleanToken($tToken)) {
                    $placeholderValues[] = $tToken;
                    $startFrom--;
                } else {
                    $placeholderValues[] = [T_WHITESPACE, ''];
                }
            } else {
                $name = trim($pToken[1], '\'\"?');
                $map = [
                    "<white_space>" => T_WHITESPACE,
                    "<comment>" => T_COMMENT,
                    "<string>" => T_CONSTANT_ENCAPSED_STRING,
                    "<str>" => T_CONSTANT_ENCAPSED_STRING,
                    "<variable>" => T_VARIABLE,
                    "<var>" => T_VARIABLE,
                    "<number>" => T_LNUMBER,
                    "<name>" => T_STRING,
                    "<,>" => ',',
                ];

                if ($tToken[0] === $map[$name]) {
                    $placeholderValues[] = $tToken;
                    $startFrom--;
                } else {
                    $placeholderValues[] = [T_WHITESPACE, ''];
                }
            }
            $j--;

            if (! isset($patternTokens[$j])) {
                return array_reverse($placeholderValues);
            }
            $pToken = $patternTokens[$j];
            $tToken = $tokens[$startFrom];
        }
    }

    public static function getNextToken($tokens, $i, $notIgnored = null)
    {
        $ignored = self::$ignored;

        if ($notIgnored) {
            unset($ignored[$notIgnored]);
        }

        $i++;
        $token = $tokens[$i] ?? '_';
        while (in_array($token[0], $ignored, true)) {
            $i++;
            $token = $tokens[$i] ?? [null, null];
        }

        return [$token, $i];
    }

    public static function is($token, $keyword)
    {
        return $token[0] === T_CONSTANT_ENCAPSED_STRING && in_array(trim($token[1], '\'\"?'), (array) $keyword, true);
    }

    public static function isOptional($token)
    {
        return self::endsWith(trim($token, '\'\"'), '?');
    }

    public static function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    public static function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    public static function areTheSame($pToken, $token)
    {
        if ($pToken[0] !== $token[0]) {
            return false;
        }

        if (in_array($pToken[0], self::$placeHolders, true) && ! isset($pToken[1])) {
            return 'placeholder';
        }

        if (! isset($pToken[1]) || ! isset($token[1])) {
            return true;
        }

        if ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
            return trim($pToken[1], '\'\"') === trim($token[1], '\'\"');
        }

        if ($pToken[0] === T_STRING && (in_array(strtolower($pToken[1]), ['true', 'false', 'null'], true))) {
            return strtolower($pToken[1]) === strtolower($token[1]);
        }

        return $pToken[1] === $token[1];
    }

    public static function getMatches(
        $patternTokens,
        $tokens,
        $predicate = null,
        $mutator = null,
        $namedPatterns = [],
        $filters = [],
        $startFrom = 1,
        $maxDepth = 200
    ) {
        $pIndex = self::firstNonOptionalPlaceholder($patternTokens);
        $optionalStartingTokens = array_slice($patternTokens, 0, $pIndex);

        $matches = [];
        $i = $startFrom;
        $allCount = count($tokens);

        while ($i < $allCount) {
            $restPatternTokens = array_slice($patternTokens, $pIndex);
            $isMatch = self::compareTokens($restPatternTokens, $tokens, $i, $namedPatterns);
            if (! $isMatch) {
                $i++;
                continue;
            }

            [$optionalPatternMatchCount, $matched_optional_values] = self::optionalStartingTokens($optionalStartingTokens, $tokens, $i);

            [$end, $matchedValues, $repeatings] = $isMatch;
            $matchedValues = array_merge($matched_optional_values, $matchedValues);
            $data = ['start' => $i - $pIndex, 'end' => $end, 'values' => $matchedValues, 'repeatings' => $repeatings];
            if (Filters::apply($filters, $data, $tokens)) {
                if (! $predicate || call_user_func($predicate, $data, $tokens)) {
                    $mutator && $matchedValues = call_user_func($mutator, $matchedValues);
                    $matches[] = ['start' => $i - $optionalPatternMatchCount, 'end' => $end, 'values' => $matchedValues, 'repeatings' => $repeatings];
                    if (count($matches) === $maxDepth) {
                        return $matches;
                    }
                }
            }

            $end > $i && $i = $end - 1; // fast-forward
            $i++;
        }

        return $matches;
    }

    public static function compareIt($tToken, int $type, $token, &$i)
    {
        if ($tToken[0] === $type) {
            return $tToken;
        }

        if (self::isOptional($token)) {
            $i--;

            return [T_WHITESPACE, ''];
        }
    }

    private static function forwardToNextToken($pToken, $tokens, $startFrom)
    {
        if (self::is($pToken, '<white_space>')) {
            return self::getNextToken($tokens, $startFrom, T_WHITESPACE);
        } elseif (self::is($pToken, '<comment>')) {
            return self::getNextToken($tokens, $startFrom, T_COMMENT);
        } else {
            return self::getNextToken($tokens, $startFrom);
        }
    }

    public static function matchesAny($avoidResultIn, $newTokens)
    {
        foreach ($avoidResultIn as $pattern) {
            $_matchedValues = Finder::getMatches(PatternParser::tokenize($pattern), $newTokens);
            if ($_matchedValues) {
                return true;
            }
        }

        return false;
    }

    public static function isRepeatingPattern($pToken)
    {
        if ($pToken[0] === T_CONSTANT_ENCAPSED_STRING && self::startsWith($pName = trim($pToken[1], '\'\"'), '<repeating:')) {
            return rtrim(Str::replaceFirst('<repeating:', '', $pName), '>');
        }
    }

    public static function isOptionalPlaceholder($token)
    {
        if ($token[0] !== T_CONSTANT_ENCAPSED_STRING) {
            return false;
        }

        $optionals = [
            "<any>?",
            "<white_space>?",
            "<comment>?",
            "<string>?",
            "<str>?",
            "<variable>?",
            "<var>?",
            "<number>?",
            "<num>?",
            "<name>?",
            "<boolean>?",
            "<bool>?",
            "<,>?",
        ];

        $name = trim($token[1], '\"\'');

        return in_array($name, $optionals, true);
    }

    public static function getPortion($start, $end, $tokens)
    {
        $output = '';
        for ($i = $start - 1; $i < $end; $i++) {
            $output .= $tokens[$i][1] ?? $tokens[$i][0];
        }

        return $output;
    }

    private static function firstNonOptionalPlaceholder($patternTokens)
    {
        $i = 0;
        foreach ($patternTokens as $i => $pt) {
            if (! self::isOptionalPlaceholder($pt)) {
                return $i;
            }
        }

        return $i;
    }

    private static function optionalStartingTokens($optionalStartingTokens, $tokens, $i)
    {
        $optionalPatternMatchCount = 0;
        if ($optionalStartingTokens) {
            $matched_optional_values = self::compareOptionalTokens($optionalStartingTokens, $tokens, $i - 1);
            foreach ($matched_optional_values as $xToken1) {
                if ($xToken1 !== [T_WHITESPACE, '']) {
                    $optionalPatternMatchCount++;
                }
            }
        } else {
            $matched_optional_values = [];
        }

        return [$optionalPatternMatchCount, $matched_optional_values];
    }

    private static function isBooleanToken($tToken)
    {
        return $tToken[0] === T_STRING && in_array(strtolower($tToken[1]), ['true', 'false']);
    }

    public static function extractValue($matches, $first = '')
    {
        $segments = [$first];
        
        foreach ($matches as $match) {
            $segments[] = $match[0][1];
        }

        return [T_STRING, implode('\\', $segments), $match[0][2]];
    }
}
