import React, { Component } from 'react';
import './discountlist.scss';

class DiscountList extends Component {
  // static propTypes = {
  //   cars: PropTypes.array.isRequired,
  //   onSelect: PropTypes.func.isRequired,
  // };

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    return (
      <div>
      <div className="item">
        <div className="item-inner">
          <i className="radius-top"></i>
          <div className="item-info">
            <div className="item-left">
              <h3>体验券</h3>
              <p>1小时</p>
              <address>西单大悦城停车券</address>
            </div>
            <div className="item-right">
              <h5>11.15-11.16</h5>
              <span>2016</span>
            </div>
          </div>
          <i className="radius-bottom"></i>
        </div>
      </div>

      <div className="item active">
        <div className="item-inner">
          <i className="radius-top"></i>
          <div className="item-info">
            <div className="item-left">
              <h3>体验券</h3>
              <p>1小时</p>
              <address>西单大悦城停车券</address>
            </div>
            <div className="item-right">
              <h5>11.15-11.16</h5>
              <span>2016</span>
            </div>
          </div>
          <i className="radius-bottom"></i>
        </div>
      </div>


      </div>
    );
  }
}

export default DiscountList;
