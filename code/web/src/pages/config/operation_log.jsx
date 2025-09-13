import React from "react";
import {Button, Input, message, Modal, Table} from "antd";
import {request_get, request_post} from "@/utils/request_tool";
import BaseComponent from "@/pages/BaseComponent";

export default class index extends BaseComponent {
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

  fetch() {
    this.setState({loading: true}, () => {
      request_get('/api/home/getOperationLogList', this.state.param).then((res) => {
        this.setStateSimple('list', res.info.list)
        this.setStateSimple('total', res.info.total)
        this.setStateSimple('loading', false)
      })
    })
  }

  columns = [
    {
      title: 'ID',
      dataIndex: 'id',
      width: 100,
    },
    {
      title: '操作名称',
      dataIndex: 'name',
      width: 180,
    },
    {
      title: '操作人',
      dataIndex: 'appid',
      width: 120,
    },
    {
      title: '接口',
      dataIndex: 'url',
      width: 220,
    },
    {
      title: '操作时间',
      dataIndex: 'create_time',
      width: 160,
    },
    {
      title: '相关参数',
      dataIndex: 'param',
    },
    // {
    //   title: '更新时间',
    //   dataIndex: 'update_time',
    // },
  ]

  render() {
    return <div>
      <div
        style={{
          background: 'white',
          padding: 20,
          margin: "20px 0",
          height: 70,
        }}
      >
        <Button
          type='danger'
          onClick={() => {
            Modal.confirm({
              title: '注意',
              content: '日志删除不可恢复，您确定要删除吗？',
              onOk: () => {
                request_post('/api/home/delOperationLog', this.state.param).then(res => {
                  if (res.code === 200) {
                    message.success('删除成功')
                    this.fetch()
                  }
                })
              }
            })
          }}
        >
          清理日志
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
        style={{wordBreak: 'break-all'}}
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


    </div>
  }

}
