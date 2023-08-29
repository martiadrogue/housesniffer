<?php

namespace App\Service\Pointer\Style;

use Symfony\Component\Yaml\Yaml;

class RequestParser implements Method
{
    private const PATH = "config/hints/";

    public function process(array $hintSet, string $target): array
    {
        return Yaml::parseFile(sprintf(self::PATH . "%s.yml", $target));
    }
}
