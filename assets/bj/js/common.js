//公用
window.Z = {};
Z.getSys = function() {
	var Sys = {};
	var ua = navigator.userAgent.toLowerCase();
	var s;
	(s = ua.match(/msie ([\d.]+)/)) ? Sys.ie = s[1] :
		(s = ua.match(/firefox\/([\d.]+)/)) ? Sys.firefox = s[1] :
		(s = ua.match(/chrome\/([\d.]+)/)) ? Sys.chrome = s[1] :
		(s = ua.match(/opera.([\d.]+)/)) ? Sys.opera = s[1] :
		(s = ua.match(/version\/([\d.]+).*safari/)) ? Sys.safari = s[1] : 0;
	return Sys;
};
//addEvent
Z.addEvent = function(Elem, type, handle, boo) {
	if (Elem.addEventListener) {
		var _boo = boo ? true : false;
		Elem.addEventListener(type, handle, _boo);
	} else if (Elem.attachEvent) {
		Elem.attachEvent("on" + type, handle);
	};
};
//delEvent
Z.delEvent = function(Elem, type, handle, boo) {
	if (Elem.removeEventListener) {
		var _boo = boo ? true : false;
		Elem.removeEventListener(type, handle, _boo);
	} else if (Elem.detachEvent) {
		Elem.detachEvent("on" + type, handle);
	};
};
//位置
Z.getElemPos = function(obj) {
	var _x,
		_y;
	//左偏移
	_x = parseInt(obj.getBoundingClientRect().left) || 0;
	_x -= parseInt(document.documentElement.clientLeft) || 0;
	_x += parseInt(Math.max(document.body.scrollLeft, document.documentElement.scrollLeft)) || 0;
	//上偏移
	_y = parseInt(obj.getBoundingClientRect().top) || 0;
	_y -= parseInt(document.documentElement.clientTop) || 0;
	//_y += parseInt(obj.offsetHeight) || 0;
	_y += parseInt(Math.max(document.body.scrollTop, document.documentElement.scrollTop)) || 0;
	return {
		'x': _x,
		'y': _y
	};
};
//className
Z.getElemByClass = function(o, ClassName, tag) {
	o = o || document;
	if (o.getElementsByClassName) {
		return (o.getElementsByClassName(ClassName))
	} else {
		tag = tag || "*";
		var r = new RegExp("\\b" + ClassName + "\\b");
		var a = new Array();
		var t = o.getElementsByTagName(tag);
		for (i = 0; i < t.length; i++) {
			if (r.test(t[i].className)) {
				a.push(t[i])
			};
		};
		return a;
	};
};
Z.Ajax = function(set) {
	var that = this;
	this.url = set.url;
	this.callback = set.callback || 0;
	this.set = set || {};
	this.type = this.set.type || 'POST';
	this.data = this.set.data || '';
	this.dataType = this.set.dataType || 'json';
	this.before = this.set.before || 0;
	this.data.toUpperCase();
	//xmlHttp;
	try {
		this.xmlHttp = new XMLHttpRequest();
	} catch (e) {
		try {
			this.xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				this.xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
				alert('您的浏览器不支持ajax！');
			};
		};
	};
	var xmlHttp = this.xmlHttp
		//before send
	if (this.before) {
		this.before();
	};
	xmlHttp.onreadystatechange = function() {
		if (xmlHttp.readyState == 4) {
			if (xmlHttp.status == 200 && that.callback) {
				var res = xmlHttp.responseText;
				if (that.dataType == 'json') {
					res = eval('(' + res + ')');
				};
				that.callback(res);
			};
		};
	};
	if (this.type == 'POST') {
		xmlHttp.open(this.type, this.url, true);
		xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=utf-8;");
		xmlHttp.setRequestHeader("Cache-Control", "no-cache");
		xmlHttp.send(this.data);
	} else {
		var url = this.url;
		if (this.data) {
			if (url.indexOf('?') != -1) {
				url += '&' + this.data;
			} else {
				url += '?' + this.data;
			};
		};
		xmlHttp.open(this.type, url, true);
		xmlHttp.send(null);
	};
};
Z.getUrl = function(url, arg) {
	var reg = new RegExp('(^|\\?|&)' + arg + '=([^&]*)(\\s|&|$)', 'i');
	if (reg.test(url)) {
		return unescape(RegExp.$2.replace(/\+/g, ' '));
	} else {
		return '';
	};
};
//全局变量 力美
var LM = {};
//
(function() {
	//
//	var _back = document.getElementById('back');
//	if (_back) {
//		Z.addEvent(window, 'scroll', function() {
//			var _t = document.body.scrollTop || document.documentElement.scrollTop || 0;
//			if (_t > 100) {
//				_back.style.display = 'block';
//			} else {
//				_back.style.display = 'none';
//			};
//		});
//	};
	//
	if (document.getElementById('login')) {
		var _login = document.getElementById('login');
		var _reg = document.getElementById('reg');
		var _pop = document.getElementById('pop_menu');
		var _t;
		var _link = _pop.getElementsByTagName('a');
		_login.onmouseenter = function() {
			clearTimeout(_t);
			var _pos = Z.getElemPos(this);
			_pop.style.left = (_pos.x + 1) + 'px';
			_pop.style.top = '55px';
			_pop.style.display = 'block';
			this.className += ' active';
			_reg.className = 'a_1';
			_link[0].href = 'login.php?type=adn';
			_link[1].href = 'login.php?type=dsp';
		};
		_reg.onmouseenter = function() {
			clearTimeout(_t);
			var _pos = Z.getElemPos(this);
			_pop.style.left = (_pos.x + 1) + 'px';
			_pop.style.top = '55px';
			_pop.style.display = 'block';
			this.className += ' active';
			_login.className = 'a_0';
			_link[0].href = 'reg.php?type=adn';
			_link[1].href = 'reg.php?type=dsp';
		};
		_pop.onmouseenter = function() {
			clearTimeout(_t);
		};

		function hideMenu() {
			_t = setTimeout(function() {
				_pop.style.display = 'none';
				_login.className = 'a_0';
				_reg.className = 'a_1';
			}, 100);
		};
		_login.onmouseleave = hideMenu;
		_reg.onmouseleave = hideMenu;
		_pop.onmouseleave = hideMenu;
	};
}());