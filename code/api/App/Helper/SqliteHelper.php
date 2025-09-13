<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 13:52
 */

namespace App\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use Swoole\Coroutine;

class SqliteHelper
{
    private static $instance;
    private $table;
    private $table_alias = null;
    private $where = [];
    private $select = ['*'];
    private $order = [];
    private $join = [];
    private $group = [];
    private $page = null;
    private $page_rows = null;
    private $connect = null;
    private $operations = ['=', '>', '<', '>=', '<=', 'like', 'in', 'not in'];

    public static function getConnection(): Connection
    {
        $file = EASYSWOOLE_ROOT . '/' . \App\Helper\ConfigHelper::getEnv('database.database', 'test') . '.sqlite3';
        if (!file_exists($file)) { //不存在需要执行迁移
            go(function () {
                Coroutine::exec('php vendor/robmorgan/phinx/bin/phinx migrate');
            });
        }
        $config = [
            'path' => $file,
            'driver' => 'pdo_sqlite',
        ];
        return DriverManager::getConnection($config);
    }

    public function createQueryBuilder(): QueryBuilder
    {
        if (!is_null($this->connect)) {
            $connect = $this->connect;
        } else {
            $connect = self::getConnection();
        }
        return $connect->createQueryBuilder();
    }

    public static function table(string $table, $alias = null)
    {
        self::$instance = new self();
        self::$instance->table = $table;
        self::$instance->table_alias = $alias;
        self::$instance->page = null;
        self::$instance->page_rows = null;
        return self::$instance;
    }

    public function useConnect($connect)
    {
        $this->connect = $connect;
        return $this;
    }

    public function join($join_table_alias, $table, $on, $join_type = 'inner')
    {
        $table = explode(' ', $table);
        $alias = end($table);
        $table = $table[0];
        $this->join[] = [
            $join_table_alias,
            $table,
            $alias,
            $on,
            $join_type,
        ];
        return $this;
    }

    public function insert(array $data)
    {
        $values = array_values($data);
        foreach ($data as &$v) {
            $v = '?';
        }
        return $this->createQueryBuilder()
            ->insert($this->table)
            ->values($data)
            ->setParameters($values)
            ->executeStatement();
    }

    public function update(array $data)
    {
        $query = $this->createQueryBuilder()
            ->update($this->table);
        $index = 0;
        foreach ($data as $field => $value) {
            $query = $query->set($field, '?');
            $query = $query->setParameter($index, $value);
            $index++;
        }
        foreach ($this->where as $k => $value) {
            if ($k == 0) {
                $query = $query->where("{$value[0]} $value[1] ?");
            } else {
                $query = $query->andWhere("{$value[0]} $value[1] ?");
            }
            $query = $query->setParameter($index, $value[2]);
            $index++;
        }
        return $query->executeStatement();
    }

    public function delete()
    {
        $query = $this->createQueryBuilder()
            ->delete($this->table);
        $query = $this->buildWhere($query);
        return $query->executeStatement();
    }

    public function where($field, $operation = null, $value = null)
    {
        if (is_array($field)) {
            $this->where = array_merge($this->where, $field);
            return $this;
        } else {
            if (is_null($value) && !in_array($operation, $this->operations)) {
                $value = $operation;
                $operation = '=';
            }
        }
        $this->where[] = [$field, $operation, $value];
        return $this;
    }

    public function groupBy($field)
    {
        $this->group[] = $field;
        return $this;
    }

    public function select(array $fields)
    {
        $this->select = $fields;
        return $this;
    }

    public function orderBy($sort, $order = null)
    {
        if (is_array($sort)) {
            foreach ($sort as $s => $o) {
                $this->order[] = [$s, $o];
            }
        } else {
            $this->order[] = [$sort, $order];
        }
        return $this;
    }

    private function getFromTable()
    {
        $query = $this->createQueryBuilder()
            ->from($this->table, $this->table_alias);
        if (!empty($this->join)) {
            foreach ($this->join as $item) {
                $item[4] = strtolower($item[4]);
                switch ($item[4]) {
                    case 'inner':
                        $query = $query->innerJoin($item[0], $item[1], $item[2], $item[3]);
                        break;
                    case 'left':
                        $query = $query->leftJoin($item[0], $item[1], $item[2], $item[3]);
                        break;
                    case 'right':
                        $query = $query->rightJoin($item[0], $item[1], $item[2], $item[3]);
                        break;
                }
            }
        }
        return $query;
    }

