<?php

namespace Imanghafoori\SearchReplace\PatternParser;

use Imanghafoori\SearchReplace\Searcher;
use Imanghafoori\SearchReplace\Stringify;

class Normalizer
{
    public static function normalize($to, $all)
    {
        $to['search'] = self::addQuotes($to['search'], $all);

        is_string($to['replace'] ?? 0) && ($to['replace'] = self::addQuotes(
            $to['replace'],
            [['search' => '<"<int>">', 'replace' => '"<"<1>">"',]]
        ));

        return $to;
    }

    private static function addQuotes(string $search, array $all)
    {
        [$tokens,] = Searcher::searchParsed($all, token_get_all('<?php '.$search));
        unset($tokens[0]);

        return Stringify::fromTokens($tokens);
    }
}