<?php
namespace Ciebit\Labels\Tests;

use Ciebit\Labels\Label;
use Ciebit\Labels\Status;
use PHPUnit\Framework\TestCase;

use function end;

class LabelTest extends TestCase
{
    /** @var string */
    public const ID = '3';

    /** @var string */
    public const PARENT_ID = '2';

    /** @var int */
    public const STATUS = 3;

    /** @var string */
    public const TITLE = 'Title Example';

    /** @var string */
    public const SLUG = 'title-example';

    static function getLabel(): Label
    {
        return (new Label(
            self::TITLE,
            self::SLUG,
            new Status(self::STATUS)
        ))->setId(self::ID)
        ->setParentId(self::PARENT_ID);
    }

    public function testCreateFromManual(): void
    {
        $label = self::getLabel();
        $this->assertEquals(self::ID, $label->getId());
        $this->assertEquals(self::TITLE, $label->getTitle());
        $this->assertEquals(self::SLUG, $label->getSlug());
        $this->assertEquals(self::STATUS, $label->getStatus()->getValue());
        $this->assertEquals(self::PARENT_ID, $label->getParentId());
    }
}
