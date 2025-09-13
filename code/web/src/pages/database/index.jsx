import React from "react";
import {Button, Divider, Form, Input, message, Modal, Popconfirm, Select, Switch, Table, Tag} from "antd";
import {request_get, request_post} from "@/utils/request_tool";
import BaseComponent from "@/pages/BaseComponent";
import {getFullPath} from "@/utils/utils";

export default class database extends BaseComponent {
  state = {
    param: {
      page: 1,
      page_rows: 10,
    },
    list: [],
    total: 0,
    visible: false,
    temp_data: {},
    loading: false,
    working: [],
    timer: null,
  }

  componentDidMount() {
    this.fetch()
  }

  getWorking = (that) => {
    request_get('/api/home/getBackupImportWorking').then(res => {
      if (res.code === 200) {

        if (res.info.length !== this.state.working.length) {
          that.setStateSimple('working', res.info, () => {
            if (res.info.length > 0) {
              if (!this.state.timer) {
                this.setStateSimple('timer', setInterval(() => {
                  that.getWorking(that)
                }, 3000))
              }
            } else {
              if (this.state.timer) {
                clearInterval(this.state.timer);
                this.setStateSimple('timer', null)
              }
            }

            that.fetch()

          });
        }

      }
    })
  }

  fetch() {
    this.setState({loading: true}, () => {
      request_get('/api/home/getDatabaseList', this.state.param).then((res) => {
        this.setStateSimple('list', res.info.list)
        this.setStateSimple('total', res.info.total)
        this.setStateSimple('loading', false)
        this.getWorking(this)
      })
    })
  }

  columns = [
    {
      title: 'ID',
      dataIndex: 'id',
    },
    {
      title: '名称',
      dataIndex: 'title',
    },
    {
      title: '主机',
      dataIndex: 'host',
    },
    {
      title: '端口',
      dataIndex: 'port',
    },
    {
      title: '账号',
      dataIndex: 'user',
    },
    {
      title: '密码',
      dataIndex: 'password',
      render: value => {
        return <Input.Password readOnly value={value} style={{width: 120, border: 'none'}}/>
      }
    },
    {
      title: '数据库',
      dataIndex: 'database',
    },
    {
      title: '时间',
      render: record => {
        return <div>
          <Tag>创建：{record.create_time}</Tag>
          <div style={{marginTop: 5}}></div>
          <Tag>更新：{record.update_time}</Tag>
        </div>
      },
    },
    {
      title: '操作',
      render: (record) => {
        return <div>
          <a
            onClick={() => {
              window.location.href = getFullPath('/database/backup/' + record.id)
            }}
          >
            备份配置
          </a>
          <Divider type='vertical'/>
          <Button
            disabled={record.can_import === '0'}
            size='small'
            type='link'
            loading={this.state.working.indexOf(record.id) !== -1}
            onClick={() => {
              this.setStateSimple('importVisible', true)
              this.setStateSimple('temp_data.target_database_id', record.id)
            }}
          >
            导入备份
          </Button>
          <Divider type='vertical'/>
          <a
            onClick={() => {
              this.setStateSimple('temp_data', record, () => {
                this.setStateSimple('visible', true)
              })
            }}
          >
            编辑
          </a>
          <Divider type='vertical'/>
          <Popconfirm
            title='您确定要删除吗？'
            onConfirm={() => {
              request_post('/api/home/delDatabase', {id: record.id}).then(res => {
                if (res.code === 200) {
                  message.success('删除成功')
                  this.fetch()
                }
              })
            }}
          >
            <a
              style={{color: 'red'}}
            >
              删除
            </a>
          </Popconfirm>
        </div>
      }
    },
  ]

