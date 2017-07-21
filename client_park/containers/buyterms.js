import React, { Component } from 'react';
import utils from '../utils';
import BottomBtn from '../components/bottombtn';
const conf = window.conf;

class Buyterms extends Component {

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  goBuy() {
    location.href = `/park/buyticket?key_admin=${conf.key}&id=${utils.qs('id')}`;
  }

  render() {
    const btns = [{
      id: 1,
      btnName: '开始购买',
      btnFun: this.goBuy,
    }];
    return (
      <div className="wrap wrapbg">
        <div className="terms-tit">
          <i className="iconfont icon-gantanhao"></i>
          <span>购买条款</span>
        </div>
        <div className="attention">
          <h3>注意：</h3>
          <div className="look-box">
            <p>1.停车券购买后不能退款</p>
            <p>2.停车券长期有效</p>
            <p>3.可以在线开电子发票</p>
          </div>
        </div>
        <div className="disagree">
          不同意条款请点击左上角返回。同意条款请点击下方开始购买
        </div>
        <BottomBtn btnList={btns} />
      </div>
    );
  }
}

export default Buyterms;
