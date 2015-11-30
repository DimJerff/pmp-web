$(function(){
	$.validator.addMethod("register_idCard", function(value, element) {
		return $('#file_businessLicense').val() || value;
	});
	$.validator.addMethod("register_businessLicense", function(value, element) {
		return ($('#file_identityCard').val() && $('#file_identityCard2').val()) || value;
	});
	/***************************************** 用户登陆 begin *********************************************/
	/* 登录表单 */
	$('#login_form').each(function(){
		$(this).validate({
			rules:{
	  			'login[email]': { required:true },
	  			'login[passwd]':{ required:true }
	          },
	          messages:{
	          	'login[email]':{
	          		required: LANG.base.isEmpty
	          	},
	            'login[passwd]':{
	                required: LANG.base.passwdIsEmpty
	            }
	          },
	        errorPlacement: function(error, element) {  
	            error.insertAfter(element.parent().addClass('reg_error'));  
	        },
	        success:function(element){
	            $(element).text('').hide().prev().removeClass('reg_error');
	        }
		});
	});
	/***************************************** 用户登陆 end *********************************************/
	
	/***************************************** 忘记密码 begin *********************************************/
	/* 忘记密码 */
	$('#forgot_form').each(function(){
		$(this).validate({
			rules:{
	  			'forgot[email]': { required:true, email:true,
	                  remote:{
	                      url:"/develop/user/exists",
	                      dataType:"json",
	                      type:"get",
	                      data: {
	                      	email: function() {
	                      		return $("#forgot_email").val();
	                      	}
	                      },
	                      complete:function(data){
	                    	  this.success(data.responseText != 'true');
	                      }
	                  }
	  			},
	  			'forgot[telephone]':{ required:true, tel:true }
	          },
	          messages:{
	          	'forgot[email]':{
	          		required: LANG.base.isEmpty,
	          		email: LANG.base.emailFormatError,
	                remote: LANG.base.emailNotExists
	          	},
	            'forgot[telephone]':{
	                required: LANG.base.isEmpty,
	                tel: LANG.base.mobileFormatError
	            }
	          },
	        errorPlacement: function(error, element) {  
	            error.insertAfter(element.parent().addClass('reg_error'));  
	        },
	        success:function(element){
	            $(element).text('').hide().prev().removeClass('reg_error');
	        }
		});
	});
	
	/* 重置密码 */
	$('#forgotpasswd_form').submit(function(){
		var passwd = $.trim($('#forgot_passwd').val()),
		confirmPasswd = $.trim($('#forgot_confirmpasswd').val());
		
		if(passwd.length < 1) {
			$('#forgot_passwd').focus().next().html($.LANG('{base.passwdIsEmpty}!'));
			return false;
		}
		$('#forgot_passwd').next().html('');

		if(confirmPasswd.length < 1) {
			$('#forgot_confirmpasswd').focus().next().html($.LANG('{base.confirmPasswdIsEmpty}!'));
			return false;
		}
		if(confirmPasswd != passwd) {
			$('#forgot_confirmpasswd').focus().next().html($.LANG('{base.passwdIsDiff}!'));
			return false;
		}
		$('#forgot_confirmpasswd').next().html('');
		return true;
	});
	/***************************************** 忘记密码 end *********************************************/
	
	/***************************************** 公司注册 begin *********************************************/
	/* 选择默认时区 */
	$('#register_timezone').each(function(){
		/* 设置时区默认选中 */
		var currentTimezone = (new Date().getTimezoneOffset()/-60);
		var selector = currentTimezone == 8 ? ':contains("Asia/Shanghai")' : '[offset='+currentTimezone+']';
		$('#register_timezone option'+selector+':first').attr('selected', true);
	});

	/* 登陆弹出层 */
	$('#login_box').click(function(){
		var body = '<form id="loginbox_form" class="form-horizontal" method="post" action="">' +
				'<div class="control-group">' +
			    '	<label class="control-label" for="loginbox_username">{base.userName}</label>' +
				'    <div class="controls">' +
				'    	<input type="text" id="loginbox_username" name="loginbox[email]" placeholder="{base.userName}">' +
				'    </div>' +
			    '</div>' +
				'<div class="control-group">' +
			    '	<label class="control-label" for="loginbox_passwd">{base.password}</label>' +
				'    <div class="controls">' +
				'    	<input type="password" id="loginbox_passwd" name="loginbox[passwd]"placeholder="{base.password}">' +
				'    </div>' +
			    '</div>' +
				'</form>';
		var footer = '<a href="javascript:;" class="btn btn-primary">{base.login}</a>' +
				'<a href="" onclick="return false" class="btn btn-cancel">{base.cancel}</a>';
		var box = $.modalBox('{base.login}', body, footer);
		var okCallback = function(){box.find('form').submit();}
		var submitBtn = box.find('a.btn btn-primary');
		submitBtn.click(function(){
			$('#loginbox_form').submit();
			return false;
		});
		/* 登陆弹出层提交事件 */
		$('#loginbox_form').submit(function(){
			var username = $.trim($('#loginbox_username').val()),
				passwd = $.trim($('#loginbox_passwd').val());
				submitBtn.addClass('disabled');
			
			if(username.length < 1) {
				$('#loginbox_username').focus();
				submitBtn.removeClass('disabled');
				return false;
			}
			if(passwd.length < 1) {
				$('#loginbox_passwd').focus();
				submitBtn.removeClass('disabled');
				return false;
			}
			$.ajaxCall('/develop/user/loginapi', function(data){
				$('#register_email').val(data.email);
				$('#register_firstname').val(data.firstname);
				$('#register_lastname').val(data.lastname);
				$('#register_telephone').val(data.telephone);
				$('#register_passwd').val('abc123456789');
				$('#register_confirmPassword').val('abc123456789');
				$('#register_userId').val(data.id);
				box.modal('hide');
			}, {
				type:'POST',
				data: {loginbox:{email:username, passwd:passwd}},
				complete: function(){
					submitBtn.removeClass('disabled');
				}
			});
			return false;
		});
		
		box.unbind().focus(function(){ $('#loginbox_username').focus(); })
			.find('div.modal-footer a:first').unbind()
			.keypress(function(e){
				if(e.keyCode == 13 || e.keyCode == 32) {
					okCallback();
					box.modal('hide');
					return false;
				}
			})
			.click(okCallback).next().unbind()
			.click(function(){
				box.modal('hide');
				return false;
			});
		box.modal().css({width: 560});
		return false;
	});

	/* 校验注册表单 */
	$('#register_form').each(function(){
		$(this).validate({
	  		rules:{
	  			'register[companyName]': { required:true, rangelength:[2,30] },
	  			'register[currency]':{ required:true },
	  			'register[timezone]':{ required:true },
	  			'register[website]':{ required:true, url: true },
	  			'register[businessLicense]':{ register_businessLicense:true },
	  			'register[identityCard]':{ register_idCard:true },
	  			'register[identityCard2]':{ register_idCard:true },
	  			'register[email]':{
	                  required:true,
	                  email:true,
	                  remote:{
	                      url:"/develop/user/exists",
	                      dataType:"json",
	                      type:"get",
	                      data: {
	                      	email: function() {
	                      		return $("#register_email").val();
	                      	},
	                      	userId: function(){
	                      		return $('#register_userId').val();
	                      	}
	                      }
	                  }
	              },
	            'register[firstname]': { required:true, stringCheck:true, rangelength:[1,30] },
	            'register[lastname]': { required:true, stringCheck:true, rangelength:[1,30] },
	            'register[telephone]':{
	                  required:true,
	                  tel:true
	            },
	            'register[passwd]': {
					required:true,
					rangelength: [6,16],
					char_num:true
	              },
	            'register[confirmPassword]': {
					required:true,
					rangelength: [6,16],
					char_num:true,
					equalTo: "#register_passwd"
	              }
	          },
	          messages:{
	          	'register[companyName]':{
	          		required: LANG.base.isEmpty,
					rangelength: LANG.base.rangeLength
	          	},
	              'register[currency]':{
	                  required: LANG.base.currencyIsEmpty
	              },
	              'register[timezone]':{
	                  required: LANG.base.timezoneIsEmpty
	              },
	              'register[website]':{
	                  required: LANG.base.websiteIsEmpty,
	                  url: LANG.base.websiteFormatError
	              },
	              'register[businessLicense]':{
	            	  register_businessLicense: LANG.base.businessLicenseIsEmpty
	              },
	              'register[identityCard]':{
	            	  register_idCard: LANG.base.identityCardIsEmpty
	              },
	              'register[identityCard2]':{
	            	  register_idCard: LANG.base.identityCard2IsEmpty
	              },
	              'register[email]':{
	                  required: LANG.base.isEmpty,
	                  email: LANG.base.emailFormatError,
	                  remote: LANG.base.emailisExists
	              },
	              'register[telephone]':{
	                  required: LANG.base.telephoneIsEmpty,
	                  tel: LANG.base.mobileFormatError
	              },
	              'register[firstname]':{
	                  required: LANG.base.firstNameIsEmpty,
	                  rangelength: LANG.base.rangeLength
	              },
	              'register[lastname]':{
	                  required: LANG.base.lastNameIsEmpty,
	                  rangelength: LANG.base.rangeLength
	              },
	              'register[passwd]':{
	                  required:LANG.base.passwdIsEmpty,
	                  rangelength: LANG.base.rangeLength,
	                  char_num: LANG.base.passwdFormatError
	              },
	              'register[confirmPassword]':{
	                  required:LANG.base.confirmPasswdIsEmpty,
	                  equalTo: LANG.base.passwdIsDiff,
	                  rangelength: LANG.base.rangeLength,
	                  char_num: LANG.base.passwdFormatError
	              }
	          },
	        errorPlacement: function(error, element) {  
	            error.insertAfter(element.parent().addClass('reg_error'));  
	        },
	        success:function(element){
	            $(element).text('').hide().prev().removeClass('reg_error');
	        }
		});
	});
	
	/* 校验注册表单 */
	$('#invite_form').each(function(){
		$(this).validate({
	  		rules:{
	  			'invite[email]':{
	                  required:true,
	                  email:true,
	                  remote:{
	                      url:"/develop/user/exists",
	                      dataType:"json",
	                      type:"get",
	                      data: {
	                      	email: function() {
	                      		return $("#invite_email").val();
	                      	}
	                      }
	                  }
	             },
	            'invite[firstname]': { required:true, stringCheck:true, rangelength:[1,30] },
	            'invite[lastname]': { required:true, stringCheck:true, rangelength:[1,30] },
	            'invite[telephone]':{
	                  required:true,
	                  tel:true
	            },
	            'invite[passwd]': {
					required:true,
					rangelength: [6,16],
					char_num:true
	              },
	            'invite[confirmPassword]': {
					required:true,
					rangelength: [6,16],
					char_num:true,
					equalTo: "#invite_passwd"
	              }
	          },
	          messages:{
	              'invite[email]':{
	                  required: LANG.base.isEmpty,
	                  email: LANG.base.emailFormatError,
	                  remote: LANG.base.emailisExists
	              },
	              'invite[telephone]':{
	                  required: LANG.base.telephoneIsEmpty,
	                  tel: LANG.base.mobileFormatError
	              },
	              'invite[firstname]':{
	                  required: LANG.base.firstNameIsEmpty,
	                  rangelength: LANG.base.rangeLength
	              },
	              'invite[lastname]':{
	                  required: LANG.base.lastNameIsEmpty,
	                  rangelength: LANG.base.rangeLength
	              },
	              'invite[passwd]':{
	                  required:LANG.base.passwdIsEmpty,
	                  rangelength:LANG.base.rangeLength,
	                  char_num: LANG.base.passwdFormatError
	              },
	              'invite[confirmPassword]':{
	                  required:LANG.base.confirmPasswdIsEmpty,
	                  equalTo: LANG.base.passwdIsDiff,
	                  rangelength:LANG.base.rangeLength,
	                  char_num: LANG.base.passwdFormatError
	              }
	          },
	        highlight:function(element){
	            $(element).closest('.control-group').removeClass('success').addClass('error');
	        },
	        success:function(element){
	            $(element).text('').addClass('valid').closest(".control-group").removeClass('error');
	        }
		});
	});

	/* 文件上传 */
	$('#businessLicense').each(function(){
		$(this).fileupload({
			url: "/site/upload?type=businessLicense",
	        dataType: 'json',
	        maxFileSize: 10485760,
	        previewMaxWidth: 100,
	        previewMaxHeight: 100,
	        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
	        add:function(e,data) {
	            data.submit().success(function (result, textStatus, jqXHR) {
	                if($.isArray(result) && result[0] == 'normal'){
	                	result = result[1];
	                    $("#businessLicense_img").attr('src', $.showAttachUrl(result.thumbUrl)).show();
	                    $("#file_businessLicense").val(result.url);
	                } else if ($.isArray(result) && result[0] == 'error') {
                        if(result[1].message[0] < 100){
                            $.tipsError(result[1].message[1]);
                        }else{
                            $.tipsError(result[1].message[1]+'：'+result[1].message[2]);
                        }
                    } else{
                        $.tipsError('上传失败，请稍后再试！');
                    }
	            })
	            .error(function (jqXHR, textStatus, errorThrown) {
                    $.tipsError('上传失败，请稍后再试！');
                })
	            .complete(function (result, textStatus, jqXHR) {});
	        },
	        done: function(e, result) {}
		});
    });
	/* 身份证正面 */
	$('#identityCard').each(function(){
		$(this).fileupload({
			url: "/site/upload?type=identityCard",
	        dataType: 'json',
	        maxFileSize: 10485760,
	        previewMaxWidth: 100,
	        previewMaxHeight: 100,
	        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
	        add:function(e,data) {
	            data.submit().success(function (result, textStatus, jqXHR) {
	                if($.isArray(result) && result[0] == 'normal'){
	                	result = result[1];
	                	$("#identityCard_img").attr('src', $.showAttachUrl(result.thumbUrl)).show();
	                    $("#file_identityCard").val(result.url);
	                } else if ($.isArray(result) && result[0] == 'error') {
                        if(result[1].message[0] < 100){
                            $.tipsError(result[1].message[1]);
                        }else{
                            $.tipsError(result[1].message[1]+'：'+result[1].message[2]);
                        }
                    } else{
                        $.tipsError('上传失败，请稍后再试！');
                    }
	            })
	            .error(function (jqXHR, textStatus, errorThrown) {
                        $.tipsError('上传失败，请稍后再试！');
                    })
	            .complete(function (result, textStatus, jqXHR) {});
	        },
	        done: function(e, result) {}
		});
    });
	/* 身份证反面 */
	$('#identityCard2').each(function(){
		$(this).fileupload({
			url: "/site/upload?type=identityCard&file=identityCard2",
	        dataType: 'json',
	        maxFileSize: 10485760,
	        previewMaxWidth: 100,
	        previewMaxHeight: 100,
	        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
	        add:function(e,data) {
	            data.submit().success(function (result, textStatus, jqXHR) {
	                if($.isArray(result) && result[0] == 'normal'){
	                	result = result[1];
	                	$("#identityCard2_img").attr('src', $.showAttachUrl(result.thumbUrl)).show();
	                    $("#file_identityCard2").val(result.url);
	                } else if ($.isArray(result) && result[0] == 'error') {
                        if(result[1].message[0] < 100){
                            $.tipsError(result[1].message[1]);
                        }else{
                            $.tipsError(result[1].message[1]+'：'+result[1].message[2]);
                        }
                    } else{
                        $.tipsError('上传失败，请稍后再试！');
                    }
	            })
	            .error(function (jqXHR, textStatus, errorThrown) {
                        $.tipsError('上传失败，请稍后再试！');
                    })
	            .complete(function (result, textStatus, jqXHR) {});
	        },
	        done: function(e, result) {}
		});
    });
	/***************************************** 公司注册 end *********************************************/
	
	/***************************************** 修改密码 begin *********************************************/
	/* 修改密码表单校验 */
	$('#change_form').each(function(){
		$(this).validate({
	  		rules:{
	  			'change[passwd]': {
					required:true
	              },
	  			'change[newPasswd]': {
					required:true,
					rangelength: [6,16],
					char_num:true
	              },
	  			'change[confirmPasswd]': {
					required:true,
					rangelength: [6,16],
					char_num:true,
					equalTo: "#change_newPasswd"
	              }
	          },
	          messages:{
	        	  'change[passwd]':{
	                  required:LANG.base.passwdIsEmpty,
	                  rangelength:LANG.base.rangeLength,
	                  char_num: LANG.base.passwdFormatError
	              },
	              'change[newPasswd]':{
	                  required:LANG.base.passwdIsEmpty,
	                  rangelength:LANG.base.rangeLength,
	                  char_num: LANG.base.passwdFormatError
	              },
	              'change[confirmPasswd]':{
	                  required:LANG.base.confirmPasswdIsEmpty,
	                  equalTo: LANG.base.passwdIsDiff,
	                  rangelength:LANG.base.rangeLength,
	                  char_num: LANG.base.passwdFormatError
	              }
	          },
	        highlight:function(element){
	            $(element).closest('.control-group').removeClass('success').addClass('error');
	        },
	        success:function(element){
	            $(element).text('').addClass('valid').closest(".control-group").removeClass('error');
	        }
		});
	});
	/***************************************** 修改密码 end *********************************************/
	
	/***************************************** 基本信息 begin *********************************************/
	/* 修改密码表单校验 */
	$('#edit_form').each(function(){
		$(this).validate({
	  		rules:{
	  			'edit[firstname]': {required:true, stringCheck:true, rangelength:[1,30]},
	  			'edit[lastname]': {required:true, stringCheck:true, rangelength:[1,30]},
	  			'edit[telephone]': {
	  				required:true,
	  				tel:true
	  			},
	  			'edit[language]': {required:true}
	          },
	          messages:{
				'edit[firstname]': {
					required: LANG.base.firstNameIsEmpty,
	                  rangelength: LANG.base.rangeLength
				},
				'edit[lastname]': {
					required: LANG.base.lastNameIsEmpty,
	                  rangelength: LANG.base.rangeLength
	            },
				'edit[telephone]': {
					required: LANG.base.telephoneIsEmpty,
	                tel: LANG.base.mobileFormatError
				},
				'edit[language]': {required: LANG.base.languageIsEmpty}
	          },
	        highlight:function(element){
	            $(element).closest('.control-group').removeClass('success').addClass('error');
	        },
	        success:function(element){
	            $(element).text('').addClass('valid').closest(".control-group").removeClass('error');
	        }
		});
	});
	/***************************************** 基本信息 end *********************************************/
	/***************************************** 公司信息表单校验 begin *********************************************/
	/* 校验注册表单 */
	$('#company_form').each(function(){
		$(this).validate({
	  		rules:{
	  			'edit[companyName]': { required:true, rangelength:[2,30] },
	  			'edit[website]':{ required:true, url: true }
	          },
	          messages:{
				'edit[companyName]':{
					required: LANG.base.isEmpty,
					rangelength: LANG.base.rangeLength
				},
				'edit[website]':{
					required: LANG.base.websiteIsEmpty,
				    url: LANG.base.websiteFormatError
				}
	          },
	        highlight:function(element){
	            $(element).closest('.control-group').removeClass('success').addClass('error');
	        },
	        success:function(element){
	            $(element).text('').addClass('valid').closest(".control-group").removeClass('error');
	        }
		});
	});
	/***************************************** 公司信息表单校验 end *********************************************/
	/********************** 城市联动 begin *********************************/
	$('[data-type=countryCode]').change(function(){
		var cityObj = $(this), url = '/develop/billing/city?parentId='+cityObj.val();
		if($(this).data('ajax') != null) $(this).data('ajax').abort();
		$(this).data('ajax',$.ajaxCall(url,
			function(data){
				var html = '',
					cityObj2 = cityObj.siblings('select'),
					currentId = cityObj2.attr('currentId');
				cityObj2.attr('currentId', 0);
				/* 没同级，显示上级 */
				if(data.length == 0) {
					html += '<option value="'+cityObj.val()+'">'+cityObj.find('option:selected').html()+'</option>';
				}else{
					for(var i = 0, l = data.length; i < l; ++i) {
						html += '<option value="'+data[i].id+'"'+(currentId == data[i].id ? ' selected="selected"' : '')+'>'+data[i].zhName+'</option>';
					}
				}
				cityObj2.html(html);
			}
		));
	}).change();
	$('#invoiceCityCode').change(function(){$('#invoiceAddress').focus();});
	$('#companyCityCode').change(function(){$('#companyAddress').focus();});
	/********************** 城市联动 end *********************************/
	/********************** 修改发票类型 begin ******************************/
	$('#radio_invoice_type :radio').change(function () {
		var selector = '[data-invoiceType={id}]',
			regexp = /\{id\}/ig;
		$(selector.replace(regexp, this.value)).show();
		$(this).parent().siblings().find(':radio').each(function () {
			$(selector.replace(regexp, this.value)).hide();
		});
	}).each(function () {
		var val = $('#radio_invoice_type').attr('value');
		if (this.value == val) $(this).prop('checked', true).change();
	});
	/********************** 修改发票类型 end ******************************/
	/********************** 发票表单校验 begin *********************************/
	$('#invoice_form').each(function(){
		$(this).validate({
	  		rules:{
	  			'invoice[invoiceName]':{ required:true, rangelength:[2, 30] },
				'invoice[companyAddress]': {required:true, rangelength:[5, 120]},
				'invoice[taxpayerId]':{ required:{depends: function () {
					return $('#radio_invoice_type :checked').val() == '1';
				}}, rangelength:[5,32] },
				'invoice[companyTel]':{ required:{depends: function () {
					return $('#radio_invoice_type :checked').val() == '1';
				}}, tel:true },
				'invoice[companyBank]':{ required:{depends: function () {
					return $('#radio_invoice_type :checked').val() == '1';
				}}, rangelength:[5,50] },
				'invoice[companyAccount]':{ required:{depends: function () {
					return $('#radio_invoice_type :checked').val() == '1';
				}}, rangelength:[5,50] },
	  			'invoice[invoiceAddress]': {required:true, rangelength:[5, 120]},
	  			'invoice[invoicePostalCode]':{isZipCode:true},
	  			'invoice[invoiceContact]':{required:true, rangelength:[1, 30], stringCheck:true},
	  			'invoice[invoiceContactTel]':{
		                required:true,
		                tel:true
		          }
			},
			messages:{
				'invoice[invoiceName]':{ required:LANG.base.isEmpty, rangelength:LANG.base.rangeLength },
				'invoice[companyAddress]':{ required:LANG.base.isEmpty, rangelength:LANG.base.rangeLength },
				'invoice[taxpayerId]':{ required:LANG.base.isEmpty, rangelength:LANG.base.rangeLength },
				'invoice[companyTel]':{ required:LANG.base.isEmpty, tel: LANG.base.telephoneError },
				'invoice[companyBank]':{ required:LANG.base.isEmpty, rangelength:LANG.base.rangeLength },
				'invoice[companyAccount]':{ required:LANG.base.isEmpty, rangelength:LANG.base.rangeLength},
				'invoice[invoiceAddress]':{ required:LANG.base.isEmpty, rangelength:LANG.base.rangeLength },
				'invoice[invoicePostalCode]':{ isZipCode:LANG.base.ZipCodeFormatError },
				'invoice[invoiceContact]':{ required:LANG.base.isEmpty, rangelength:LANG.base.rangeLength },
				'invoice[invoiceContactTel]':{
					required: LANG.base.telephoneIsEmpty,
					tel: LANG.base.telephoneError
				}
			},
	        highlight:function(element){
	            $(element).closest('.control-group').removeClass('success').addClass('error');
	        },
	        success:function(element){
	            $(element).text('').addClass('valid').closest(".control-group").removeClass('error');
	        }
		});
	});
	/********************** 发票表单校验 end *********************************/
	/********************** 发票表单校验 begin *********************************/
	$('#message_form').each(function(){
		$(this).validate({
	  		rules:{
	  			'message[setting_num][2]':{
	  				required:{depends: function (o) {
	  					return $(o).prev().find(':checkbox').prop('checked');
	  				}},
					min:10, digits:true
				}
	          },
	          messages:{
					'message[setting_num][2]':{
						required:LANG.base.isEmpty,
						min:LANG.base.min,
						digits:LANG.base.digits
					}
	          },
	        highlight:function(element){
	            $(element).closest('.control-group').removeClass('success').addClass('error');
	        },
	        success:function(element){
	            $(element).text('').addClass('valid').closest(".control-group").removeClass('error');
	        }
		});
	});
	/********************** 发票表单校验 end *********************************/
	/********************** 发送邀请表单校验 begin *********************************/
	$('#invite_send_form').each(function(){
		$(this).validate({
	  		rules:{
	  			'invite[email]':{ required:true, email:true }
	          },
	          messages:{
					'invite[email]':{
						required: LANG.base.isEmpty,
		                email: LANG.base.emailFormatError
	  	          }
	          },
	        highlight:function(element){
	            $(element).closest('.control-group').removeClass('success').addClass('error');
	        },
	        success:function(element){
	            $(element).text('').addClass('valid').closest(".control-group").removeClass('error');
	        }
		});
	});
	/********************** 发票表单校验 end *********************************/
	/********************** 域名绑定 begin *********************************/
	/* 登录logo */
	$('#logoFile1').each(function(){
		$(this).fileupload({
			url: "/develop/user/upload?type=companyDomainLogo1",
	        dataType: 'json',
	        maxFileSize: 1024*1024*10,
	        previewMaxWidth: 134,
	        previewMaxHeight: 41,
	        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
	        add:function(e,data) {
	            data.submit().success(function (data, textStatus, jqXHR) {
	                if($.isArray(data)){
	                	result = data[1];
	                	if(data[0] == 'normal') {
		                    $("#logoFile1_img").attr('src', $.showAttachUrl(result.urlPath)).show();
		                    $("#file_logoFile1").val(result.urlPath);
	                	}else{
	                		for(var k in result.message) {
	                			for(var i = 0; i < result.message[k].length; i++) {
	                				$.alert(result.message[k][i], null, 400);
	                				break;
		                		}
	                			break;
	                		}
	                	}
	                }
	            }) .error(function (jqXHR, textStatus, errorThrown) {
	            	
	            }).complete(function (result, textStatus, jqXHR) {
	            	
	            });
	        },
	        done: function(e, result) {}
		});
    });
	/* 站内logo */
	$('#logoFile2').each(function(){
		$(this).fileupload({
			url: "/develop/user/upload?type=companyDomainLogo2",
	        dataType: 'json',
	        maxFileSize: 1024*1024*10,
	        previewMaxWidth: 162,
	        previewMaxHeight: 38,
	        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
	        add:function(e,data) {
	            data.submit().success(function (data, textStatus, jqXHR) {
	            	 if($.isArray(data)){
		                	result = data[1];
		                	if(data[0] == 'normal') {
			                    $("#logoFile2_img").attr('src', $.showAttachUrl(result.urlPath)).show();
			                    $("#file_logoFile2").val(result.urlPath);
		                	}else{
		                		for(var k in result.message) {
		                			for(var i = 0; i < result.message[k].length; i++) {
		                				$.alert(result.message[k][i], null, 400);
		                				break;
			                		}
		                			break;
		                		}
		                	}
		                }
	            })
	            .error(function (jqXHR, textStatus, errorThrown) {})
	            .complete(function (result, textStatus, jqXHR) {});
	        },
	        done: function(e, result) {}
		});
    });
	/********************** 域名绑定 end *********************************/
	/********************** 域名绑定 begin *********************************/
	$('#company_domain_form').each(function(){
		$(this).validate({
	  		rules:{
	  			'edit[domain]':{
	  				required:true,
	  				pattern:/^\s*([a-z\d\-]+\.)+[a-z]{2,5}(?!\.limei\.com)\s*$/i,
	  				remote:{
	  	                 url:"/develop/user/domainExists",
	  	                 cache:false,
	  	                 data:{
	  	                      domain:function(){return $("#edit_domain").val();}
	  	                 },
	  	                 dataFilter: function(data, type) {
	  	                	data = $.parseJSON(data);
	  	                     return $.isArray(data) && data[1] == false;
	  	                 }
	  	              }
	  			},
	  			'edit[title]':{ required:true },
	  			'edit[platformName]':{ required:true },
	  			'edit[copyright]':{ required:true }
	          },
	        messages: {
	        	'edit[domain]': {pattern: LANG.base.invalidDomain, remote:LANG.base.repeatDomain}
	        },
	        highlight:function(element){
	            $(element).closest('.control-group').removeClass('success').addClass('error');
	        },
	        success:function(element){
	            $(element).text('').addClass('valid').closest(".control-group").removeClass('error');
	        }
		});
	});
	/********************** 域名绑定 end *********************************/
	
});