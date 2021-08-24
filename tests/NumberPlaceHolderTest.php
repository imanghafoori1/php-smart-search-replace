<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class NumberPlaceHolderTest extends BaseTestClass
{
    /** @test */
    public function number_matching()
    {
        $patterns = [
            'name' => [
                'search' => '["<number>","<number>"]',
                'replace' => '[]',
            ]
        ];

        $startFile = '<?php [1,2.2];';
        $resultFile = '<?php [];';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function integer_number()
    {
        $patterns = [
            'name' => [
                'search' => '"<int>"',
                'replace' => '',
            ]
        ];

        $startFile = '<?php [1,2.2,3];';
        $resultFile = '<?php [,2.2,];';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function float_number()
    {
        $patterns = [
            'name' => [
                'search' => '"<float>",',
                'replace' => '',
            ]
        ];

        $startFile = '<?php [1,2.2,3,];';
        $resultFile = '<?php [1,3,];';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }
}
