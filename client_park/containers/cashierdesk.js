import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import * as actions from '../actions/cashierdesk';
import BottomBtn from '../components/bottombtn';
// const conf = window.conf;
import utils from '../utils';

class Cashierdesk extends Component {
  static propTypes = {
    pay: PropTypes.object.isRequired,
    payFun: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    const data = JSON.parse(decodeURIComponent(utils.qs('data')));
    const orders = JSON.parse(decodeURIComponent(utils.qs('orders')));
    this.state = {
      prize_id: data.prize_id,
      num: data.num,
      name: data.name,
      total: data.total,
      order_id: orders.ordId,
      orderItemId: orders.ordItemIds,
    };
    console.log(data);
    console.log(orders);
    this.payFun = this.payFun.bind(this);
  }

  payFun() {
    this.props.payFun(this.state);
  }

  render() {
    const btns = [{
      id: 1,
      btnName: '确认支付',
      btnFun: this.payFun,
    }];
    return (
      <div className="buy">
        <section>
          <div className="type-item">
            <h4>{this.state.name}</h4>
            <div className="type-item-con">￥{this.state.total}</div>
          </div>
        </section>
        <section>
          <div className="type-item">
            <h4>选择支付方式</h4>
            <div className="type-item-con"></div>
          </div>
          <div className="type-item">
            <h4><em className="iconfont icon-weixin"></em>微信支付</h4>
            <div className="type-item-con">
              <em className="iconfont icon-duihao"></em>
            </div>
          </div>
        </section>
        <BottomBtn btnList={btns} />
      </div>
    );
  }
}

const mapStateToProps = state => ({
  pay: state.pay,
});
const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Cashierdesk);
