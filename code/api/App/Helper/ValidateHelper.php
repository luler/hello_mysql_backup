<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 13:52
 */

namespace App\Helper;

use EasySwoole\Validate\Validate;

class ValidateHelper
{
    public static function checkData(array $param, array $rules, array $messages = [])
    {
        $validate = new Validate();
        foreach ($rules as $key => $rule) {
            $temp_key = explode('|', $key);
            $temp_rule = explode('|', $rule);
            $alias = $temp_key[1] ?? $temp_key[0];
            $column = $validate->addColumn($temp_key[0]);
            foreach ($temp_rule as $item) {
                $one = explode(':', $item);
                switch ($one[0]) {
                    case 'required':
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 不能为空');
                        $column->required($msg)->notEmpty($msg);
                        break;
                    case 'url':
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 不是合法链接');
                        $column->url($msg);
                        break;
                    case 'integer':
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 不是整数');
                        $column->integer($msg);
                        break;
                    case 'inArray'://inArray:1,2,3
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 不在数组' . $one[1] . '里');
                        $column->inArray(explode(',', $one[1]), false, $msg);
                        break;
                    case 'notInArray'://notInArray:1,2,3
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 取值不能在数组' . $one[1] . '范围里');
                        $column->notInArray(explode(',', $one[1]), false, $msg);
                        break;
                    case 'between'://between:1,5
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 取值不能在' . $one[1] . '范围里');
                        $cut = explode(',', $one[1]);
                        $column->between($cut[0], $cut[1], $msg);
                        break;
                    case 'float':
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 不是浮点型数字');
                        $column->float($msg);
                        break;
                    case 'min'://min:1
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 最小值不能小于' . $one[1]);
                        $column->min($one[1], $msg);
                        break;
                    case 'max'://max:1
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 最大值不能大于' . $one[1]);
                        $column->max($one[1], $msg);
                        break;
                    case 'mbLengthMin'://max:1
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 长度不能小于' . $one[1]);
                        $column->mbLengthMin($one[1], $msg);
                        break;
                    case 'mbLengthMax'://max:1
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 长度不能大于' . $one[1]);
                        $column->mbLengthMax($one[1], $msg);
                        break;
                    case 'isIp':
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 不是合法ip');
                        $column->isIp($msg);
                        break;
                    case 'numeric':
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 不是一个数字值');
                        $column->numeric($msg);
                        break;
                    case 'date':
                        $msg = $messages[$temp_key[0] . '.' . $one[0]] ?? ($alias . ' 不是一个合法日期');
                        if (isset($param[$temp_key[0]])) {
                            if (empty(strtotime($param[$temp_key[0]]))) {
                                throwException($msg);
                            }
                        }
                        break;
                }
            }
        }
        //验证
        $bool = $validate->validate($param);
        if ($bool === false) {
            throwException($validate->getError()->__toString());
        }
        return true;
    }
}
