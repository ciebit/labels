<?php
namespace Ciebit\Labels\Tests\Storages\Database;

use Ciebit\Labels\Collection;
use Ciebit\Labels\Label;
use Ciebit\Labels\Status;
use Ciebit\Labels\Storages\Storage;
use Ciebit\Labels\Storages\Database\Sql;
use Ciebit\Labels\Tests\BuildPdo;
use Ciebit\Labels\Tests\LabelTest;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

class SqlTest extends TestCase
{
    private function getStorage(): Sql
    {
        return new Sql(BuildPdo::build());
    }

    private function setDatabaseDefault(): void
    {
        $pdo = BuildPdo::build();
        $pdo->query('DELETE FROM `cb_labels`');
        $pdo->query(file_get_contents(__DIR__.'/../../../../database/data-example.sql'));
    }

    public function testDestroy(): void
    {
        $this->setDatabaseDefault();
        $label = LabelTest::getLabel()->setId('1');
        $storage = $this->getStorage();
        $storage->destroy($label);
        $this->assertEmpty($label->getId());
        $label = $storage->addFilterById('=', '1')->findOne();
        $this->assertNull($label);
    }

    public function testFindAll(): void
    {
        $this->setDatabaseDefault();
        $storage = $this->getStorage();
        $labels = $storage->findAll();
        $this->assertInstanceOf(Collection::class, $labels);
        $this->assertCount(6, $labels);
    }

    public function testFindAllFilterById(): void
    {
        $id = 3;
        $storage = $this->getStorage();
        $storage->addFilterById('=', $id+0);
        $labels = $storage->findAll();
        $this->assertCount(1, $labels->getIterator());
        $this->assertEquals($id, $labels->getArrayObject()->offsetGet(0)->getId());
    }

    public function testFindAllFilterByMultipleIds(): void
    {
        $storage = $this->getStorage();
        $storage->addFilterById('=', ...[2,3,4]);
        $labels = $storage->findAll();

        $labelsArray = $labels->getArrayObject();
        $this->assertEquals(2, $labelsArray->offsetGet(0)->getId());
        $this->assertEquals(3, $labelsArray->offsetGet(1)->getId());
        $this->assertEquals(4, $labelsArray->offsetGet(2)->getId());
    }

    public function testFindAllFilterByStatus(): void
    {
        $storage = $this->getStorage();
        $storage->addFilterByStatus('=', Status::ACTIVE());
        $labels = $storage->findAll();
        $this->assertCount(2, $labels);
        $this->assertEquals(Status::ACTIVE(), $labels->getArrayObject()->offsetGet(0)->getStatus());
    }

    public function testFindAllOrder(): void
    {
        $this->setDatabaseDefault();
        $sql = $this->getStorage();
        $labels = $sql->addOrder(Storage::FIELD_TITLE, 'DESC')->findAll();
        $label = $labels->getArrayObject()->offsetGet(0);
        $this->assertEquals(5, $label->getId());
    }

    public function testFindOne(): void
    {
        $label = $this->getStorage()->findOne();
        $this->assertInstanceOf(Label::class, $label);
    }

    public function testFindOneFilterById(): void
    {
        $id = 2;
        $storage = $this->getStorage();
        $storage->addFilterById('=', $id+0);
        $label = $storage->findOne();
        $this->assertEquals($id, $label->getId());
    }

    public function testFindOneFilterBySlug(): void
    {
        $slug = 'relatorios';
        $storage = $this->getStorage();
        $storage->addFilterBySlug('=', $slug.'');
        $label = $storage->findOne();
        $this->assertEquals($slug, $label->getSlug());
    }

    public function testFindOneFilterByStatus(): void
    {
        $sql = $this->getStorage();
        $sql->addFilterByStatus('=', Status::ACTIVE());
        $label = $sql->findOne();
        $this->assertEquals(Status::ACTIVE(), $label->getStatus());
    }

    public function testFindOneOrder(): void
    {
        $this->setDatabaseDefault();
        $sql = $this->getStorage();
        $label = $sql->addOrder(Storage::FIELD_TITLE, 'DESC')->findOne();
        $this->assertEquals(5, $label->getId());
    }

    public function testStore(): void
    {
        $label1 = LabelTest::getLabel()->setId('');
        $storage = $this->getStorage()->store($label1);
        $label2 = $storage->addFilterById('=', $label1->getId())->findOne();
        $this->assertEquals($label1, $label2);
    }

    public function testUpdate(): void
    {
        $label1 = LabelTest::getLabel()->setId('5');
        $storage = $this->getStorage()->update($label1);
        $label2 = $storage->addFilterById('=', $label1->getId())->findOne();
        $this->assertEquals($label1, $label2);
    }
}
