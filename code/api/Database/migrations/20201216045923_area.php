<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Area extends AbstractMigration
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
        $table = $this->table('area', array('engine' => 'InnoDB', 'comment' => '全国地理位置表', 'collation' => 'utf8mb4_general_ci'));
        $table->addColumn('pid', 'integer', ['default' => 1, 'comment' => '父级id', 'null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'default' => '', 'comment' => '行政区域名称', 'null' => false])
            ->addColumn('pinyin', 'string', ['limit' => 255, 'default' => '', 'comment' => '拼音', 'null' => false])
            ->addColumn('type', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'default' => 0, 'comment' => '类型，0-全国，1-省份，2-城市，3-镇、区', 'null' => false])
            ->create();
    }
}
