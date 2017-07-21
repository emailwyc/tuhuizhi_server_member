import React, { Component } from 'react';
import './back.scss';

class Back extends Component {

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  back() {
    location.href = 'http://fw.joycity.mobi/weixin/index.php?action=car&option=entry';
  }

  render() {
    return (
      <div className="back-box">
        <span className="iconfont icon-xiaoyuhao" onClick={this.back}>返回</span>
      </div>
    );
  }
}

export default Back;
