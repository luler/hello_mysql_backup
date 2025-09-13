<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class User extends AbstractMigration
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
        $table = $this->table('users', array('engine' => 'InnoDB', 'comment' => '用户表', 'collation' => 'utf8mb4_general_ci'));
        $table->addColumn('appid', 'string', ['limit' => 255, 'default' => '', 'comment' => 'appid', 'null' => false])
            ->addColumn('appsecret', 'string', ['limit' => 255, 'default' => '', 'comment' => 'appsecret', 'null' => false])
            ->addColumn('cas_open_id', 'string', ['limit' => 255, 'default' => '', 'comment' => '中央认证唯一标识', 'null' => false])
            ->addColumn('create_time', 'integer', ['default' => 0, 'comment' => '创建时间', 'null' => false])
            ->addColumn('update_time', 'integer', ['default' => 0, 'comment' => '更新时间', 'null' => false])
            ->create();
    }
}
