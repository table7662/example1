function notif(body='',type='error',sec=5000){
	toastr.options = {
	  "closeButton": true,
	  "debug": false,
	  "progressBar": false,
	  "positionClass": "toast-top-right",
	  "onclick": null,
	  "showDuration": "400",
	  "hideDuration": "400",
	  "timeOut": sec,
	  "extendedTimeOut": "2000",
	  "showEasing": "swing",
	  "hideEasing": "linear",
	  "showMethod": "fadeIn",
	  "hideMethod": "fadeOut"
	}
	var $toast = toastr[type](body,'');
}

function isset() {
    var a=arguments, l=a.length, i=0;
    
    if (l===0) {
        throw new Error('Empty isset'); 
    }
    
    while (i!==l) {
        if (typeof(a[i])=='undefined' || a[i]===null) { 
            return false; 
        } else { 
            i++; 
        }
    }
    return true;
}


function search_location_input(p,v){
	clearInterval(window.search_location_input_timer);
	var t = 0;
	var obj = $('#search_location_list');
	var input = $('#search_location_input');
	input.removeClass('input_error');
	if (p=='with_delay') t = 100;
	window.search_location_input_timer = setTimeout(function(){
		if (v == '') v = input.val();
		$.get('/ajax/func.php?type=get_locations&q='+encodeURIComponent(v),function(data){
			obj.html(data).show();
			if (data == ''){
				input.addClass('input_error');
				obj.hide();
			}
		});
	},t);
}

function search_location_input_save(){
	$('[data-modal="search_location"] .modal-close').click();
	var obj = $('#search_location');
	var li = $('#search_location_list li.active');
	if (li.length <= 0) return;
	obj.find('option').remove();
	obj.append('<option value="'+li.attr('data-location_url')+'" selected>'+li.attr('data-location')+'</option>');
	search();
}

function search(){
	var obj = $('#search_btn');
	obj.addClass('disabled');
	var l=$('#search_location').val();
	var c=$('#search_category').val();
	if (l=='') l ='';
	if (c=='') c ='';
	if (l != '' && c != '') c='/'+c;
	document.location.href='/'+l+c;
}

function top_menu_collapse(){
	var obj = $('#top_navbar');
	obj.toggleClass('in');
}

function add_location1_change(p){
	if (p == 0) return false;
	$('#add_location2').prop('disabled',true);
	$.get('/ajax/func.php?type=add_location1&location_id='+p,function(data){
		$('#add_location2').html(data).prop('disabled',false);
		$('#add_location2').parent().parent().removeClass('has-error');
	});
}

function photos_upload(e,obj){
	e.preventDefault();		
	$('#add_new_btn').addClass('disabled');
	var span=obj.find('span');
	var span_html = span.html();
	var form_data = new FormData(obj[0]);

	obj[0].reset();
	obj.find('i').hide();
	obj.find('.btn').removeClass('btn-success').addClass('disabled').addClass('btn-default');
	span.html('0%');

	$.ajax({
	type: 'POST',
	url: '/ajax/func.php?type=add_photos_upload',
	data: form_data,
	contentType: false,
	processData: false,
	xhr: function() {
		var myXhr = $.ajaxSettings.xhr();
		if(myXhr.upload){
			myXhr.upload.addEventListener('progress',function(e){
			if(e.lengthComputable){
				var max = e.total;
				var current = e.loaded;
				var Percentage = parseInt((current * 100)/max);
				span.html(Percentage+'%');
				if (Percentage == 100) span.html('Обработка фото...');
			}
			});
		}
		return myXhr;
	},
	success: function(data){
		var data=$.parseJSON(data);
		if (data.result == 'error'){
			if (data.msg) notif(data.msg,'error');
			else notif('Ошибка!','error');
		}else if(data.result == 'ok'){
			var s = '';
			$.each(data.photos,function(key,val){
				s+='<div class="add_img" data-add-new-img="'+key+'"><div class="img_remove"><i class="fa fa-close"></i></div><img class="img" src="'+val+'"></div>';
			});
			$('#add_photos_div').append(s);
		}
	},
	complete: function(){
		obj.find('i').show();
		obj.find('.btn').addClass('btn-success').removeClass('disabled').removeClass('btn-default');
		span.html(span_html);
		$('#add_new_btn').removeClass('disabled');
	},
	error: function(e,ee){
		notif('Проверьте интернет-соединение!','error');
	}
	});
}

