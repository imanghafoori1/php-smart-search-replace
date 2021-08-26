<?php

namespace Imanghafoori\SearchReplace;

class Replacer
{
    public static function applyAllMatches($patternMatches, $replace, $tokens, $namedPatterns, $syntaxErrors)
    {
        $replacementLines = [];
        foreach ($patternMatches as $matchValue) {
            [$tokens, $lineNum] = self::applyMatch($replace, $matchValue, $tokens, $syntaxErrors, [], [], $namedPatterns);
            $replacementLines[] = $lineNum;
        }

        return [$tokens, $replacementLines];
    }

    public static function applyMatch($replace, $match, $tokens, $preventSyntaxErrors = false, $avoiding = [], $postReplaces = [], $namedPatterns = [])
    {
        $newValue = self::applyWithPostReplacements($replace, $match['values'], $postReplaces, $namedPatterns, $match['repeatings']);

        [$newTokens, $lineNum] = self::replaceTokens($tokens, $match['start'], $match['end'], $newValue);

        $wasPostReplaced = false;
        $code = Stringify::fromTokens($newTokens);
        $hasAny = Finder::matchesAny($avoiding, token_get_all($code));

        if ($hasAny || ($preventSyntaxErrors && ! self::isValidPHP($code))) {
            return [$tokens, null, $wasPostReplaced];
        }

        return [$newTokens, $lineNum, $wasPostReplaced];
    }

    public static function isValidPHP($code)
    {
        file_put_contents(__DIR__.'/tmp.php', $code);
        $output = shell_exec(sprintf('php -l %s 2>&1', escapeshellarg(__DIR__.'/tmp.php')));
        unlink(__DIR__.'/tmp.php');

        return preg_match('!No syntax errors detected!', $output);
    }

    public static function applyWithPostReplacements($replace, $values, $postReplaces, $namedPatterns = [], $repeating = [])
    {
        $newValue = self::applyOnReplacements($replace, $values);

        [$newTokens,] = PostReplace::applyPostReplaces($postReplaces, token_get_all('<?php '.$newValue));
        array_shift($newTokens);

        foreach ($newTokens as $index => $t) {
            $r = Finder::isRepeatingPattern($t);
            if (! $r) {
                continue;
            }
            [$num, $pName] = explode(':', $r);
            $pattern = $namedPatterns[$pName];

            $newTokens = self::applyRepeats($repeating, $pattern, $newTokens, $index);
        }

        return Stringify::fromTokens($newTokens);
    }

    public static function applyOnReplacements($replace, $values)
    {
        if (is_callable($replace)) {
            return call_user_func($replace, $values);
        }

        $newValue = $replace;
        foreach ($values as $number => $value) {
            ! is_array($value[0]) && $newValue = str_replace(['"<'.($number + 1).'>"', "'<".($number + 1).">'"], $value[1] ?? $value[0], $newValue);
        }

        return $newValue;
    }

    private static function replaceTokens($tokens, $from, $to, string $with)
    {
        $lineNumber = 0;

        for ($i = $from; $i <= $to; $i++) {
            if ($i === $from) {
                $lineNumber = $tokens[$i][2] ?? 0;
                $tokens[$i] = [T_STRING, $with, 1];
                continue;
            }

            if ($i > $from && $i <= $to) {
                ! $lineNumber && ($lineNumber = $tokens[$i][2] ?? 0);
                $tokens[$i] = [T_STRING, '', 1];
            }
        }

        $j = 0;
        while ($lineNumber === 0 && $j < 5) {
            $j++;
            $lineNumber = $tokens[$i++][2] ?? 0;
        }

        return [$tokens, $lineNumber];
    }

    private static function applyRepeats($repeating, $pattern, $newTokens, $index)
    {
        foreach ($repeating as $repeat) {
            $repeatsValues = [];
            foreach ($repeat as $r) {
                $repeatsValues[] = self::applyOnReplacements($pattern, $r);
            }
            $newTokens[$index][1] = implode('', $repeatsValues);
        }

        return $newTokens;
    }

}