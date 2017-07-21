import React, { Component, PropTypes } from 'react';
import './header.scss';
import utils from '../utils';
console.log(utils);

class Header extends Component {

  static propTypes = {
    pic: PropTypes.string,
  };

  constructor() {
    super();
    this.state = {
    };
  }

  render() {
    const pic = this.props.pic && { backgroundImage: `url(${this.props.pic}-w640)` } || {};
    const date = new Date().nowdate();
    return (
      <header>
        <div className="date-left">
          <i className="year">{date.year}</i>
          <p className="date">{date.month} 月 {date.da} 日</p>
          <i className="day">{date.day}</i>
        </div>
        <div className="pic-right">
          <div className="pic" style={pic}></div>
        </div>
      </header>
    );
  }
}

export default Header;
