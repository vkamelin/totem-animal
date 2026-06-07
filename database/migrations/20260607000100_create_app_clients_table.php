<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Phinx\Migration\AbstractMigration;

final class CreateAppClientsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('app_clients', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'signed' => false,
        ]);

        $table
            ->addColumn('public_id', 'char', ['limit' => 36, 'null' => false])
            ->addColumn('first_seen_at', 'timestamp', ['null' => true])
            ->addColumn('last_seen_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->addIndex(['public_id'], ['unique' => true])
            ->addIndex(['last_seen_at'])
            ->create();
    }
}
