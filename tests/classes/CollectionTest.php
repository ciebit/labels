<?php
namespace Ciebit\Labels\Tests;

use ArrayIterator;
use ArrayObject;
use Ciebit\Labels\Collection;
use Ciebit\Labels\Label;
use Ciebit\Labels\Status;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testCreate()
    {
        $collection = new Collection;
        $label = LabelTest::getLabel();
        $collection->add(
            $label,
            new Label('Tests 2', 'teste-2', Status::ACTIVE())
        );

        $this->assertEquals(2, $collection->count());
        $this->assertEquals(LabelTest::ID, $collection->getById(LabelTest::ID)->getId());
        $this->assertInstanceOf(ArrayIterator::class, $collection->getIterator());
        $this->assertInstanceOf(ArrayObject::class, $collection->getArrayObject());

        $collection->getArrayObject()->append('test');
        $this->assertEquals(2, $collection->count());
    }
}
