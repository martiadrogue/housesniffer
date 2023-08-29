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

    /**
     * Perform an operation involving $hintMap
     *
     * @param mixed[] $hintMap
     * @return mixed[]
     */
    public function check(array $hintMap): array
    {
        if (empty($this->next)) {
            return $hintMap;
        }

        return $this->next->check($hintMap);
    }
}
