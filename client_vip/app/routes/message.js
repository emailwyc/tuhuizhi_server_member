
const conf = require('../package');
const resource = conf.resource;

module.exports = function() {
  router.get('/Message/list', function* (next) {
    yield this.render('Message_list', {
      partials: {
        MessageSideb: 'Message_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'message/messageList',
        title: '消息列表',
      },
      isMsgList: true,
      id: this.query.id,
    });
  });

  router.get('/Message/addMsg', function* (next) {
    yield this.render('Message_Add', {
      partials: {
        MessageSideb: 'Message_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'message/messageAdd',
        title: '添加消息',
      },
      isAddMsg: true,
      // useEditor: true,
      id: this.query.id,
    });
  });

  router.get('/Message/templateMsgList', function* (next) {
    yield this.render('template_templateMsgList', {
      partials: {
        MessageSideb: 'Message_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'message/templateMsgList',
        title: '模版消息列表',
      },
      isTemplateMsgList: true,
    });
  });

  router.get('/Message/addTemplateMsg', function* (next) {
    yield this.render('template_addTemplateMsg', {
      partials: {
        MessageSideb: 'Message_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'message/addTemplateMsg',
        title: '添加模版消息',
      },
      isAddTemplateMsg: true,
      id: this.query.id,
    });
  });
};
