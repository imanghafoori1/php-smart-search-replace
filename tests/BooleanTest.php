<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class BooleanTest extends BaseTestClass
{
    /** @test */
    public function boolean_6()
    {
        $patterns = [
            'name' => [
                'search' => '<boolean>; "<boolean>"; "<boolean>?"; <boolean>?;',
                'replace' => '"<4>";<3>;<2>;"<1>";'
            ],
        ];

        $startFile = '<?php true; false; TRUE; FALSE;';
        $resultFile = '<?php FALSE;TRUE;false;true;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function boolean()
    {
        $patterns = [
            'name' => [
                'search' => '"<boolean>"; "<boolean>"; "<boolean>"; "<boolean>";',
                'replace' => '"<4>";"<3>";"<2>";"<1>";'
            ],
        ];

        $startFile = '<?php true; false; TRUE; FALSE;';
        $resultFile = '<?php FALSE;TRUE;false;true;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);

        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function boolean_1()
    {
        $patterns = [
            'name' => [
                'search' => '"<boolean>"',
                'replace' => 'aa'
            ],
        ];

        $start_File = '<?php true();';
        $resultFile = '<?php true();';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($start_File));

        $this->assertEquals($resultFile, $newVersion);

        $this->assertEquals([], $replacedAt);
    }

    /** @test */
    public function boolean_2()
    {
        $patterns = [
            'name' => [
                'search' => '"<boolean>"',
                'replace' => 'aa'
            ],
        ];

        $start_File = '<?php "------";';
        $resultFile = '<?php "------";';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($start_File));

        $this->assertEquals($resultFile, $newVersion);

        $this->assertEquals([], $replacedAt);
    }
}
