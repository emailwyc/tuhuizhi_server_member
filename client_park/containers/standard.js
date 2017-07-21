import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import ReactDOM from 'react-dom';
import { bindActionCreators } from 'redux';
// import Loading from 'rtui/loading';
import * as actions from '../actions/standard';

class Standard extends Component {
  static propTypes = {
    getInfo: PropTypes.func.isRequired,
    getInfoObj: PropTypes.object.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      standardWord: '',
    };
  }

  componentDidMount() {
    this.props.getInfo();
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      standardWord: nextProps.getInfoObj.obj.function_name || '',
    });
  }

  componentDidUpdate() {
    ReactDOM.findDOMNode(this.refs.standardWord).innerHTML = this.state.standardWord;
  }

  render() {
    return (
      <div className="standard-box">
        <div className="tap-hea">
          <div className="tap active">收费标准</div>
          {
            // <div className="tap">会员优惠</div>
          }
        </div>
        <div className="tap-con">
          <div className="con-word" ref="standardWord"></div>
          {
            // <ul>
            //   <li ref="standardWord"></li>
            //   <li>2.30分钟-4小时5元 </li>
            //   <li>3.4小时以上每小时2元</li>
            //   <li>4.24小时内封顶25元</li>
            // </ul>
          }
        </div>
      </div>
    );
  }
}

const mapStateToProps = state => ({
  getInfoObj: state.getInfoObj,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Standard);
