<?php

declare (strict_types=1);
namespace Spatie\Response_Cache\Support;

use Spatie\Attributes\Attributes;
class Attribute_Reader
{
    /**
     * Get the first matching attribute from a controller action.
     *
     * @param  string  $action  The controller action in "Controller@method" format
     * @param  array  $attributeClasses  Array of attribute class names to search for
     * @return object|null The first matching attribute instance or null if none found
     */
    public static function get_first_attribute(string $action, array $attribute_classes): ?object
    {
        [$controller, $method] = static::parse_action($action);
        if (!$controller || !$method) {
            return null;
        }
        if (!class_exists($controller)) {
            return null;
        }
        // Check method-level attributes first (they take precedence)
        foreach ($attribute_classes as $attribute_class) {
            $attribute = Attributes::on_method($controller, $method, $attribute_class);
            if ($attribute) {
                return $attribute;
            }
        }
        // Then check class-level attributes
        foreach ($attribute_classes as $attribute_class) {
            $attribute = Attributes::get($controller, $attribute_class);
            if ($attribute) {
                return $attribute;
            }
        }
        return null;
    }
    protected static function parse_action(string $action): array
    {
        if (!str_contains($action, '@')) {
            return [null, null];
        }
        return explode('@', $action);
    }
}