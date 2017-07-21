import React, { Component, PropTypes } from 'react';
import Header from '../components/header';
import { bindActionCreators } from 'redux';
import { wxsdk } from 'wxsdk';
import Alert from 'rtui/alert';
import ShowFee from '../components/showfee';
import BottomBtn from '../components/bottombtn';

const wx = window.wx;
const conf = window.conf;
import { connect } from 'react-redux';
import * as actions from '../actions/pay';
import MoneyTicket from '../components/moneyticket';
class Pay extends Component {
  static propTypes = {
    park: PropTypes.object.isRequired,
    pay: PropTypes.object.isRequired,
    getCarInfo: PropTypes.func.isRequired,
    wxPay: PropTypes.func.isRequired,
    defaultPay: PropTypes.func.isRequired,
    pointOrder: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      active: 'active',
      btn1Color: '',
    };
    this.ok = this.ok.bind(this);
    this.changeActive = this.changeActive.bind(this);
    this.wxFun = this.wxFun.bind(this);
    this.pointFun = this.pointFun.bind(this);
    this.checkEnoughPoint = this.checkEnoughPoint.bind(this);
    wxsdk({ key_admin: conf.key }).then((json) => {
      const jsconf = json;
      jsconf.debug = false;
      jsconf.jsApiList = ['chooseWXPay'];
      wx.config(jsconf);
      wx.ready(() => {});
    });
  }

  componentDidMount() {
    this.props.getCarInfo();
  }

  componentWillReceiveProps(nextProps) {
    const { park } = nextProps;
    if (park.info.coupons && park.info.coupons.length > 0 && this.state.active === 'active') {
      this.setState({
        btn1Color: '',
      });
    } else {
      this.setState({
        btn1Color: '#279df2',
      });
      // this.checkEnoughPoint(park);
    }
  }

  ok() {
    if (this.props.pay.isSuccess) {
      this.props.defaultPay();
      setTimeout(() => {location.href = `/park/records?key_admin=${conf.key}`;}, 300);
    } else {
      this.props.defaultPay();
    }
  }

  wxFun() {
    const { park } = this.props;
    if (park.info.MoneyValue === 0) {
      alert('消费金额为0，无需支付！');
    } else {
      alert('线上支付不再提供纸质发票，如有需要可在微信交易记录中申请电子发票！');
      this.props.wxPay(this.state.active,
         this.props.park.info.coupons.length > 0 && this.props.park.info.coupons[0].num);
    }
  }

  pointFun() {
    const { park } = this.props;
    if (this.state.btn1Color === '#279df2') {
      if (park.info.IntValue === 0) {
        alert('消费金额为0，无需支付！');
      } else {
        this.props.pointOrder();
        // if (park.info.bonus < park.info.IntValue) {
        //   alert('积分不足！');
        // } else {
        //   this.props.pointOrder();
        // }
      }
    }
  }

  bottomBtn() {
    const { park } = this.props;
    let btns;
    if (park.info.MoneyValue !== 0 && !park.info.MoneyValue) {
      return false;
    }

    if (park.info.is_scorepay === '1') {
      btns = [{
        id: 2,
        btnName: `积分支付  ${park.info.IntValue}分`,
        btnFun: this.pointFun,
        style: { backgroundColor: this.state.btn1Color },
      }, {
        id: 1,
        btnName: '',
        btnFun: this.wxFun,
      }];
      if (park.info.payFee === 0) {
        btns[1].btnName = '确认支付';
      } else {
        btns[1].btnName = `微信支付  ${this.state.active === 'active'
         ? park.info.payFee : park.info.MoneyValue}元`;
      }
    } else {
      btns = [{
        id: 1,
        btnName: '',
        btnFun: this.wxFun,
      }];
      if (park.info.payFee === 0) {
        btns[0].btnName = '确认支付';
      } else {
        btns[0].btnName = `微信支付  ${this.state.active === 'active'
         ? park.info.payFee : park.info.MoneyValue}元`;
      }
    }

    return <BottomBtn btnList={btns} />;
  }

  checkEnoughPoint(park) {
    if (park.info.bonus >= park.info.IntValue) {
      this.setState({
        btn1Color: '#279df2',
      });
    } else {
      this.setState({
        btn1Color: '',
      });
    }
  }

  changeActive() {
    // const { park } = this.props;
    if (this.state.active === 'active') {
      this.setState({
        active: '',
        btn1Color: '#279df2',
      });
      // this.checkEnoughPoint(park);
    } else {
      this.setState({
        active: 'active',
        btn1Color: '',
      });
    }
  }

  render() {
    const { park, pay } = this.props;
    return (
      <div className="pay-box">
        <Header pic={park.info.carimg} />
        <div className="car-need-box">
          <h3 className="car-no">车牌号：{park.info.CarSerialNo}</h3>
          <ul className="tap-box">
            <li className="item-tap">
              <div className="item-con">
                <span>停车时间</span>
              </div>
              <div className="item-right">
                <span>{park.info.BeginTime}</span>
              </div>
            </li>
            <li className="item-tap">
              <div className="item-con">
                <span>费用金额</span>
              </div>
              <div className="item-right">
                <span>{park.info.MoneyValue}元</span>
              </div>
            </li>
            {
              <MoneyTicket dataList={park.info.coupons || []} value={this.state.active} onClickFun={
                this.changeActive}
              />
            // <li className="item-tap">
            //   <div className="item-con">
            //     <span>优惠券</span>
            //   </div>
            //   <div className="item-right">
            //     <span>
            //       <strong>2张可用</strong>
            //       <em>-￥10</em>
            //     </span>
            //     <i className="iconfont icon-jinru">
            //     </i>
            //   </div>
            // </li>
          }
          </ul>
        </div>
        <div className="fee-btn-box">
          <ShowFee />
          {this.bottomBtn()}
        </div>
        <Alert {...pay} ok={ this.ok } />
      </div>
    );
  }
}

const mapStateToProps = state => ({
  park: state.park,
  pay: state.pay,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Pay);
