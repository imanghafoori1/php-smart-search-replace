<?php

namespace Imanghafoori\SearchReplace\Tests;

use Imanghafoori\SearchReplace\Searcher;

class FullClassReferenceTest extends BaseTestClass
{
    /** @test */
    public function full_class_ref()
    {
        $patterns = [
            'name' => [
                'search' => "new '<full_class_ref>'(); '<full_class_ref>'();",
                'replace' => '"<1>";'],
        ];
        $startCode = '<?php new \App\Models\User(); \App\Models\User();';
        $resultCode = '<?php \App\Models\User;';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }

    /** @test */
    public function full_class_ref_does_not_capture_semi_qualified_refs()
    {
        $patterns = [
            'name' => [
                'search' => "new '<full_class_ref>'();",
                'replace' => '"<1>";'],
        ];
        $startCode = '<?php new App\Models\User();';
        $resultCode = '<?php new App\Models\User();';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([], $replacedAt);
    }

    /** @test */
    public function full_class_ref_not_capture_non_qualified_refs()
    {
        $patterns = [
            'name' => [
                'search' => "new '<full_class_ref>'();",
                'replace' => '"<1>";'],
        ];
        $startCode = '<?php new User();';
        $resultCode = '<?php new User();';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([], $replacedAt);
    }

    /** @test */
    public function full_class_ref_not_capture_non_qualified_ref_1()
    {
        $patterns = [
            'name' => [
                'search' => "'<full_class_ref>'();",
                'replace' => '"<1>"();'],
        ];

        $startCode = '<?php new \User();';
        $resultCode = '<?php new \User();';
        [$newVersion, $replacedAt] = Searcher::searchReplace($patterns, token_get_all($startCode));

        $this->assertEquals($resultCode, $newVersion);
        $this->assertEquals([1], $replacedAt);
    }
}
