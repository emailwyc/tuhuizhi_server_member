import React, { Component, PropTypes } from 'react';
// import ReactDOM from 'react-dom';
// ReactDOM.findDOMNode(this.refs.recordsTbody);
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import Back from '../components/back';
import * as actions from '../actions/records';
import utils from '../utils';
const conf = window.conf;
console.log(utils);

class Records extends Component {
  static propTypes = {
    getRecords: PropTypes.func.isRequired,
    getRecordsObj: PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      page: 2,
      data: this.props.getRecordsObj.list,
      hasData: true,
    };
  }

  componentDidMount() {
    this.props.getRecords();
  }

  componentWillReceiveProps(nextProps) {
    let hasDa;
    if (this.state.data.concat(nextProps.getRecordsObj.list).length > 0) {
      hasDa = true;
    } else {
      hasDa = false;
    }
    this.setState({
      data: this.state.data.concat(nextProps.getRecordsObj.list),
      hasData: hasDa,
    });
  }

  pageFun() {
    this.setState({
      page: this.state.page + 1,
    });
    this.props.getRecords(this.state.page);
  }

  render() {
    const { getRecordsObj } = this.props;
    // 发票
    // <td className={item.invoice_time !== '0' ? 'getit active' : 'getit'}>
    // {item.invoice_time !== '0' ? '已开票' : '未开票'}</td>
    const records = this.state.data.map((item, i) => (
      <tr key={i} data-id={item.id}>
        <td>
          <i>{(new Date(parseInt(item.pay_time || item.begintime, 10) * 1000)).format('h:m')}</i>
          <p>{(new Date(parseInt(item.pay_time || item.begintime, 10) * 1000)).format('M-d')}</p>
          <i>{(new Date(parseInt(item.pay_time || item.begintime, 10) * 1000)).format('yyyy')}</i>
        </td>
        <td>{item.carno}</td>
        <td>￥{item.payfee}</td>
      </tr>
    ));
    return (
      <div className="records-div">
        {conf.key === 'e4273d13a384168962ee93a953b58ffd' ? <Back /> : ''}
        <table className="records-box">
          <thead>
            <tr>
              <th>缴费时间</th>
              <th>车辆</th>
              <th>停车缴费</th>
              {
                // <th>发票状态</th>
              }
            </tr>
          </thead>
          <tbody ref="recordsTbody">{
            records
          }</tbody>
        </table>
        <div className={this.state.hasData ? 'no-records' : 'no-records active'}>
          <span>无缴费记录！</span>
        </div>
        <div className={getRecordsObj.isMore ? 'more-btn' : 'more-btn no-data'}>
          <span onClick={() => this.pageFun()}>加载更多</span>
        </div>
      </div>
    );
  }
}

const mapStateToProps = state => ({
  getRecordsObj: state.getRecordsObj,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Records);
