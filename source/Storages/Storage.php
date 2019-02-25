<?php
namespace Ciebit\Labels\Storages;

use Ciebit\Labels\Collection;
use Ciebit\Labels\Label;
use Ciebit\Labels\Status;

interface Storage
{
    /** @var string */
    public const FIELD_ID = 'id';

    /** @var string */
    public const FIELD_PARENT_ID = 'parent_id';

    /** @var string */
    public const FIELD_SLUG = 'slug';

    /** @var string */
    public const FIELD_STATUS = 'status';

    /** @var string */
    public const FIELD_TITLE = 'title';

    public function addFilterById(string $operator, string ...$id): self;

    public function addFilterByParentId(string $operator, string ...$id): self;

    public function addFilterBySlug(string $operator, string ...$slug): self;

    public function addFilterByStatus(string $operator, Status ...$status): self;

    public function addFilterByTitle(string $operator, string ...$title): self;

    public function addOrder(string $field, string $order): self;

    public function destroy(Label $label): self;

    public function getTotalItemsOfLastFindWithoutFilters(): int;

    public function findAll(): Collection;

    public function findOne(): ?Label;

    public function setLimit(int $limit): self;

    public function setOffset(int $offset): self;

    public function store(Label $label): self;

    public function update(Label $label): self;
}
