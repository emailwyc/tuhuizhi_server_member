import React, { Component, PropTypes } from 'react';
import { wxsdk } from 'wxsdk';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import BottomBtn from '../components/bottombtn';
import Back from '../components/back';
import * as actions from '../actions/activate';

const wx = window.wx;
const conf = window.conf;

class Activate extends Component {
  static propTypes = {
    activateFun: PropTypes.func.isRequired,  // 激活
    activateInfo: PropTypes.object.isRequired,
  };

  constructor() {
    super();
    this.state = {
      val: '',
    };
    this.handleChange = this.handleChange.bind(this);
    this.handleClick = this.handleClick.bind(this);
    this.handleScanQR = this.handleScanQR.bind(this);
  }

  componentDidMount() {
    wxsdk({ key_admin: conf.key }).then((json) => {
      const jsconf = json;
      jsconf.debug = false;
      jsconf.jsApiList = ['scanQRCode'];
      wx.config(jsconf);
      wx.ready(() => {});
    });
  }

  handleChange(e) {
    const target = e.target;
    this.setState({
      val: target.value,
    });
  }

  handleClick() {
    if (!this.state.val) {
      alert('请输入激活码！');
    } else {
      this.props.activateFun(this.state.val);
    }
  }

  handleScanQR() {
    wx.scanQRCode({
      needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
      scanType: ['qrCode', 'barCode'], // 可以指定扫二维码还是一维码，默认二者都有
      success: (res) => {
        this.props.activateFun(res.resultStr);
      },
      cancel: () => {
        // alert(JSON.stringify(error));
      },
      fail: () => {
        // alert(JSON.stringify(error));
      },
    });
  // this.props.scanQR();
  }

  bottomBtn() {
    const btns = [{
      id: 1,
      btnName: '切换扫码激活',
      btnFun: this.handleScanQR,
    }];
    return <BottomBtn btnList={btns} />;
  }

  render() {
    return (
      <div className="activate-box">
        {conf.key === 'e4273d13a384168962ee93a953b58ffd' ? <Back /> : ''}
        <div className="activate-input">
          <input value={this.state.val} onChange={this.handleChange} />
          <span className="activate-btn" onTouchStart={this.handleClick}>激活</span>
        </div>
        {this.bottomBtn()}
      </div>
    );
  }
}

const mapStateToProps = state => ({
  activateInfo: state.activateInfo,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Activate);
