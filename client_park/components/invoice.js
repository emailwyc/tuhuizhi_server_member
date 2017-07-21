import React, { Component } from 'react';
import './invoice.scss';

class Invoice extends Component {

  static propTypes = {
    // data: PropTypes.object.isRequired,
  };

  constructor() {
    super();
    this.state = {
    };
  }

  render() {
    // const { data } = this.props;
    return (
      <div className="invoice-component">
        <h3 className="tit">
          <strong>发票金额：</strong><span>50元</span>
        </h3>
        <div className="invoice-tit-box">
          <h3 className="tit">
            <strong>发票金额：</strong>
          </h3>
          <div className="invoice-con">
            <label>
              <input type="radio" name="invoice" defaultChecked="true" value="" /><span>个人</span>
            </label>
            <label>
              <input type="radio" name="invoice" value="" /><span>个人</span>
              <input type="text" value="" placeholder="单位名称" />
            </label>
          </div>
        </div>
      </div>
    );
  }
}

export default Invoice;
