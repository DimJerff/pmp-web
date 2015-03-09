$(function(){
	/**************************** 初始化日期范围选择 begin ****************************/
	$('div.reportrange2').timeRange({
		init: function(control){
			var inputId = control.attr('data-name'),
				inputObject = $('#'+inputId),
				dateMatch, returnValue = {};
			if(dateMatch = /^(\d{4}\/\d{1,2}\/\d{1,2})-(\d{4}\/\d{1,2}\/\d{1,2})$/i.exec(inputObject.val())) {
				returnValue.startDate = dateMatch[1].replace(/\//g, '-');
				returnValue.endDate = dateMatch[2].replace(/\//g, '-');
				$('span.word',control).html(inputObject.val().replace(/\-/g, ' '+LANG.base.to+' '));
			}
			return returnValue;
		},
		complete: function(start, end) {
			var control = this.element,
				inputId = control.attr('data-name');
			$('#'+inputId).val(start.format('YYYY/MM/DD') + '-' + end.format('YYYY/MM/DD'));
			control.closest('form').submit();
		}
	});
	/**************************** 初始化日期范围选择 end ****************************/
	/**************************** 自定义报表范围(报废) begin ****************************/
	$('#rangeCustom').click(function(){
		var object = $(this), html = '',
			inputObject = $('#report_range'),
			dataList = window['creativeList'],
    		tmpList = [];

    	for(var k in dataList) {
    		tmpList.push(dataList[k]);
    	}
    	tmpList.sort(function(x,y){return x[2].localeCompare(y[2]);});

		/* 生成树 */
		html = (function(list, parentId){
			var html = '', childHtml = '', item, id;
    		for(var i = 0, l = list.length; i < l; ++i) {
    			item = list[i]; id = item[0];
    			if(item[1] != parentId) continue;
    			childHtml = arguments.callee(list, id);
				html += '<li id="choose_item_'+id+'" data-id="'+id+'" style="display: list-item;">';
				html += childHtml ? '<a class="arrow-btn"></a>' : '<a class="placeholder"></a>';
				html += '<input type="checkbox" value="'+id+'" data-parent="'+parentId+'" style="display:none" >';
				if(item[3]) {
					html += '<a href="" onclick="$(this).prev().prev().click();return false">'+item[2]+'</a>';
				}else{
					html += '<a href="" onclick="$(this).prev().each(function(){this.checked=!this.checked}).change();return false">'+item[2]+'</a>';
				}
				html += childHtml + '</li>';
			}
			if(!html) return '';
			else if(parentId == 0) return html;
			else return '<ul class="children" style="display:none">' + html + '</ul>';
		})(tmpList, 0);
	
		var body = '<div><ul id="chooseList" class="list-level2">'+html+'</ul></div>';
		var footer = '<a href="" onclick="return false" class="btn btn-primary">{base.confirm}</a><a href="" class="btn btn-cancel">{base.cancel}</a>';
		var box = $.modalBox('{base.chooseRange}', body, footer);
		/* 小title */
		box.find('div.modal-body')
			.before($.LANG('<div class="modal-sub-header clearfix"><div>{base.chooseCampaignAdgroupCreative}</div></div>'));

		/* 展开收起 */
		box.find('a.arrow-btn, a.placeholder').click(function(){
			var object = $(this);
			if(object.hasClass('opened')) {
				object.parent().find('a.arrow-btn').removeClass('opened');
				object.parent().find('ul.children').hide();
			}else{
				object.addClass('opened').parent().find('>ul.children').show();
			}
			/* 删除所有选中状态 */
			box.find('a.selected').removeClass('selected').css({fontWeight:'normal'});
			object.next().next().removeClass('selected').addClass('selected').css({fontWeight:'bold'});
		});
		/* 选择 */
		box.find(':checkbox').change(function(){
			var object = $(this),
				isChecked = object.prop('checked'),
				parentObject = object,
				tmpObject = null,
				isBreak = false, parentId = 0;
			
			/* 删除所有选中状态 */
			box.find('a.selected').removeClass('selected').css({fontWeight:'normal'});
			object.next().removeClass('selected').addClass('selected').css({fontWeight:'bold'});
		});
		
		/* 选中状态 */
		inputObject.each(function(){
			var idList = inputObject.val().replace(/(^\[|,)0(\]$|,)|[\[\]]/g, '').split(',');
			for(var i = 0; i < idList.length; ++i) {
				$('#choose_item_'+idList[i]+'>:checkbox').prop('checked', true).change().each(function(){
					/* 展开父级 */
					var parentId, object = $(this);
					while(true) {
						parentId = object.attr('data-parent');
						if(parentId == 0) break;
						$('#choose_item_'+parentId+'>a.arrow-btn').each(function(){
							if(!$(this).hasClass('opened')) $(this).click();
						});
						object = $('#choose_item_'+parentId+'>:checkbox');
					}
					/* 删除所有选中状态 */
					box.find('a.selected').removeClass('selected').css({fontWeight:'normal'});
					$(this).next().removeClass('selected').addClass('selected').css({fontWeight:'bold'});
				});
			}
		});
		
		var okCallback = function() {
			var object = box.find('a.selected').parent();
			if(object.length == 1) {
				/* 填写内容 */
				inputObject.val(object.attr('data-id')).change();
			}
			box.modal('hide');
			inputObject.closest('form').submit();
			return false;
		}
		box.unbind().focus(function(){
			box.find('div.modal-footer a:first').focus();
		}).find('div.modal-footer a:first').unbind().keypress(function(e){
			if(e.keyCode == 13 || e.keyCode == 32) {
				$(this).click();
				box.modal('hide');
				return false;
			}
		}).click(okCallback).next().unbind().click(function(){
			box.modal('hide');
			return false;
		});
		var boxWidth = 400;
		box.modal().css({width: boxWidth, marginLeft:-(boxWidth/2)});
		box.on('hide.bs.modal', function(){
			box.find('div.modal-sub-header').remove();
		});
		return false;
	}).siblings('li').click(function(){
		$('#report_range').val($(this).attr('value'));
		$(this).closest('form').submit();
	});
	/**************************** 初始化日期范围选择 end ****************************/
	/**************************** 生成选中信息(报废) begin ****************************/
	$('#report_range').change(function(){
		var match = /^(cac|ca|c)([1-9]\d*)$/.exec(this.value),
			selectedHtml = '';
		if(match) {
			var item = creativeList[match[0]], selectedHtml = '<span>'+item[2]+'</span>';
			while(item = creativeList[item[1]]) {
				selectedHtml = ' ' + item[2] + ' &gt; ' + selectedHtml;
			}
		}
		$('#selected_ad').html(selectedHtml);
	}).change();
	/**************************** 生成选中信息 end ****************************/
	/**************************** 打开比较(报废) begin ****************************/
	$('#compareSwitch').tzCheckbox({labels:['Enable','Disable']}).parent().click(function(){
		var object = $(this).find(':checkbox');
		if(object.prop('checked')) {
			buildCompareTimeBycompareType($($('#reportCompareTypeUl li.selected')[0]).attr('value'));
		}else{
			$('#report_compareTime').val('');
		}
		$(this).closest('form').submit();
	});
	/**************************** 打开比较 end ****************************/
	/**************************** 显示线(报废) begin ****************************/
	$('#lineColumn>div').click(function(){
		var object = $(this);
		$('#report_line').val(object.attr('value'));
		object.closest('form').submit();
	});
	/**************************** 显示线 end ****************************/
	/**************************** 环比同比(报废) begin ****************************/
	$('#reportCompareTypeUl li').click(function(){
		buildCompareTimeBycompareType($(this).attr('value'));
		$(this).closest('form').submit();
		return false;
	});
	/**************************** 环比同比 end ****************************/
	/**************************** 报表 begin ****************************/
	$('#report_container1,#report_container2,#report_container3,#report_container4').each(function(){
		$(this).reportChart({varName:$(this).attr('data-var-name')});
	});
	/**************************** 报表 end ****************************/
	/*************** 鼠标经过显示编辑图标 begin ******************/
    hoverIEditBtn();
	/*************** 鼠标经过显示编辑图标 end ******************/
	/*************** 启动和暂停按钮 begin ******************/
    startOrEndIBtn();
	/*************** 启动和暂停按钮 end ******************/
	/*************** 不显示消耗报表 begin ******************/
	$('#report_container3').each(function(){
		if($('#report_container1').length > 0) return;
		$(this).parent().insertAfter($('#report_container2').parent());
	});
	/*************** 不显示消耗报表 end ******************/
});

/* 根据比较类型和起始时间，生成比较时间 */
function buildCompareTimeBycompareType(value)
{
	var compareTime = $('#report_compareTime').val().split('-'),
	time = $('#report_time').val().split('-'),
	myDate = null;

	$('#report_compareType').val(value);
	
	/* 同比环比 */
	if(value == 1) {
		/* 同比 */
		myDate = new Date(time[0]);
		compareTime[0] = (myDate.getFullYear()-1)+'/'+(myDate.getMonth()+1)+'/'+myDate.getDate();
		myDate = new Date(time[1]);
		compareTime[1] = (myDate.getFullYear()-1)+'/'+(myDate.getMonth()+1)+'/'+myDate.getDate();
	}else{
		/* 环比 */
		var msec = (new Date(time[1])).valueOf() - (new Date(time[0])).valueOf();
		myDate = new Date((new Date(time[0])).valueOf()-msec-864000);
		compareTime[0] = myDate.getFullYear()+'/'+(myDate.getMonth()+1)+'/'+myDate.getDate();
		myDate = new Date((new Date(time[0])).valueOf()-864000);
		compareTime[1] = myDate.getFullYear()+'/'+(myDate.getMonth()+1)+'/'+myDate.getDate();
	}
	$('#report_compareTime').val(compareTime.join('-'));
	$('#compareDateSpan').html(compareTime.join(' '+LANG.base.to+' '));
}

/**
 * 启动和暂停按钮
 */
function startOrEndIBtn() {
    $('i.icon-stop, i.icon-on').closest('a').click(function(){
        var object = $(this);
        $.confirm('{'+object.attr('confirmLang')+'}', function(){
            location.href = object.attr('href');
        }, null, 200);
        return false;
    });
}

/**
 * 鼠标经过显示编辑图标
 */
function hoverIEditBtn() {
    $('i.icon-edit-custom,i.icon-eye-open').closest('td').hover(function(){
        $(this).find('i.icon-edit-custom,i.icon-eye-open').parent().css({visibility: 'visible'});
    }, function(){
        $(this).find('i.icon-edit-custom,i.icon-eye-open').parent().css({visibility: 'hidden'});
    });
}
