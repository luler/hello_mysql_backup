<?php
/**
 * Created by PhpStorm.
 * User: 我只想看看蓝天 <1207032539@qq.com>
 * Date: 2020/1/19
 * Time: 14:41
 */

/**
 * 判断是否登录
 * @return false|mixed
 * @author 我只想看看蓝天 <1207032539@qq.com>
 */
function is_login()
{
    $request = \EasySwoole\Component\Context\ContextManager::getInstance()->get('request');
    $uid = $request->getAttribute('__user_info__')['id'] ?? false;
    if (empty($uid)) {
        return false;
    }
    return $uid;
}

/**
 * 获取学年学期
 * @param int $time
 * @return array
 * @author lihq1403 <lihaiqing1994@163.com>
 */
function getCurrentYearTerm($time = 0)
{
    if (empty($time) || !is_numeric($time)) {
        $time = time();
    }
    $data = [];
    $year = date('Y', $time);
    $month = date('m', $time);
    //找学年
    if ($month < 8) {
        $data['year'] = $year - 1;
    } else {
        $data['year'] = $year;
    }
    //找学期
    if ($month >= 2 && $month < 8) {
        $data['term'] = 2;
    } else {
        $data['term'] = 1;
    }
    return $data;
}

/**
 * 抛出异常（限制异常code）
 * @param $message
 * @param $code
 * @throws Exception
 * @author 我只想看看蓝天 <1207032539@qq.com>
 */
function throwException($message, $code = 400)
{
    throw new \Exception($message, $code);
}

/**
 * 打印变量
 * @param $var
 * @author 我只想看看蓝天 <1207032539@qq.com>
 */
function dump($var)
{
    ob_start();
    var_dump($var);
    $output = ob_get_clean();
    $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
    if (PHP_SAPI == 'cli') {
        $output = PHP_EOL . $output . PHP_EOL;
    } else {
        if (!extension_loaded('xdebug')) {
            $output = htmlspecialchars($output, ENT_SUBSTITUTE);
        }
        $output = '<pre>' . $output . '</pre>';
    }
    echo($output);
    return;
}

/**
 * 打印变量并终止程序向下运行
 * @param $var
 * @author 我只想看看蓝天 <1207032539@qq.com>
 */
function halt($var)
{
    dump($var);
    if (PHP_SAPI == 'cli') {
        exit();
    }
    throw new \Exception();
}

/**
 * 获取请求参数(白名单筛选)
 * @param array $fields
 * @return array|mixed
 * @author 我只想看看蓝天 <1207032539@qq.com>
 */
function _apiParam(array $fields = [])
{
    $param = \EasySwoole\Component\Context\ContextManager::getInstance()->get('request_param');
    if (empty($param)) {
        $request = \EasySwoole\Component\Context\ContextManager::getInstance()->get('request');
        $param = $request->getRequestParam();
        $body_param = json_decode($request->getBody()->getContents(), true) ?: [];
        $param = array_merge($param, $body_param);
        \EasySwoole\Component\Context\ContextManager::getInstance()->set('request_param', $param);
    }
    if (!empty($fields)) {
        foreach ($param as $key => $value) {
            if (!in_array($key, $fields)) {
                unset($param[$key]);
            }
        }
    }
    return $param;
}

/**
 * 时间戳转字符串
 * @param $timestamp
 * @return string
 * @author 我只想看看蓝天 <1207032539@qq.com>
 */
function date_simple_format($timestamp = -1)
{
    if ($timestamp === -1) {
        $timestamp = time();
    }
    return date('Y-m-d H:i:s', $timestamp) ?: '';
}

/**
 * 扫描目录获取全部文件信息
 * @param $root
 * @return array
 * @author 我只想看看蓝天 <1207032539@qq.com>
 */
function scanFiles($root)
{
    $dir = scandir($root);
    $files = [];
    foreach ($dir as $item) {
        if (in_array($item, ['.', '..'])) {
            continue;
        }
        $item = rtrim($root, "/\\") . '/' . $item;
        if (is_dir($item)) {
            $files = array_merge($files, scanFiles($item));
        } else {
            $info = stat($item);
            $files[] = [
                'file_name' => basename($item),
                'file_path' => $item,
                'size' => $info['size'],
                'create_time' => $info['ctime'],
                'update_time' => $info['mtime'],
            ];
        }
    }

    return $files;
}

/**
 * 递归删除目录及其下文件
 * @param $dirName
 * @author 我只想看看蓝天 <1207032539@qq.com>
 */
function delDirAndFile($dirName)
{
    if ($handle = opendir("$dirName")) {
        while (false !== ($item = readdir($handle))) {
            if ($item != "." && $item != "..") {
                if (is_dir("$dirName/$item")) {
                    delDirAndFile("$dirName/$item");
                } else {
                    unlink("$dirName/$item");
                }
            }
        }
        closedir($handle);
        rmdir($dirName);
    }
}

/**
 * 递归删除空目录
 * @param $path
 * @author 我只想看看蓝天 <1207032539@qq.com>
 */
function rm_empty_dir($path)
{
    if (is_dir($path) && ($handle = opendir($path)) !== false) {
        while (($file = readdir($handle)) !== false) {     // 遍历文件夹
            if ($file != '.' && $file != '..') {
                $curfile = $path . '/' . $file;          // 当前目录
                if (is_dir($curfile)) {                // 目录
                    rm_empty_dir($curfile);          // 如果是目录则继续遍历
                    if (count(scandir($curfile)) == 2) { // 目录为空,=2是因为. 和 ..存在
                        rmdir($curfile);             // 删除空目录
                    }
                }
            }
        }
        closedir($handle);
    }
}

/**
 * 转换成 年 天 时 分 秒
 * @param $time
 * @return false|string
 */
function second_to_time($time)
{
    if (is_numeric($time)) {
        $value = array(
            "years" => 0, "days" => 0, "hours" => 0,
            "minutes" => 0, "seconds" => 0,
        );
        $t = '';
        if ($time >= 31556926) {
            $value["years"] = floor($time / 31556926);
            $time = ($time % 31556926);
            $t .= $value["years"] . "年";
        }
        if ($time >= 86400) {
            $value["days"] = floor($time / 86400);
            $time = ($time % 86400);
            $t .= $value["days"] . "天";
        }
        if ($time >= 3600) {
            $value["hours"] = floor($time / 3600);
            $time = ($time % 3600);
            $t .= $value["hours"] . "小时";
        }
        if ($time >= 60) {
            $value["minutes"] = floor($time / 60);
            $time = ($time % 60);
            $t .= $value["minutes"] . "分";
        }
        $value["seconds"] = floor($time);
        $t .= $value["seconds"] . "秒";
        return $t;
    } else {
        return false;
    }
}