import React, { Component, PropTypes } from 'react';
import './qrcode.scss';

import 'qrcode';
const $ = window.$;

class Qrcode extends Component {

  static propTypes = {
    qrcode: PropTypes.string,
  };

  constructor() {
    super();
    this.state = {
    };
  }

  componentDidMount() {
    const { qrcode } = this.props;
    $('.imgqr').qrcode({ width: 480, height: 480, text: qrcode });
  }

  render() {
    const { qrcode } = this.props;
    return (
      <div className="wrap">
        <div className="qrcode">
          <div className="imgqr"></div>
        </div>
        <div className="code-txt">{qrcode}</div>
        <div className="xeplain">西单大悦城服务号 &gt;我要- &gt;停车缴费 &gt;激活停车券扫描或输入激活码 </div>
      </div>
    );
  }
}

export default Qrcode;
