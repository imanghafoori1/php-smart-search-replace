<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class CommentPlaceholderTest extends BaseTestClass
{
    /** @test */
    public function match_comment()
    {
        $patterns = [
           'name' => [
               'search' => '"<comment>"]',
                'replace' => ']',
            ]
        ];

        $startFile = '<?php [/**/]; [/**/ ]; [ /**/ ]; [1,]; ["s"];';
        $resultFile = '<?php []; []; [ ]; [1,]; ["s"];';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function match_comment_without_quote()
    {
        $patterns = [
           'name' => [
               'search' => '<comment>]',
                'replace' => ']',
            ]
        ];

        $startFile = '<?php [/**/]; [/**/ ]; [ /**/ ]; [1,]; ["s"];';
        $resultFile = '<?php []; []; [ ]; [1,]; ["s"];';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function match_comment_()
    {
        $patterns = [
           'name' => [
               'search' => '"<comment>"',
                'replace' => '',
            ]
        ];

        $startFile = '<?php [/** h */];
# s
// selectRaw';
        $resultFile = '<?php [/** h */];';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, trim($newVersion));
    }

    /** @test */
    public function doc_comment()
    {
        $patterns = [
           'name' => [
               'search' => '"<doc_block>"',
                'replace' => '',
            ]
        ];

        $startFile = '<?php [/** h */];
# s
// selectRaw';
        $resultFile = '<?php [];
# s
// selectRaw';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }
}
