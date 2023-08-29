<?php

namespace App\Service\Pointer;

use App\Service\Pointer\HintMiddleware;

abstract class HintMiddleware
{
    private HintMiddleware $next;

    public function linkWith(HintMiddleware $next): HintMiddleware
    {
        $this->next = $next;

        return $next;
    }

    public function check(array $hintSet): array
    {
        if (!$this->next) {
            return $hintSet;
        }

        return $this->next->check($hintSet);
    }
}
