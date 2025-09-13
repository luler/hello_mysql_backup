export default [
  {
    path: '/user',
    layout: false,
    routes: [
      {
        path: '/user',
        routes: [
          {
            name: 'login',
            path: '/user/login',
            component: './user/Login',
          },
          {
            component: './404',
          },
        ],
      },
    ],
  },
  {
    path: '/',
    redirect: '/database/list',
  },
  {
    path: '/database',
    name: '数据库管理',
    icon: 'DatabaseOutlined',
    routes: [
      {
        name: '数据库列表',
        path: '/database/list',
        component: './database',
      },
      {
        name: '数据库备份',
        path: '/database/backup/:id',
        component: './database/backup',
        hideInMenu: true,
      },
      {
        name: '备份文件',
        path: '/database/backup_file/:id',
        component: './database/backup_file',
        hideInMenu: true,
      },
      {
        component: './404',
      },
    ],
  },
  {
    path: '/config',
    name: '基础配置',
    icon: 'DatabaseOutlined',
    routes: [
      {
        name: '操作日志',
        path: '/config/operation_log',
        component: './config/operation_log',
      },
      {
        component: './404',
      },
    ],
  },
  {
    component: './404',
  },
];
