import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import * as actions from '../actions/myticket';
import Tap from '../components/tap';
import Back from '../components/back';
const conf = window.conf;

class Myticket extends Component {
  static propTypes = {
    myTicketFun: PropTypes.func.isRequired,
    myTicketList: PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      taps: [{
        tapName: '未使用',
        id: 1,
        status: 2,
      }, {
        tapName: '已使用',
        id: 2,
        status: 3,
      }],
    };
    this.useFun = this.useFun.bind(this);
  }

  componentDidMount() {
    this.props.myTicketFun(2);
  }

  useFun(status) {
    this.props.myTicketFun(status);
  }

  ticketInfo(id, status) {
    const testurl = location.href.indexOf('h5.rtmap.com') > 0 ? 'h5' : 'h2';
    location.href = `http://fw.joycity.mobi/share/yuemi/park_ticket.html?isTestUrl=${testurl}&key_admin=${
      conf.key}&id=${id}&status=${status}`;
  }

  loadData() {
    if (this.props.myTicketList.info && this.props.myTicketList.list.length === 0) {
      return <div className="no-discount">暂无数据</div>;
    }
    return this.props.myTicketList.list.map((item, index) => (
      <div className="item" key={index} onClick={() => this.ticketInfo(item.prize_id, item.status)}>
        <div className="item-inner">
          <i className="radius-top"></i>
          <div className="item-info">
            <dl className="item-left">
              <dt><em style={{ backgroundImage: `url(${item.image_url})` }}></em></dt>
              <dd>
              <h2>{item.main_info}</h2>
              <h3>{item.extend_info}</h3>
              <p className={item.status === 2 ? '' : 'active'}>{item.num}张</p>
              </dd>
            </dl>
            <div className="iconfont icon-jinru"></div>
          </div>
          <i className="radius-bottom"></i>
        </div>
      </div>
    ));
  }

  render() {
    return (
      <div>
        {conf.key === 'e4273d13a384168962ee93a953b58ffd' ? <Back /> : ''}
        <div className="myticket-box">
          <Tap data={this.state.taps} parentFun={this.useFun} />
          <div className="ticket-list">
          {
            this.loadData()
          }
          </div>
        </div>
      </div>
    );
  }
}

const mapStateToProps = state => ({
  myTicketList: state.myTicketList,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Myticket);
