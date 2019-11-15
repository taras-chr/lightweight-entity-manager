<?php

namespace EntityManager;

use Doctrine\Common\Collections\ArrayCollection;

interface Mapper
{
    /**
     * Receive your collection in this method
     * You can work with collection in any other method only through this
     * Sub mappers sets collection to this method automatically
     *
     * @param ArrayCollection $collection
     */
    public function setCollection(ArrayCollection $collection);

    /**
     * Result of mapper process returned in this method
     * It can be used anywhere
     * Don't mind if PHPStorm shows you an error when you return child of Entity instead of \EntityManager\Entity
     *
     * @return \EntityManager\Entity|\EntityManager\Entity[]|array<\EntityManager\Entity>|mixed
     */
    public function getMapped();
}
