<?php

namespace BGAWorkbench\Test\HamcrestMatchers;

use Hamcrest\Matcher;

/**
 * TODO: containsKeys would be useful
 * @param array $keys
 * @return Matcher
 */
function hasKeys(array $keys)
{
    return allOf(array_map('hasKey', $keys));
}
