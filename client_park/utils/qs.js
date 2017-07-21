export default (name) => {
  const reg = new RegExp(`(^|&)${name}=([^&]*)(&|$)`);
  const r = window.location.search.substr(1).replace(/\?/g, '&').match(reg);
  if (r !== null) {
    return decodeURIComponent(r[2]);
  }
  return '';
};
