<?php


namespace App\WebSocket;


use EasySwoole\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Socket\Bean\Caller;
use EasySwoole\Socket\Bean\Response;

class WebSocketParser implements ParserInterface
{
    /**
     * decode
     * @param string $raw 客户端原始消息
     * @param WebSocket $client WebSocket Client 对象
     * @return Caller         Socket  调用对象
     */
    public function decode($raw, $client): ?Caller
    {
        //$raw的格式：{"class":"index","action":"index","param":[1,2,3]}
        // 解析 客户端原始消息
        $data = json_decode($raw, true) ?: [];

        // new 调用者对象
        $caller = new Caller();
        /**
         * 设置被调用的类 这里会将ws消息中的 class 参数解析为具体想访问的控制器
         * 如果更喜欢 event 方式 可以自定义 event 和具体的类的 map 即可
         * 注 目前 easyswoole 3.0.4 版本及以下 不支持直接传递 class string 可以通过这种方式
         */
        $class = ucfirst($data['class'] ?? 'IndexSocket');
        $class = stripos($class, 'Socket') === false ? $class . 'Socket' : $class;
        $class = '\\App\\WebSocket\\Socket\\' . $class;
        if (!class_exists($class)) {//防止实例化类报错
            //日志：todo
            return null;
        }
        $caller->setControllerClass($class);

        // 设置被调用的方法
        $caller->setAction($data['action'] ?? 'index');

        // 设置被调用的Args
        $args = isset($data['param']) ? ['param' => $data['param']] : ['param' => ''];
        $caller->setArgs($args);
        return $caller;
    }

    /**
     * encode
     * @param Response $response Socket Response 对象
     * @param WebSocket $client WebSocket Client 对象
     * @return string             发送给客户端的消息
     */
    public function encode(Response $response, $client): ?string
    {
        /**
         * 这里返回响应给客户端的信息
         * 这里应当只做统一的encode操作 具体的状态等应当由 Controller处理
         */
        return $response->getMessage();
    }
}