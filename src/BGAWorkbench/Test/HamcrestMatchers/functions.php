<?php

namespace BGAWorkbench\Test\HamcrestMatchers;

use Hamcrest\Matcher;

/**
 * @param array $entries
 * @return Matcher
 */
function hasEntries(array $entries)
{
    return allOf(array_map('hasEntry', array_keys($entries), $entries));
}

/**
 * TODO: containsKeys would be useful
 * @param array $keys
 * @return Matcher
 */
function hasKeys(array $keys)
{
    return allOf(array_map('hasKey', $keys));
}
