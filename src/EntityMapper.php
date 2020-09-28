<?php
declare(strict_types=1);

namespace EntityManager;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

use function class_exists;
use function in_array;
use function is_callable;
use function ucfirst;

final class EntityMapper
{
    /**
     * @var ArrayCollection
     */
    private $collection;

    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var array<string,Mapper>
     * @var Mapper[]
     */
    private $nestedMapper = [];

    /**
     * @var array<\EntityManager\Entity>|Entity[]
     */
    private $mapped = [];

    /**
     * @var array<string,mixed>
     */
    private $bindings = [];

    /**
     * Mapper constructor.
     * @param ArrayCollection $collection
     * @param Entity $entity
     */
    public function __construct(ArrayCollection $collection, Entity $entity)
    {
        $this->collection = $collection;
        $this->entity = $entity;
    }

    /**
     * @param ArrayCollection $collection
     * @param Entity $entity
     * @return EntityMapper
     */
    public static function createFrom(ArrayCollection $collection, Entity $entity): EntityMapper
    {
        return new self($collection, $entity);
    }

    /**
     * @param ArrayCollection $collection
     * @param Entity $entity
     * @throws ReflectionException
     * @throws EntityManagerException
     */
    private function map(ArrayCollection $collection, Entity $entity): void
    {
        $entityReflection = new ReflectionClass($entity);
        $entityProperties = $entityReflection->getProperties(ReflectionProperty::IS_PRIVATE);
        $collectionProperties = $collection->getKeys();
        $annotationReader = new AnnotationReader();

        foreach ($entityProperties as $property) {
            $fieldName = $this->getFieldName($property, $annotationReader);
            $propertyName = $property->getName();

            if (in_array($fieldName, $collectionProperties, true)) {
                $setterMethod = 'set' . ucfirst($propertyName);
                $value = $this->validateValue($annotationReader, $property, $collection->get($fieldName));

                if (isset($this->nestedMapper[$propertyName]) && $this->nestedMapper[$propertyName] instanceof Mapper) {
                    $value = $this->bindNestedMapper($this->nestedMapper[$propertyName], $value, $propertyName);
                }

                if (is_callable([$entity, $setterMethod])) {
                    $entity->$setterMethod($value);
                }
            }
        }

        $this->mapped[] = $entity;
    }

    /**
     * Runs field validator if it expected
     * Or returns "raw" value
     *
     * @param AnnotationReader $annotationReader
     * @param ReflectionProperty $property
     * @param $value
     * @return mixed
     * @throws EntityManagerException
     */
    private function validateValue(AnnotationReader $annotationReader, ReflectionProperty $property, $value)
    {
        /** @var Property|null $annotation */
        $annotation = $annotationReader->getPropertyAnnotation($property, Property::class);
        if ($annotation && $annotation->validator && class_exists($annotation->validator)) {
            /** @var Validator $validator */
            $validator = new $annotation->validator($value);
            if (!$validator->isValid()) {
                throw new EntityManagerException("Invalid property value in {$property->getDeclaringClass()->getName()}::\${$property->getName()} (validated by {$annotation->validator})");
            }
            $value = $validator->getValue();
        }
        return $value;
    }

    /**
     * Bind property with other nested mapper
     * It can be endless chain of nested mappers
     * Main process will be finished only after processed nested mappers
     *
     * @param Mapper $mapper
     * @param $value
     * @param string $property
     * @return mixed
     */
    private function bindNestedMapper(Mapper $mapper, $value, string $property)
    {
        if ($value instanceof ArrayCollection) {
            $mapper->setCollection($value);
        } elseif (is_array($value)) {
            $mapper->setCollection(new ArrayCollection($value));
        } else {
            $mapper->setCollection(new ArrayCollection([$property => $value]));
        }

        return $mapper->getMapped();
    }

    /**
     * Map for list of collection
     * Returns array of entities
     *
     * @return Entity[]
     * @throws ReflectionException
     * @throws EntityManagerException
     */
    public function mapList(): array
    {
        foreach ($this->collection as $item) {
            $this->processCollectionToEntity($item, $this->entity);
        }

        return $this->mapped;
    }

    /**
     * Get just one entity as result
     *
     * @return Entity
     * @throws ReflectionException
     * @throws EntityManagerException
     */
    public function mapSingle(): Entity
    {
        $this->processCollectionToEntity($this->collection, $this->entity);
        return !empty($this->mapped) ? $this->mapped[0] : $this->entity;
    }

    /**
     * Bind collection property with entity properties
     *
     * @param ArrayCollection|array $collection
     * @param Entity $entity
     * @throws ReflectionException
     * @throws EntityManagerException
     */
    private function processCollectionToEntity($collection, Entity $entity): void
    {
        $object = null;
        if ($collection instanceof ArrayCollection) {
            $object = $collection;
        } elseif (is_array($collection)) {
            $object = new ArrayCollection($collection);
        }

        if ($object) {
            $object = $this->setBindings($object);
            $this->map($object, $entity);
        }
    }

    /**
     * @param string $property
     * @param Mapper $mapper
     * @return EntityMapper
     */
    public function setNestedMapper(string $property, Mapper $mapper): EntityMapper
    {
        $this->nestedMapper[$property] = $mapper;
        return $this;
    }

    /**
     * @param ReflectionProperty $property
     * @param AnnotationReader $annotationReader
     * @return string
     */
    private function getFieldName(ReflectionProperty $property, AnnotationReader $annotationReader): string
    {
        /** @var Property|null $annotation */
        $annotation = $annotationReader->getPropertyAnnotation($property, Property::class);

        if ($annotation && $annotation->field) {
            $fieldName = $annotation->field;
        } else {
            $fieldName = $property->getName();
        }

        return $fieldName;
    }

    /**
     * @param string $property
     * @param mixed $value
     * @return EntityMapper
     */
    public function bindProperty(string $property, $value): EntityMapper
    {
        $this->bindings[$property] = $value;
        return $this;
    }

    /**
     * Alias method for EntityMapper::bindProperty()
     * Requires Mapper as second parameter
     * @param string $property
     * @param Mapper $mapper
     * @return EntityMapper
     */
    public function bindPropertyWithMapper(string $property, Mapper $mapper): EntityMapper
    {
        $this->bindProperty($property, $mapper);
        return $this;
    }

    /**
     * @param ArrayCollection $collection
     * @return ArrayCollection
     */
    private function setBindings(ArrayCollection $collection): ArrayCollection
    {
        if (!empty($this->bindings)) {
            foreach ($this->bindings as $key => $value) {
                if($value instanceof Mapper) {
                    $value->setCollection($collection);
                    $collection->set($key, $value->getMapped());
                    continue;
                }
                $collection->set($key, $value);
            }
        }
        return $collection;
    }
}
