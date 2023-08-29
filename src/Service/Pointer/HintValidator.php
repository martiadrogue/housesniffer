<?php

namespace App\Service\Pointer;

use App\Service\Pointer\Style\Evaluation;
use App\Service\Pointer\HintMiddleware;

class HintValidator extends HintMiddleware
{
    private Evaluation $tactic;
    private string $target;
    private bool $isUnknown;

    public function __construct(Evaluation $tactic, string $target)
    {
        $this->tactic = $tactic;
        $this->target = $target;
        $this->isUnknown = true;
    }

    public function check(array $hintMap): array
    {
        if ($this->isUnknown) {
            $this->isUnknown = !$this->validate($hintMap);
        }

        return parent::check($hintMap);
    }

    /**
     * Look for violations
     *
     * @param mixed[] $hintMap
     * @return boolean
     */
    private function validate(array $hintMap): bool
    {
        return $this->tactic->process($hintMap, $this->target);
    }
}