  render() {
    return <div>
      <div
        style={{
          background: 'white',
          padding: 20,
          margin: "20px 0"
        }}
      >
        <Button
          type='primary'
          onClick={() => {
            this.setStateSimple('visible', true)
          }}
        >
          添加数据库
        </Button>

        <Input.Search
          style={{
            width: 500,
            float: 'right'
          }}
          placeholder='请输入搜索关键字'
          onSearch={value => {
            this.setState({
              param: {
                ...this.state.param,
                search: value,
                page: 1,
              }
            }, () => {
              this.fetch()
            })
          }}
        />
      </div>
      <Table
        onChange={(pagination) => {
          this.setState({
            param: {
              ...this.state.param,
              page: pagination.current,
              page_rows: pagination.pageSize,
            }
          }, () => {
            this.fetch()
          })
        }}
        pagination={{
          showSizeChanger: true,
          current: this.state.param.page,
          total: this.state.total,
          pageSize: this.state.param.page_rows,
          showTotal: (total) => {
            return <div>总共 {total} 条数据</div>
          }
        }}
        loading={this.state.loading}
        rowKey='id'
        dataSource={this.state.list}
        columns={this.columns}
      />
      <Modal
        title='数据库配置'
        visible={this.state.visible}
        onCancel={() => {
          this.setStateSimple('visible', false)
          this.setStateSimple('temp_data', {})
        }}
        onOk={() => {
          request_post('/api/home/saveDatabase', this.state.temp_data).then(res => {
            if (res.code === 200) {
              message.success(res.message)
              this.setStateSimple('visible', false)
              this.setStateSimple('temp_data', {})
              this.fetch()
            }
          })
        }}
      >
        <Form
          labelCol={{span: 4}}
          wrapperCol={{span: 20}}
          autoComplete='off'
        >
          <Form.Item label='名称' required>
            <Input placeholder='请输入' onChange={(e) => {
              this.setStateSimple('temp_data.title', e.target.value)
            }} value={this.state.temp_data.title || ''}/>
          </Form.Item>
          <Form.Item label='主机' required>
            <Input placeholder='请输入' onChange={(e) => {
              this.setStateSimple('temp_data.host', e.target.value)
            }} value={this.state.temp_data.host || ''}/>
          </Form.Item>
          <Form.Item label='端口' required>
            <Input placeholder='请输入' onChange={(e) => {
              this.setStateSimple('temp_data.port', e.target.value)
            }} value={this.state.temp_data.port || ''}/>
          </Form.Item>
          <Form.Item label='账号' required>
            <Input placeholder='请输入' onChange={(e) => {
              this.setStateSimple('temp_data.user', e.target.value)
            }} value={this.state.temp_data.user || ''}/>
          </Form.Item>
          <Form.Item label='密码' required>
            <Input.Password placeholder='请输入' onChange={(e) => {
              this.setStateSimple('temp_data.password', e.target.value)
            }} value={this.state.temp_data.password || ''}/>
          </Form.Item>
          <Form.Item label='数据库' required>
            <Input placeholder='请输入' onChange={(e) => {
              this.setStateSimple('temp_data.database', e.target.value)
            }} value={this.state.temp_data.database || ''}/>
          </Form.Item>
          <Form.Item label='导入备份' required>
            <Switch
              checkedChildren="开启"
              unCheckedChildren="关闭"
              checked={(this.state.temp_data.can_import || '0') === '1'}
              onChange={checked => {
                this.setStateSimple('temp_data.can_import', checked ? '1' : '0')
              }}
            />
          </Form.Item>
        </Form>
      </Modal>

      <Modal
        width={600}
        title='导入备份'
        visible={this.state.importVisible || false}
        onCancel={() => {
          this.setStateSimple('importVisible', false)
          this.setStateSimple('temp_data', {})
        }}
        onOk={() => {
          Modal.confirm({
            title: '警告',
            content: '导入备份属于危险操作，可能造成目标数据库无法挽回的数据丢失，您仍要继续吗？',
            onOk: () => {

              request_post('/api/home/startBackupImport', this.state.temp_data).then(res => {
                if (res.code === 200) {
                  message.success(res.message)
                  this.setStateSimple('importVisible', false)
                  this.setStateSimple('temp_data', {})
                  this.fetch()
                }
              })

            }
          })
        }}
      >
        <Form
          labelCol={{span: 4}}
          wrapperCol={{span: 20}}
          autoComplete='off'
        >
          <Form.Item label='来源数据库' required extra='输入数据库信息，搜索出存在备份数据的数据库'>
            <Select
              showSearch
              value={this.state.temp_data.database_id || ''}
              filterOption={false}
              onSearch={(value) => {
                request_get('/api/home/getDropDatabaseList', {search: value}).then(res => {
                  if (res.code === 200) {
                    this.setStateSimple('getDropDatabaseList', res.info.map(value => {
                      return <Select.Option key={value.id} value={value.id}>{value.title}</Select.Option>
                    }))
                  }
                })
              }}
              onChange={value => {
                this.setStateSimple('temp_data.database_id', value)
                this.setStateSimple('temp_data.num', '')
                //获取备份序号
                request_get('/api/home/getDropBackupNumList', {database_id: value}).then(res => {
                  this.setStateSimple('getDropBackupNumList', res.info.map(value => {
                    return <Select.Option
                      key={value.num}
                      value={value.num}>{value.num + ' (' + (value.tables || '整库') + ')'}
                    </Select.Option>
                  }))
                })
              }}
            >
              {this.state.getDropDatabaseList || []}
            </Select>
          </Form.Item>

          <Form.Item label='备份序号' required
                     extra='选择合适的备份序号，选中后，该备份序号下的所有备份数据都会被执行到目标数据库'>
            <Select
              showSearch
              filterOption={false}
              allowClear
              value={this.state.temp_data.num || ''}
              onSearch={(value) => {
                //获取备份序号
                request_get('/api/home/getDropBackupNumList', {
                  database_id: this.state.temp_data.database_id,
                  search: value
                }).then(res => {
                  this.setStateSimple('getDropBackupNumList', res.info.map(value => {
                    return <Select.Option
                      key={value.num}
                      value={value.num}>{value.num + ' (' + (value.tables || '整库') + ')'}
                    </Select.Option>
                  }))
                })
              }}
              onChange={value => {
                this.setStateSimple('temp_data.num', value)
              }}
            >
              {this.state.getDropBackupNumList || []}
            </Select>
          </Form.Item>

          <Form.Item label='是否覆盖表' required
                     extra='无论是否选择，备份数据都会完全执行。如果选择了是，则在备份过程中会删除目标数据库已存在的表，完全将备份数据的表镜像复制到目标数据库'>
            <Switch
              checked={this.state.temp_data.is_overwrite_tables ?? false}
              onChange={value => {

                this.setStateSimple('temp_data.is_overwrite_tables', value)

              }}
              checkedChildren='是'
              unCheckedChildren='否'
            />
          </Form.Item>
        </Form>
      </Modal>
    </div>
  }

}
