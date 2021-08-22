<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class UntilTest extends BaseTestClass
{
    /** @test */
    public function until_placeholder()
    {
        $patterns = [
            'name' => [
                'search' => 'return response()"<until>";',
                'replace' => 'response()"<1>"->throwResponse();'
            ],
        ];

        $startFile = file_get_contents(__DIR__.'/stubs/SimplePostController.stub');
        $resultFile = file_get_contents(__DIR__.'/stubs/SimplePostController2.stub');
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));

        $this->assertEquals($resultFile, $newVersion);

        $this->assertEquals([16, 23], $replacedAt);
    }

    /** @test */
    public function in_between()
    {
        $patterns = [
            "name" => [
                'search' => "if('<in_between>'){}",
                'replace' => 'if(true) {"<1>";}'
            ],
        ];

        $startFile = '<?php if(foo()->bar()) {}';
        $resultFile = '<?php if(true) {foo()->bar();}';

        $tokens = token_get_all($startFile);
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, $tokens);

        $this->assertEquals($resultFile, $newVersion);

        $this->assertEquals([1], $replacedAt);
    }
}
