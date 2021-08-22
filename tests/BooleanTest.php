<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class BooleanTest extends BaseTestClass
{
    /** @test */
    public function boolean()
    {
        $patterns = [
            'name' => [
                'search' => '"<bool>";',
                'replace' => ''
            ],
        ];

        $startFile = '<?php true; false; TRUE; FALSE;';
        $resultFile = '<?php    ';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);

        $this->assertEquals([1, 1, 1,1], $replacedAt);
    }

    /** @test */
    public function boolean_1()
    {
        $patterns = [
            'name' => [
                'search' => '"<boolean>";',
                'replace' => ''
            ],
        ];

        $startFile = '<?php true; false; TRUE; FALSE;';
        $resultFile = '<?php    ';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);

        $this->assertEquals([1, 1, 1,1], $replacedAt);
    }
}
