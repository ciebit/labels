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

    /** @var array <string> */
    public const ASCENDANTS_ID = ['1', '2'];

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
        ->setAscendantsId(self::ASCENDANTS_ID);
    }

    public function testCreateFromManual(): void
    {
        $label = self::getLabel();
        $ascendantsId = self::ASCENDANTS_ID;

        $this->assertEquals(self::ID, $label->getId());
        $this->assertEquals(self::TITLE, $label->getTitle());
        $this->assertEquals(self::SLUG, $label->getSlug());
        $this->assertEquals(self::STATUS, $label->getStatus()->getValue());
        $this->assertEquals(self::ASCENDANTS_ID, $label->getAscendantsId());
        $this->assertEquals(end($ascendantsId), $label->getParentId());
    }
}
