/* basic extension */
(function($){
	/* 首字母大写 */
	String.prototype.toFirstUpperCase = function() {
		return this.replace(/\b\w+\b/g, function(str){
			return str.substring(0,1).toUpperCase() + str.substring(1);
		});
	};

	/* 首字母小写 */
	String.prototype.toFirstLowerCase = function() {
		return this.replace(/\b\w+\b/g, function(str){
			return str.substring(0,1).toLowerCase() + str.substring(1);
		});
	};

    /* 日期选择器初始化 */
	$('.date-picker-input').datepicker();
	/* 格式化金钱 */
	$.fmoney = function(s, n) {
		n = n > 0 && n <= 20 ? n : 2;
		s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
		var l = s.split(".")[0].split("").reverse(),
			r = s.split(".")[1];
		t = "";
		for(i = 0; i < l.length; i ++ )
		{
			t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");
		}
		return t.split("").reverse().join("") + "." + r;
	};
	/* 修改cookie */
	$.cookie = function(cookieName, cookieValue, seconds, path, domain, secure) {
		if(arguments.length == 1) {
			var cookie_start = document.cookie.indexOf(cookieName);
			var cookie_end = document.cookie.indexOf(";", cookie_start);
			return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + cookieName.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
		}else{
			var expires;
			if(!isNaN(seconds)) {
				expires = new Date();
				expires.setTime(expires.getTime() + seconds);
			}
			document.cookie = escape(cookieName) + '=' + escape(cookieValue)
				+ (expires ? '; expires=' + expires.toGMTString() : '')
				+ ('; path=' + (path ? path : '/'))
				+ (domain ? '; domain=' + domain : '')
				+ (secure ? '; secure' : '');
		}
	}

	/* 获取url参数 */
	$.getParam = function(key) {
		var params = typeof(arguments[1]) == 'string' ? arguments[1] : location.search.substr(1);
		if(!$.getParam.list[params]) {
			var items = params.split('&'),
				list = {},
				tmp = null;
			for(var i = 0; i < items.length; i++) {
				tmp = items[i].split('=');
				list[tmp[0]] = typeof(tmp[1]) == 'string' ? decodeURIComponent(tmp[1]) : tmp[1];
			}
			$.getParam.list[params] = list;
		}
		return $.getParam.list[params][key];
	}
	$.getParam.list = {};

	/* 语言包替换 */
	$.LANG = function(list) {
		var isOne = false;
		if(typeof(list) == 'string') {
			list = [list];
			isOne = true;
		}else if(!$.isArray(list)) {
			return '';
		}
		var patt = new RegExp('\\\{([a-z0-9]+)\\\.([a-z0-9]+)\\\}', 'gi'),
			matches = [],
			str = '',
			replaceList = [],
			item = null;
		for(var i = 0, l = list.length; i < l; ++i) {
			replaceList = [];
			while((matches = patt.exec(list[i])) != null) {
				replaceList.push(matches);
			}
			patt.lastIndex = 0;
			for(var j = 0, jl = replaceList.length; j < jl; ++j) {
				item = replaceList[j];
				list[i] = list[i].replace((new RegExp('\\\{'+item[1]+'\\\.'+item[2]+'\\\}', 'g')), LANG[item[1]][item[2]])
			}
		}
		return isOne ? list[0] : list;
	}

	/* 弹出层 */
	$.modalBox = function(title, content, button, cancelCallback) {
		/* 语言包 */
		title = $.LANG(title);
		content = $.LANG(content);
		button = $.LANG(button);

        var modalboxHtml = '<div id="modalbox" class="modal filter-modal hide fade" tabindex="-1" ' +
            'role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">' +
            '<div class="modal-header">' +
            '<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="$(\'#modalbox\').modal(\'hide\')">×</button>' +
            '<h3></h3>' +
            '</div>' +
            '<div class="modal-body"></div>' +
            '<div class="modal-footer"></div>' +
            '</div>';
        if ($("#modalboxContent").length <= 0) {
            $('body').append('<div id="modalboxContent"></div>');
        }
        $('#modalboxContent').html(modalboxHtml);

		var box = $('#modalbox');
		box.removeClass('filter-modal confirm-modal-2').addClass('filter-modal');
		/* 取消所有事件 */
		box.unbind();
		box.find('div.modal-header h3').html(title);
		box.find('div.modal-body').html(content);
		box.find('div.modal-footer').html(button);

		box.on('hide.bs.modal', function(){
			if(typeof(cancelCallback) == 'function') cancelCallback.call(this);
			box.find('div.modal-body').html('');
		}).focus(function(){
			box.find('div.modal-footer a:first').focus();
		}).modal().css({
			width: 'auto',
			'margin-left': function () {
				return -($(this).width() / 2);
			}
		});
		return box;
	};

	$.stopBubble = function(e) {
		if (e && e.stopPropagation)  e.stopPropagation();
		else window.event.cancelBubble = true;
	};

	/* 警告框 */
	$.alert = function(text, callback, widthSize) {
		var footer = '<a href="#" class="btn btn-primary" data-dismiss="modal" aria-hidden="true">{base.confirm}</a>',
			box = $.modalBox('{base.warning}', '<i class="icon-exclamation"></i><span>'+$.LANG(text)+'</span>', footer, callback)
				.modal().css({width: widthSize ? widthSize : 150});
		box.removeClass('filter-modal').addClass('confirm-modal-2').find('div.modal-footer a:first').keypress(function(e){
			if(e.keyCode == 13 || e.keyCode == 32) {
				box.modal('hide');
				return false;
			}
		}).click(function(){ box.modal('hide'); return false; });
	};

	/* 确认框 */
	$.confirm = function(text, okCallback, cancelCallback, widthSize) {
		var footer = '<a href="javascript:;" class="btn btn-primary" onclick="return false">{base.confirm}</a>' +
			'<a href="javascript:;" class="btn btn-cancel" data-dismiss="modal" aria-hidden="true" onclick="$(\'#modalbox\').modal(\'hide\');">{base.cancel}</a>';
		var box = $.modalBox('{base.confirm}', '<i class="icon-question"></i><span>'+$.LANG(text)+'</span>', footer, cancelCallback);
		box.removeClass('filter-modal').addClass('confirm-modal-2').find('div.modal-footer a:first').keypress(function(e){
			if(e.keyCode == 13 || e.keyCode == 32) {
				$(this).click();
				box.modal('hide');
				return false;
			}
		}).click(function(){
			okCallback();
			box.modal('hide');
			return false;
		}).focus();
		box.modal().css({width: widthSize ? widthSize : 200});
	}

	/* 正常提示 */
	$.tips = function(content) {
		var object = $('div.content-header>div.alert'),
			interval = arguments[1] ? arguments[1] : 10000,
			status = arguments[2] ? arguments[2] : 'success';
		if(object.length <= 0) {
			object = $('div.content-header>h3:first').after('<div class="alert left text-hidden"></div>').next();
		}
		object.show().removeClass('alert-error,alert-success').addClass('alert-'+status).html($.LANG(content)+'.');
		if($.tips.handle) clearTimeout($.tips.handle);
		$.tips.handle = setTimeout(function(){
			object.remove();
			$.tips.handle = null;
		}, interval);
	}
	$.tips.handle = null;
	/* 错误提示 */
	$.tipsError = function() {
		$.tips(arguments[0], arguments[1], 'error');
	}


	/* 二次封装ajax，增加统一的处理报错 */
	$.ajaxCall = function(url, success) {
		var fun = arguments.callee,
			config = typeof(arguments[2]) == 'object' ? arguments[2] : {};
		return $.ajax($.extend({
			url: url,
			dataType: 'json',
			success: function(data){
				this.clear();
				if($.isArray(data)) {
					if(data[0] == 'normal') {
						success(data[1]);
					}else if(data[0] == 'error') {
						var str = '', message = data[1].message;
						if(typeof(message) == 'string') message = {0:message};
						for(var k in message) {
							str = message[k];
							break;
						}
						fun.timeoutHandler = setTimeout(function(){
							if(data[1].code == 401) return location.reload();
							$.alert(data[1].code+': ' + str, null, 400);
							fun.timeoutHandler = null;
						}, 500);
					}
				}else{
					this.error();
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				this.clear();
				if(textStatus != 'abort') {
					fun.timeoutHandler = setTimeout(function(){
						$.alert('{base.requestFailed}', null, 400);
						fun.timeoutHandler = null;
					}, 500);
				}
			},
			clear: function(){
				if(fun.timeoutHandler) {
					clearTimeout(fun.timeoutHandler);
					fun.timeoutHandler = null;
				}
			}
		}, config));
	}
	/****************************** 全站通用业务逻辑 ****************************************/
	/* 分页选择页面记录数量 */
	$('#select-pagesize li').click(function(){
		var size = parseInt($(this).find('a').html());
		/* update cookie */
		$.cookie('defps', size);
		location.reload();
		return false;
	});
	$('#switchCompany').click(function(){
		var body = '<form class="form-horizontal" method="get" action="">' +
			'<div class="control-group">' +
			'	<label class="control-label">{base.switchDeveloper}</label>' +
			'    <div class="controls">' +
			'    	<select id="switch_company" name="companyId">' +
			'		</select>' +
			'    </div>' +
			'</div>' +
			'</form>';
		var box = $.modalBox('{base.switchDeveloper}', body, '');
		var okCallback = function(){
			$('#switch_company').change();
		}
		box.unbind().focus(function(){
			$('#switch_company').focus();
		}).find('div.modal-footer a:first').unbind().keypress(function(e){
			if(e.keyCode == 13 || e.keyCode == 32) {
				okCallback();
				box.modal('hide');
				return false;
			}
		}).click(okCallback).next().unbind().click(function(){
			box.modal('hide'); return false;
		});
		box.modal().css({width: 560});

		/* 加载公司列表 */
		$.ajax({
			url: '/develop/user/switch',
			dataType: 'json',
			success: function(data){
				if($.isArray(data)) {
					if(data[0] == 'normal') {
						var html = '';
						data = data[1];
						for(var i = 0, l = data[1].length; i < l; ++i) {
							html += '<option value="'+data[1][i].id+'">'+data[1][i].companyName+'</option>';
						}
						/* 切换事件 */
						$('#switch_company').html(html).val(data[0]);
					}else if(data[0] == 'error') {
						var errStr = '';
						for(var k in data[1].message) {
							errStr = data[1].message[k];
							break;
						}
						setTimeout(function(){
							if(data[1].code == 401) return location.reload();
							$.alert(data[1].code+': '+errStr, null, 400);
						}, 500);
					}
				}else{
					this.error();
				}
			},
			error: function() {
				setTimeout(function(){
					$.alert('{base.requestFailure}', null, 400);
				}, 500);
			}
		});
		$('#switch_company').change(function(){
			location.href = '/develop/user/switch?companyId='+$(this).val();
		});
	});

	/* 校验表单插件初始化 */
	if(typeof($.validator) == 'function') {
		/* 新增校验电话号码 */
		$.validator.addMethod("tel",function(value,element,param) {
			return /^\d{11}$/.test(value);
		});
	}

	$.auditReason = function(content) {
		var idList = [], matches, reasions = typeof(auditReason) == 'object' ? auditReason : {};
		if(matches = /^(\d+(?:\,\d+)*)\|([\s\S]*?)$/i.exec(content)) {
			idList = matches[1].split(',');
			content = [];
			for(var i = 0; i < idList.length; i++) {
				content.push(reasions[idList[i]]);
			}
			if($.trim(matches[2])) content.push($.trim(matches[2]));
			content = content.join('<br/>');
		}
		content = $.trim(content.replace(/^\|+|\|+$/g, ''));
		if(content.length == 0) content = '无';
		return content;
	}

	$.jsonEnCode = function(obj) {
		var t = typeof (obj);
		if (t != "object" || obj === null) {
			// simple data type
			if (t == "string") obj = '"'+obj+'"';
			return String(obj);
		}
		else {
			// recurse array or object
			var n, v, json = [], arr = (obj && obj.constructor == Array);
			for (n in obj) {
				v = obj[n]; t = typeof(v);
				if (t == "string") v = '"'+v+'"';
				else if (t == "object" && v !== null) v = JSON.stringify(v);
				json.push((arr ? "" : '"' + n + '":') + String(v));
			}
			return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
		}
	};

	$('span.auditReason').each(function(){
		$(this).html($.auditReason($(this).html()));
	});

	/* QA弹出层 */
    popoverNote();
    $('.icon-qa').popover();

	/* 下拉菜单 */
	$('ul.dropdown-menu:not([disabled])').each($.dropdownMenu = function(){
		var ul = $(this), target = ul.attr('dropMenuTarget');
		if(!target) return;
		var targetObject = $('#'+target),
			targetValue = targetObject.val();
		ul.find('a').click(function(e){
			if($(this).attr('disabled')) return $.stopBubble(e);
			/* 选中事件  */
			var object = $(this), value = object.attr('value');
			var ul = $(this).closest('ul');
			if(value == undefined) value = object.parent().attr('value');
			if(value == undefined) value = object.html().replace(/^\s*|\s*$/g, '');
			$('#'+ul.attr('dropMenuTarget')).val(value).trigger('change').blur();
			ul.prev('a').html(object.html() + '<span class="caret"></span>');
		});
		var hasValue = false;
		if(targetValue.length>0) {
			ul.find('a').each(function(){
				/* 默认选中状态 */
				var object = $(this), value = object.attr('value');
				if(value == undefined) value = object.parent().attr('value');
				if(value == undefined) value = object.html().replace(/^\s*|\s*$/g, '');
				if(value == targetValue) {
					hasValue = true;
					object.click();
					return false;
				}
			});
			if(!hasValue) ul.find('a:first').click();
		}else{
			ul.find('a:first').click();
		}
	});
	Highcharts.setOptions().colors = ['#3DA0EA', '#9FCC33', '#FFC800', '#FF8500', '#a888ff', '#F377AB', '#77B7C5', '#4EC9CE', '#29c7ff', '#9CEB9E', '#FFF263'];
	/* 扩展 */
	$.fn.extend({
		/* 统计报表 */
		reportChart:function() {
			var params = arguments[0];
			if(typeof(params) != 'object') params = {config:{}};
			if(typeof(params['config']) != 'object') params.config = {};
			if(typeof(params['varName']) != 'string') params.varName = 'chartData';
			if(params['pieIndex'] == null || params['pieIndex'] == undefined) params['pieIndex'] = 0;
			if(params['initHeight'] == null || params['initHeight'] == undefined) params['initHeight'] = 0;
			var chartData = window[params.varName];
			if(!chartData || !chartData['data'] || chartData['data'].length == 0) return;

			var type = 'line';

			if(params.config && params.config.chart && params.config.chart.type)
				type = params.config.chart.type;

			return this.each(function(){
				var currentObject = $(this), langBase = LANG.base;
				/* 初始化高度 */
				var initWidth = params['initWidth'] || $(this).attr('data-init-width'),
					initHeight = params['initHeight'] || $(this).attr('data-init-height');
				if(initHeight) $(this).css({height:initHeight});
				if(initWidth) $(this).css({width:initWidth});

				/* 初始化参数 */
				if(!$.isArray(chartData.dataTitle)) chartData.dataTitle = [];

				var line = currentObject.attr('line'), columnTitle = '';
				if(line == 'cost') {
					columnTitle = langBase.cost;
				}else if(line == 'impressions') {
					columnTitle = langBase.impressions;
				}else if(line == 'clicks') {
					columnTitle = langBase.clicks;
				}else{
					columnTitle = langBase.ctr;
				}

				var seriesList = [], seriesData, name,
					suffix = type == 'pie' || currentObject.attr('line') == 'ctr' ? '%' : '',
					colors = Highcharts.getOptions().colors;

				/* 生成seriesList数组 */
				if(type == 'pie') {
					var i = params.pieIndex, data = chartData.data[i], cate = chartData.categoriesList[i];
					name = chartData.dataTitle[i];
					var index = 0;
					/* 内圈 */
					var dataList = [], sum = 0, sumList = [], n = 0, n3 = 0, parentMap = {};
					for(var j = 0, l2 = chartData.parentCategories.length; j < l2; j++) {
						if(chartData.parentCategories[j] == null) continue;
						if(typeof(sumList[j]) == 'undefined') sumList[j] = [0, 0, 0, j, decodeURIComponent(chartData.parentCategories[j])];//sum, color, count，index, name
						var n2 = sumList[j][4];
						for(var k = 0, l3 = cate.length; k < l3; k++) {
							//console.log([chartData.categories[k],chartData.parentCategories[j],chartData.categories[k].indexOf(chartData.parentCategories[j], 0)]);
							if(decodeURIComponent(cate[k]).indexOf(n2, 0) == 0){
								parentMap[k] = j;
								sumList[j][0] += data[k];
								sumList[j][2]++;
							}
						}
						sum += sumList[j][0];
						++n3;
					}
					if(n3 > 0) {
						//重新排序父类
						sumList.sort(function(a,b){
							return a[0] > b[0] ? -1 : 1;
						});
						var data_tmp = [];
						var cate_tmp = [];
						var parentMap_tmp = [];
						var kk = 0;
						for(var j in sumList) {
							n = parseFloat((sumList[j][0]/sum*100).toFixed(2));
							if(isNaN(n)) n = 0;
							sumList[j][1] = colors[j];
							dataList.push({name: sumList[j][4]+': '+n+'%', y:n, color: sumList[j][1]});
							//重新排序子类
							for(var k in parentMap){
								if(parentMap[k] == sumList[j][3]){
									parentMap_tmp[kk++] = j;
									data_tmp.push(data[k]);
									cate_tmp.push(cate[k]);
								}
							}
						}
						data = data_tmp;
						cate = cate_tmp;
						parentMap = parentMap_tmp;
						seriesList.push({
							index:index++,
							name: (name ? name + " " : "" ) + columnTitle,
							type: 'pie',
							data: dataList,
							size: '60%',
							dataLabels: {
								formatter: function() {
									return this.y > 5 ? this.point.name : null;
								},
								color: 'white',
								distance: -50
							}
						});
					}

					/* 外圈 */
					var dataList = [], item = null, sum = 0;
					for(var j = 0, l2 = cate.length; j < l2; j++) {
						if(!$.isArray(data)) return;
						sum += data[j];
					}
					for(var j = 0, l2 = cate.length; j < l2; j++) {
						if(!$.isArray(data)) return;
						n = parseFloat((data[j]/sum*100).toFixed(2));
						item = {name:decodeURIComponent(cate[j]), y:n};
						if(sumList[parentMap[j]] != null){
							//高亮外圈
							var brightness = 0.2 - (j / sumList[parentMap[j]][2]) / 8 ;
							item.color = Highcharts.Color(sumList[parentMap[j]][1]).brighten(brightness).get();
						}
						dataList.push(item);
					}
					item = {
						index:index,
						name: decodeURIComponent(name ? name: columnTitle),
						type: 'pie',
						data: dataList
					};
					/* 外圈样式 */
					if(seriesList.length == 1) {
						item.size = '80%';
						item.innerSize = '60%';
						item.dataLabels = {
							formatter: function() {
								if(isNaN(this.y)) this.y = 0;
								return this.y > 1 ? '<b>'+ this.point.name +':</b> '+ this.y +'%'  : null;
							}
						};
					}
					seriesList.push(item);
				}else {
					for(var j = 0, l = chartData.data.length; j < l; ++j) {
						name = chartData.dataTitle[j];
						seriesList.push({index:j, name: decodeURIComponent(name ? name: columnTitle), data: chartData.data[j]});
					}
				}

				/* 间隔 */
				var categoriesLength = chartData.categories.length, tickInterval = 2;
				if(params.tickInterval != undefined) tickInterval = params.tickInterval;
				else if(categoriesLength < 5) tickInterval = 1;
				else if(categoriesLength > 15) tickInterval = 3;
				/* 初始化 */
				currentObject.removeClass('nodata').html('').highcharts($.extend({
					chart: {
						type: 'line',
						marginTop: 25,
						marginBottom: 20,
						marginRight: 0,
						zoomType: 'x'
					},
					zoomType: 'xy',
					reflow: true,
					title: { text: '', x: -20 },
					legend: chartData.data.length > 1 ? {
						layout: 'horizontal',
						align: 'center',
						verticalAlign: 'bottom',
						x: 0,
						y: 15,
						borderWidth: 1
					} : {enabled:false},
					exporting: {enabled:false},
					credits: {enabled:false},
					xAxis: {categories: chartData.categories, tickInterval: tickInterval},
					yAxis: {
						min: 0,
						title: {text: ''},
						labels: {
							formatter: function() {
								var value = $.fmoney(this.value.toString(), 2).replace(/\.0+$/, '');
								return (type != 'pie' && line == 'cost' ? '¥' : '') + value + suffix;
							},
							rotation: type == 'bar' ? 20 : 0
						},
						plotLines: [{ value: 0, width: 1, color: '#808080' }]
					},
					tooltip: {
						formatter: function() {
							var s = "", value = this.y ? $.fmoney(this.y.toString(), 0).replace(/\.0+$/, '') : '0';
							var title = '';
							if(type == 'pie') {
								title = this.key.indexOf(':');
								title = title == -1 ? this.key : this.key.substr(0, title);
							}else{
								title = isNaN(this.x) ? this.x : chartData.categoriesList[this.series.index][this.x];
							}
							if(suffix == '%' && parseFloat(value) < 0.01) value = 0;
							s = title + '<br/>'+ this.series.name +': ' + (type != 'pie' && line == 'cost' ? '¥' : '') + value + suffix;
							return s;
						},
						shared: false
					},
					series: seriesList
				}, params.config));
			});
		},

		/* 时间范围 */
		timeRange: function(params) {
			return this.each(function(){
				var control = $(this), langBase = LANG.base, ranges = {};

				if(typeof(params.init) != 'function') {
					params.init = function(){ return {}; }
				}

				ranges[langBase.today] = [moment(), moment()];
				ranges[langBase.yesterday] = [moment().subtract('days', 1), moment().subtract('days', 1)];
				ranges[langBase.last7days] = [moment().subtract('days', 6), moment()];
				ranges[langBase.last30days] = [moment().subtract('days', 29), moment()];
				ranges[langBase.currentMonth] = [moment().startOf('month'), moment().endOf('month')];
				ranges[langBase.previousMonth] = [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')];
				ranges[langBase.last1years] = [moment().subtract('year', 1), moment()];
				control.daterangepicker($.extend({
					startDate: moment().subtract('days', 29),
					endDate: moment(),
					showDropdowns: true,
					ranges: ranges,
					opens: 'left',
					buttonClasses: ['btn btn-default'],
					applyClass: 'btn-small btn-primary',
					cancelClass: 'btn-small btn-cancel',
					format: 'YYYY/MM/DD',
					separator: ' to ',
					locale: {
						applyLabel: langBase.submit,
						cancelLabel: langBase.cancel,
						daysOfWeek: [langBase.sundayShort, langBase.mondayShort, langBase.tuesdayShort, langBase.wednesdayShort, langBase.thursdayShort, langBase.fridayShort, langBase.saturdayShort],
						monthNames: [langBase.january, langBase.february, langBase.march, langBase.april, langBase.may, langBase.june, langBase.july, langBase.august, langBase.september, langBase.october, langBase.november, langBase.december],
						firstDay: 1
					}
				}, params.init.call(this, control)),
					params.complete
				);
			});
		},

		/* 表单ajax提交 */
		ajaxFormCall: function(params){
			$(this).submit(function(event){
				if(event.isDefaultPrevented() || event.isPropagationStopped()) return false;
                if (params['func'] && typeof params['func'] == 'function') params['func']();
				if(!$(this).valid()) return false;
				var button = $(this).find(':button');
				button.prop('disabled', true);
				var postUrl = params['url'] ? params['url'] : location.href;
				$(this).ajaxSubmit({
					url: postUrl,
					dataType: 'json',
					data:{ajax:true},
					success: function(data){
						if($.isArray(data)) {
							if(data[0] == 'normal') {
                                if (params['successFunc'] && typeof params['successFunc'] == 'function') {
                                    params['successFunc'](data[1]);
                                    return false;
                                }

								$.tips(params['tips']);
								if(params['href']){
									setTimeout(function(){location.href=params['href']}, 5000);
								}else{
									button.prop('disabled', false);
								}
							}else if(data[0] == 'error') {
								button.prop('disabled', false);
								var str = '', message = data[1];
								if(typeof(message) == 'string') message = {0:message};
								for(var k in message) {
									str = message[k][1] ? message[k][1] : message[k][0];
									break;
								}
								$.alert(str, null, 400);
							}
						}else{
							this.error();
						}
					},
					error: function() {
						button.prop('disabled', false);
						$.alert('{base.requestFailed}', null, 400);
					}
				});
				return false;
			});
		}
	});

	/* 修改指定网址的参数 */
	$.paramUrl = function(params) {
		var url = arguments[1] ? arguments[1] : location.href,
			pos = url.indexOf('?'),
			paramList = {};
		if(pos > -1) {
			var paramTmpList = url.substr(pos+1).split('&'),
				item = null;
			url = url.substr(0, pos);
			/* 没解析多层结构的参数 */
			for(var i = 0; i < paramTmpList.length; ++i) {
				pos = paramTmpList[i].indexOf('=');
				if(pos == -1) break;
				item = decodeURIComponent(paramTmpList[i].substr(pos+1));
				if(/^\d+$/i.test(item)) item = parseInt(item);
				else if(/^\d+\.\d+$/i.test(item)) item = parseFloat(item);
				paramList[decodeURIComponent(paramTmpList[i].substr(0, pos))] = item;
			}
		}
		for(var k in params) {
			paramList[k] = params[k];
		}
		url += '?' + $.param(paramList);
		return url;
	}

	/* json to string */
	var JSON = {};
	JSON.stringify = JSON.stringify || function (obj) {
		var t = typeof (obj);
		if (t != "object" || obj === null) {
			/* simple data type */
			if (t == "string") obj = '"'+obj+'"';
			return String(obj);
		}
		else {
			/* recurse array or object */
			var n, v, json = [], arr = (obj && obj.constructor == Array);
			for (n in obj) {
				v = obj[n]; t = typeof(v);
				if (t == "string") v = '"'+v+'"';
				else if (t == "object" && v !== null) v = JSON.stringify(v);
				json.push((arr ? "" : '"' + n + '":') + String(v));
			}
			return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
		}
	};

	$.copy2clipboard = function(txt) {
		if( typeof(clipboardData) == 'object' ) {
			clipboardData.clearData();
			clipboardData.setData('Text', txt);
		}else if( typeof(netscape) == 'object' ) {
			try{
				netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
			}catch(e){ alert(LANG.base.copyToClipboardFireFoxFailed); }
			var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
			if( typeof(clip) != 'object' ) return;
			var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
			trans.addDataFlavor('text/unicode');
			var strobj = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
			strobj.data = txt;
			trans.setTransferData( 'text/unicode', strobj, txt.length*2 );
			clip.setData(trans, null, Components.interfaces.nsIClipboard.kGlobalClipboard);
		}else{
			alert(LANG.base.copyToClipboardFailed);
			return false;
		}
		return true;
	}

	$('.fancybox').fancybox({
		'autoWidth':true,
		'autoResize':true
	});


	$.validator.addMethod("maxLine", function(value, element, param) {
		return this.optional(element) || $.trim(value).replace(/[\n\s]+/g, "\n").split("\n").length <= param;
	}, LANG.base.maxLine);
	$.validator.addMethod("stringCheck", function(value, element, param) {
		return this.optional(element) || (param && /^[a-z\u4e00-\u9fa5\s]+$/i.test(value));
	}, LANG.base.stringCheck);
	$.validator.addMethod("isZipCode", function(value, element) {
		return this.optional(element) || /^\d{6}$/.test(value);
	},  LANG.base.isZipCode);
	$.showAttachUrl = function(url) {
		var prefix = 'http://res.limei.com/',
				relationDir = '/upload/';
		if(typeof(url) == 'undefined' || (url = $.trim(url)).length == 0) return '';
		if(url.indexOf(prefix) == -1) url = relationDir + url;
		else url = url.replace(prefix, relationDir);
		return url;
	}
})(jQuery);

/**
 * pmp图表
 * @param data
 * data 参数范例
 * var data = {
		'bidRequest': [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
		'impressions': [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
		'clicks': [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
		'fillingr': [1016, 1016, 1015.9, 1015.5, 1012.3, 1009.5, 1009.6, 1010.2, 1013.1, 1016.9, 1018.2, 1016.7],
		'ctr': [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
		'categories': ['2014/02', '2014/03', '2014/04', '2014/05', '2014/06', '2014/07', '2014/08', '2014/09', '2014/10', '2014/11', '2014/12', '2015/01']
	}
 */
$.fn.chartPMP = function (data) {
    // 生成图表
    $(this).highcharts({
        chart: {
            zoomType: 'xy'
        },
        title: {
            text: ''
        },
        credits: {
            enabled: false
        },
        exporting: {
            enabled:false
        },
        xAxis: [{
            categories: data.categories
        }],
        yAxis: [{
            labels: {
                style: {
                    color: '#009DE0'
                }
            },
            title: {
                text: '请求数',
                style: {
                    color: '#009DE0'
                }
            }

        }, {
            title: {
                text: '展示数',
                style: {
                    color: '#89BD2C'
                }
            },
            labels: {
                style: {
                    color: '#89BD2C'
                }
            }

        }, {
            title: {
                text: '点击数',
                style: {
                    color: '#FCCC00'
                }
            },
            labels: {
                style: {
                    color: '#FCCC00'
                }
            }

        },{
            title: {
                text: '填充率',
                style: {
                    color: '#FE9A00'
                }
            },
            labels: {
                formatter: function() {
                    return this.value +' %';
                },
                style: {
                    color: '#FE9A00'
                }
            },
            opposite: true
        }, {
            title: {
                text: '点击率',
                style: {
                    color: '#CE3533'
                }
            },
            labels: {
                formatter: function() {
                    return this.value +' %';
                },
                style: {
                    color: '#CE3533'
                }
            },
            opposite: true
        }],
        tooltip: {
            shared: true
        },
        plotOptions: {
            column: {
                events:{
                    legendItemClick: function(event) {
                        if (event.currentTarget.name == event.target.chart.options.series[1].name) {
                            return false;
                        }
                        if (event.currentTarget.name == event.target.chart.options.series[0].name) {
                            if (event.currentTarget.visible) {
                                this.chart.series[3].hide();
                            } else {
                                this.chart.series[3].show();
                            }
                        }
                        if (event.currentTarget.name == event.target.chart.options.series[2].name) {
                            if (event.currentTarget.visible) {
                                this.chart.series[4].hide();
                            } else {
                                this.chart.series[4].show();
                            }
                        }
                    }
                }
            },
            line: {
                events:{
                    legendItemClick: function(event) {
                        return false;
                    }
                }
            }
        },
        series: [{
            name: '请求数',
            color: '#009DE0',
            type: 'column',
            yAxis: 0,
            data: data.bidRequest
        },{
            name: '展示数',
            color: '#89BD2C',
            type: 'column',
            yAxis: 1,
            data: data.impressions
        },{
            name: '点击数',
            color: '#FCCC00',
            type: 'column',
            yAxis: 2,
            data: data.clicks
        }, {
            name: '填充率',
            type: 'line',
            color: '#FE9A00',
            yAxis: 3,
            data: data.fillingr,
            tooltip: {
                valueSuffix: '%'
            }

        }, {
            name: '点击率',
            color: '#CE3533',
            type: 'line',
            yAxis: 4,
            data: data.ctr,
            tooltip: {
                valueSuffix: '%'
            }
        }]
    });
};

/**
 * 小弹框提示
 */
function popoverNote() {
    $('a.tooltip_qa').each(function(){
        var object = $(this);
        object.attr('data-content', object.next().html());
    });
    $('a.tooltip_qa').popover({'trigger':'hover','html':true, 'actualWidth':300});
}

/**
 * js数组去重
 * @param arr 需要去重的数组
 * @returns {Array}
 */
function arrayUnique(arr) {
    var result = [], hash = {};
    for (var i = 0, elem; (elem = arr[i]) != null; i++) {
        if (!hash[elem]) {
            result.push(elem);
            hash[elem] = true;
        }
    }
    return result;
}

/**
 * 是否存在指定函数
 * @param funcName 函数名称
 * @returns {boolean}
 */
function isExitsFunction(funcName) {
    try {
        if (typeof(eval(funcName)) == "function") {
            return true;
        }
    } catch(e) {}
    return false;
}
/**
 * 是否存在指定变量
 * @param variableName 变量名
 * @returns {boolean}
 */
function isExitsVariable(variableName) {
    try {
        if (typeof(variableName) == "undefined") {
            return false;
        } else {
            return true;
        }
    } catch(e) {}
    return false;
}

/**
 * 二维数组中子数组合并
 * @param arrs
 * @returns {*}
 */
function arrayIntersect(arrs){
    var arr = arrs.shift();
    for(var i=arrs.length;i--;){
        var p = {"boolean":{}, "number":{}, "string":{}}, obj = [];
        arr = arr.concat(arrs[i]).filter(function (x) {
            var t = typeof x;
            return !((t in p) ? !p[t][x] && (p[t][x] = 1) : obj.indexOf(x) < 0 && obj.push(x));
        });
        if(!arr.length) return null;//发现不符合马上退出
    }
    return arr;
}

/**
 * 类是于php in_array()函数
 * @param stringToSearch
 * @param arrayToSearch
 * @returns {boolean}
 */
function inArray(stringToSearch, arrayToSearch) {
    for (var s = 0; s < arrayToSearch.length; s++) {
        var thisEntry = arrayToSearch[s].toString();
        if (thisEntry == stringToSearch) {
            return true;
        }
    }
    return false;
}

/**
 * js复制
 * 按钮对象上需要添加属性 data-clipboard-target="text"
 * @param id　按钮对象
 */
function copyToClipboard(id){
    var obj = document.getElementById(id);
    var clip =  new ZeroClipboard(obj,{moviePath:'/assets/common/js/ZeroClipboard.swf'});
    clip.setHandCursor(true);
    clip.addEventListener('complete', function(client,text){
        var oldHtml = obj.innerHTML;
        obj.innerHTML = "复制成功";
        setTimeout(function() {
            obj.innerHTML = oldHtml;
        }, 1500);
    });
}

/**
 * ajax分页公用方法
 * @param listId 列表的id
 * @param url 请求的url
 * @param param 请求的参数
 */
function ajaxPage(listId, url, param) {
    var listObj = $("#" + listId);

    if (param == undefined || !param) {
        param = "pagenum=" + listObj.data("pagenum");
    }
    param = param.split("=");

    listObj.data(param[0], param[1]);

    $.ajaxCall(url, function(data){
        listObj.html(data.html);
    }, {data : listObj.data()});
}

/**
 * 集成sdk
 * @param adslotId 广告id
 */
function ajaxSdk(adslotId) {
    var url = "/develop/down/sdk/adslotId/" + adslotId;
    $.ajaxCall(url, function(res) {
        var box = $.modalBox(res.title, res.body, res.footer);
        box.on('hidden', function () {})
    });
}


