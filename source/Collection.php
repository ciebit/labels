<?php
declare(strict_types=1);
namespace Ciebit\Labels;

use ArrayIterator;
use ArrayObject;

class Collection
{
    private $labels; #: ArrayObject

    public function __construct()
    {
        $this->labels = new ArrayObject;
    }

    public function add(Label $label): self
    {
        $this->labels->append($label);
        return $this;
    }

    public function getById(int $id): ?File
    {
        $iterator = $this->getIterator();
        foreach ($iterator as $label) {
            if ($label->getId() == $id) {
                return $label;
            }
        }
        return null;
    }

    public function getArrayObject(): ArrayObject
    {
        return clone $this->labels;
    }

    public function getIterator(): ArrayIterator
    {
        return $this->labels->getIterator();
    }
}
