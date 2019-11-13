<?php
declare(strict_types=1);

namespace EntityManager;

use Doctrine\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use function ucfirst;

class Mapper
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
     * @return Mapper
     */
    public static function createFrom(ArrayCollection $collection, Entity $entity): Mapper
    {
        return new self($collection, $entity);
    }

    /**
     * @throws \Doctrine\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    private function map()
    {
        $entityReflection = new \ReflectionClass($this->entity);
        $entityProperties = $entityReflection->getProperties(\ReflectionProperty::IS_PRIVATE);
        $collectionProperties = $this->collection->getKeys();
        $annotationReader = new AnnotationReader();

        foreach ($entityProperties as $property) {
            /** @var Property|null $annotation */
            $annotation = $annotationReader->getPropertyAnnotation(
                $property,
                Property::class
            );

            if($annotation) {
                $propertyName = $annotation->field;
            } else {
                $propertyName = $property->getName();
            }

            $methodName = 'set' . ucfirst($propertyName);
            if(is_callable([$this->entity, $methodName])) {

            }
        }
    }
}
