<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class BeginWithOptionalPlaceHolderTest extends BaseTestClass
{
    /** @test */
    public function match_optional_comment_4()
    {
        $patterns = [
            'name' => [
                'search' => '<integer>?"<comment>?""<white_space>?"]',
                'replace' => ']',
            ]
        ];

        $startFile = '<?php [1/**/ ];';
        $resultFile = '<?php [];';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startFile));
        $this->assertEquals($resultFile, $newVersion);
    }

    /** @test */
    public function begins_with_optional_white_space()
    {
        $patterns = [
            'name'=> [
                'search' => "'<white_space>?'];'<white_space>?'",
                'replace' => ',"<1>"];"<2>"'],
        ];
        $startCode = '<?php [1,2,3]; ';

        $resultCode = '<?php [1,2,3,]; ';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function begins_with_optional_white_space_5()
    {
        $patterns = [
            'name'=> [
                'search' => "'<white_space>?'];'<white_space>?'",
                'replace' => ',"<1>"];"<2>"'],
        ];
        $start_code = '<?php [1,2,3 ]; ';
        $resultCode = '<?php [1,2,3, ]; ';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($start_code));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function begins_with_optional_white_space3()
    {
        $patterns = [
            'name'=> [
                'search' => "'<white_space>?'];'<white_space>?'",
                'replace' => ',"<1>"];"<2>"'],
        ];
        $startCode = '<?php [1,2,3 ]; ';

        $resultCode = '<?php [1,2,3, ]; ';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function begins_with_optional_white_space_2()
    {
        $patterns = [
            'name'=> [
                'search' => "'<string>?''<white_space>?'];",
                'replace' => '];"<1>";'],
        ];
        $startCode = '<?php [1,2,"3"   ]; ';
        $resultCode = '<?php [1,2,];"3"; ';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function begins_with_optional_white_space_4()
    {
        $patterns = [
            'name'=> [
                'search' => "'<string>?''<white_space>'];",
                'replace' => '];'],
        ];
        $startCode =  '<?php [1,2 ];';
        $resultCode = '<?php [1,2];';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }
    /** @test */
    public function begins_with_optional_white_space_s4()
    {
        $patterns = [
            'name'=> [
                'search' => "'<bool>?''<white_space>?'];",
                'replace' => '];"<1>""<2>";'],
        ];
        $startCode =  '<?php [1,TRUE ];';
        $resultCode = '<?php [1,];TRUE ;';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);

        $patterns = [
            'name'=> [
                'search' => "'<bool>?''<white_space>?'];",
                'replace' => ']"<1>""<2>";'],
        ];
        $startCode =  '<?php [1,"TRUE"   ];';
        $resultCode = '<?php [1,"TRUE"]   ;';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);

        $patterns = [
            'name'=> [
                'search' => "'<bool>?''<white_space>?'];",
                'replace' => '];"<1>""<2>";'],
        ];
        $startCode =  '<?php [1,TRUE];';
        $resultCode = '<?php [1,];TRUE;';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }
}
