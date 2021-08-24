<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class CommentingPatternsTest extends BaseTestClass
{
    /** @test */
    public function comment_numbering_is_not_important()
    {
        $patterns = [
            'name' => [
                'search' => '["<10:any>""<8:white_space>?"]',
                'replace' => '["<1>""<2>","<1>"]',
                'predicate' => function ($matches) {
                    return $matches['values'][0][0] === T_CONSTANT_ENCAPSED_STRING;
                }
            ]
        ];

        $startFile = '<?php [1 ]; ["s" ]; ["d"];';
        $resultFile = '<?php [1 ]; ["s" ,"s"]; ["d","d"];';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }
}
