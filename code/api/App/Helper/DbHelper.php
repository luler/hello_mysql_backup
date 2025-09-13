<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 13:52
 */

namespace App\Helper;

use EasySwoole\EasySwoole\Config;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Result;
use EasySwoole\ORM\DbManager;

class DbHelper
{
    private $connection_name;

    private $table_name;
    private $query_builder;
    private $is_get_last_query = false;

    private $page = 1;
    private $page_rows = 10;

    public function __construct(string $connection_name = 'default')
    {
        //获取连接
        if (!DbManager::getInstance()->getConnection($connection_name)) {
            $init_config = Config::getInstance()->getConf('database.' . $connection_name);
            $mysql_config = new \EasySwoole\ORM\Db\Config($init_config);
            $mysql_config->setMinObjectNum(0);
            $mysql_config->setMaxObjectNum(1);
            DbManager::getInstance()->addConnection(new Connection($mysql_config), $connection_name);
        }
        $this->connection_name = $connection_name;
    }

    public function table(string $table_name)
    {
        $this->table_name = $table_name;
        $this->query_builder = new QueryBuilder();
        $this->is_get_last_query = false;
        return $this;
    }

    public function alias(string $alia_name)
    {
        $this->table_name = $this->table_name . ' as ' . $alia_name;
        return $this;
    }

    public function where($whereProp, $whereValue = 'DBNULL', $operator = '=', $cond = 'AND')
    {
        $this->query_builder = $this->query_builder->where($whereProp, $whereValue, $operator, $cond);
        return $this;
    }

    public function join($joinTable, $joinCondition, $joinType = '')
    {
        $this->query_builder = $this->query_builder->join($joinTable, $joinCondition, $joinType);
        return $this;
    }

    public function groupBy($groupByField)
    {
        $this->query_builder = $this->query_builder->groupBy($groupByField);
        return $this;
    }

    public function orderBy($orderByField, $orderbyDirection = "DESC", $customFieldsOrRegExp = null)
    {
        $this->query_builder = $this->query_builder->orderBy($orderByField, $orderbyDirection, $customFieldsOrRegExp);
        return $this;
    }

    public function fields(string $fields)
    {
        $this->query_builder = $this->query_builder->fields($fields);
        return $this;
    }

    public function limit(int $one, ?int $two = null)
    {
        $this->query_builder = $this->query_builder->limit($one, $two);
        return $this;
    }

    public function getLastQuery($is_get_last_query = true)
    {
        $this->is_get_last_query = $is_get_last_query;
        return $this;
    }

    public function find()
    {
        $this->query_builder->limit(1)->get($this->table_name);
        if ($this->is_get_last_query) {
            return $this->query_builder->getLastQuery();
        }
        return DbManager::getInstance()->query($this->query_builder, true, $this->connection_name, null)->getResultOne();
    }

    public function column(string $fields, $key = '')
    {
        $all_fields = $fields;
        if (!empty($key) && strpos($fields, $key) === false) {
            $all_fields = $fields . ',' . $key;
        }
        $this->query_builder->getColumn($this->table_name, $all_fields);
        if ($this->is_get_last_query) {
            return $this->query_builder->getLastQuery();
        }
        $res = DbManager::getInstance()
            ->query($this->query_builder, true, $this->connection_name, null)
            ->getResult();

        if (!empty($key)) {
            $temp = [];
            foreach ($res as $value) {
                $temp[$value[$key]] = $value;
            }
            $res = $temp;
            unset($temp);
        }

        $fields = preg_replace('/\w\./', '', $fields);
        $fields = explode(',', $fields);
        if (count($fields) == 1) {
            foreach ($res as &$value) {
                $value = $value[$fields[0]];
            }
        }
        return $res;
    }

    public function select()
    {
        $this->query_builder->get($this->table_name);
        if ($this->is_get_last_query) {
            return $this->query_builder->getLastQuery();
        }
        return DbManager::getInstance()->query($this->query_builder, true, $this->connection_name, null)->getResult();
    }

    public function autoPage($page_key = 'page', $page_rows_key = 'page_rows')
    {
        $param = _apiParam([$page_key, $page_rows_key]);
        $param[$page_key] = $param[$page_key] ?? $this->page;
        $param[$page_rows_key] = $param[$page_rows_key] ?? $this->page_rows;
        $this->page = (int)$param[$page_key];
        $this->page_rows = (int)$param[$page_rows_key];
        return $this;
    }

    public function get()
    {
        //分页设置
        $this->limit(($this->page - 1) * $this->page_rows, $this->page_rows);
        //获取数量
        $db_count = new self($this->connection_name);
        $db_count->query_builder = clone $this->query_builder;
        $db_count->table_name = $this->table_name;
        $db_count->is_get_last_query = $this->is_get_last_query;
        if ($this->is_get_last_query) {
            $res = [
                $this->select(),
                $db_count->count(),
            ];
        } else {
            $res = [];
            $res['list'] = $this->select();
            $res['total'] = $db_count->count();
            $res['page'] = $this->page;
            $res['page_rows'] = $this->page_rows;
        }
        return $res;
    }

    public function count()
    {
        $this->limit(1);
        $this->fields('count(*) as count');
        $this->query_builder->get($this->table_name);
        if ($this->is_get_last_query) {
            return $this->query_builder->getLastQuery();
        }
        return DbManager::getInstance()
            ->query($this->query_builder, true, $this->connection_name, null)
            ->getResultOne()['count'];
    }

    public function query(QueryBuilder $builder): Result
    {
        return DbManager::getInstance()->query($builder, true, $this->connection_name, null);
    }

    public function startTransaction(): bool
    {
        return DbManager::getInstance()->startTransaction($this->connection_name);
    }

    public function commit(): bool
    {
        return DbManager::getInstance()->commit($this->connection_name);
    }

    public function rollback(): bool
    {
        return DbManager::getInstance()->rollback($this->connection_name);
    }
}
