<?php

namespace EntityManager;

interface Type
{
    /**
     * Type constructor.
     * @param mixed $value
     */
    public function __construct($value);

    /**
     * @return mixed
     */
    public function getValue();
}
