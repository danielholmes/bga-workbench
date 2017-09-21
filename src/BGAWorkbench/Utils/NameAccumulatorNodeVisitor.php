<?php

namespace BGAWorkbench\Utils;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

class NameAccumulatorNodeVisitor extends NodeVisitorAbstract
{
    public $names = [];

    public function leaveNode(Node $node)
    {
        if ($node instanceof Name) {
            $this->names[] = $node;
        }
    }
}
