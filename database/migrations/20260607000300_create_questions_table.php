<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Phinx\Migration\AbstractMigration;

final class CreateQuestionsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('questions', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'signed' => false,
        ]);

        $table
            ->addColumn('code', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('text', 'text', ['null' => false])
            ->addColumn('is_active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('sort_order', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addIndex(['code'], ['unique' => true])
            ->addIndex(['is_active'])
            ->addIndex(['sort_order'])
            ->addIndex(['deleted_at'])
            ->create();
    }
}
