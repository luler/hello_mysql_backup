<?php

return [
    'simple_log' => [
        'tag' => \App\Helper\ConfigHelper::getEnv('simple_log.tag', 'logname'),//项目名称
    ],
];