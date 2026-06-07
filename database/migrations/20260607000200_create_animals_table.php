<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Phinx\Migration\AbstractMigration;

final class CreateAnimalsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('animals', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'signed' => false,
        ]);

        $table
            ->addColumn('code', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('name', 'string', ['limit' => 120, 'null' => false])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('description', 'text', ['null' => false])
            ->addColumn('image_path', 'string', ['limit' => 500, 'null' => false])
            ->addColumn('extraversion', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('openness', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('self_control', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('agreeableness', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('emotional_stability', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('dominance', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('adaptability', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('is_active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('sort_order', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addIndex(['code'], ['unique' => true])
            ->addIndex(['is_active'])
            ->addIndex(['sort_order'])
            ->addIndex(['deleted_at'])
            ->addIndex(['extraversion'])
            ->addIndex(['openness'])
            ->addIndex(['self_control'])
            ->addIndex(['agreeableness'])
            ->addIndex(['emotional_stability'])
            ->addIndex(['dominance'])
            ->addIndex(['adaptability'])
            ->addIndex(['is_active', 'extraversion'])
            ->addIndex(['is_active', 'openness'])
            ->addIndex(['is_active', 'self_control'])
            ->addIndex(['is_active', 'agreeableness'])
            ->addIndex(['is_active', 'emotional_stability'])
            ->addIndex(['is_active', 'dominance'])
            ->addIndex(['is_active', 'adaptability'])
            ->create();
    }
}
