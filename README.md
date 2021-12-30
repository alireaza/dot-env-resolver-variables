# Dot Env Resolver Variables


## Install

Via Composer
```bash
$ composer require alireaza/dot-env-resolver-variables
```


## Usage

```php
use AliReaza\DotEnv\DotEnv;
use AliReaza\DotEnv\Resolver\Variables;

$env = new DotEnv('.env', [
    new Variables($_SERVER + $_ENV),
]);
$env->toArray(); // Array of variables defined in .env
```


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.