import React from 'react';
import { render } from 'react-dom';

import './shareticket.scss';
import Share from '../containers/share';

const rootElement = document.getElementById('main');

render(
  <Share />,
  rootElement
);
