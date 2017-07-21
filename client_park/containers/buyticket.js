import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import * as actions from '../actions/buyticket';
import Hea from '../components/hea';
import Explain from '../components/explain';
import Add from '../components/add';
import BottomBtn from '../components/bottombtn';
import Tip from '../components/tip';
import utils from '../utils';

class Buyticket extends Component {
  static propTypes = {
    buyTicketInfoFun: PropTypes.func.isRequired,
    createOrder: PropTypes.func.isRequired,
    buyTicketInfo: PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      num: 1,
    };
    this.payFun = this.payFun.bind(this);
    this.getNum = this.getNum.bind(this);
  }

  componentDidMount() {
    this.props.buyTicketInfoFun(utils.qs('id'));
  }

  getNum(num) {
    this.setState({
      num,
    });
  }

  header(data) {
    if (data.main_info) {
      return <Hea data={data} />;
    }
    return '';
  }

  explain(data) {
    return <Explain data={data} />;
  }

  add(data) {
    return <Add parentFun={this.getNum} dataNum={`${data.num}`} />;
  }

  payFun() {
    // 提交订单
    this.props.createOrder({ prize_id: this.props.buyTicketInfo.obj.id,
       num: this.state.num, name: this.props.buyTicketInfo.obj.main_info,
        total: (this.state.num * this.props.buyTicketInfo.obj.price).toFixed(2) });
  }

  bottomBtn() {
    const btns = [{
      id: 1,
      btnName: '提交订单',
      btnFun: this.payFun,
    }];
    return <BottomBtn btnList={btns} />;
  }

  render() {
    const { buyTicketInfo } = this.props;
    return (
      <div>
      {this.header(buyTicketInfo.obj)}
      {this.explain(buyTicketInfo.obj)}
      {this.add(buyTicketInfo.obj)}
      {this.bottomBtn()}
      <Tip share="buy" price={buyTicketInfo.obj.price} num={`${this.state.num}`} />
      {
        // <Buyfailed />
      }
      </div>
    );
  }
}

const mapStateToProps = state => ({
  buyTicketInfo: state.buyTicketInfo,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Buyticket);
