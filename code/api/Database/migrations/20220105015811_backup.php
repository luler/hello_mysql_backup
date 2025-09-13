<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Backup extends AbstractMigration
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
        $table = $this->table('backup', array('engine' => 'InnoDB', 'comment' => '备份配置表', 'collation' => 'utf8mb4_general_ci'));
        $table->addColumn('database_id', 'integer', ['default' => 0, 'comment' => '数据库', 'null' => false])
            ->addColumn('cron', 'string', ['limit' => 50, 'default' => '', 'comment' => '定时配置，crontab语法一致', 'null' => false])
            ->addColumn('tables', 'string', ['limit' => 255, 'default' => '', 'comment' => '指定备份表', 'null' => false])
            ->addColumn('status', 'integer', ['default' => 1, 'comment' => '状态，0-禁用，1-启用', 'null' => false])
            ->addColumn('keep_count', 'integer', ['default' => 0, 'comment' => '备份数量', 'null' => false])
            ->addColumn('last_cost_time', 'integer', ['default' => 0, 'comment' => '最后消耗时间，单位秒', 'null' => false])
            ->addColumn('create_time', 'integer', ['default' => 0, 'comment' => '创建时间', 'null' => false])
            ->addColumn('update_time', 'integer', ['default' => 0, 'comment' => '更新时间', 'null' => false])
            ->create();
    }
}
