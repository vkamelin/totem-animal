<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Phinx\Migration\AbstractMigration;

final class CreateAnswersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('answers', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'signed' => false,
        ]);

        $table
            ->addColumn('question_id', 'biginteger', ['signed' => false, 'null' => false])
            ->addColumn('code', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('text', 'text', ['null' => false])
            ->addColumn('weights', 'json', ['null' => false])
            ->addColumn('sort_order', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('is_active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addIndex(['question_id'])
            ->addIndex(['is_active'])
            ->addIndex(['sort_order'])
            ->addIndex(['deleted_at'])
            ->addIndex(['question_id', 'code'], ['unique' => true])
            ->addForeignKey('question_id', 'questions', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
