<?php

namespace EntityManager\Example\Entity;

use EntityManager\Entity;
use EntityManager\Property;
use EntityManager\Example\Validator\CountryNameValidator;

class Country implements Entity
{
    /**
     * @var string
     * @Property(validator=CountryNameValidator::class)
     */
    private $name;

    /**
     * @var string
     */
    private $capital;

    /**
     * @var string
     */
    private $nativeName;

    /**
     * @var Currency[]
     * @Property(field="currencies")
     */
    private $currency;

    /**
     * @var Coordinates
     * @Property(field="latlng")
     */
    private $coordinates;

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCapital(): string
    {
        return $this->capital;
    }

    /**
     * @param string $capital
     */
    public function setCapital(string $capital): void
    {
        $this->capital = $capital;
    }

    /**
     * @return Currency[]
     */
    public function getCurrency(): array
    {
        return $this->currency;
    }

    /**
     * @param Currency[] $currency
     */
    public function setCurrency(array $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return Coordinates
     */
    public function getCoordinates(): Coordinates
    {
        return $this->coordinates;
    }

    /**
     * @param Coordinates $coordinates
     */
    public function setCoordinates(Coordinates $coordinates): void
    {
        $this->coordinates = $coordinates;
    }

    /**
     * @return string
     */
    public function getNativeName(): string
    {
        return $this->nativeName;
    }

    /**
     * @param string $nativeName
     */
    public function setNativeName(string $nativeName): void
    {
        $this->nativeName = $nativeName;
    }
}
