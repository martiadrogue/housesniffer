<?php

namespace App\Service\Pointer;

use App\Service\Pointer\Style\Method;
use App\Service\Pointer\HintMiddleware;

class HintMutator extends HintMiddleware
{
    private Method $tactic;
    private string $target;

    public function __construct(Method $tactic, string $target)
    {
        $this->target = $target;
        $this->tactic = $tactic;
    }

    public function check(array $hintSet): array
    {
        $hintSet = $this->mutate($hintSet);

        return parent::check($hintSet);
    }

    /**
     * Get hints for content
     *
     * @param mixed[] $hintSet
     * @return mixed[]
     */
    private function mutate(array $hintSet): array
    {
        return $this->tactic->process($hintSet, $this->target);
    }
}
