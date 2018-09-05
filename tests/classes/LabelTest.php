<?php
namespace Ciebit\Labels\Tests;

use Ciebit\Labels\Label;
use Ciebit\Labels\Status;
use PHPUnit\Framework\TestCase;

class LabelTest extends TestCase
{
    const ID = '2';
    const PARENT_ID = '1';
    const STATUS = 3;
    const TITLE = 'Title Example';
    const URI = 'title-example';

    static function getLabel(): Label
    {
        return (new Label(
            self::TITLE,
            self::URI,
            new Status(self::STATUS)
        ))->setId(self::ID)
        ->setAscendantsId([1]);
    }

    public function testCreateFromManual(): void
    {
        $label = self::getLabel();

        $this->assertEquals(self::ID, $label->getId());
        $this->assertEquals(self::TITLE, $label->getTitle());
        $this->assertEquals(self::URI, $label->getUri());
        $this->assertEquals(self::STATUS, $label->getStatus()->getValue());
        $this->assertEquals(self::PARENT_ID, $label->getParentId());
    }
}
