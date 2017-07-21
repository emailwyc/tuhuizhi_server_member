import React, { Component } from 'react';
// import { connect } from 'react-redux';
// import { bindActionCreators } from 'redux';
import ShowFee from '../components/showfee';
// import * as actions from '../actions/records';

class RecordsInfo extends Component {
  // static propTypes = {
  //   getRecords: PropTypes.func.isRequired,
  //   getRecordsObj: PropTypes.object.isRequired,
  // };

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    return (
      <div className="records-info">
        <div className="code-box">
          <figure className="qr-code">
            <img src="https://img.rtmap.com/2.pic_hd.jpg" alt="二维码" />
          </figure>
          <figure className="bar-code">
            <img src="https://img.rtmap.com/3.pic_hd.jpg" alt="条形码" />
            <figcaption>5016-4497-8528</figcaption>
          </figure>
        </div>
        <ul className="detail-car">
          <li>车牌号： 京A2820D</li>
          <li>停车时间： 2016-09-16  14：33</li>
          <li>费用金额： ￥30</li>
          <li>会员优惠： ￥5</li>
          <li><strong>停车缴费： ￥25</strong></li>
        </ul>
        <dl className="order-info">
          <dd>发票状态： <span className="receive-state active">已领取</span></dd>
          <dd>订单号： 190345190513</dd>
        </dl>
        <ShowFee />
      </div>
    );
  }
}

// const mapStateToProps = state => ({
//   getRecordsObj: state.getRecordsObj,
// });
//
// const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default RecordsInfo; // connect(mapStateToProps, mapDispatchToProps)(RecordsInfo);
