<?php

declare(strict_types=1);

namespace EntityManager\Example\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use EntityManager\Entity;
use EntityManager\EntityMapper;
use EntityManager\Example\Entity\Coordinates;
use EntityManager\Mapper;

class CoordinatesMapper implements Mapper
{
    /**
     * @var ArrayCollection
     */
    private $collection;

    /**
     * @param ArrayCollection $collection
     */
    public function setCollection(ArrayCollection $collection)
    {
        $this->collection = new ArrayCollection(
            ['longitude' => $collection->get(1), 'latitude' => $collection->get(0)]
        );
    }

    /**
     * @return Coordinates&Entity
     * @throws \ReflectionException
     * @throws \EntityManager\EntityManagerException
     */
    public function getMapped(): Coordinates
    {
        return EntityMapper::createFrom($this->collection, new Coordinates())->mapSingle();
    }
}
