<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Phinx\Migration\AbstractMigration;

final class CreateTestSessionsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('test_sessions', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'signed' => false,
        ]);

        $table
            ->addColumn('public_id', 'char', ['limit' => 36, 'null' => false])
            ->addColumn('client_id', 'biginteger', ['signed' => false, 'null' => true])
            ->addColumn('client_public_id', 'char', ['limit' => 36, 'null' => true])
            ->addColumn('status', 'enum', [
                'values' => ['started', 'completed', 'abandoned'],
                'default' => 'started',
                'null' => false,
            ])
            ->addColumn('questions_count', 'smallinteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('answers_count', 'smallinteger', ['signed' => false, 'default' => 0, 'null' => false])
            ->addColumn('started_at', 'timestamp', ['null' => true])
            ->addColumn('completed_at', 'timestamp', ['null' => true])
            ->addColumn('last_activity_at', 'timestamp', ['null' => true])
            ->addColumn('client_ip_hash', 'char', ['limit' => 64, 'null' => true])
            ->addColumn('user_agent_hash', 'char', ['limit' => 64, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->addIndex(['public_id'], ['unique' => true])
            ->addIndex(['client_id'], ['unique' => true])
            ->addIndex(['client_public_id'], ['unique' => true])
            ->addIndex(['status'])
            ->addIndex(['started_at'])
            ->addIndex(['completed_at'])
            ->addIndex(['client_public_id', 'status', 'completed_at'])
            ->addIndex(['client_id', 'status', 'completed_at'])
            ->addForeignKey('client_id', 'app_clients', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
