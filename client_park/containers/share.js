import React, { Component } from 'react';
import Hea from '../components/hea';
import Qrcode from '../components/qrcode';
import utils from '../utils';

class Share extends Component {

  constructor(props) {
    super(props);
    const dataObj = JSON.parse(decodeURIComponent(utils.qs('data')));
    dataObj.status = null;

    this.state = {
      data: dataObj,
      code: utils.qs('code'),
    };
  }

  header(data) {
    return <Hea data={data} />;
  }

  qrcode(data) {
    return <Qrcode qrcode={data} />;
  }

  render() {
    const { data, code } = this.state;
    return (
      <div>
      {this.header(data)}
      {this.qrcode(code)}
      </div>
    );
  }
}

export default Share;
