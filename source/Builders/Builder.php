<?php
declare(strict_types=1);
namespace Ciebit\Labels\Builders;

use Ciebit\Labels\Label;

interface Builder
{
    public function build(): Label;
}
