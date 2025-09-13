import {
  LockOutlined,
  UserOutlined,
} from '@ant-design/icons';
import {Alert} from 'antd';
import React, {useState} from 'react';
import {ProFormText, LoginForm} from '@ant-design/pro-form';
// import {useIntl, FormattedMessage} from 'umi';
import Footer from '@/components/Footer';
import styles from './index.less';
import defaultSettings from "../../../../config/defaultSettings";
import {request_post} from "@/utils/request_tool";
import {setAccessToken} from "@/utils/authority";
import {getFullPath, getQueryString} from "@/utils/utils";

const LoginMessage = ({content}) => (
  <Alert
    style={{
      marginBottom: 24,
    }}
    message={content}
    type="error"
    showIcon
  />
);

const Login = () => {
  const [userLoginState] = useState({});
  const [type] = useState('account');
  // const intl = useIntl();


  const handleSubmit = async (values) => {
    request_post('/api/getAccessToken', values).then(res => {
      if (res.code === 200) {
        setAccessToken(res.info)
        const redirect = getQueryString('redirect')
        window.location.href = redirect || getFullPath('/')
      } else {

      }
    })
  };

  const {status, type: loginType} = userLoginState;
  return (
    <div className={styles.container}>
      <div className={styles.content}>
        <LoginForm
          logo={<img alt="logo" src={defaultSettings.logo}/>}
          title={defaultSettings.title}
          subTitle='方便对多个mysql数据库进行定制化备份，可视化备份策略，实现mysql资源灵活、高效、统一的管理'
          initialValues={{
            autoLogin: true,
          }}
          actions={[]}
          onFinish={async (values) => {
            await handleSubmit(values);
          }}
        >

          {/*{status === 'error' && loginType === 'account' && (*/}
          {/*  <LoginMessage*/}
          {/*    content={intl.formatMessage({*/}
          {/*      id: 'pages.login.accountLogin.errorMessage',*/}
          {/*      defaultMessage: '账户或密码错误(admin/ant.design)',*/}
          {/*    })}*/}
          {/*  />*/}
          {/*)}*/}
          {type === 'account' && (
            <>
              <ProFormText
                name="appid"
                fieldProps={{
                  size: 'large',
                  prefix: <UserOutlined className={styles.prefixIcon}/>,
                }}
                placeholder='请输入账号'
                rules={[
                  {
                    required: true,
                    message: '账号不能为空',
                  },
                ]}
              />
              <ProFormText.Password
                name="appsecret"
                fieldProps={{
                  size: 'large',
                  prefix: <LockOutlined className={styles.prefixIcon}/>,
                }}
                placeholder='请输入密码'
                rules={[
                  {
                    required: true,
                    message: '密码不能为空',
                  },
                ]}
              />
            </>
          )}
        </LoginForm>
      </div>
      <Footer/>
    </div>
  );
};

export default Login;
