<?php

declare(strict_types=1);

namespace AliReaza\Tests\DotEnv\Resolver\Unit;

use AliReaza\DotEnv\DotEnv;
use AliReaza\DotEnv\Resolver\Variables;
use PHPUnit\Framework\TestCase;

class VariablesTest extends TestCase
{
    public function test_When_use_Variables_Resolver_Expect_env_property_must_array_of_file_variables_and_all_variables_inside_values_have_value()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'FOO=BAR' . "\n" . 'BAZ=${FOO}');

        $env = new DotEnv($file, [
            new Variables()
        ]);

        unlink($file);

        rmdir($tmpdir);

        $this->assertIsArray($env->toArray());

        $this->assertSame(true, $env->has('BAZ'));

        $this->assertSame('BAR', $env->get('BAZ'));
    }
}
