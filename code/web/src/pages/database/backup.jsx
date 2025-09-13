import React from "react";
import {Button, Divider, Form, Input, message, Modal, Popconfirm, Radio, Select, Switch, Table, Tag} from "antd";
import {request_get, request_post} from "@/utils/request_tool";
import BaseComponent from "@/pages/BaseComponent";
import {LeftCircleOutlined} from "@ant-design/icons";
import {getFullPath} from "@/utils/utils";

export default class backup extends BaseComponent {
  state = {
    param: {
      id: 0,
      page: 1,
      page_rows: 10,
    },
    list: [],
    total: 0,
    visible: false,
    loading: false,
    all_tables: [],
    temp_data: {},
    working: [],
    timer: null,
    table_limits: {
      '0': <Tag color="#108ee9">全表</Tag>,
      '1': <Tag color="#87d068">指定表</Tag>,
      '2': <Tag color="#2db7f5">排除表</Tag>,
    },
  }

  componentDidMount() {
    this.setStateSimple('param.id', this.props.match.params.id)
    this.fetch()

    request_get('/api/home/getDatabaseTables', {database_id: this.state.param.id}).then(res => {
      this.setStateSimple('all_tables', res.info)
    })
  }

  getWorking = (that) => {
    request_get('/api/home/getBackupWorking').then(res => {
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
      request_get('/api/home/getBackupList', this.state.param).then((res) => {
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
      title: '数据库名称',
      dataIndex: 'title',
    },
    {
      title: '限定表',
      width: 300,
      render: record => {
        return <div style={{wordWrap: 'break-word', wordBreak: 'break-word'}}>
          {this.state.table_limits[record.table_limit]}
          {record.tables}
        </div>
      }
    },
    {
      title: '定时配置',
      dataIndex: 'cron',
    },
    {
      title: '状态',
      render: record => {
        return <Switch
          checkedChildren="启用"
          unCheckedChildren="禁用"
          checked={record.status !== '0'}
          onChange={checked => {
            request_post('/api/home/saveBackupStatus', {
              id: record.id,
              status: checked ? 1 : 0
            }).then(res => {
              this.fetch()
            })
          }}
        />
      }
    },
    {
      title: '保留数量',
      render: record => {
        return record.use_count + '/' + record.keep_count
      }
    },
    {
      title: '空间',
      render: record => {
        return <div>
          <Tag>
            已用:{record.size}
          </Tag>
          <div style={{marginTop: 5}}></div>
          <Tag>
            可用:{record.available_size}
          </Tag>

        </div>
      }
    },
    {
      title: '最后备份耗时',
      render: record => {
        return <div>
          {record.last_cost_time}
        </div>
      },
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
          <Button
            size='small'
            type='link'
            loading={this.state.working.indexOf(record.id) !== -1}
            onClick={() => {
              Modal.confirm({
                title: '提示',
                content: '您正在进行立即备份操作，该操作可能比较耗时，您确定要立即备份吗？',
                onOk: () => {
                  request_post('/api/home/startBackup', {id: record.id}).then(res => {
                    if (res.code === 200) {
                      message.success(res.message)
                      this.fetch()
                    }
                  })
                }
              })

            }}
          >
            立即备份
          </Button>
          <Divider type='vertical'/>
          <a
            onClick={() => {
              window.location.href = getFullPath('/database/backup_file/' + record.id)
            }}
          >
            查看数据
          </a>
          <Divider type='vertical'/>
          <a
            onClick={() => {
              this.setStateSimple('temp_data', JSON.parse(JSON.stringify(record)), () => {
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
              request_post('/api/home/delBackup', {id: record.id}).then(res => {
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
      <div>
        <a onClick={() => {
          window.history.back()
        }}><LeftCircleOutlined/>&nbsp;返回</a>
      </div>
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
            this.setStateSimple('temp_data', {
              cron: '0 4 * * *',
              keep_count: 30,
              table_limit: '0',
            })
          }}
        >
          添加备份
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
        title='备份配置'
        visible={this.state.visible}
        onCancel={() => {
          this.setStateSimple('visible', false)
          this.setStateSimple('temp_data', {})
        }}
        onOk={() => {
          request_post('/api/home/saveBackup', {
            ...this.state.temp_data,
            database_id: this.state.param.id,
            tables: this.state.temp_data.tables_arr?.join(','),
          }).then(res => {
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
          <Form.Item label='定时配置' required extra='遵循crontab语法，如:* * * * *,代表每分钟运行'>
            <Input placeholder='请输入' onChange={(e) => {
              this.setStateSimple('temp_data.cron', e.target.value)
            }} value={this.state.temp_data.cron || ''}/>
          </Form.Item>
          <Form.Item label='保留数量' required extra='控制当前备份数量，若大于该值则删除最旧的备份数据'>
            <Input type='number' placeholder='请输入' onChange={(e) => {
              this.setStateSimple('temp_data.keep_count', e.target.value)
            }} value={this.state.temp_data.keep_count || ''}/>
          </Form.Item>
          <Form.Item label='限定表类型'>
            <Radio.Group
              disabled={!!this.state.temp_data.id}
              options={[{label: '全表', value: '0'}, {label: '指定表', value: '1'}, {label: '排除表', value: '2'},]}
              value={this.state.temp_data.table_limit || '0'}
              onChange={e => this.setStateSimple('temp_data.table_limit', e.target.value)}
            />
          </Form.Item>
          {(this.state.temp_data.table_limit || '0') !== '0' && <Form.Item label='指定表' extra='限定数据表，可多选'>
            <Select
              disabled={!!this.state.temp_data.id}
              showSearch
              mode='multiple'
              placeholder='请选择'
              value={this.state.temp_data.tables_arr || []}
              onChange={(value, option) => {
                this.setStateSimple('temp_data.tables_arr', value)
              }}
            >
              {this.state.all_tables.map(value => {
                return <Select.Option key={value} value={value}>{value}</Select.Option>
              })}
            </Select>
          </Form.Item>}
        </Form>
      </Modal>
    </div>
  }

}
