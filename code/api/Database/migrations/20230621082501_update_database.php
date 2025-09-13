<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateDatabase extends AbstractMigration
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
        $table = $this->table('database');
        $table->addColumn('can_import', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '是否可以导入备份，0-否，1-是，', 'null' => false, 'after' => 'database'])
            ->update();
    }
}
