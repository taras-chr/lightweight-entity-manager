<?php
declare(strict_types=1);

namespace EntityManager;

use Doctrine\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use function in_array;
use function is_callable;
use function ucfirst;

class EntityMapper
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
    private $subMapper = [];

    /**
     * @var array
     */
    private $mapped = [];

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
     * @throws \Doctrine\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function map(ArrayCollection $collection, Entity $entity)
    {
        $entityReflection = new \ReflectionClass($entity);
        $entityProperties = $entityReflection->getProperties(\ReflectionProperty::IS_PRIVATE);
        $collectionProperties = $collection->getKeys();
        $annotationReader = new AnnotationReader();

        foreach ($entityProperties as $property) {
            $fieldName = $this->getFieldName($property, $annotationReader);
            $propertyName = $property->getName();

            if (in_array($fieldName, $collectionProperties, true)) {
                $methodName = 'set' . ucfirst($propertyName);
                $value = $collection->get($fieldName);

                if (isset($this->subMapper[$propertyName]) && $this->subMapper[$propertyName] instanceof Mapper) {
                    $value = $this->bindSubMapper($this->subMapper[$propertyName], $value, $propertyName);
                }

                if (is_callable([$entity, $methodName])) {
                    $entity->$methodName($value);
                }
            }
        }

        $this->mapped[] = $entity;
    }

    /**
     * @param Mapper $mapper
     * @param $value
     * @param string $property
     * @return mixed
     */
    private function bindSubMapper(Mapper $mapper, $value, string $property)
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
     * @return Entity[]
     * @throws \Doctrine\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function mapList(): array
    {
        foreach ($this->collection as $item) {
            $this->processCollectionToEntity($item, $this->entity);
        }

        return $this->mapped;
    }

    /**
     * @throws \Doctrine\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function mapSingle()
    {
        $this->processCollectionToEntity($this->collection, $this->entity);
        return array_shift($this->mapped);
    }

    /**
     * @param ArrayCollection|array $collection
     * @param Entity $entity
     * @throws \Doctrine\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function processCollectionToEntity($collection, Entity $entity)
    {
        if ($collection instanceof ArrayCollection) {
            $this->map($collection, $entity);
        } elseif (is_array($collection)) {
            $this->map(new ArrayCollection($collection), $entity);
        }
    }

    /**
     * @param string $property
     * @param Mapper $mapper
     * @return EntityMapper
     */
    public function setSubMapper(string $property, Mapper $mapper): EntityMapper
    {
        $this->subMapper[$property] = $mapper;
        return $this;
    }

    /**
     * @param \ReflectionProperty $property
     * @param AnnotationReader $annotationReader
     * @return string
     */
    private function getFieldName(\ReflectionProperty $property, AnnotationReader $annotationReader): string
    {
        /** @var Property|null $annotation */
        $annotation = $annotationReader->getPropertyAnnotation(
            $property,
            Property::class
        );

        if ($annotation) {
            $fieldName = $annotation->field;
        } else {
            $fieldName = $property->getName();
        }

        return $fieldName;
    }
}
