import React from 'react';
import { render } from 'react-dom';

import './activateopen.scss';
import ActivateOpen from '../containers/activateopen';

const rootElement = document.getElementById('main');

render(
  <ActivateOpen />,
  rootElement
);
