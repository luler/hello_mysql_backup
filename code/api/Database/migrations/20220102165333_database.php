<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Database extends AbstractMigration
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
        $table = $this->table('database', array('engine' => 'InnoDB', 'comment' => '数据源', 'collation' => 'utf8mb4_general_ci'));
        $table->addColumn('title', 'string', ['limit' => 255, 'default' => '', 'comment' => '数据库名称', 'null' => false])
            ->addColumn('host', 'string', ['limit' => 50, 'default' => '', 'comment' => '主机', 'null' => false])
            ->addColumn('user', 'string', ['limit' => 50, 'default' => '', 'comment' => '账号', 'null' => false])
            ->addColumn('password', 'string', ['limit' => 50, 'default' => '', 'comment' => '密码', 'null' => false])
            ->addColumn('port', 'string', ['limit' => 10, 'default' => '', 'comment' => '端口', 'null' => false])
            ->addColumn('database', 'string', ['limit' => 50, 'default' => '', 'comment' => '数据库', 'null' => false])
            ->addColumn('create_time', 'integer', ['default' => 0, 'comment' => '创建时间', 'null' => false])
            ->addColumn('update_time', 'integer', ['default' => 0, 'comment' => '更新时间', 'null' => false])
            ->create();
    }
}
