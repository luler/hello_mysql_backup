<?php


namespace App\WebSocket\Socket;


use App\Model\Area;
use App\WebSocket\BaseSocket;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Task\TaskManager;

class IndexSocket extends BaseSocket
{
    public function test()
    {
        $field = ['title'];
        $param = $this->_apiParam($field);
        return $this->successResponse('获取成功2', $param);
        $test = new Area();
        $test->name = 'test';
        $test->save();
        $this->response()->setMessage('success');
    }

    function hello()
    {
        $this->response()->setMessage('call hello with arg:' . json_encode($this->caller()->getArgs()));
    }

    public function who()
    {
        $this->response()->setMessage('your fd is ' . $this->caller()->getClient()->getFd());
    }

    function delay()
    {
        $this->response()->setMessage('this is delay action');
        $client = $this->caller()->getClient();

        // 异步推送, 这里直接 use fd也是可以的
        TaskManager::getInstance()->async(function () use ($client) {
            $server = ServerManager::getInstance()->getSwooleServer();
            $i = 0;
            while ($i < 5) {
                sleep(1);
                $server->push($client->getFd(), 'push in http at ' . date('H:i:s'));
                $i++;
            }
        });
    }
}