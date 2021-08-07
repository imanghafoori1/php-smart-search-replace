<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\LaravelMicroscope\Tests\BaseTestClass;
use Imanghafoori\SearchReplace\Searcher;

class BeginWithOptionalPlaceHolder extends BaseTestClass
{
    /** @test */
    public function begins_with_optional_white_space()
    {
        $patterns = [
            "'<white_space>?'];'<white_space>?'" => ['replace' => ',"<1>"];"<2>"'],
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
            "'<white_space>?'];'<white_space>?'" => ['replace' => ',"<1>"];"<2>"'],
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
            "'<white_space>?'];'<white_space>?'" => ['replace' => ',"<1>"];"<2>"'],
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
            "'<string>?''<white_space>?'];" => ['replace' => '];"<1>";'],
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
            "'<string>?''<white_space>'];" => ['replace' => '];'],
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
            "'<bool>?''<white_space>?'];" => ['replace' => '];"<1>""<2>";'],
        ];
        $startCode =  '<?php [1,TRUE ];';
        $resultCode = '<?php [1,];TRUE ;';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);

        $patterns = [
            "'<bool>?''<white_space>?'];" => ['replace' => ']"<1>""<2>";'],
        ];
        $startCode =  '<?php [1,"TRUE"   ];';
        $resultCode = '<?php [1,"TRUE"]   ;';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);

        $patterns = [
            "'<bool>?''<white_space>?'];" => ['replace' => '];"<1>""<2>";'],
        ];
        $startCode =  '<?php [1,TRUE];';
        $resultCode = '<?php [1,];TRUE;';

        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }
}
