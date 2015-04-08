
LM.Reg = {};
LM.Reg.adn = {};
LM.Reg.adn.o = {};
LM.Reg.adn.d = {};

LM.Reg.checkForm = function(type) {
	for (var i = 0; i < type.length; i++) {
		checkForm(type[i]);
	};

	function checkForm(arg) {
		var _input = document.getElementById(arg);
		_input.onblur = function() {
			if (this.value == '') {
				LM.Reg.showError(arg, LM.Reg.Tips[arg] + '不能为空！');
			} else if (LM.Reg.regexp[arg] && !LM.Reg.regexp[arg].test(this.value)) {
				LM.Reg.showError(arg, LM.Reg.Tips[arg] + '格式不正确！');
			} else {
				LM.Reg.hideError(arg);
			};
		};
	};
	return false;
};
LM.Reg.regexp = {
	'email': /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/
};
LM.Reg.Tips = {
	'email': '电子邮件',
	'password': '密码',
	'repassword': '密码',
	'name': '姓名/公司名',
	'tel': '手机',
	'qq': 'QQ',
	'vcode': '验证码'
};
LM.Reg.showError = function(type, text) {
	var _d = LM.Reg.adn.o[type].parentNode;
	_d.className += ' reg_error';
	LM.Reg.adn.o.error.innerHTML = text;
	LM.Reg.adn.d.correct[type] = 0;
};
LM.Reg.hideError = function(type) {
	var _d = LM.Reg.adn.o[type].parentNode;
	_d.className = _d.className.replace(/ reg_error/ig, '');
	LM.Reg.adn.o.error.innerHTML = '&nbsp;';
	LM.Reg.adn.d.correct[type] = 1;
};

LM.Reg.adn.o.form = document.getElementById('reg_adn_form');
if (LM.Reg.adn.o.form) {
	LM.Reg.adn.o.email = document.getElementById('email');
	LM.Reg.adn.o.password = document.getElementById('password');
	LM.Reg.adn.o.repassword = document.getElementById('repassword');
	LM.Reg.adn.o.name = document.getElementById('name');
	LM.Reg.adn.o.tel = document.getElementById('tel');
	LM.Reg.adn.o.qq = document.getElementById('qq');
	LM.Reg.adn.o.vcode = document.getElementById('vcode');
	LM.Reg.adn.o.type = document.getElementsByName('reg_type');
	LM.Reg.adn.o.error = document.getElementById('reg_adn_error');
	LM.Reg.adn.o.btn = document.getElementById('reg_adn_btn');
	LM.Reg.adn.o.media = document.getElementById('media_label');
	LM.Reg.adn.o.ad = document.getElementById('ad_label');

	LM.Reg.adn.o.media.onclick = function () {
		this.className += ' active';
		LM.Reg.adn.o.ad.className = LM.Reg.adn.o.ad.className.replace(/ active/ig,'');
	};
	LM.Reg.adn.o.ad.onclick = function () {
		this.className += ' active';
		LM.Reg.adn.o.media.className = LM.Reg.adn.o.media.className.replace(/ active/ig,'');
	};

	LM.Reg.adn.d.correct = {
		'email': 0,
		'password': 0,
		'repassword': 0,
		'name': 0,
		'tel': 0,
		'qq': 0,
		'vcode': 0
	};
	LM.Reg.checkForm(['email', 'password', 'name', 'tel', 'qq', 'vcode']);
	LM.Reg.adn.o.repassword.onblur = function() {
		var _d = this.parentNode;
		if (this.value == '') {
			LM.Reg.showError('repassword', '密码不能为空！');
		} else if (this.value != LM.Reg.adn.o.password.value) {
			LM.Reg.showError('repassword', '2次输入的密码不一致！请重新输入');
		} else {
			LM.Reg.hideError('repassword');
		};
	};
	setTimeout(function() {
		LM.Reg.adn.o.email.focus();
	}, 0);

	LM.Reg.adn.o.btn.onclick = function() {
		if (LM.Reg.adn.o.email.value == '') {
			LM.Reg.showError('email', '电子邮件不能为空！');
		} else if (!LM.Reg.regexp.email.test(LM.Reg.adn.o.email.value)) {
			LM.Reg.showError('email', '电子邮件格式不正确！');
		};
		if (LM.Reg.adn.o.password.value == '') {
			LM.Reg.showError('password', '密码不能为空！');
		};
		if (LM.Reg.adn.o.repassword.value == '') {
			LM.Reg.showError('repassword', '密码不能为空！');
		} else if (LM.Reg.adn.o.repassword.value != LM.Reg.adn.o.password.value) {
			LM.Reg.showError('repassword', '2次输入的密码不一致！请重新输入');
		};
		if (LM.Reg.adn.o.name.value == '') {
			LM.Reg.showError('name', '姓名/公司名不能为空！');
		};
		if (LM.Reg.adn.o.tel.value == '') {
			LM.Reg.showError('tel', '手机不能为空！');
		};
		if (LM.Reg.adn.o.qq.value == '') {
			LM.Reg.showError('qq', 'QQ不能为空！');
		};
		if (LM.Reg.adn.o.vcode.value == '') {
			LM.Reg.showError('vcode', '验证码不能为空！');
		};
		if (LM.Reg.adn.d.correct.email && LM.Reg.adn.d.correct.password && LM.Reg.adn.d.correct.repassword && LM.Reg.adn.d.correct.name && LM.Reg.adn.d.correct.tel && LM.Reg.adn.d.correct.qq && LM.Reg.adn.d.correct.vcode) {
			setTimeout(function() {
				LM.Reg.adn.o.form.submit();
			}, 0)
		};
	};
};