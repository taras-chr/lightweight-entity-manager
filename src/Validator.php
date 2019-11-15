<?php

namespace EntityManager;

/**
 * Interface Validator
 * @package EntityManager
 */
interface Validator
{
    /**
     * Validator constructor.
     * Receive value of the given property in constructor
     * @param $value
     */
    public function __construct($value);

    /**
     * Set logical result of validator
     * If false it will be thrown EntityManagerException
     * @return bool
     */
    public function isValid(): bool;

    /**
     * The validator result will be set to the specified property
     * @return mixed
     */
    public function getValue();
}
