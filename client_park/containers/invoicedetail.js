import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import * as actions from '../actions/shareticket';
import Hea from '../components/hea';
import Explain from '../components/explain';
import BottomBtn from '../components/bottombtn';
import utils from '../utils';

class Invoicedetail extends Component {
  static propTypes = {
    myTicketInfoFun: PropTypes.func.isRequired,
    myTicketInfo: PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  componentDidMount() {
    this.props.myTicketInfoFun(utils.qs('id'));
  }

  header(data) {
    if (data.main_info) {
      return <Hea data={data} isNeed="true" />;
    }
    return '';
  }

  explain(data) {
    return <Explain data={data} />;
  }

  download() {
    alert('下载电子发票');
  }

  invoiceOpen() {
    alert('我要开发票');
  }

  bottomBtn(data) {
    // 0-已开发票  1-未开发票
    if (data.status === 1) {
      return <BottomBtn info="下载电子发票" fun={this.download} infoClass="true" />;
    } else if (data.status === 0) {
      return <BottomBtn info="我要开发票" fun={this.invoiceOpen} infoClass="true" />;
    }
    return <BottomBtn info="正在开发票" infoClass="false" />;
  }

  render() {
    const { myTicketInfo } = this.props;
    return (
      <div>
      {this.header(myTicketInfo.obj)}
      {this.explain(myTicketInfo.obj)}
      {this.bottomBtn(myTicketInfo.obj)}
      </div>
    );
  }
}

const mapStateToProps = state => ({
  myTicketInfo: state.myTicketInfo,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Invoicedetail);
