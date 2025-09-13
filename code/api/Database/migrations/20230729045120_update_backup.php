<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateBackup extends AbstractMigration
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
        $table = $this->table('backup');
        $table->addColumn('table_limit', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '限定表类型，0-全表，1-指定表，2-排除表', 'null' => false, 'after' => 'tables'])
            ->update();
    }
}
