import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import * as actions from '../actions/myticket';
import Tap from '../components/tap';
const conf = window.conf;

class Invoice extends Component {
  static propTypes = {
    myTicketFun: PropTypes.func.isRequired,
    myTicketList: PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      taps: [{
        tapName: '未开发票',
        id: 1,
        status: 0,
      }, {
        tapName: '已开发票',
        id: 2,
        status: 1,
      }],
    };
    this.useFun = this.useFun.bind(this);
  }

  componentDidMount() {
    this.props.myTicketFun(0);
  }

  useFun(status) {
    this.props.myTicketFun(status);
  }

  ticketInfo(id) {
    location.href = `/park/invoicedetail?key_admin=${conf.key}&id=${id}`;
  }

  loadData() {
    if (this.props.myTicketList.list.length === 0) {
      return <div className="no-discount">暂无数据</div>;
    }
    return this.props.myTicketList.list.map((item) => (
      <div className="item" key={item.prize_id} onClick={() => this.ticketInfo(item.prize_id)}>
        <div className="item-inner">
          <i className="radius-top"></i>
          <div className="item-info">
            <dl className="item-left">
              <dt><em style={{ backgroundImage: `url(${item.image_url})` }}></em></dt>
              <dd>
              <h2>{item.main_info}</h2>
              <h3>{item.extend_info}</h3>
              <div className="invoice-box">
                <p className={item.status === 0 ? '' : 'active'}>{item.num}张</p>
                <span>未开发票</span>
              </div>
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
      <div className="myticket-box">
        <Tap data={this.state.taps} parentFun={this.useFun} />
        <div className="ticket-list">
        {
          this.loadData()
          // <div className="item">
          //   <i className="radius-top"></i>
          //   <div className="item-info">
          //     <dl className="item-left">
          //       <dt><em></em></dt>
          //       <dd>
          //       <h2>悦米停车券</h2>
          //       <h3>抵用停车费0.10元</h3>
          //       <p className="active">500张</p>
          //       </dd>
          //     </dl>
          //     <div className="iconfont-in"
          //  dangerouslySetInnerHTML={ { __html: '&#xe6cb;' } }></div>
          //   </div>
          //   <i className="radius-bottom"></i>
          // </div>
        }
        </div>
      </div>
    );
  }
}

const mapStateToProps = state => ({
  myTicketList: state.myTicketList,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Invoice);
