<?php

namespace App\Component\Yaml;

use Symfony\Component;

/**
 * Adapter for Yaml Fabien Potencier parser, offers convenience methods to load
 * and dump YAML.
 *
 * @final
 */
class Yaml
{
    /**
     * Parses a YAML file into a PHP array. And make sure all null values are
     * empty strings
     *
     * Usage:
     *
     *     $array = Yaml::parseFile('config.yml');
     *     print_r($array);
     *
     * @param string $filename The path to the YAML file to be parsed
     * @param int    $flags    A bit field of PARSE_* constants to customize the YAML parser behavior
     * @return mixed[]
     */
    public static function parseFile(string $filename, int $flags = 0): array
    {
        $content = Component\Yaml\Yaml::parseFile(sprintf($filename, $flags));

        $decoded = json_decode(json_encode($content), true);

        if (null === $decoded) {
            return [];
        }

        return $decoded;
    }
}
