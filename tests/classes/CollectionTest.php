<?php
namespace Ciebit\Labels\Tests;

use ArrayIterator;
use ArrayObject;
use Ciebit\Labels\Collection;
use Ciebit\Labels\Label;
use Ciebit\Labels\Status;
use Ciebit\Labels\Tests\LabelTest;
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

        $this->assertCount(2, $collection);
        $this->assertEquals(LabelTest::ID, $collection->getById(LabelTest::ID)->getId());
        $this->assertInstanceOf(ArrayIterator::class, $collection->getIterator());
        $this->assertInstanceOf(ArrayObject::class, $collection->getArrayObject());

        $collection->getArrayObject()->append('test');
        $this->assertEquals(2, $collection->count());
    }

    public function testJsonSerialize(): void
    {
        $collection = new Collection;
        $collection->add(
            new Label('Tests 1', 'test-1', Status::ACTIVE()),
            new Label('Tests 2', 'test-2', Status::ACTIVE()),
            new Label('Tests 3', 'test-3', Status::ACTIVE())
        );
        $json = json_encode($collection);

        $this->assertJson($json);

        $data = json_decode($json);
        $this->assertCount(3, $data);
        $this->assertEquals(
            $collection->getArrayObject()->offsetGet(0)->getTitle(), 
            $data[0]->title
        );
    }
}
