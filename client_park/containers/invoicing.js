import React, { Component } from 'react';
// import { connect } from 'react-redux';
// import { bindActionCreators } from 'redux';
// import * as actions from '../actions/shareticket';
import Hea from '../components/hea';
import Invoice from '../components/invoice';
import BottomBtn from '../components/bottombtn';

class Invoicing extends Component {
  // static propTypes = {
  //   myTicketInfoFun: PropTypes.func.isRequired,
  //   myTicketInfo: PropTypes.object.isRequired,
  // };

  constructor(props) {
    super(props);
    this.state = {
      data: {
        status: 0,
        main_info: 'dddd',
        image_url: 'http://res.rtmap.com/image/prize_pic/2016-11/1478616606234.jpg',
      },
    };
  }

  componentDidMount() {
  }

  invoiceFun() {
    alert('提交开发票');
  }

  render() {
    const btns = [{
      id: 1,
      btnName: '开发票',
      btnFun: this.invoiceFun,
    }];
    return (
      <div>
      <Hea data={this.state.data} isNeed="" />
      {
        <Invoice money="1" />
      }
      <BottomBtn btnList={btns} />
      </div>
    );
  }
}

// const mapStateToProps = state => ({
//   myTicketInfo: state.myTicketInfo,
// });
//
// const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default Invoicing; // connect(mapStateToProps, mapDispatchToProps)(Invoicing);
