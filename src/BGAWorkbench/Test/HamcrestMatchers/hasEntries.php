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
