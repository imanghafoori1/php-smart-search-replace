<?php


use Imanghafoori\SearchReplace\Searcher;
use Imanghafoori\SearchReplace\Tests\BaseTestClass;

class CompareTest extends BaseTestClass
{
    /** @test */
    public function comparison()
    {
        $patterns = [
            'name' => [
                'search' => '2 <compare> 3;',
                'replace' => '4 <1> 5;',
            ]
        ];

        $startFile = '<?php 2 === 3;';
        $resultFile = '<?php 4 === 5;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);

        $startFile = '<?php 2 < 3;';
        $resultFile = '<?php 4 < 5;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);

        $startFile = '<?php 2 >= 3;';
        $resultFile = '<?php 4 >= 5;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals(1, $replacedAt[0]);

        $patterns = [
            'name' => [
                'search' => '2 <comparison> 3;',
                'replace' => '4 <1> 5;',
            ]
        ];

        $startFile = '<?php 2 <= 3;';
        $resultFile = '<?php 4 <= 5;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals(1, $replacedAt[0]);

    }
}
