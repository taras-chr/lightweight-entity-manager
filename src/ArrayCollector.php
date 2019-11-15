<?php
declare(strict_types=1);

namespace EntityManager;

use Doctrine\Common\Collections\ArrayCollection;

class ArrayCollector
{
    /**
     * @param Entity $entity
     * @return ArrayCollection
     * @throws \ReflectionException
     */
    public static function fromEntity(Entity $entity): ArrayCollection
    {
        $reflection = new \ReflectionClass($entity);
        $collect = [];
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PRIVATE) as $property) {
            $getter = 'get' . ucfirst($property->getName());
            if (is_callable([$entity, $getter])) {
                $value = $entity->$getter();
                if (is_scalar($value) || is_null($value)) {
                    $collect[$property->getName()] = $value;
                } elseif ($value instanceof Entity) {
                    $collect[$property->getName()] = self::fromEntity($value);
                } elseif (is_array($value)) {
                    $collect[$property->getName()] = self::setFromArray($value);
                }
            }
        }
        return new ArrayCollection($collect);
    }

    /**
     * @param Entity[] $entities
     * @return ArrayCollection[]
     * @throws \ReflectionException
     */
    public static function fromEntityList(array $entities): array
    {
        $list = [];
        foreach ($entities as $entity) {
            $list[] = self::fromEntity($entity);
        }
        return $list;
    }

    /**
     * @param array $array
     * @return array
     * @throws \ReflectionException
     */
    private static function setFromArray(array $array): array
    {
        $result = [];
        foreach ($array as $value) {
            if ($value instanceof Entity) {
                $result[] = self::fromEntity($value);
            } else {
                $result[] = $value;
            }
        }
        return $result;
    }
}
