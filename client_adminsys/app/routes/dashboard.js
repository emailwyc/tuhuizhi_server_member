var conf = require('../package');
var resource = conf.resource;
module.exports = function() {
	router.get('/dashboard', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		console.log('/dashboard', this.url);
		yield this.render('index', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/index',
				title: '管理后台'
			},
			isDashboard: true,
			isIdList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			describe: this.query.describe,
			payAccNum: this.query.payAccNum,
		});
	});
	router.get('/dashboard/login', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('login', {
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/login',
				title: '登陆'
			},
			isLoginPage: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/createaccount', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('createaccount', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/createaccount',
				title: '新建帐号'
			},
			isDashboard: true,
			isActive: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/modifiaccount/:id', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('modifiaccount', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/modifiaccount',
				title: '帐号修改'
			},
			isDashboard: true,
			isIdList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/accountdetails/:id', function* (next) {

		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('accountdetails', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/accountdetails',
				title: '帐号详情'
			},
			isDashboard: true,
			isIdList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/interfacelist/:id', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('interfacelist', {
			partials: {
				member_sideb: 'membersideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/interfacelist',
				title: '接口列表(request_keys)'
			},
			isMember: true,
			// isActive: true,
			isApiList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/interfacelist2/:id', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('interfacelist2', {
			partials: {
				member_sideb: 'membersideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/interfacelist2',
				title: '接口列表(resphone_keys)'
			},
			isMember: true,
			// isActive: true,
			isApiList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/memberconfig', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('memberconfig', {
			partials: {
				member_sideb: 'membersideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/memberconfig',
				title: '会员配置'
			},
			isMember: true,
			isApiList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/interfaceconfig', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('interfaceconfig', {
			partials: {
				member_sideb: 'membersideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/interfaceconfig',
				title: '接口配置'
			},
			isMember: true,
			isApiConfig: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/interfaceconfig2/:id', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('interfaceconfig2', {
			partials: {
				member_sideb: 'membersideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/interfaceconfig2',
				title: '接口配置'
			},
			isMember: true,
			isApiConfig: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/modifipassword/:id', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('modifipassword', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/modifipassword',
				title: '修改密码'
			},
			isIdList: true,
			isDashboard: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/resources', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('resources', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/resources',
				title: '资源管理'
			},
			isDashboard: true,
			isResources: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			serverTime: (new Date).getTime()
		});
	});

	router.get('/dashboard/binding/:id', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('binding', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/binding',
				title: '资源绑定'
			},
			isDashboard: true,
			isResources: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			name: this.query.name,
			id: this.params.id,
			serverTime: (new Date).getTime()
		});
	});

	router.get('/dashboard/authorize', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('authorize', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/authorize',
				title: '微信第三方授权'
			},
			isAuthorize: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/buildingid/:id', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('buildingid', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/buildingid',
				title: '添加建筑物id'
			},
			isDashboard: true,
			isIdList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			name: this.query.name,
			serverTime: (new Date).getTime()
		});
	});

	router.get('/dashboard/version', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('version', {
			partials: {
				member_sideb: 'membersideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/version',
				title: '版本管理'
			},
			isMember: true,
			// isApiList: true,
			isVersion: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			serverTime: (new Date).getTime()
		});
	});

	router.get('/dashboard/versionlist/:id', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('versionlist', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/versionlist',
				title: '查看版本'
			},
			isIdList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			name: this.query.name,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/subaccount', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('subaccount', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/subaccount',
				title: '子账户'
			},
			isDashboard: true,
			isIdList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.query.id,
			name: this.query.name,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/establishaccount', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('establish_subaccount', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/establishaccount',
				title: '创建子账号'
			},
			isDashboard: true,
			isIdList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.query.id,
			name: this.query.name,
			subid: this.query.subid,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/editsubaccount', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('editsubaccount', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/editsubaccount',
				title: '修改子账户'
			},
			isDashboard: true,
			isIdList: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.query.id,
			subid: this.query.subid,
			name: this.query.name,
			serverTime: (new Date).getTime()
		});
	});

	router.get('/dashboard/functionentry', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('functionentry', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/functionentry',
				title: '功能入口'
			},
			isDashboard: true,
			isFunctionentry: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/versionlistall', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('versionlistall', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/versionlistall',
				title: '功能入口'
			},
			isDashboard: true,
			isFunctionentry: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			doid: this.query.doid,
			name: this.query.name,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/subcolumn', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('subcolumn', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/subcolumn',
				title: '子栏目管理'
			},
			isDashboard: true,
			isFunctionentry: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			doid: this.query.doid,
			name: this.query.name,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/subcolumnall', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('subcolumnall', {
			partials: {
				_sideb: '_sideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/subcolumnall',
				title: '子栏目管理'
			},
			isDashboard: true,
			isFunctionentry: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			id: this.params.id,
			doid: this.query.doid,
			catalogid: this.query.catalogid,
			name: this.query.name,
			serverTime: (new Date).getTime()
		});
	});
	router.get('/dashboard/messageasgin', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('message', {
			partials: {
				_messagesideb: 'messagesideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/messageasgin',
				title: '短信签名管理'
			},
			isInfo: true,
			isMessageasgin: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			describe: this.query.describe,
			payAccNum: this.query.payAccNum,
		});
	});
	router.get('/dashboard/memberformlist', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('memberformlist', {
			partials: {
				member_sideb: 'membersideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/memberformlist',
				title: '表单列表'
			},
			isMember: true,
			ismemberformlist: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			serverTime: (new Date).getTime()
		});
	});

	router.get('/dashboard/columnadvertisement', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('columnadvertisement', {
			partials: {
				_columnadvertisementsideb: 'columnadvertisementsideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/columnadvertisement',
				title: '栏目广告列表'
			},
			isInfo: true,
			isColumnAdvertisement: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
		});
	});

	router.get('/dashboard/columnadvertisementdetails', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('columnadvertisementdetails', {
			partials: {
				_columnadvertisementsideb: 'columnadvertisementsideb'
			},
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/columnadvertisementdetails',
				title: '栏目广告详情'
			},
			isInfo: true,
			isColumnAdvertisement: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			name: this.query.name,
			id: this.query.id,
		});
	});

	router.get('/dashboard/pageview', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('pageview', {
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/pageview',
				title: '页面访问量统计'
			},
			isInfo: true,
			isPageView: true,
			ukey: this.query.ukey,
			openid: this.query.openid,
			name: this.query.name,
			id: this.query.id,
		});
	});

	router.get('/dashboard/pageviewdetails', function* (next) {
		var isH2 = this.req.headers['x-forwarded-proto-version'] === 'h2';
		yield this.render('pageviewdetails', {
			page: {
				staticBase: isH2 ? '' : this.protocol + '://res.rtmap.com',
				version: resource.static,
				mode: resource.mode,
				id: 'dashboard/pageviewdetails',
				title: '页面访问量详细数据'
			},
			isInfo: true,
			isPageView: true,
			ukey: this.query.ukey,
			id: this.query.id,
			adminid: this.query.adminid,
		});
	});
};
