const $ = window.$;
export const out = () => {
  if (confirm('确定要退出吗？')) {
    $.cookie('ukey', '', { path: '/' });
    location.href = '/user/login';
  }
};
