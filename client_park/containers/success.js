import React, { Component } from 'react';
import utils from '../utils';

class Success extends Component {

  constructor(props) {
    super(props);
    this.state = {
      data: JSON.parse(decodeURIComponent(utils.qs('data'))),
    };
  }

  render() {
    const { data } = this.state;
    return (
      <div className="success-box">
        <header className="success-hea">
          <span className="success-icon iconfont-success"
            dangerouslySetInnerHTML={ { __html: '&#xe675;' } }
          ></span>
          <div className="success-money">
            <i>支付成功！</i>
            <p>￥{utils.transformFen(data.total_fee)}</p>
          </div>
        </header>
        <div className="ticket">
          {
            // <span>悦米停车券:</span><strong>500张</strong>
          }
        </div>
        <div className="success-info">
          <dl>
            <dt>收款商户名</dt>
            <dd>西单大悦城股份有限公司</dd>
          </dl>
          <dl>
            <dt>交易时间</dt>
            <dd>{(new Date(parseInt(data.timeStamp, 10) * 1000)).format('yyyy-M-d h:m:s')}</dd>
          </dl>
          <dl>
            <dt>交易单号</dt>
            <dd>{data.outTradeNo}</dd>
          </dl>
        </div>
      </div>
    );
  }
}

export default Success;
