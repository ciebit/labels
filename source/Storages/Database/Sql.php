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
use function json_decode;

class Sql implements Database
{
    /** @var string */
    private const FIELD_ASCENDANT_ID = 'ascendants_id';

    /** @var string */
    private const FIELD_ID = 'id';

    /** @var string */
    private const FIELD_SLUG = 'slug';

    /** @var string */
    private const FIELD_STATUS = 'status';

    /** @var string */
    private const FIELD_TITLE = 'title';

    /** @var PDO */
    private $pdo;

    /** @var SqlHelper */
    private $sqlHelper;

    /** @var string */
    private $table;

    /** @var int */
    private $totalItemsOfLastFindWithoutFilters;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->sqlHelper = new SqlHelper;
        $this->table = 'cb_labels';
        $this->totalItemsOfLastFindWithoutFilters = 0;
    }

    private function addFilter(string $fieldName, int $type, string $operator, ...$value): self
    {
        $field = "`{$this->table}`.`{$fieldName}`";
        $this->sqlHelper->addFilterBy($field, $type, $operator, ...$value);
        return $this;
    }

    public function addFilterByAscendantId(string $operator, string ...$ids): Storage
    {
        $ids = array_map('intval', $ids);
        $this->addFilter(self::FIELD_ASCENDANT_ID, PDO::PARAM_INT, $operator, ...$ids);
        return $this;
    }

    public function addFilterById(string $operator, string ...$ids): Storage
    {
        $ids = array_map('intval', $ids);
        $this->addFilter(self::FIELD_ID, PDO::PARAM_INT, $operator, ...$ids);
        return $this;
    }

    public function addFilterBySlug(string $operator, string ...$slug): Storage
    {
        $this->addFilter(self::FIELD_SLUG, PDO::PARAM_STR, $operator, ...$slug);
        return $this;
    }

    public function addFilterByStatus(string $operator, Status ...$status): Storage
    {
        $statusInt = array_map(function($status){
            return (int) $status->getValue();
        }, $status);
        $this->addFilter(self::FIELD_STATUS, PDO::PARAM_INT, $operator, ...$statusInt);
        return $this;
    }

    public function addFilterByTitle(string $operator, string ...$titles): Storage
    {
        $this->addFilter(self::FIELD_TITLE, PDO::PARAM_STR, $operator, ...$titles);
        return $this;
    }

    private function build(array $data): Label
    {
        $label = new Label(
            $data[self::FIELD_TITLE],
            $data[self::FIELD_SLUG],
            new Status((int) $data[self::FIELD_STATUS])
        );

        $label->setId($data[self::FIELD_ID]);

        $ascendantsId = $data[self::FIELD_ASCENDANT_ID];

        if ($ascendantsId != null) {
            $label->setAscendantsId(json_decode($ascendantsId));
        }

        return $label;
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
            self::FIELD_ASCENDANT_ID,
            self::FIELD_ID,
            self::FIELD_SLUG,
            self::FIELD_STATUS,
            self::FIELD_TITLE,
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

    public function getTotalItemsOfLastFindWithoutFilters(): int
    {
        return $this->totalItemsOfLastFindWithoutFilters;
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
        $fieldAscendantId = self::FIELD_ASCENDANT_ID;
        $fieldSlug = self::FIELD_SLUG;
        $fieldTitle = self::FIELD_TITLE;
        $fieldStatus = self::FIELD_STATUS;

        $statement = $this->pdo->prepare(
            "INSERT INTO {$this->table} (
                `{$fieldTitle}`, `{$fieldSlug}`, `{$fieldAscendantId}`, `{$fieldStatus}`
            ) VALUES (
                :title, :slug, :ascendant_id, :status
            )"
        );

        $statement->bindValue(':ascendant_id', json_encode($label->getAscendantsId()), PDO::PARAM_STR);
        $statement->bindValue(':slug', $label->getSlug(), PDO::PARAM_STR);
        $statement->bindValue(':title', $label->getTitle(), PDO::PARAM_STR);
        $statement->bindValue(':status', $label->getStatus()->getValue(), PDO::PARAM_INT);

        if (! $statement->execute()) {
            throw new Exception('ciebit.labels.storages.store', 3);
        }

        $label->setId($this->pdo->lastInsertId());

        return $this;
    }

    private function updateTotalItemsWithoutFilters(): self
    {
        $this->totalItemsOfLastFindWithoutFilters = $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
        return $this;
    }
}
