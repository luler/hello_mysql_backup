<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class OperationLog extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('operation_log', array('engine' => 'InnoDB', 'comment' => '操作日志表', 'collation' => 'utf8mb4_general_ci'));
        $table->addColumn('uid', 'integer', ['default' => 0, 'comment' => '用户id', 'null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'default' => '', 'comment' => '操作名称', 'null' => false])
            ->addColumn('url', 'string', ['limit' => 255, 'default' => '', 'comment' => '操作路径', 'null' => false])
            ->addColumn('param', 'text', ['default' => '', 'comment' => '相关参数', 'null' => false])
            ->addColumn('create_time', 'integer', ['default' => 0, 'comment' => '创建时间', 'null' => false])
            ->addColumn('update_time', 'integer', ['default' => 0, 'comment' => '更新时间', 'null' => false])
            ->create();
    }
}
