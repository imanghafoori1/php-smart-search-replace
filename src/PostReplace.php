<?php

namespace Imanghafoori\SearchReplace;

class PostReplace
{
    public static function applyPostReplaces($postReplaces, $tokens)
    {
        $wasReplaced = false;

        foreach ($postReplaces as $key => $postReplace) {
            [$tokens, $lines] = Searcher::search([$key => $postReplace], token_get_all(Stringify::fromTokens($tokens)));
            $lines && $wasReplaced = true;
        }

        return [$tokens, $wasReplaced];
    }
}
