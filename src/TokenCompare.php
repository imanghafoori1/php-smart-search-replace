<?php

namespace Imanghafoori\SearchReplace;

use Imanghafoori\SearchReplace\Tokens\Any;
use Imanghafoori\SearchReplace\Tokens\ClassRef;
use Imanghafoori\SearchReplace\Tokens\Comment;
use Imanghafoori\SearchReplace\Tokens\FullClassRef;
use Imanghafoori\SearchReplace\Tokens\InBetween;
use Imanghafoori\SearchReplace\Tokens\IsGlobalFuncCall;
use Imanghafoori\SearchReplace\Tokens\IsRepeatingPattern;
use Imanghafoori\SearchReplace\Tokens\Statement;
use Imanghafoori\SearchReplace\Tokens\Token;
use Imanghafoori\SearchReplace\Tokens\Until;
use Imanghafoori\SearchReplace\Tokens\WhiteSpace;
use Imanghafoori\TokenAnalyzer\Str;

class TokenCompare
{
    private static $placeHolders = [T_CONSTANT_ENCAPSED_STRING, T_VARIABLE, T_LNUMBER, T_STRING];

    private static $ignored = [
        T_WHITESPACE => T_WHITESPACE,
        T_COMMENT => T_COMMENT,
        //',' => ',',
    ];

    public static function readExpression($i, $tokens)
    {
        $level = 0;
        $collected = [];
        $line = 1;

        for ($k = $i; true; $k++) {
            $nextToken = $tokens[$k] ?? '_';
            $collected[] = $nextToken;

            if ($nextToken === ';' && $level === 0) {
                $value = [T_STRING, Stringify::fromTokens($collected), $line];

                return [$value, $k];
            }

            if (\in_array($nextToken[0], ['[', '(', '{', T_CURLY_OPEN], true)) {
                $level++;
            }

            if (\in_array($nextToken[0], [']', ')', '}'], true)) {
                $level--;
            }

            isset($nextToken[2]) && $line = $nextToken[2];
        }
    }

    public static function compareTokens($pattern, $tokens, $startFrom, $namedPatterns = [])
    {
        $pi = $j = 0;
        $tCount = count($tokens);
        $pCount = count($pattern);
        $repeatings = $placeholderValues = [];

        $tToken = $tokens[$startFrom];
        $pToken = $pattern[$j];

         $keywords = [
             FullClassRef::class,
             ClassRef::class,
             Statement::class,
             IsRepeatingPattern::class,
             IsGlobalFuncCall::class,
             Until::class,
             InBetween::class,
             Any::class,
             WhiteSpace::class,
             Comment::class,
             Token::class,
         ];

        while ($startFrom < $tCount && $j < $pCount) {
            foreach($keywords as $class_token) {
                 if($class_token::is($pToken, $namedPatterns)) {
                     if($class_token::mustStart($tToken, $tokens, $startFrom, $placeholderValues, $pToken, $pattern, $pi, $j, $namedPatterns, $repeatings) === false) {
                         return false;
                     } else {
                         break;
                     }
                 }
             }

            [$pToken, $j] = self::getNextToken($pattern, $j);

            $pi = $startFrom;
            [$tToken, $startFrom] = self::forwardToNextToken($pToken, $tokens, $startFrom);
        }

        if ($pCount === $j) {
            return [$pi, $placeholderValues, $repeatings,];
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
            } elseif (self::is($pToken, '<bool>') || self::is($pToken, '<boolean>')) {
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

    private static function getNextToken($tokens, $i, $notIgnored = null)
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

    public static function getAnti(string $startingToken)
    {
        return [
            '(' => ')',
            '{' => '}',
            '[' => ']',
        ][$startingToken];
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
        $pToken = $patternTokens[$pIndex];
        $optionalStartingTokens = array_slice($patternTokens, 0, $pIndex);

        $matches = [];
        $i = $startFrom;
        $allCount = count($tokens);

        while ($i < $allCount) {
            if (! IsStarting::check($namedPatterns, $pToken, $tokens, $i)) {
                $i++;
                continue;
            }

            [$optionalPatternMatchCount, $matched_optional_values] = self::optionalStartingTokens($optionalStartingTokens, $tokens, $i);

            $restPatternTokens = array_slice($patternTokens, $pIndex);
            $isMatch = self::compareTokens($restPatternTokens, $tokens, $i, $namedPatterns);
            if (! $isMatch) {
                $i++;
                continue;
            }

            [$k, $matchedValues, $repeatings] = $isMatch;
            $matchedValues = array_merge($matched_optional_values, $matchedValues);
            $data = ['start' => $i - $pIndex, 'end' => $k, 'values' => $matchedValues, 'repeatings' => $repeatings];
            if (Filters::apply($filters, $data, $tokens)) {
                if (! $predicate || call_user_func($predicate, $data, $tokens)) {
                    $mutator && $matchedValues = call_user_func($mutator, $matchedValues);
                    $matches[] = ['start' => $i - $optionalPatternMatchCount, 'end' => $k, 'values' => $matchedValues, 'repeatings' => $repeatings];
                    if (count($matches) === $maxDepth) {
                        return $matches;
                    }
                }
            }

            $k > $i && $i = $k - 1; // fast-forward
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

    public static function findRepeatingMatches($startFrom, $tokens, $analyzedPattern)
    {
        $repeatingMatches = [];
        $end = $startFrom;
        while (true) {
            $isMatch = self::compareTokens($analyzedPattern, $tokens, $startFrom, []);

            if (! $isMatch) {
                break;
            }

            $end = $isMatch[0];
            [, $startFrom] = self::getNextToken($tokens, $end);
            $repeatingMatches[] = $isMatch[1];
        }

        return [$repeatingMatches, $end];
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
            $_matchedValues = TokenCompare::getMatches(PatternParser::tokenize($pattern), $newTokens);
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

    public static function isGlobalFuncCall($pToken)
    {
        if ($pToken[0] === T_CONSTANT_ENCAPSED_STRING && self::startsWith($pName = trim($pToken[1], '\'\"'), '<global_func_call:')) {
            return rtrim(Str::replaceFirst('<global_func_call:', '', $pName), '>');
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

    public static function readUntilMatch($i, $tokens, $startingToken)
    {
        $anti = self::getAnti($startingToken);
        $untilTokens = [];
        $line = 1;
        $level = 0;
        for ($k = $i + 1; true; $k++) {
            if ($tokens[$k] === $anti && $level === 0) {
                break;
            }

            $tokens[$k] === $startingToken && $level--;
            $tokens[$k] === $anti && $level++;

            ! $line && isset($tokens[$k][2]) && $line = $tokens[$k][2];
            $untilTokens[] = $tokens[$k];
        }

        $startFrom = $k - 1;
        $value = [T_STRING, Stringify::fromTokens($untilTokens), $line];

        return [$value, $startFrom];
    }

    public static function readUntil($pi, $tokens, $pattern)
    {
        $untilTokens = [];
        $line = 1;
        for ($k = $pi + 1; $tokens[$k] !== $pattern; $k++) {
            ! $line && isset($tokens[$k][2]) && $line = $tokens[$k][2];
            $untilTokens[] = $tokens[$k];
        }
        $placeholderValue = [T_STRING, Stringify::fromTokens($untilTokens), $line];

        return [$placeholderValue, $k - 1];
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

    public static function concatinate(array $matches)
    {
        $segments = [''];
        foreach ($matches as $match) {
            $segments[] = $match[1];
        }

        return [T_STRING, implode('\\', $segments), $match[2]];
    }
}
