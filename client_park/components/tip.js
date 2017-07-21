import React, { Component, PropTypes } from 'react';
import './tip.scss';

class Tip extends Component {

  static propTypes = {
    num: PropTypes.string.isRequired,
    share: PropTypes.string.isRequired,
    price: PropTypes.number,
  };

  constructor() {
    super();
    this.state = {
    };
  }

  render() {
    const price = this.props.num * this.props.price;
    const content = this.props.share === 'share' ?
     `您将要转赠${this.props.num || ''}张悦米停车券，请点击右上角分享按钮 开始转赠`
      : `您将要购买${this.props.num || ''}张悦米停车券，共计${(price ? price.toFixed(2) : '')}元！`;
    return (
      <div className="tip">{content}</div>
    );
  }
}

export default Tip;
