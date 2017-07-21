import React, { Component, PropTypes } from 'react';
import './showfee.scss';
const conf = window.conf;

class ShowFee extends Component {

  static propTypes = {
    pic: PropTypes.string,
  };

  constructor() {
    super();
    this.state = {
    };
  }

  toUrl() {
    location.href = `/park/standard?key_admin=${conf.key}`;
  }

  render() {
    return (
      <div className="standard-btn">
        <span onClick={
          () => {
            this.toUrl();
          }
        }
        >收费优惠标准</span>
      </div>
    );
  }
}

export default ShowFee;
