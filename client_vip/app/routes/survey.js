var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/survey/survey', function* (next) {

    yield this.render('survey', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/survey',
        title: '问卷调查',
      },
      isSurvey: true,
    });
  });
  router.get('/survey/declare', function* (next) {

    yield this.render('survey_declare', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/declare',
        title: '问卷调查',
      },
      isDeclare: true,
      useEditor: true,
    });
  });
  router.get('/survey/surveyAdd', function* (next) {

    yield this.render('surveyAdd', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/surveyAdd',
        title: '新增问卷',
      },
      isAddSurvey: true,
      id: this.query.paperId,
    });
  });
  router.get('/survey/surveydetails', function* (next) {

    yield this.render('surveydetails', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/surveydetails',
        title: '调查详情',
      },
      isSurvey: true,
    });
  });
  router.get('/survey/surveyuserlist', function* (next) {

    yield this.render('surveyuserlist', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/surveyuserlist',
        title: '用户列表',
      },
      isSurvey: true,
    });
  });
  router.get('/survey/surveyanswerdetail', function* (next) {

    yield this.render('surveyanswerdetail', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/surveyanswerdetail',
        title: '答题详情',
      },
      isSurvey: true,
    });
  });

  router.get('/survey/editsurver', function* (next) {

    yield this.render('editsurver', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/editsurver',
        title: '编辑修改问卷',
      },
      isSurvey: true,
      id: this.query.paperId,
      ticket: this.query.ticket,
    });
  });

  router.get('/survey/surveyAd', function* (next) {

    yield this.render('surveyAd', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/surveyAd',
        title: '添加问卷调查广告',
      },
      isAd: true,
    });
  });

  router.get('/survey/group', function* (next) {

    yield this.render('survey_group', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/group',
        title: '分组管理',
      },
      isGroup: true,
    });
  });

  router.get('/survey/groupadd', function* (next) {

    yield this.render('survey_groupadd', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/groupadd',
        title: '分组管理',
      },
      id: this.query.id,
      isGroup: true,
    });
  });

  router.get('/survey/property', function* (next) {

    yield this.render('survey_property', {
      partials: {
				surveySide: 'survey_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'survey/property',
        title: '属性配置',
      },
      isProperty: true,
    });
  });
};
