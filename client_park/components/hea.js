import React, { Component, PropTypes } from 'react';
import './hea.scss';

class Hea extends Component {

  static propTypes = {
    data: PropTypes.object.isRequired,
    isNeed: PropTypes.string,
  };

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  // componentWillReceiveProps(nextProps) {
  //   console.log(nextProps);
  // }

  classFun() {
    const { data } = this.props;
    return (data.status === 0 && !data.instanceList) ||
     (data.status === 0 && data.instanceList && data.instanceList[0].status === 2) ||
     (data.status && data.status === 2) ? 'active' : '';
  }

  numFun() {
    const { data } = this.props;
    if ((data.status === 0 && !data.instanceList) || (data.status && data.status === 2)) {
      return data.num;
    } else if (data.status === 0 && data.instanceList && data.instanceList[0].status === 2) {
      return data.instanceList.length;
    }
    return '';
  }

  render() {
    const { data, isNeed } = this.props;
    // 开发票状态class active(已开发票) opening(开票中)
    return (
      <dl className="hea">
        <dt><em style={{ backgroundImage: `url(${data.image_url})` }}></em></dt>
        <dd>
          <h2>{data.main_info}</h2>
          <h3>{data.extend_info}</h3>
          {
            isNeed ? <div className="invoice-box"><p className="active">{data.num}张</p>
            <span className="">未开发票</span></div>
             : <p className={this.classFun()}>{this.numFun()}张</p>
          }
        </dd>
      </dl>
    );
  }
}

export default Hea;
