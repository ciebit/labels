<?php
declare(strict_types=1);
namespace Ciebit\Labels;

use ArrayIterator;
use ArrayObject;
use Countable;
use IteratorAggregate;

class Collection implements Countable, IteratorAggregate
{
    private $labels; #: ArrayObject

    public function __construct()
    {
        $this->labels = new ArrayObject;
    }

    public function add(Label ...$labels): self
    {
        foreach ($labels as $label) {
            $this->labels->append($label);
        }
        return $this;
    }

    public function count(): int
    {
        return $this->labels->count();
    }

    public function getById(int $id): ?Label
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
