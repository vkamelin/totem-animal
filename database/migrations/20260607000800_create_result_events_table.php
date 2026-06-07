<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Phinx\Migration\AbstractMigration;

final class CreateResultEventsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('result_events', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'signed' => false,
        ]);

        $table
            ->addColumn('test_result_id', 'biginteger', ['signed' => false, 'null' => false])
            ->addColumn('client_public_id', 'char', ['limit' => 36, 'null' => true])
            ->addColumn('event_type', 'enum', [
                'values' => ['view', 'share', 'copy_link'],
                'null' => false,
            ])
            ->addColumn('payload', 'json', ['null' => true])
            ->addColumn('client_ip_hash', 'char', ['limit' => 64, 'null' => true])
            ->addColumn('user_agent_hash', 'char', ['limit' => 64, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['null' => true])
            ->addIndex(['test_result_id'])
            ->addIndex(['client_public_id'])
            ->addIndex(['event_type'])
            ->addIndex(['created_at'])
            ->addForeignKey('test_result_id', 'test_results', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
