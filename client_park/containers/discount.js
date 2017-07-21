import React, { Component } from 'react';
// import { connect } from 'react-redux';
// import { bindActionCreators } from 'redux';
import DiscountList from '../components/discountlist';
// import * as actions from '../actions/records';

class Discount extends Component {
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
      <div className="discount-box">
        <header className="discount-tit">可用优惠券 （2）</header>
        <div className="discount-con">
          <div className="no-discount">暂无优惠券</div>
          <DiscountList />
        </div>
        <div className="sure-btn">确定</div>
      </div>
    );
  }
}

// const mapStateToProps = state => ({
//   getRecordsObj: state.getRecordsObj,
// });
//
// const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default Discount; // connect(mapStateToProps, mapDispatchToProps)(RecordsInfo);
