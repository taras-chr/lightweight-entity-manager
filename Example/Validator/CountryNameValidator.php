<?php

namespace EntityManager\Example\Validator;

use EntityManager\Validator;

/**
 * Class CountryNameValidator
 * @package EntityManager\Example
 */
class CountryNameValidator implements Validator
{
    private $value;

    /**
     * @inheritDoc
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->value;
    }
}
