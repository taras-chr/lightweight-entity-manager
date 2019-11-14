<?php

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

    /**облемой.
     * @param ArrayCollection $collection
     */
    public function setCollection(ArrayCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @inheritDoc
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @return Country
     * @throws \Doctrine\Annotations\AnnotationException
     * @throws \EntityManager\EntityManagerException
     * @throws \ReflectionException
     */
    public function getMapped(): Country
    {
        return EntityMapper::createFrom($this->collection, new Country())
            ->setSubMapper('currency', new CurrencyMapper())
            ->setSubMapper('coordinates', new CoordinatesMapper())
            ->mapSingle();
    }
}
