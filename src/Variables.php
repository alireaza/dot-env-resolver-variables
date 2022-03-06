<?php

namespace AliReaza\DotEnv\Resolver;

use LogicException;

class Variables
{
    protected array $variables;

    public function __construct(array $variables = [])
    {
        $this->variables = $variables;
    }

    public function __invoke(string $data, array $env): string
    {
        if (str_contains($data, '$')) {
            $regex = '/
            (?<!\\\\)
            (?P<backslashes>\\\\*)                # escaped with a backslash?
            \$
            (?!\()                                # no opening parenthesis
            (?P<opening_brace>\{)?                # optional opening brace
            (?P<key>(?i:[A-Z][A-Z0-9_]*+))?       # key
            (?P<default_value>:[-=][^\}]++)?      # optional default value
            (?P<closing_brace>\})?                # optional closing brace
        /x';

            $data = preg_replace_callback($regex, function ($matches) use ($env): string {
                // odd number of backslashes means the $ character is escaped
                if (strlen($matches['backslashes']) % 2 === 1) {
                    return substr($matches[0], 1);
                }

                // unescaped $ not followed by variable name
                if (!isset($matches['key'])) {
                    return $matches[0];
                }

                if ($matches['opening_brace'] === '{' && !isset($matches['closing_brace'])) {
                    throw new LogicException('Unclosed braces on variable expansion: ' . $matches[0]);
                }

                $key = $matches['key'];

                $value = '';

                if (isset($matches['default_value']) && $matches['default_value'] !== '') {
                    $unsupportedChars = strpbrk($matches['default_value'], '\'"{');
                    if ($unsupportedChars !== false) {
                        throw new LogicException(sprintf('Unsupported character "%s" found in the default value of variable "%s".', $unsupportedChars[0], $key));
                    }

                    $value = substr($matches['default_value'], 2);
                    $value = $this($value, $env);
                }

                if (isset($env[$key])) {
                    $value = $env[$key];
                } else if (isset($this->variables[$key])) {
                    $value = $this->variables[$key];
                }

                if (!$matches['opening_brace'] && isset($matches['closing_brace'])) {
                    $value .= '}';
                }

                return $matches['backslashes'] . $value;
            }, $data);
        }

        return $data;
    }
}
