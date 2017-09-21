<?php

namespace BGAWorkbench;

use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;
use Functional as F;

class Utils
{
    /**
     * @param \SplFileInfo $file
     * @param string|callable $namePredicate
     * @return Option
     */
    public static function getVariableNameFromFile(\SplFileInfo $file, $namePredicate) : Option
    {
        return self::getVariableFromFile($file, $namePredicate)
            ->map(function (array $variable) {
                list($name, $value) = $variable;
                return $name;
            });
    }

    /**
     * @param \SplFileInfo $file
     * @param string|callable $namePredicate
     * @return Option
     */
    public static function getVariableValueFromFile(\SplFileInfo $file, $namePredicate) : Option
    {
        return self::getVariableFromFile($file, $namePredicate)
            ->map(function (array $variable) {
                list($name, $value) = $variable;
                return $value;
            });
    }

    /**
     * @param \SplFileInfo $file
     * @param string|callable $namePredicate
     * @return Option
     */
    private static function getVariableFromFile(\SplFileInfo $file, $namePredicate) : Option
    {
        if (!$file->isReadable()) {
            throw new \InvalidArgumentException("Couldn't open file {$file->getPathname()}");
        }

        if (is_string($namePredicate)) {
            $stringNeedle = $namePredicate;
            $namePredicate = function ($name) use ($stringNeedle) {
                return $name === $stringNeedle;
            };
        }

        include($file->getPathname());
        $definedVars = get_defined_vars();
        return F\reduce_left(
            array_keys($definedVars),
            function ($name, $i, $all, Option $current) use ($namePredicate, $definedVars) {
                if ($namePredicate($name)) {
                    return new Some([$name, $definedVars[$name]]);
                }
                return $current;
            },
            None::create()
        );
    }

    /**
     * @param object $object
     * @param string $methodName
     * @param mixed ...$args
     * @return mixed
     */
    public static function callProtectedMethod($object, string $methodName, ...$args)
    {
        $gameClass = new \ReflectionClass($object);
        $method = $gameClass->getMethod($methodName);
        if (!$method->isProtected()) {
            throw new \RuntimeException("Method {$gameClass->getName()}->{$methodName} isn't protected");
        }

        $method->setAccessible(true);
        return $method->invokeArgs($object, $args);
    }
}
