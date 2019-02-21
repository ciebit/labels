<?php
namespace Ciebit\Labels;

use Ciebit\Labels\Status;

use function array_map;
use function end;
use function strval;

class Label
{
    /** @var array <string> */
    private $ascendantsId;

    /** @var string */
    private $id;

    /** @var string */
    private $slug;

    /** @var string */
    private $title;

    /** @var Status */
    private $status;

    public function __construct (
        string $title,
        string $slug,
        Status $status
    ) {
        $this->ascendantsId = [];
        $this->id = '';
        $this->title = $title;
        $this->slug = $slug;
        $this->status = $status;
    }

    public function getAscendantsId(): array
    {
        return $this->ascendantsId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getParentId(): string
    {
        return end($this->ascendantsId);
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setAscendantsId(array $ids): self
    {
        $ids = array_map('strval', $ids);
        $this->ascendantsId = $ids;
        return $this;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }
}
