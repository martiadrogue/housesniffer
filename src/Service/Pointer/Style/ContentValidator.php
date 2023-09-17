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
            throw new ParseException('Data file is not valid according to the model file.');
        }

        $this->logger->notice("Valid syntax for {$target}.");

        return true;
    }

    private function buildConstraintMap(): Collection
    {
        $itemList = [ 'reference','url', 'title', 'price', 'picture', 'address', 'zip_code', 'latitude', 'longitude', 'rooms', 'size', 'bathrooms', 'floor', ];
        $pageList = [ 'paginator','total_items', 'current', 'total_pages', 'next_page', ];
        $collectionItemList = $this->getCollectionList($itemList);
        $collectionPageList = $this->getCollectionList($pageList);

        return new Collection([
            'allowExtraFields' => true,
            'allowMissingFields' => true,
            'fields' => [
                'item' => new Constraints\Collection([
                    'path' => [
                        new Constraints\NotBlank(),
                        new Constraints\Type(['type' => 'string']),
                    ],
                ]),
                'fieldList' => new Collection($collectionItemList),
                'page' =>  new Collection($collectionPageList),
            ]
        ]);
    }

    /**
     * Return a collection for all fields
     *
     * @param mixed[] $fieldList
     * @return mixed[]
     */
    private function getCollectionList(array $fieldList): array
    {
        return array_fill_keys($fieldList, new Constraints\Collection([
            'path' => new Constraints\Optional([
                new Constraints\Type(['type' => 'string']),
            ]),
            'source' => new Constraints\Optional([
                new Constraints\Type(['type' => 'string']),
            ]),
            'purge' => new Constraints\Optional([
                new Constraints\Type(['type' => 'string']),
            ]),
        ]));
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
