<?php
namespace Ciebit\Labels\Tests;

use Ciebit\Labels\Label;
use Ciebit\Labels\Status;
use PHPUnit\Framework\TestCase;

class LabelTest extends TestCase
{
    const ID = '2';
    const PARENT_ID = '1';
    const PARENT_STATUS = 4;
    const PARENT_TITLE = 'Title Parent Example';
    const PARENT_URI = 'title-parent-example';
    const STATUS = 3;
    const TITLE = 'Title Example';
    const URI = 'title-example';

    public function testCreateFromManual()
    {
        $parent = (
            new Label(
                self::PARENT_TITLE,
                self::PARENT_URI,
                null,
                new Status(self::PARENT_STATUS)
            )
        )->setId(self::PARENT_ID);

        $label = (
            new Label(
                self::TITLE,
                self::URI,
                $parent,
                new Status(self::STATUS)
            )
        )->setId(self::ID);

        $this->assertEquals(self::ID, $label->getId());
        $this->assertEquals(self::TITLE, $label->getTitle());
        $this->assertEquals(self::URI, $label->getUri());
        $this->assertEquals(self::STATUS, $label->getStatus()->getValue());
        $this->assertEquals(self::PARENT_ID, $label->getParent()->getId());
        $this->assertEquals(self::PARENT_TITLE, $label->getParent()->getTitle());
        $this->assertEquals(self::PARENT_URI, $label->getParent()->getUri());
        $this->assertEquals(self::PARENT_STATUS, $label->getParent()->getStatus()->getValue());
    }
}
