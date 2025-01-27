<?php

namespace Imanghafoori\SearchReplace;

class Tokens
{
    public static $keywords = [
        Keywords\Any::class,
        Keywords\Variable::class,
        Keywords\Number::class,
        Keywords\Integer::class,
        Keywords\FloatNum::class,
        Keywords\Str::class,
        Keywords\Name::class,
        Keywords\DocBlock::class,
        Keywords\WhiteSpace::class,
        Keywords\BlackSpace::class,
        Keywords\Comment::class,
        Keywords\MethodVisibility::class,
        Keywords\Boolean::class,
        Keywords\Cast::class,
        Keywords\FullClassRef::class,
        Keywords\ClassRef::class,
        Keywords\GlobalFunctionCall::class,
        Keywords\InBetween::class,
        Keywords\Statement::class,
        Keywords\Comparison::class,
        Keywords\Until::class,
    ];

    private static $ignored = [
        T_WHITESPACE => T_WHITESPACE,
        T_COMMENT => T_COMMENT,
        //',' => ',',
    ];

    public static function compareTokens($pattern, $tokens, $startFrom, $namedPatterns = [], $ignoreWhitespace = true)
    {
        $pi = $j = 0;
        $tCount = count($tokens);
        $pCount = count($pattern);
        $repeating = $placeholderValues = [];

        $pToken = $pattern[$j];

        while ($startFrom < $tCount && $j < $pCount) {
            if ($pToken[0] === T_CONSTANT_ENCAPSED_STRING && $pToken[1][1] === '<') {
                $trimmed = trim($pToken[1], '\'\"?');
                if (Finder::startsWith($trimmed, '<repeating:')) {
                    if (false === Keywords\RepeatingPattern::getValue($startFrom, $repeating, $tokens, $pToken, $namedPatterns)) {
                        return false;
                    }
                } elseif (false === self::checkKeywords($startFrom, $placeholderValues, $trimmed, $tokens, $pToken, $pattern, $pi, $j)) {
                    return false;
                }
            } elseif (! Finder::areTheSame($pToken, $tokens[$startFrom])) {
                return false;
            }

            [$pToken, $j] = self::getNextToken($pattern, $j, $ignoreWhitespace ? null : T_WHITESPACE);

            $pi = $startFrom;

            if ($ignoreWhitespace) {
                [, $startFrom] = self::forwardToNextToken($pToken, $tokens, $startFrom);
            } else {
                [, $startFrom] = self::getNextToken($tokens, $startFrom, T_WHITESPACE);
            }
        }

        if ($pCount === $j) {
            return [$pi, $placeholderValues, $repeating,];
        }

        return false;
    }

    private static function forwardToNextToken($pToken, $tokens, $startFrom)
    {
        if (isset($pToken[1]) && (self::is($pToken, ['<white_space>', '<not_whitespace>']))) {
            return self::getNextToken($tokens, $startFrom, T_WHITESPACE);
        } elseif (isset($pToken[1]) && self::is($pToken, '<comment>')) {
            return self::getNextToken($tokens, $startFrom, T_COMMENT);
        } else {
            return self::getNextToken($tokens, $startFrom);
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
        return in_array(trim($token[1], '\'\"?'), (array) $keyword, true);
    }

    private static function checkKeywords(&$startFrom, &$placeholderValues, $trimmed, $tokens, $pToken, $pattern, $pi, $j)
    {
        foreach (self::$keywords as $classToken) {
            if ($classToken::is($trimmed)) {
                if ($classToken::getValue($tokens, $startFrom, $placeholderValues, $pToken, $pattern, $pi, $j) === false) {
                    return false;
                } else {
                    break;
                }
            }
        }
    }
}