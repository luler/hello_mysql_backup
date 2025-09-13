<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 9:48
 */

namespace App\Model;


class Area extends BaseModel
{
    protected $tableName = 'area';

    public function getInfo($id)
    {
        $res = $this->field('id,name')->get($id);
        return $res;
    }
}
