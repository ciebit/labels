<?php
declare(strict_types=1);

namespace Ciebit\Labels\Storages\Database;

use Ciebit\Labels\Collection;
use Ciebit\Labels\Builders\FromArray as BuilderFromArray;
use Ciebit\Labels\Label;
use Ciebit\Labels\Status;
use Ciebit\Labels\Storages\Storage;
use Ciebit\Labels\Storages\Database\SqlFilters;
use Exception;
use PDO;

class Sql extends SqlFilters implements Storage
{
    static private $counterKey = 0;
    private $pdo; #: PDO
    private $table; #: string

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->table = 'cb_labels';
    }

    public function addFilterById(int $id, string $operator = '='): Storage
    {
        $key = 'id';
        $sql = "`label`.`id` $operator :{$key}";

        $this->addfilter($key, $sql, PDO::PARAM_INT, $id);
        return $this;
    }

    public function addFilterByIds(string $operator = '=', int ...$ids): Storage
    {
        $keyPrefix = 'id';
        $keys = [];
        $operator = $operator == '!=' ? 'NOT IN' : 'IN';
        foreach ($ids as $id) {
            $key = $keyPrefix . self::$counterKey++;
            $this->addBind($key, PDO::PARAM_INT, $id);
            $keys[] = $key;
        }
        $keysStr = implode(', :', $keys);
        $this->addSqlFilter("`label`.`id` {$operator} (:{$keysStr})");
        return $this;
    }

    public function addFilterByStatus(Status $status, string $operator = '='): Storage
    {
        $key = 'status';
        $sql = "`label`.`status` {$operator} :{$key}";
        $this->addFilter($key, $sql, PDO::PARAM_INT, $status->getValue());
        return $this;
    }

    public function addFilterByTitle(string $title, string $operator = '='): Storage
    {
        $key = 'title';
        $field = '`label`.`title`';
        $sql = "{$field} {$operator} :{$key}";

        $this->addfilter($key, $sql, PDO::PARAM_STR, $title);

        return $this;
    }

    private function build(array $data): Label
    {
        $label = new Label(
            $data['title'],
            $data['uri'],
            new Status((int) $data['status'])
        );

        $label->setId($data['id']);

        if ($data['ascendants_id'] != null) {
            $label->setAscendantsId(explode(',', $data['ascendants_id']));
        }

        return $label;
    }

    public function get(): ?Label
    {
        $statement = $this->pdo->prepare("
            SELECT
            {$this->getFields()}
            FROM {$this->table} as `label`
            WHERE {$this->generateSqlFilters()}
            LIMIT 1
        ");

        $this->bind($statement);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.labels.storages.database.get_error', 2);
        }

        $labelData = $statement->fetch(PDO::FETCH_ASSOC);

        if ($labelData == false) {
            return null;
        }

        return $this->build($labelData);
    }

    public function getAll(): Collection
    {
        $statement = $this->pdo->prepare("
            SELECT SQL_CALC_FOUND_ROWS
            {$this->getFields()}
            FROM {$this->table} as `label`
            WHERE {$this->generateSqlFilters()}
            {$this->generateSqlLimit()}
        ");

        $this->bind($statement);

        if ($statement->execute() === false) {
            throw new Exception('ciebit.labels.storages.database.get_error', 2);
        }

        $collection = new Collection;

        while ($label = $statement->fetch(PDO::FETCH_ASSOC)) {
            $collection->add(
                $this->build($label)
            );
        }

        return $collection;
    }

    private function getFields(): string
    {
        return '
            `label`.`id`,
            `label`.`title`,
            `label`.`ascendants_id`,
            `label`.`uri`,
            `label`.`status`
        ';
    }

    public function getTotalRows(): int
    {
        return $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
    }

    public function setStarting(int $lineInit): Storage
    {
        parent::setOffset($lineInit);
        return $this;
    }

    public function setTable(string $name): self
    {
        $this->table = $name;
        return $this;
    }

    public function setTotal(int $total): Storage
    {
        parent::setLimit($total);
        return $this;
    }
}
