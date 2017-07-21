export const code502 = (code) => {
  if (code === 502) {
    alert(code.msg);
    location.href = '/user/login';
    return;
  }
};
