<?php

namespace Imanghafoori\SearchReplace\PatternParser;

use Imanghafoori\SearchReplace\Finder;
use Imanghafoori\SearchReplace\Str;
use Imanghafoori\SearchReplace\Tokenizer;

class Filters
{
    public static function extractFilter($search)
    {
        $addedFilters = [];
        $tokens = Tokenizer::tokenize($search);
        $count = 0;
        foreach ($tokens as $i => $pToken) {
            if ($pToken[0] !== T_CONSTANT_ENCAPSED_STRING) {
                continue;
            }

            // If is placeholder "<like_this>"
            if ($pToken[1][1] === '<' && '>' === $pToken[1][strlen($pToken[1]) - 2]) {
                $count++;
            } else {
                continue;
            }

            $ids = self::getPlaceholderIds();
            foreach ($ids as [$id, $mutator]) {
                if (Finder::startsWith(trim($pToken[1], '\'\"'), "<$id:")) {
                    $tokens[$i][1] = "'<$id>'";
                    $readParams = self::getParams($pToken, $id);
                    $mutator && $readParams = $mutator($readParams);
                    $addedFilters[] = [$count, $readParams];
                }
            }
        }

        return [$tokens, $addedFilters];
    }

    private static function getPlaceholderIds()
    {
        return [
            [
                'global_func_call',
                function ($values) {
                    $values = $u = explode(',', $values);
                    foreach ($u as $val) {
                        $values[] = '\\'.$val;
                    }

                    return $values;
                },
            ],
            ['name', null],
        ];
    }

    private static function getParams($pToken, $id)
    {
        $pName = trim($pToken[1], '\'\"');

        return rtrim(Str::replaceFirst("<$id:", '', $pName), '>');
    }
}