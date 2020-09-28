<?php

declare(strict_types=1);

namespace EntityManager;

/**
 * Class PropertyAnnotation
 * @package EntityManager
 * @Annotation
 */
final class Property
{
    /**
     * You can map property with field of collection if names is different
     * Just specify annotation with field name in collection before entity property
     * It will automatically map property with that field
     * @example: @Property(field=my_field)
     *
     * @var string
     */
    public $field;

    /**
     * Specify own validation rules for property
     * Validators must implement Validator interface
     * @example: @Property(validator=MyValidator::class)
     *
     * @var string
     */
    public $validator;
}
