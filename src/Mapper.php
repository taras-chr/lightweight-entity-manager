<?php

namespace EntityManager;

use Doctrine\Common\Collections\ArrayCollection;

interface Mapper
{
    public function setCollection(ArrayCollection $collection);

    public function getMapped();
}
