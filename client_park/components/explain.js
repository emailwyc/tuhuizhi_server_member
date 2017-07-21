import React, { Component, PropTypes } from 'react';
import './explain.scss';

class Explain extends Component {

  static propTypes = {
    data: PropTypes.object.isRequired,
  };

  constructor() {
    super();
    this.state = {
    };
  }

  render() {
    const { data } = this.props;
    return (
      <div>
        {
          data.desc_clause && <div className="explain">
            <h3>使用说明</h3>
            <div><pre>{data.desc_clause}</pre></div>
          </div>
        }
      </div>
    );
  }
}

export default Explain;
