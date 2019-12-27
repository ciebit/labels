<?php
namespace Ciebit\Labels;

use Ciebit\Labels\Status;
use JsonSerializable;

class Label implements JsonSerializable
{
    /** @var string */
    private $id;

    /** @var string */
    private $parentId;

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
        $this->id = '';
        $this->parentId = '';
        $this->title = $title;
        $this->slug = $slug;
        $this->status = $status;
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
        return $this->parentId;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'parentId' => $this->getParentId(),
            'slug' => $this->getSlug(),
            'status' => $this->getStatus(),
            'title' => $this->getTitle(),
        ];
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setParentId(string $id): self
    {
        $this->parentId = $id;
        return $this;
    }
}
