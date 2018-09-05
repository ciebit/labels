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

        if ($labelData['parent']) {
            $parents = explode(",", $labelData['parent']);
            foreach ($parents as $parent_id) {
                $database = clone $this;
                $database->addFilterById((int) $parent_id);
                $parent = $database->get();
                // var_dump($parent);
                $labelData['parent'] = $parent;
            }
        }
        var_dump($labelData);

        return (new BuilderFromArray)->setData($labelData)->build();
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

        $builder = new BuilderFromArray;
        while ($label = $statement->fetch(PDO::FETCH_ASSOC)) {
            $collection->add(
                $builder->setData($label)->build()
            );
        }

        return $collection;
    }

    private function getFields(): string
    {
        return '
            `label`.`id`,
            `label`.`title`,
            `label`.`parent`,
            `label`.`uri`,
            `label`.`status`
        ';
    }

    public function getTotalRows(): int
    {
        return $this->pdo->query('SELECT FOUND_ROWS()')->fetchColumn();
    }

    public function setStartingLine(int $lineInit): Storage
    {
        parent::setOffset($lineInit);
        return $this;
    }

    public function setTable(string $name): self
    {
        $this->table = $name;
        return $this;
    }

    public function setTotalLines(int $total): Storage
    {
        parent::setLimit($total);
        return $this;
    }
}
