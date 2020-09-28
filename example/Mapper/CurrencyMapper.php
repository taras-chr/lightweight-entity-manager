<?php

declare(strict_types=1);

namespace EntityManager\Example\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use EntityManager\EntityMapper;
use EntityManager\Example\Entity\Currency;
use EntityManager\Mapper;

class CurrencyMapper implements Mapper
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
        $this->collection = $collection;
    }

    /**
     * @return Currency[]
     * @throws \ReflectionException
     * @throws \EntityManager\EntityManagerException
     */
    public function getMapped(): array
    {
        return EntityMapper::createFrom($this->collection, new Currency())
            ->bindProperty('date', new \DateTime())
            ->mapList();
    }
}
