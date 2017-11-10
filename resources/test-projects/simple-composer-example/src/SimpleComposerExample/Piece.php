<?php


namespace SimpleComposerExample;

use PhpOption\Option;
use PhpOption\Some;

class Piece
{
    /**
     * @var Option
     */
    private $color;

    /**
     * @param Option $color
     */
    public function __construct(Option $color)
    {
        $this->color = Some::create($color->getOrElse('black'));
    }
}