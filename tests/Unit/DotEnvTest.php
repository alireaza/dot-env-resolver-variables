<?php

declare(strict_types=1);

namespace AliReaza\Tests\DotEnv\Resolver\Unit;

use AliReaza\DotEnv\DotEnv;
use PHPUnit\Framework\TestCase;
use Throwable;

class DotEnvTest extends TestCase
{
    public function test_When_create_new_DotEnv_without_arguments_Expect_env_property_must_array_and_empty()
    {
        $env = new DotEnv();

        $array = $env->toArray();

        $this->assertTrue(is_array($array) && empty($array));
    }

    public function test_When_create_new_DotEnv_and_load_file_Expect_env_property_must_array_of_file_variables()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'FOO=BAR');

        $env = new DotEnv();

        $env->load($file);

        unlink($file);

        rmdir($tmpdir);

        $this->assertTrue(is_array($env->toArray()) && $env->has('FOO') && $env->get('FOO') === 'BAR');
    }

    public function test_When_create_new_DotEnv_with_file_argument_Expect_env_property_must_array_of_file_variables()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'FOO=BAR');

        $env = new DotEnv($file);

        unlink($file);

        rmdir($tmpdir);

        $this->assertTrue(is_array($env->toArray()) && $env->has('FOO') && $env->get('FOO') === 'BAR');
    }

    public function test_When_create_new_DotEnv_with_file_and_resolvers_arguments_Expect_env_property_must_array_of_file_variables_that_must_be_converted_to_lowercase_by_resolver()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'FOO=BAR');

        $env = new DotEnv($file, [
            new class {
                public function __invoke(string $data)
                {
                    return strtolower($data);
                }
            }
        ]);

        unlink($file);

        rmdir($tmpdir);

        $this->assertTrue(is_array($env->toArray()) && $env->has('FOO') && $env->get('FOO') === 'bar');
    }

    public function test_When_create_new_DotEnv_with_empty_file_Expect_env_property_must_array_and_empty()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, '');

        $env = new DotEnv($file);

        unlink($file);

        rmdir($tmpdir);

        $array = $env->toArray();

        $this->assertTrue(is_array($array) && empty($array));
    }

    public function test_When_create_new_DotEnv_with_missing_equal_for_variable_in_file_Expect_throw_exception()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'FOO');

        $this->expectExceptionMessage('Missing = in the environment variable declaration: FOO');

        try {
            new DotEnv($file);
        } catch (Throwable $exception) {
            unlink($file);

            rmdir($tmpdir);

            throw $exception;
        }

        unlink($file);

        rmdir($tmpdir);
    }

    public function test_When_create_new_DotEnv_with_whitespace_character_after_variable_in_file_Expect_throw_exception()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'FOO =BAR');

        $this->expectExceptionMessage('Whitespace characters are not supported after the variable name: FOO');

        try {
            new DotEnv($file);
        } catch (Throwable $exception) {
            unlink($file);

            rmdir($tmpdir);

            throw $exception;
        }

        unlink($file);

        rmdir($tmpdir);
    }

    public function test_When_create_new_DotEnv_with_export_before_variable_in_file_Expect_env_property_must_array_of_file_variables()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'export FOO=BAR');

        $env = new DotEnv($file);

        unlink($file);

        rmdir($tmpdir);

        $this->assertTrue(is_array($env->toArray()) && $env->has('FOO') && $env->get('FOO') === 'BAR');
    }

    public function test_When_create_new_DotEnv_with_whitespace_character_inside_variable_in_file_Expect_throw_exception()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'FOO BAR=BAZ');

        $this->expectExceptionMessage('Invalid character in variable name: FOO BAR');

        try {
            new DotEnv($file);
        } catch (Throwable $exception) {
            unlink($file);

            rmdir($tmpdir);

            throw $exception;
        }

        unlink($file);

        rmdir($tmpdir);
    }

    public function test_When_create_new_DotEnv_with_whitespace_character_before_value_in_file_Expect_throw_exception()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'FOO= BAR');

        $this->expectExceptionMessage('Whitespace characters are not supported before the value:  BAR');

        try {
            new DotEnv($file);
        } catch (Throwable $exception) {
            unlink($file);

            rmdir($tmpdir);

            throw $exception;
        }

        unlink($file);

        rmdir($tmpdir);
    }

    public function test_When_create_new_DotEnv_with_whitespace_character_inside_value_in_file_Expect_env_property_must_array_of_file_variables()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'FOO=BAR BAZ');

        $env = new DotEnv($file);

        unlink($file);

        rmdir($tmpdir);

        $this->assertTrue(is_array($env->toArray()) && $env->has('FOO') && $env->get('FOO') === 'BAR BAZ');
    }

    public function test_When_create_new_DotEnv_with_value_with_single_quotes_in_file_Expect_env_property_must_array_of_file_variables_without_quotes()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, "FOO='BAR BAZ'");

        $env = new DotEnv($file);

        unlink($file);

        rmdir($tmpdir);

        $this->assertTrue(is_array($env->toArray()) && $env->has('FOO') && $env->get('FOO') === 'BAR BAZ');
    }

    public function test_When_create_new_DotEnv_with_value_with_double_quotes_in_file_Expect_env_property_must_array_of_file_variables_without_quotes()
    {
        mkdir($tmpdir = sys_get_temp_dir() . '/dotenv');

        $file = tempnam($tmpdir, 'alireaza-');

        file_put_contents($file, 'FOO="BAR BAZ"');

        $env = new DotEnv($file);

        unlink($file);

        rmdir($tmpdir);

        $this->assertTrue(is_array($env->toArray()) && $env->has('FOO') && $env->get('FOO') === 'BAR BAZ');
    }
}