import React, { Component, PropTypes } from 'react';
import './tap.scss';

class Tap extends Component {

  static propTypes = {
    data: PropTypes.array.isRequired,
    parentFun: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      currentIndex: 1,
      taps: [],
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      taps: nextProps.data,
    });
  }

  tapFun(tapId, status) {
    this.setState({
      currentIndex: tapId,
    });
    this.props.parentFun(status);
  }

  loadTap() {
    return this.state.taps.map((item, index) => (
      <li key={index} onClick={() => this.tapFun(item.id, item.status)}
        className={item.id === this.state.currentIndex ? 'active' : ''}
      >{item.tapName}</li>));
  }

  render() {
    return (
      <ul className="tap-box">
        {
          this.loadTap()
        }
      </ul>
    );
  }
}

export default Tap;
