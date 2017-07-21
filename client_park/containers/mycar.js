import React, { Component } from 'react';
import { connect } from 'react-redux';
const conf = window.conf;

class Mycar extends Component {

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  goLink(toUrl) {
    location.href = `${toUrl}?key_admin=${conf.key}`;
  }

  render() {
    return (
      <div className="mycar-box">
        <ul className="tap-box">
          <li className="item-tap" onClick={() => this.goLink('/park/records')}>
            <div className="item-con">停车缴费记录、领发票</div>
            <div className="in-box">
              {
                // <strong>0</strong>
              }
              <i className="iconfont icon-jinru"></i>
            </div>
          </li>
        </ul>
        {
          // <div className="attention-btn">
          //   <span>《汽车服务协议》</span>
          // </div>
        }
      </div>
    );
  }
}

const mapStateToProps = state => ({
  car: state.car,
});

export default connect(mapStateToProps)(Mycar);
