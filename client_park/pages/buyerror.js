import React from 'react';
import { render } from 'react-dom';

import './buyerror.scss';
import BuyError from '../containers/buyerror';

const rootElement = document.getElementById('main');

render(
    <BuyError />,
  rootElement
);
