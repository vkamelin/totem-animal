<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Phinx\Migration\AbstractMigration;

final class CreateTestResultsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('test_results', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'signed' => false,
        ]);

        $table
            ->addColumn('public_id', 'char', ['limit' => 36, 'null' => false])
            ->addColumn('test_session_id', 'biginteger', ['signed' => false, 'null' => false])
            ->addColumn('client_id', 'biginteger', ['signed' => false, 'null' => true])
            ->addColumn('client_public_id', 'char', ['limit' => 36, 'null' => true])
            ->addColumn('animal_id', 'biginteger', ['signed' => false, 'null' => true])
            ->addColumn('animal_code', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('animal_name', 'string', ['limit' => 120, 'null' => false])
            ->addColumn('result_title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('result_description', 'text', ['null' => false])
            ->addColumn('result_image_path', 'string', ['limit' => 500, 'null' => false])
            ->addColumn('user_extraversion', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('user_openness', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('user_self_control', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('user_agreeableness', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('user_emotional_stability', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('user_dominance', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('user_adaptability', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('animal_extraversion', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('animal_openness', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('animal_self_control', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('animal_agreeableness', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('animal_emotional_stability', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('animal_dominance', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('animal_adaptability', 'tinyinteger', ['signed' => false, 'null' => false])
            ->addColumn('score_distance', 'decimal', ['precision' => 10, 'scale' => 4, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->addIndex(['public_id'], ['unique' => true])
            ->addIndex(['test_session_id'], ['unique' => true])
            ->addIndex(['client_id'], ['unique' => true])
            ->addIndex(['client_public_id'], ['unique' => true])
            ->addIndex(['animal_id'])
            ->addIndex(['animal_code'])
            ->addIndex(['created_at'])
            ->addIndex(['user_extraversion'])
            ->addIndex(['user_openness'])
            ->addIndex(['user_self_control'])
            ->addIndex(['user_agreeableness'])
            ->addIndex(['user_emotional_stability'])
            ->addIndex(['user_dominance'])
            ->addIndex(['user_adaptability'])
            ->addForeignKey('test_session_id', 'test_sessions', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('client_id', 'app_clients', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('animal_id', 'animals', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
