<?php

namespace App\Service\Pointer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GroupSequence;

class HintValidator
{
    private LoggerInterface $logger;
    private const PATH = "config/hints/";

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function validate(string $name): void
    {
        $input = Yaml::parseFile(sprintf(self::PATH . "%s.yml", $name));
        $groups = new GroupSequence(['Default', 'custom']);
        $constraint = $this->buildConstraintSet($name);

        $validator = Validation::createValidator();
        $violationSet = $validator->validate($input, $constraint, $groups);

        foreach ($violationSet as $violation) {
            $this->logger->error('A violation occurred in {property}: {message}', [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ]);
        }

        if (count($violationSet) > 0) {
            throw new ParseException('The data file is not valid according to the model file.');
        }

        $this->logger->notice("YAML syntax for {$name} is valid.");
    }

    private function buildConstraintSet($name): Collection
    {
        if (false !== strpos($name, 'item')) {
            return new Collection([
                'item' =>  [
                    new Constraints\NotBlank(),
                    new Constraints\Type(['type' => 'string']),
                ],
                'fieldList' =>  new Collection([
                    'reference' => [
                        new Constraints\NotBlank(),
                        new Constraints\Type(['type' => 'string']),
                    ],
                    'url' => [
                        new Constraints\NotBlank(),
                        new Constraints\Choice(['GET', 'POST', 'PUT', 'PATCH', 'DELETE']),
                    ],
                    'title' => [
                        new Constraints\NotBlank(),
                        new Constraints\Type(['type' => 'string']),
                    ],
                    'picture' => new Constraints\Optional([
                        new Constraints\Type('string'),
                    ]),
                    'price' => [
                        new Constraints\NotBlank(),
                        new Constraints\Type(['type' => 'int']),
                    ],
                    'rooms' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'int']),
                    ]),
                    'size' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'int']),
                    ]),
                    'bathrooms' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'int']),
                    ]),
                    'floor' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'int']),
                    ]),
                ]),
                'page' =>  new Collection([
                    'total_items' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'int']),
                    ]),
                    'current' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'int']),
                    ]),
                    'total' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'int']),
                    ]),
                ]),
            ]);
        }

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
                new Constraints\Count(['min' => 1]),
            ]),
            'parameters' => new Constraints\Optional([
                new Constraints\Type('array'),
                new Constraints\Count(['min' => 1]),
            ]),
            'headers' => new Constraints\Optional([
                new Constraints\Type('array'),
                new Constraints\Count(['min' => 1]),
            ]),
            'error' => new Constraints\Type(['type' => 'int']),
        ]);
    }
}
