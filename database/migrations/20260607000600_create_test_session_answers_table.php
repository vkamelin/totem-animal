<?php

declare(strict_types=1);

namespace App\Database\Migration;

use Phinx\Migration\AbstractMigration;

final class CreateTestSessionAnswersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('test_session_answers', [
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
            'signed' => false,
        ]);

        $table
            ->addColumn('test_session_id', 'biginteger', ['signed' => false, 'null' => false])
            ->addColumn('question_id', 'biginteger', ['signed' => false, 'null' => false])
            ->addColumn('answer_id', 'biginteger', ['signed' => false, 'null' => false])
            ->addColumn('question_code', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('answer_code', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('question_text', 'text', ['null' => false])
            ->addColumn('answer_text', 'text', ['null' => false])
            ->addColumn('weights_snapshot', 'json', ['null' => false])
            ->addColumn('answered_at', 'timestamp', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['null' => true])
            ->addColumn('updated_at', 'timestamp', ['null' => true])
            ->addIndex(['test_session_id'])
            ->addIndex(['question_id'])
            ->addIndex(['answer_id'])
            ->addIndex(['answered_at'])
            ->addIndex(['test_session_id', 'question_id'], ['unique' => true])
            ->addForeignKey('test_session_id', 'test_sessions', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('question_id', 'questions', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('answer_id', 'answers', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}
