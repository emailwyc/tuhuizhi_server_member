export const activateInfo = (state = {
  obj: {},
}, action) => {
  switch (action.type) {
    case 'INFOREQUEST':
      return Object.assign({}, state, { obj: {} });
    case 'INFOSUCCESS':
      return Object.assign({}, state, { obj: action.data });
    case 'INFOERROR':
      return Object.assign({}, state, { obj: {} });
    default:
      return state;
  }
};

// export const qrscan = (state = {
//   obj: {},
// }, action) => {
//   switch (action.type) {
//     case 'QRREQUEST':
//       return Object.assign({}, state, { obj: {} });
//     case 'QRSUCCESS':
//       return Object.assign({}, state, { obj: action.data });
//     case 'QRERROR':
//       return Object.assign({}, state, { obj: {} });
//     default:
//       return state;
//   }
// };
