<?php

declare(strict_types=1);

namespace App\Console;

use Symfony\Component\Console\Application;

final class ConsoleApplication
{
    public function create(): Application
    {
        return new Application('Totem Animal', '0.1.0');
    }
}