    public function first()
    {
        $query = $this->getFromTable();
        $query = $this->buildWhere($query);
        foreach ($this->order as $k => $value) {
            $query = $query->orderBy($value[0], $value[1]);
        }
        $query = $query->select($this->select)->setFirstResult(0)->setMaxResults(1);
        return $query->fetchAssociative();
    }

    public function value($filed)
    {
        $query = $this->getFromTable();
        $query = $this->buildWhere($query);
        foreach ($this->order as $k => $value) {
            $query = $query->orderBy($value[0], $value[1]);
        }
        $query = $query->select([$filed])->setFirstResult(0)->setMaxResults(1);
        return $query->fetchAssociative()[$filed] ?? null;
    }

    private function buildWhere($query, $index = 0): QueryBuilder
    {
        foreach ($this->where as $k => $value) {
            switch ($value[1]) {
                case '>':
                case '<':
                case '=':
                case '>=':
                case '<=':
                case '<>':
                case '!=':
                    $query_str = "{$value[0]} $value[1] ?";
                    $query = $query->setParameter($index, $value[2]);
                    $index++;
                    break;
                case 'in':
                case 'not in':
                    foreach ($value[2] as &$item) {
                        if (is_null($item)) {
                            $item = 'NULL';
                        } else {
                            $item = is_numeric($item) ? $item : "'" . addslashes($item) . "'";
                        }
                    }
                    $value[2] = '(' . join(',', $value[2]) . ')';
                    $query_str = "{$value[0]} {$value[1]} {$value[2]}";
                    break;
                case 'like':
                    $fields = explode('|', $value[0]);
                    $query_str = [];
                    foreach ($fields as $field) {
                        $query_str[] = "{$field} like ?";
                        $query = $query->setParameter($index, $value[2]);
                        $index++;
                    }
                    $query_str = '(' . join(' or ', $query_str) . ')';
                    break;
                default:
                    throwException('SqliteHelper->where,操作符不存在');
            }
            if ($k == 0) {
                $query = $query->where($query_str);
            } else {
                $query = $query->andWhere($query_str);
            }
        }
        if (!empty($this->group)) {
            $query = $query->groupBy(join(',', $this->group));
        }
        return $query;
    }

    public function page(int $page, int $page_rows = 10)
    {
        $this->page = $page;
        $this->page_rows = $page_rows;
        return $this;
    }

    public function get()
    {
        $query = $this->getFromTable();
        $query = $this->buildWhere($query);
        foreach ($this->order as $k => $value) {
            $query = $query->orderBy($value[0], $value[1]);
        }
        if (!is_null($this->page)) {
            $query = $query->setFirstResult(($this->page - 1) * $this->page_rows);
            $query = $query->setMaxResults($this->page_rows);
        }
        $query = $query->select($this->select);
        return $query->fetchAllAssociative();
    }

    public function column(string $field, string $key = null)
    {
        $raw_field = preg_replace('/.*\./', '', $field);
        $raw_key = preg_replace('/.*\./', '', $key);
        if (is_null($key)) {
            $res = $this->select([$field])->get();
            foreach ($res as &$value) {
                $value = $value[$raw_field];
            }
        } else {
            $res = $this->select([$field, $key])->get();
            $data = [];
            foreach ($res as $value) {
                $data[$value[$raw_key]] = $value[$field];
            }
            $res = $data;
            unset($data);
        }
        return $res;
    }

    public function count($field = '*')
    {
        $query = $this->getFromTable();
        $query = $this->buildWhere($query);
        $query = $query->select(["count({$field})"]);
        return (int)$query->fetchOne();
    }

    public function paginate()
    {
        $param = _apiParam(['page', 'page_rows']);
        list($page, $page_rows) = [$param['page'] ?? 1, $param['page_rows'] ?? 10];
        $this->page($page, $page_rows);
        $res = [];
        $res['list'] = $this->get();
        $res['total'] = $this->count();
        $res['page'] = (int)$page;
        $res['page_rows'] = (int)$page_rows;
        return $res;
    }

}
