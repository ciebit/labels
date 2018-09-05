<?php
declare(strict_types=1);
namespace Ciebit\Labels;

class Label
{
    private $id; #int
    private $title; #string
    private $parent; #?Label
    private $uri; #string
    private $status; #Status

    public function __construct
    (
        string $title,
        string $uri,
        ?Label $parent,
        Status $status
    )
    {
        $this->title = $title;
        $this->uri = $uri;
        $this->parent = $parent;
        $this->status = $status;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getParent(): ?Label
    {
        return $this->parent;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
