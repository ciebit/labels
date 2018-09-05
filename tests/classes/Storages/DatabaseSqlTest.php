<?php
namespace Ciebit\Labels\Tests\Storages;

use Ciebit\Labels\Collection;
use Ciebit\Labels\Status;
use Ciebit\Labels\Label;
use Ciebit\Labels\Storages\Database\Sql as DatabaseSql;
use Ciebit\Labels\Tests\Connection;

class DatabaseSqlTest extends Connection
{
    public function testGet(): void
    {
        $this->database = new DatabaseSql($this->getPdo());
        $label = $this->database->get();
        $this->assertInstanceOf(Label::class, $label);
    }

    public function testGetFilterByStatus(): void
    {
        $this->database = new DatabaseSql($this->getPdo());
        $this->database->addFilterByStatus(Status::ACTIVE());
        $label = $this->database->get();
        $this->assertEquals(Status::ACTIVE(), $label->getStatus());
    }

    public function testGetFilterById(): void
    {
        $id = 2;
        $this->database = new DatabaseSql($this->getPdo());
        $this->database->addFilterById($id+0);
        $label = $this->database->get();
        $this->assertEquals($id, $label->getId());
    }

    public function testGetFilterByIds(): void
    {
        $this->database = new DatabaseSql($this->getPdo());
        $this->database->addFilterByIds('=', ...[2,3,4]);
        $labels = $this->database->getAll();

        $labelsArray = $labels->getArrayObject();
        $this->assertEquals(2, $labelsArray->offsetGet(0)->getId());
        $this->assertEquals(3, $labelsArray->offsetGet(1)->getId());
        $this->assertEquals(4, $labelsArray->offsetGet(2)->getId());
    }

    public function testGetAll(): void
    {
        $this->database = new DatabaseSql($this->getPdo());
        $labels = $this->database->getAll();
        $this->assertInstanceOf(Collection::class, $labels);
        $this->assertCount(6, $labels->getIterator());
    }

    public function testGetAllFilterByStatus(): void
    {
        $this->database = new DatabaseSql($this->getPdo());
        $this->database->addFilterByStatus(Status::ACTIVE());
        $labels = $this->database->getAll();
        $this->assertCount(2, $labels);
        $this->assertEquals(Status::ACTIVE(), $labels->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testGetAllFilterById(): void
    {
        $id = 3;
        $this->database = new DatabaseSql($this->getPdo());
        $this->database->addFilterById($id+0);
        $labels = $this->database->getAll();
        $this->assertCount(1, $labels->getIterator());
        $this->assertEquals($id, $labels->getArrayObject()->offsetGet(0)->getId());
    }
}
