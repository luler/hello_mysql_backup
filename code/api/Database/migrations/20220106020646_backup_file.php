<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BackupFile extends AbstractMigration
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
        $table = $this->table('backup_file', array('engine' => 'InnoDB', 'comment' => '备份文件表', 'collation' => 'utf8mb4_general_ci'));
        $table->addColumn('backup_id', 'integer', ['default' => 0, 'comment' => '备份id', 'null' => false])
            ->addColumn('num', 'string', ['limit' => 255, 'default' => '', 'comment' => '备份序号', 'null' => false])
            ->addColumn('file_name', 'string', ['limit' => 255, 'default' => '', 'comment' => '文件名', 'null' => false])
            ->addColumn('file_path', 'string', ['limit' => 1024, 'default' => '', 'comment' => '文件路径', 'null' => false])
            ->addColumn('size', 'integer', ['default' => 0, 'comment' => '文件大小，单位字节', 'null' => false])
            ->addColumn('create_time', 'integer', ['default' => 0, 'comment' => '创建时间', 'null' => false])
            ->addColumn('update_time', 'integer', ['default' => 0, 'comment' => '更新时间', 'null' => false])
            ->create();
    }
}
