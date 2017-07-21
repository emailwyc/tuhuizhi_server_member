import React, { Component } from 'react';
import BottomBtn from '../components/bottombtn';
import utils from '../utils';
const conf = window.conf;

class BuySuccess extends Component {

  constructor(props) {
    super(props);
    this.state = {
      data: JSON.parse(decodeURIComponent(utils.qs('data'))),
    };
    this.feeFun = this.feeFun.bind(this);
  }

  feeFun() {
    const { data } = this.state;
    if (data.bonus) {
      return <p>{data.bonus} 积分</p>;
    } else if (data.total_fee) {
      return <p>￥{utils.transformFen(data.total_fee)}</p>;
    }
    return '';
  }

  toUrl() {
    location.href = `/park/search?key_admin=${conf.key}&pic=${
      encodeURIComponent('https://oe5n68bv6.qnssl.com/img/merLogo/xidan')}`;
  }

  bottomBtn() {
    const btns = [{
      id: 2,
      btnName: '关闭',
      btnFun: this.toUrl,
    }];
    return <BottomBtn btnList={btns} />;
  }

  render() {
    const { data } = this.state;
    return (
      <div className="success-box">
        <header className="success-hea">
          <span className="success-icon iconfont icon-duihao"></span>
          <div className="success-money">
            <i>支付成功！</i>
            {this.feeFun()}
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
        {
          this.bottomBtn()
        }
      </div>
    );
  }
}

export default BuySuccess;
