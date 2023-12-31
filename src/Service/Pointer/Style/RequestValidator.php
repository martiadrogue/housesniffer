<?php

namespace App\Service\Pointer\Style;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestValidator implements Evaluation
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(array $hintSet, string $target): bool
    {
        $violationSet = $this->getValidationSet($hintSet);

        foreach ($violationSet as $violation) {
            $this->logger->error('A violation occurred in {property}: {message}', [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ]);
        }

        if (count($violationSet) > 0) {
            throw new ParseException('Data file is not valid according to the model file.');
        }

        $this->logger->notice("Valid syntax for {$target}.");

        return true;
    }

    /**
     * validates hints and returns violations
     *
     * @param mixed[] $hintSet
     * @return ConstraintViolationListInterface
     */
    private function getValidationSet(array $hintSet): ConstraintViolationListInterface
    {
        $groups = new GroupSequence(['Default', 'custom']);
        $constraint = $this->buildConstraintMap();
        $validator = Validation::createValidator();

        return $validator->validate($hintSet, $constraint, $groups);
    }

    private function buildConstraintMap(): Collection
    {
        return new Collection([
            'url' => [
                new Constraints\NotBlank(),
                new Constraints\Type(['type' => 'string']),
            ],
            'method' => [
                new Constraints\NotBlank(),
                new Constraints\Choice(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']),
            ],
            'query' => new Constraints\Optional([
                new Constraints\Type('array'),
            ]),
            'parameters' => new Constraints\Optional([
                new Constraints\Type('array'),
                new Constraints\Count(['min' => 1]),
            ]),
            'headers' => new Constraints\Optional([
                new Constraints\Type('array'),
            ]),
            'delay' => new Constraints\Optional([
                new Constraints\Type(['type' => 'int']),
            ]),
            'error' => new Constraints\Optional(new Constraints\Type(['type' => 'int'])),
        ]);
    }
}
