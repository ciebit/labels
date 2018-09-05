<?php
declare(strict_types=1);

namespace Ciebit\Labels\Builders;

use Exception;
use Ciebit\Labels\Builders\Builder;
use Ciebit\Labels\Status;
use Ciebit\Labels\Label;

class FromArray implements Builder
{
    private $data; #:array

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function build(): Label
    {
        if (
            ! is_array($this->data) OR
            ! isset($this->data['title']) OR
            ! isset($this->data['uri'])
        ) {
            throw new Exception('ciebit.labels.builders.invalid', 3);
        }

        $label = new Label(
            $this->data['title'],
            $this->data['uri'],
            $this->data['parent'] || null,
            Status::DRAFT()
        );

        return $label;
    }
}
