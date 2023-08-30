<?php

namespace App\Service\Pointer\Style;

use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GroupSequence;

class ContentValidator implements Evaluation
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(array $hintMap, string $target): bool
    {
        $violationMap = $this->getViolationMap($hintMap);

        foreach ($violationMap as $violation) {
            $this->logger->error('A violation occurred in {property}: {message}', [
                'property' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ]);
        }

        if (count($violationMap) > 0) {
            throw new ParseException('The data file is not valid according to the model file.');
        }

        $this->logger->notice("YAML syntax for the Content of {$target} is valid.");

        return true;
    }

    public function buildConstraintMap(): Collection
    {
        return new Collection([
            'allowExtraFields' => true,
            'allowMissingFields' => true,
            'fields' => [
                'item' =>  [
                    new Constraints\NotBlank(),
                    new Constraints\Type(['type' => 'string']),
                ],
                'fieldList' => new Collection([
                    new Collection([
                        'reference' => [
                            new Constraints\NotBlank(),
                            new Constraints\Type(['type' => 'string']),
                        ],
                        'sanitize' => new Constraints\Optional([
                            new Constraints\Type(['type' => 'string']),
                        ])
                    ]),
                    new Collection([
                        'url' => [
                            new Constraints\NotBlank(),
                            new Constraints\Type(['type' => 'string']),
                            ],
                        'sanitize' => new Constraints\Optional([
                            new Constraints\Type(['type' => 'string']),
                        ])
                    ]),
                    new Collection([
                        'title' => [
                            new Constraints\NotBlank(),
                            new Constraints\Type(['type' => 'string']),
                        ],
                        'sanitize' => new Constraints\Optional([
                            new Constraints\Type(['type' => 'string']),
                        ])
                    ]),
                    new Collection([
                        'price' => [
                            new Constraints\NotBlank(),
                            new Constraints\Type(['type' => 'string']),
                        ],
                        'sanitize' => new Constraints\Optional([
                            new Constraints\Type(['type' => 'string']),
                        ])
                    ]),
                    new Collection([
                        'picture' => [
                            new Constraints\Type('string'),
                        ],
                        'sanitize' => new Constraints\Optional([
                            new Constraints\Type(['type' => 'string']),
                        ])
                    ]),
                    new Collection([
                        'rooms' => [
                            new Constraints\Type(['type' => 'string']),
                        ],
                        'sanitize' => new Constraints\Optional([
                            new Constraints\Type(['type' => 'string']),
                        ])
                    ]),
                    new Collection([
                        'size' => [
                            new Constraints\Type(['type' => 'string']),
                        ],
                        'sanitize' => new Constraints\Optional([
                            new Constraints\Type(['type' => 'string']),
                        ])
                    ]),
                    new Collection([
                        'bathrooms' => [
                            new Constraints\Type(['type' => 'string']),
                        ],
                        'sanitize' => new Constraints\Optional([
                            new Constraints\Type(['type' => 'string']),
                        ])
                    ]),
                    new Collection([
                        'floor' => [
                            new Constraints\Type(['type' => 'string']),
                        ],
                        'sanitize' => new Constraints\Optional([
                            new Constraints\Type(['type' => 'string']),
                        ])
                    ]),
                ]),
                'page' =>  new Collection([
                    'paginator' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'string']),
                    ]),
                    'total_items' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'string']),
                    ]),
                    'current' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'string']),
                    ]),
                    'total' => new Constraints\Optional([
                        new Constraints\Type(['type' => 'string']),
                    ]),
                ]),
            ]
        ]);
    }

    /**
     * Returns a map with all violations
     *
     * @param mixed[] $hintMap
     * @return ConstraintViolationListInterface
     */
    private function getViolationMap(array $hintMap): ConstraintViolationListInterface
    {
        $groups = new GroupSequence(['Default', 'custom']);
        $constraint = $this->buildConstraintMap();
        $validator = Validation::createValidator();

        return $validator->validate($hintMap, $constraint, $groups);
    }
}
