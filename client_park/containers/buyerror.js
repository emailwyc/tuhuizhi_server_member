import React, { Component } from 'react';
import BottomBtn from '../components/bottombtn';
const conf = window.conf;

class BuyError extends Component {

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  toUrl() {
    location.href = `/park/buy?key_admin=${conf.key}`;
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
    return (
      <div className="wrap wrapbg">
        <div className="terms-tit">
          <i className="icon iconfont icon-gantanhao"></i>
          <span>购买失败</span>
        </div>
        {
          this.bottomBtn()
          // <div className="attention">
          //   <h3>错误：</h3>
          //   <div className="look-box">
          //     <p>库存不足。请联系系统管理员，或重新输入购买数量</p>
          //   </div>
          // </div>
          // <div className="buybtn">
          //   <button type="button" className="btn">关闭</button>
          // </div>
        }
      </div>
    );
  }
}

export default BuyError;