function add_new(obj){
	if (obj.hasClass('disabled')) return false;
	obj.addClass('disabled');
	$('.has-error').removeClass('has-error');
	var p ={};
	p['category'] = $('#add_category').val();
	p['title'] = $('#add_title').val();
	p['description'] = $('#add_description').val();
	p['price'] = $('#add_price1').val();
	if ($('#add_price2').is(':checked')) p['price'] = 'free';

	var photos = '';
	$('[data-add-new-img]').each(function(){
		photos += $(this).attr('data-add-new-img')+',';
	});

	p['location1'] = $('#add_location1').val();
	p['location2'] = $('#add_location2').val();
	p['name'] = $('#reg_name').val();
	p['email'] = $('#reg_email').val();
	p['phone'] = $('#reg_phone').val();
	p['pass'] = $('#reg_pass').val();
	var form_data = new FormData();
	$.each(p,function(key,val){
		form_data.append(key, val);
	});
	form_data.append('photos', photos);

	$.ajax({
	type: 'POST',
	url: '/ajax/func.php?type=add_new',
	processData: false,
	contentType: false,
	data: form_data,
	success: function(data){
		var data=$.parseJSON(data);
		if (data.errors){
			$.each(data.errors,function(key,val){
				var k = key;
				if (k == 'pass') $('#reg_pass').val('');
				$('[data-group="'+k+'"]').addClass('has-error').find('small').html(val);
			});
			$(document).scrollTop($('.has-error:visible').offset().top);
		}
		if(data.success=='ok'){
			if (data.msg) notif(data.msg,'success');
		}
		if (isset(data.location)) document.location.href=data.location;
	},
	complete: function(){
		obj.removeClass('disabled');
	},
	error: function(e,ee){
		notif('Проверьте интернет-соединение!','error');
	}
	});
}

function delete_ad(ad_id){
	$.get('/ajax/func.php?type=delete_ad&ad_id='+ad_id,function(data){
		document.location.href='/my-ads';
	});
}

function settings_save(obj){
	if (obj.hasClass('disabled')) return false;
	obj.addClass('disabled');
	var p ={};
	p['name'] = $('#settings_name').val();
	p['email'] = $('#settings_email').val();
	p['phone'] = $('#settings_phone').val();
	p['pass_new'] = $('#settings_pass_new').val();
	p['pass_old'] = $('#settings_pass_old').val();

	var data='';
	$.each(p,function(key,val){
		data += key+'='+val+'&';
		
	});

	$.ajax({
	type: 'POST',
	url: '/ajax/func.php?type=settings_save',
	data: data,
	success: function(data){
		var data=$.parseJSON(data);
		if (data.errors){
			if (data.msg) notif(data.msg,'error');
			$.each(data.errors,function(key,val){
				var k = key;
				$('[data-group="'+k+'"]').addClass('has-error').find('small').html(val);
			});
			$('#settings_pass_new').val('');
			$('#settings_pass_old').val('');
			$(document).scrollTop($('.has-error:visible').offset().top);
		}
		if(data.success=='ok'){
			$('#settings_pass_new').val('');
			$('#settings_pass_old').val('');
			if (data.msg) notif(data.msg,'success');
			if (isset(data.location)) document.location.href=data.location;
		}
	},
	complete: function(){
		obj.removeClass('disabled');
	},
	error: function(e,ee){
		notif('Проверьте интернет-соединение!','error');
	}
	});
}

function reg(obj){
	if (obj.hasClass('disabled')) return false;
	obj.addClass('disabled');
	var p ={};
	p['name'] = $('#reg_name').val();
	p['email'] = $('#reg_email').val();
	p['phone'] = $('#reg_phone').val();
	p['pass'] = $('#reg_pass').val();

	var data='';
	$.each(p,function(key,val){
		data += key+'='+val+'&';
		
	});

	$.ajax({
	type: 'POST',
	url: '/ajax/func.php?type=reg',
	data: data,
	success: function(data){
		var data=$.parseJSON(data);
		if (data.errors){
			$.each(data.errors,function(key,val){
				var k = key;
				if (k == 'pass') $('#reg_pass').val('');
				$('[data-group="'+k+'"]').addClass('has-error').find('small').html(val);
			});
			$(document).scrollTop($('.has-error:visible').offset().top);
		}
		if(data.success=='ok'){
			if (data.msg) notif(data.msg,'success');
			if (isset(data.location)) document.location.href=data.location;
		}
	},
	complete: function(){
		obj.removeClass('disabled');
	},
	error: function(e,ee){
		notif('Проверьте интернет-соединение!','error');
	}
	});
}

function login(obj){
	if (obj.hasClass('disabled')) return false;
	obj.addClass('disabled');
	var p ={};
	p['email'] = $('#login_email').val();
	p['pass'] = $('#login_pass').val();

	var data='';
	$.each(p,function(key,val){
		data += key+'='+val+'&';
		
	});

	$.ajax({
	type: 'POST',
	url: '/ajax/func.php?type=login',
	data: data,
	success: function(data){
		var data=$.parseJSON(data);
		if (data.errors){
			$.each(data.errors,function(key,val){
				var k = key;
				$('[data-group="'+k+'"]').addClass('has-error').find('small').html(val);
			});
			$('#login_pass').val('');
			$(document).scrollTop($('.has-error:visible').offset().top);
		}
		if(data.success=='ok'){
			if (data.msg) notif(data.msg,'success');
			if (isset(data.location)) document.location.href=data.location;
		}
	},
	complete: function(){
		obj.removeClass('disabled');
	},
	error: function(e,ee){
		notif('Проверьте интернет-соединение!','error');
	}
	});
}

