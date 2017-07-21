import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import * as actions from '../actions/buy';
import TicketItem from '../components/ticketitem';
import Back from '../components/back';
const conf = window.conf;

class Buy extends Component {
  static propTypes = {
    buysFun: PropTypes.func.isRequired,
    buyList: PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
    };
    this.tapTicket = this.tapTicket.bind(this);
  }

  componentDidMount() {
    this.props.buysFun();
  }

  // componentWillReceiveProps(nextProps) {
  //   console.log(nextProps);
  // }

  tapTicket(id) {
    location.href = `/park/buyterms?key_admin=${conf.key}&id=${id}`;
  }

  render() {
    const { buyList } = this.props;
    return (
      <div>
        {conf.key === 'e4273d13a384168962ee93a953b58ffd' ? <Back /> : ''}
        <div className="ticket-list">
          <TicketItem list={buyList.list} ontap={this.tapTicket} />
        </div>
      </div>
    );
  }
}

const mapStateToProps = state => ({
  buyList: state.buyList,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Buy);
