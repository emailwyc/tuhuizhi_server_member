import React, { Component } from 'react';
import Hea from '../components/hea';
import Explain from '../components/explain';
import BottomBtn from '../components/bottombtn';
import utils from '../utils';
const conf = window.conf;

class ActivateOpen extends Component {

  constructor(props) {
    super(props);
    this.state = {
      activateInfo: JSON.parse(decodeURIComponent(utils.qs('data'))),
    };
    console.log(JSON.parse(decodeURIComponent(utils.qs('data'))));
  }

  clickFun() {
    location.href = `/park/myticket?key_admin=${conf.key}`;
  }

  header(data) {
    return <Hea data={data} />;
  }

  explain(data) {
    return <Explain data={data} />;
  }

  bottomBtn() {
    const btns = [{
      id: 1,
      btnName: '查看我的停车券',
      btnFun: this.clickFun,
    }];
    return <BottomBtn btnList={btns} />;
  }

  render() {
    const { activateInfo } = this.state;
    return (
      <div>
      {this.header(activateInfo)}
      {this.explain(activateInfo)}
      {this.bottomBtn()}
      </div>
    );
  }
}

export default ActivateOpen;
