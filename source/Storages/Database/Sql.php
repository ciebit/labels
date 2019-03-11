<?php
namespace Ciebit\Labels\Storages\Database;

use Ciebit\Labels\Collection;
use Ciebit\Labels\Label;
use Ciebit\Labels\Status;
use Ciebit\Labels\Storages\Storage;
use Ciebit\Labels\Storages\Database\Database;
use Ciebit\SqlHelper\Sql as SqlHelper;
use Exception;
use PDO;

use function array_map;
use function implode;
use function intval;

class Sql implements Database
{
    /** @var string */
    private const COLUMN_ID = 'id';

    /** @var string */
    private const COLUMN_PARENT_ID = 'parent_id';

    /** @var string */
    private const COLUMN_SLUG = 'slug';

    /** @var string */
    private const COLUMN_STATUS = 'status';

    /** @var string */
    private const COLUMN_TITLE = 'title';

    /** @var PDO */
    private $pdo;

    /** @var SqlHelper */
    private $sqlHelper;

    /** @var string */
    private $table;

    /** @var int */
    private $totalItemsOfLastFindWithoutLimit;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->sqlHelper = new SqlHelper;
        $this->table = 'cb_labels';
        $this->totalItemsOfLastFindWithoutLimit = 0;
    }

    public function __clone()
    {
        $this->sqlHelper = clone $this->sqlHelper;
    }

    private function addFilter(string $fieldName, int $type, string $operator, ...$value): self
    {
        $field = "`{$this->table}`.`{$fieldName}`";
        $this->sqlHelper->addFilterBy($field, $type, $operator, ...$value);
        return $this;
    }

    public function addFilterById(string $operator, string ...$ids): Storage
    {
        $ids = array_map('intval', $ids);
        $this->addFilter(self::COLUMN_ID, PDO::PARAM_INT, $operator, ...$ids);
        return $this;
    }

    public function addFilterByParentId(string $operator, string ...$ids): Storage
    {
        $ids = array_map('intval', $ids);
        $this->addFilter(self::COLUMN_PARENT_ID, PDO::PARAM_INT, $operator, ...$ids);
        return $this;
    }

    public function addFilterBySlug(string $operator, string ...$slug): Storage
    {
        $this->addFilter(self::COLUMN_SLUG, PDO::PARAM_STR, $operator, ...$slug);
        return $this;
    }

    public function addFilterByStatus(string $operator, Status ...$status): Storage
    {
        $statusInt = array_map(function($status){
            return (int) $status->getValue();
        }, $status);
        $this->addFilter(self::COLUMN_STATUS, PDO::PARAM_INT, $operator, ...$statusInt);
        return $this;
    }

    public function addFilterByTitle(string $operator, string ...$titles): Storage
    {
        $this->addFilter(self::COLUMN_TITLE, PDO::PARAM_STR, $operator, ...$titles);
        return $this;
    }

    public function addOrder(string $field, string $order = 'ASC'): Storage
    {
        $this->sqlHelper->addOrderBy($field, $order);
        return $this;
    }

    private function build(array $data): Label
    {
        $label = new Label(
            $data[self::COLUMN_TITLE],
            $data[self::COLUMN_SLUG],
            new Status((int) $data[self::COLUMN_STATUS])
        );

        $label->setId($data[self::COLUMN_ID])
        ->setParentId((string) $data[self::COLUMN_PARENT_ID]);

        return $label;
    }

    /** @throws Exception */
    public function destroy(Label $label): Storage
    {
        $statement = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE `id` = :id"
        );

        $statement->bindValue(':id', $label->getId(), PDO::PARAM_INT);

        if (! $statement->execute()) {
            throw new Exception('ciebit.label.storages.destroy', 4);
        }

        $label->setId('');

        return $this;
    }

    public function findAll(): Collection
    {
        $fields = implode(',', $this->getFieldsSql());
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$fields}
            FROM {$this->table}
            WHERE {$this->sqlHelper->generateSqlFilters()}
            {$this->sqlHelper->generateSqlOrder()}
            {$this->sqlHelper->generateSqlLimit()}
        ");

        $this->sqlHelper->bind($statement);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.labels.storage.find_error', 2);
        }

        $this->updateTotalItemsWithoutFilters();

        $collection = new Collection;

        while ($label = $statement->fetch(PDO::FETCH_ASSOC)) {
            $collection->add(
                $this->build($label)
            );
        }

        return $collection;
    }

    /** @throws Exception */
    public function findOne(): ?Label
    {
        $fields = implode(',', $this->getFieldsSql());
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$fields}
            FROM {$this->table}
            WHERE {$this->sqlHelper->generateSqlFilters()}
            {$this->sqlHelper->generateSqlOrder()}
            LIMIT 1
        ");

        $this->sqlHelper->bind($statement);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.labels.storage.find_error', 2);
        }

        $this->updateTotalItemsWithoutFilters();

        $labelData = $statement->fetch(PDO::FETCH_ASSOC);

        if ($labelData == false) {
            return null;
        }

        return $this->build($labelData);
    }

    private function getFields(): array
    {
        return [
            self::COLUMN_ID,
            self::COLUMN_PARENT_ID,
            self::COLUMN_SLUG,
            self::COLUMN_STATUS,
            self::COLUMN_TITLE,
        ];
    }

    private function getFieldsSql(): array
    {
        $table = $this->table;
        return array_map(
            function($field) use ($table) { return "`{$table}`.`{$field}`"; },
            $this->getFields()
        );
    }

    public function getTotalItemsOfLastFindWithoutLimit(): int
    {
        return $this->totalItemsOfLastFindWithoutLimit;
    }

    public function setLimit(int $limit): Storage
    {
        $this->sqlHelper->setLimit($limit);
        return $this;
    }

    public function setOffset(int $offset): Storage
    {
        $this->sqlHelper->setOffset($offset);
        return $this;
    }

    public function setTable(string $name): self
    {
        $this->table = $name;
        return $this;
    }

    /** @throws Exception */
    public function store(Label $label): Storage
    {
        $fieldParentId = self::COLUMN_PARENT_ID;
        $fieldSlug = self::COLUMN_SLUG;
        $fieldTitle = self::COLUMN_TITLE;
        $fieldStatus = self::COLUMN_STATUS;

        $statement = $this->pdo->prepare(
            "INSERT INTO {$this->table} (
                `{$fieldTitle}`, `{$fieldSlug}`, `{$fieldParentId}`, `{$fieldStatus}`
            ) VALUES (
                :title, :slug, :parent_id, :status
            )"
        );

        $statement->bindValue(':parent_id', $label->getParentId(), PDO::PARAM_INT);
        $statement->bindValue(':slug', $label->getSlug(), PDO::PARAM_STR);
        $statement->bindValue(':title', $label->getTitle(), PDO::PARAM_STR);
        $statement->bindValue(':status', $label->getStatus()->getValue(), PDO::PARAM_INT);

        if (! $statement->execute()) {
            throw new Exception('ciebit.labels.storages.store', 3);
        }

        $label->setId($this->pdo->lastInsertId());

        return $this;
    }

    /** @throws Exception */
    public function update(Label $label): Storage
    {
        $fieldParentId = self::COLUMN_PARENT_ID;
        $fieldId = self::COLUMN_ID;
        $fieldSlug = self::COLUMN_SLUG;
        $fieldTitle = self::COLUMN_TITLE;
        $fieldStatus = self::COLUMN_STATUS;

        $statement = $this->pdo->prepare(
            "UPDATE {$this->table} SET
                `{$fieldTitle}` = :title,
                `{$fieldSlug}` = :slug,
                `{$fieldParentId}` =  :parent_id,
                `{$fieldStatus}` = :status
            WHERE
                `{$fieldId}` = :id
            LIMIT 1"
        );

        $statement->bindValue(':id', $label->getId(), PDO::PARAM_INT);
        $statement->bindValue(':parent_id', $label->getParentId(), PDO::PARAM_STR);
        $statement->bindValue(':slug', $label->getSlug(), PDO::PARAM_STR);
        $statement->bindValue(':title', $label->getTitle(), PDO::PARAM_STR);
        $statement->bindValue(':status', $label->getStatus()->getValue(), PDO::PARAM_INT);

        if (! $statement->execute()) {
            throw new Exception('ciebit.labels.storages.update', 4);
        }

        return $this;
    }

    private function updateTotalItemsWithoutFilters(): self
    {
        $this->totalItemsOfLastFindWithoutLimit = $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return $this;
    }
}
