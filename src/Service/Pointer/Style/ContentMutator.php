<?php

namespace App\Service\Pointer\Style;

class ContentMutator implements Method
{
    public function process(array $hintSet, string $target): array
    {
        return $hintSet;
    }
}
