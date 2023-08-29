<?php

namespace App\Service\Pointer;

use App\Service\Pointer\Style\Evaluation;
use App\Service\Pointer\HintMiddleware;

class HintValidator extends HintMiddleware
{
    private Evaluation $tactic;
    private string $target;
    private const PATH = "config/hints/";

    public function __construct(Evaluation $tactic, string $target)
    {
        $this->tactic = $tactic;
        $this->target = $target;
        $this->isUnknown = true;
    }

    public function check(array $hintSet): array
    {
        if ($this->isUnknown) {
            $this->isUnknown = !$this->validate($hintSet);
        }

        return parent::check($hintSet);
    }

    private function validate(array $hintSet): bool
    {
        return $this->tactic->process($hintSet, $this->target);
    }
}
