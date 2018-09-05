<?php
declare(strict_types=1);

namespace Ciebit\Labels\Storages;

use Ciebit\Labels\Collection;
use Ciebit\Labels\Label;
use Ciebit\Labels\Status;

interface Storage
{
    public function addFilterById(int $id, string $operator = '='): self;

    public function addFilterByStatus(Status $status, string $operator = '='): self;

    public function addFilterByTitle(string $title, string $operator = '='): self;

    public function get(): ?Label;

    public function getAll(): Collection;

    public function setStartingLine(int $lineInit): self;

    public function setTotalLines(int $total): self;
}
