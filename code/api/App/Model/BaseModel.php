<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 9:48
 */

namespace App\Model;


use EasySwoole\ORM\AbstractModel;

class BaseModel extends AbstractModel
{
    protected $autoTimeStamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected function getCreateTimeAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $value);
    }

    protected function getUpdateTimeAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $value);
    }
}
