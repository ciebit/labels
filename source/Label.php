<?php
declare(strict_types=1);

namespace Ciebit\Labels;

class Label
{
    private $ascendantsId; #: array<string>
    private $id; #string
    private $title; #string
    private $uri; #string
    private $status; #Status

    public function __construct (
        string $title,
        string $uri,
        Status $status
    ) {
        $this->ascendantsId = [];
        $this->id = '';
        $this->title = $title;
        $this->uri = $uri;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getParentId(): string
    {
        return (string) end($this->ascendantsId);
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setAscendantsId(array $ids): self
    {
        $this->ascendantsId = $ids;
        return $this;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }
}
