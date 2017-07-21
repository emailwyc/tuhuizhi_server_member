import React, { Component, PropTypes } from 'react';
import { wxsdk } from 'wxsdk';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import * as actions from '../actions/shareticket';
import Hea from '../components/hea';
import Explain from '../components/explain';
import Add from '../components/add';
import BottomBtn from '../components/bottombtn';
import Tip from '../components/tip';
import utils from '../utils';

const wx = window.wx;
const conf = window.conf;
// import Qrcode from '../components/qrcode';
// <Qrcode />

class Shareticket extends Component {
  static propTypes = {
    myTicketInfoFun: PropTypes.func.isRequired,
    myTicketInfo: PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      num: 1,
    };
    this.getNum = this.getNum.bind(this);
    localStorage.setItem(`${conf.key}share${utils.qs('id')}`, 1);
  }

  componentDidMount() {
    wxsdk({ key_admin: conf.key }).then((json) => {
      const jsconf = json;
      jsconf.debug = false;
      jsconf.jsApiList = ['onMenuShareTimeline', 'onMenuShareAppMessage'];
      wx.config(jsconf);
      wx.ready(() => {
        this.props.myTicketInfoFun(utils.qs('id'), utils.qs('status'));
      });
    });
  }

  getNum(num) {
    this.setState({
      num,
    });
    localStorage.setItem(`${conf.key}share${utils.qs('id')}`, num);
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
    if (data.status === 2) {
      return <Add parentFun={this.getNum} dataNum={`${data.num}`} />;
    }
    return '';
  }

  sendInfo() {
    alert('点击右上角分享！');
  }

  bottomBtn(data) {
    let btns = [];
    if (!data.status) {
      return false;
    }
    if (data.status === 2) {
      btns = [{
        id: 1,
        btnName: '转赠',
        btnFun: this.sendInfo,
      }];
    } else {
      btns = [{
        id: 2,
        btnName: '已使用',
      }];
    }
    return <BottomBtn btnList={btns} />;
  }

  tip(data) {
    if (data.status === 2) {
      return <Tip share="share" price={data.price} num={`${this.state.num}`} />;
    }
    return '';
  }

  render() {
    const { myTicketInfo } = this.props;
    return (
      <div>
        {this.header(myTicketInfo.obj)}
        {this.explain(myTicketInfo.obj)}
        {this.add(myTicketInfo.obj)}
        {this.bottomBtn(myTicketInfo.obj)}
        {this.tip(myTicketInfo.obj)}
      </div>
    );
  }
}

const mapStateToProps = state => ({
  myTicketInfo: state.myTicketInfo,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Shareticket);
