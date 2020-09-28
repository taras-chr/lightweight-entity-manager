<?php

declare(strict_types=1);

namespace EntityManager\Example\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use EntityManager\EntityMapper;
use EntityManager\Example\Entity\Country;
use EntityManager\Mapper;

class CountryMapper implements Mapper
{
    /**
     * @var ArrayCollection
     */
    private $collection;

    /**
     * CountryMapper constructor.
     * @param ArrayCollection $collection
     */
    public function __construct(ArrayCollection $collection)
    {
        $this->setCollection($collection);
    }

    /**
     * @param ArrayCollection $collection
     */
    public function setCollection(ArrayCollection $collection): void
    {
        $this->collection = $collection;
    }

    /**
     * @inheritDoc
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @return Country
     * @throws \EntityManager\EntityManagerException
     * @throws \ReflectionException
     */
    public function getMapped(): Country
    {
        return EntityMapper::createFrom($this->collection, new Country())
            ->setNestedMapper('currency', new CurrencyMapper())
            ->setNestedMapper('coordinates', new CoordinatesMapper())
            ->mapSingle();
    }
}
