# Lightweight Entity Manager

Simple mapper library for converting an array to a class object (entity) with your structure. 
Work with any data as object models.

### Installation
`composer require taras-chr/lightweight-entity-manager`

## Usage
Create object model with desired structure. 
* All properties must be private
* Property names must match with array keys (or you can bind that by annotation)
```php
<?php
namespace EntityManager\Example\Entity;

use EntityManager\Entity;
/**
 * All entities must implements interface Entity
 * @package EntityManager\Example\Entity
 */
class User implements Entity
{
    /** 
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $birthday;
    
    /**
     * @var string
     */
    private $otherField;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday(): \DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     */
    public function setBirthday(\DateTime $birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return string
     */
    public function getOtherField(): string
    {
        return $this->otherField;
    }

    /**
     * @param string $otherField
     */
    public function setOtherField(string $otherField): void
    {
        $this->otherField = $otherField;
    }

}
```

Create mapper for that User entity.

```php
<?php

namespace EntityManager\Example\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use EntityManager\EntityMapper;
use EntityManager\Example\Entity\User;
use EntityManager\Mapper;

class UserMapper implements Mapper
{
    /**
     * @var ArrayCollection
     */
    private $collection;

    /**
     * Required method from inteface
     * Of course you can create constructor and initialize mapper with collection
     * But this method is using by mapping process
     * @inheritDoc
     */
    public function setCollection(ArrayCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Return result of mapping
     * @inheritDoc
     */
    public function getMapped()
    {
        /**
         * Pass collection and instance of desired entity
         */
        return EntityMapper::createFrom(
            $this->collection,
            new User()
        )
            /**
             * You can add some sub mappers for entity properties
             * As example if you need transform some field from array to child Entity or DateTime as in this case
             */
            ->setSubMapper('birthday', new UserBirthDayMapper())
            /**
             * It is exists ability to bind some other property with entity
             * Useful if need to add some data that not provided by collection
             */
            ->bindProperty('otherField', 'Some information than not received from collection')
            /**
             * Use mapSingle() if need to process just one entity
             * Use mapList() if need to process list of data (collections), e.g array of users
             */
            ->mapSingle();
    }
}
```

Sub mapper for birthday
```php
<?php

namespace EntityManager\Example\Mapper;

use Doctrine\Common\Collections\ArrayCollection;
use EntityManager\Mapper;

class UserBirthDayMapper implements Mapper
{
    /**
     * @var ArrayCollection
     */
    private $collection;

    /**
     * @inheritDoc
     */
    public function setCollection(ArrayCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @inheritDoc
     */
    public function getMapped(): \DateTime
    {
        /**
         * There it can be returned anything
         * It can be other mappers or any type of object or scalar data
         */
        return new \DateTime($this->collection->get('birthday'));
    }
}
```

There is exists ability to specify some annotations for entity properties

```php
<?php
// ...
class MyEntity implements \EntityManager\Entity
{
    // ...
    /**
      * @\EntityManager\Property(field="target_array_field_name", validator=YourNamespace\\Validators\\SomeValidator::class) 
      * @var string
      */
    private $property;
}
```

* Parameter "field" add ability to bind given property with some array field that have other name than that property
* Parameter "validator" allows to validate values of property before writing by setter

```php
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
     * Values receiving by constructor
     * @inheritDoc
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Set logical result of validator
     * If false it will be thrown EntityManagerException
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return strpos($this->value, 'a') !== false;
    }

    /**
     * Result of validator can be modified as you want
     * @inheritDoc
     */
    public function getValue()
    {
        return str_replace('a', 'b', $this->value);
    }
}
```
### More examples

_See in examples directory_
