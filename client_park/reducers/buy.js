export const buyList = (state = {
  list: [],
}, action) => {
  switch (action.type) {
    case 'BUYSREQUEST':
      return Object.assign({}, state, { list: [] });
    case 'BUYSSUCCESS':
      return Object.assign({}, state, { list: action.data });
    case 'BUYSERROR':
      return Object.assign({}, state, { list: [] });
    default:
      return state;
  }
};
