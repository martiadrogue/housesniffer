<?php

namespace App\Service\Pointer\Style;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GroupSequence;

class RequestValidator implements Evaluation
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(array $hintSet, string $target): bool
    {
        $groups = new GroupSequence(['Default', 'custom']);
        $constraint = $this->buildConstraintMap();

        $validator = Validation::createValidator();
        $violationSet = $validator->validate($hintSet, $constraint, $groups);

        foreach ($violationSet as $violation) {
            $this->logger->error('A violation occurred in {property}: {message}', [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ]);
        }

        if (count($violationSet) > 0) {
            throw new ParseException('The data file is not valid according to the model file.');
        }

        $this->logger->notice("YAML syntax for the Request of {$target} is valid.");

        return true;
    }


    public function buildConstraintMap(): Collection
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
