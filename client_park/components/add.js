import React, { Component, PropTypes } from 'react';
import './add.scss';

class Add extends Component {

  static propTypes = {
    dataNum: PropTypes.string,
    parentFun: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      val: 1,
    };
    this.handleChange = this.handleChange.bind(this);
    this.decl = this.decl.bind(this);
    this.add = this.add.bind(this);
  }

  // componentWillMount() {
  //   if (this.props.dataNum) {
  //     this.props.parentFun(this.props.dataNum);
  //   }
  // }

  handleChange(e) {
    const value = parseInt(e.target.value, 10);
    if (value >= this.props.dataNum) {
      this.setState({
        val: this.props.dataNum,
      });
      this.props.parentFun(this.props.dataNum);
    } else if (value <= 1) {
      this.setState({
        val: 1,
      });
      this.props.parentFun(1);
    } else {
      this.setState({
        val: value,
      });
      this.props.parentFun(value);
    }
    // this.setState({
    //   val: target.value,
    // });
    // this.props.parentFun(target.value);
  }

  decl() {
    if (this.state.val <= 1) {
      this.setState({
        val: 1,
      });
      this.props.parentFun(1);
    } else {
      const num = this.state.val - 1;
      this.setState({
        val: num,
      });
      this.props.parentFun(num);
    }
  }

  add() {
    if (this.state.val >= this.props.dataNum) {
      this.setState({
        val: this.props.dataNum,
      });
      this.props.parentFun(this.props.dataNum);
    } else {
      const num = this.state.val + 1;
      this.setState({
        val: num,
      });
      this.props.parentFun(num);
    }
    // const num = this.state.val + 1;
    // this.setState({
    //   val: num,
    // });
    // this.props.parentFun(num);
  }

  stopFun(e) {
    e.stopPropagation();
    e.preventDefault();
    return false;
  }

  render() {
    return (
      <div className="add">
        <em className="iconfont icon-jian" onClick={this.decl
        } onDoubleClick={(e) => this.stopFun(e)}
        ></em>
        <input type="number" value={this.state.val} onChange={this.handleChange} />
        <em className="iconfont icon-jia" onClick={this.add
        } onDoubleClick={(e) => this.stopFun(e)}
        ></em>
        {
      // <Tip share={this.props.keyName} price={this.props.data.price} num={`${this.state.val}`} />
        }
      </div>
    );
  }
}

export default Add;