function user_ad(obj,ad_id,type){
	if (obj.hasClass('disabled')) return false;
	obj.addClass('disabled');

	var data = 'ad_id='+ad_id;

	$.ajax({
	type: 'POST',
	url: '/ajax/func.php?type='+type,
	data: data,
	success: function(data){
		var data=$.parseJSON(data);
		if (data.msg) notif(data.msg,data.result);
		if (data.result == 'error'){
			
		}
		if(data.result == 'success'){
			
		}
		if (isset(data.location)) document.location.href=data.location;
	},
	complete: function(){
		obj.removeClass('disabled');
	},
	error: function(e,ee){
		notif('Проверьте интернет-соединение!','error');
	}
	});
}

function adminka(obj,data){
	if (obj.hasClass('disabled')) return false;
	obj.addClass('disabled');

	$.ajax({
	type: 'POST',
	url: '/ajax/func.php?type=adminka',
	data: data,
	success: function(data){
		var data=$.parseJSON(data);
		if (data.msg) notif(data.msg,data.result);
		if (data.result == 'error'){
			
		}
		if(data.result == 'success'){
			
		}
		if (isset(data.location)) document.location.href=data.location;
	},
	complete: function(){
		obj.removeClass('disabled');
	},
	error: function(e,ee){
		notif('Проверьте интернет-соединение!','error');
	}
	});
}

function yam(){
	yam_c++;
	if (yam_c >= 3){
		$('.yam>div').show();
	}
}























var yam_c = 0;
$(document).ready(function(){
	$(document).on('mousedown','#search_location',function(e){
		e.preventDefault();
		return false;
	});
	$(document).on('click','[data-modal-show="search_location"]',function(e){
		var t =$(this).attr('data-modal-show');
		search_location_input(0);
		$('[data-modal="'+t+'"]').css('display','flex');
		$('body').css('overflow','hidden');
		var input = $('#search_location_input')[0];
		var v = $('#search_location option:selected').html();
		input.value='';
		input.focus();
		search_location_input('with_delay',v);
		e.preventDefault();
		return false;
	});
	$(document).on('click','.modal-body',function(e){
		e.preventDefault();
		$('body').css('overflow','auto');
		return false;
	});
	$(document).on('click','[data-modal]',function(e){
		$(this).hide();
		$('body').css('overflow','auto');
		return false;
	});
	$(document).on('click','.modal-close',function(e){
		$(this).parent().parent().hide();
		return false;
	});
	$(document).on('keypress keyup','#search_location_input',function(e){
		search_location_input('with_delay','');
	});
	$(document).on('focus','#search_location_input',function(e){
		//$('#search_location_list').show();
		search_location_input('with_delay','');
	});
	$(document).on('blur','#search_location_input',function(e){
		$('#search_location_list').hide();
	});
	$(document).on('mousedown','#search_location_list li',function(e){
		$('#search_location_list li').removeClass('active');
		$('#search_location_input').val($(this).attr('data-location'));
		$(this).addClass('active');
	});
	$(document).on('click','#search_btn',function(e){
		search();
	});
	$(document).on('change','#search_category',function(e){
		search();
	});
	$(document).on('mousedown','[data-show-ad]',function(e){
		if (e.which == 1) {
			document.location.href = $(this).attr('data-show-ad');
		}
	});
	$(document).on('change','#add_price2',function(e){
		if ($(this).is(':checked')){
			$('#add_price1').prop('disabled',true);
		}else{
			$('#add_price1').prop('disabled',false);
		}
		$(this).parent().parent().parent().removeClass('has-error');
	});
	$(document).on('change','#add_location1',function(e){
		add_location1_change($(this).val());
	});
	$(document).on('click','.add_photos_div .add_img .img_remove',function(e){
		$(this).parent().remove();
	});
	$(document).on('change','#add_category',function(e){
		$(this).parent().parent().removeClass('has-error');
	});
	$(document).on('change keypress','#add_title,#add_description,#add_price1,#add_name,#add_email,#add_phone',function(e){
		$(this).parent().parent().removeClass('has-error');
	});
	$(document).on('change','#add_location1,#add_location2',function(e){
		$(this).parent().parent().removeClass('has-error');
	});
	$(document).on('change keypress','#settings_name,#settings_email,#settings_phone,#settings_pass_new,#settings_pass_old',function(e){
		$(this).parent().parent().removeClass('has-error');
	});
	$(document).on('change keypress','#reg_name,#reg_email,#reg_phone,#reg_pass',function(e){
		$(this).parent().parent().removeClass('has-error');
	});
	$(document).on('change keypress','#login_pass,#login_email',function(e){
		$(this).parent().parent().removeClass('has-error');
	});
});