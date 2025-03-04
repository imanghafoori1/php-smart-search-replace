<?php


use Imanghafoori\SearchReplace\Searcher;
use Imanghafoori\SearchReplace\Tests\BaseTestClass;

class NotWhitespaceTest extends BaseTestClass
{
    /** @test */
    public function match_white_space2()
    {
        $patterns = [
            'name' => [
                'search' => ',<not_whitespace>',
                'replace' => ', <1>',
            ]
        ];

        $start = '<?php [1,]';
        $result = '<?php [1, ]';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($start));
        $this->assertEquals($result, $newVersion);
        $this->assertEquals([0], $replacedAt);

        $start = '<?php [1, 1]';
        $result = '<?php [1, 1]';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($start));
        $this->assertEquals($result, $newVersion);
        $this->assertEquals([], $replacedAt);
    }
}
