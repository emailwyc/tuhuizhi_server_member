export const getRecordsObj = (state = {
  isMore: false,
  list: [],
}, action) => {
  switch (action.type) {
    case 'GETRECORDSREQUEST':
      return Object.assign({}, state, { isMore: false, list: [] });
    case 'GETRECORDSSUCCESS':
      return Object.assign({}, state, { isMore: action.data.isMore, list: action.data.data });
    case 'GETRECORDSERROR':
      return Object.assign({}, state, { isMore: false, code: 1 });
    default:
      return state;
  }
};
