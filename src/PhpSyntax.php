<?php

namespace Imanghafoori\SearchReplace;

class PhpSyntax
{
    public static function isValid($code)
    {
        file_put_contents(__DIR__.'/tmp.php', $code);
        $output = shell_exec(sprintf('php -l %s 2>&1', escapeshellarg(__DIR__.'/tmp.php')));
        unlink(__DIR__.'/tmp.php');

        return preg_match('!No syntax errors detected!', $output);
    }
}