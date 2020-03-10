<?php
if (empty($category['category_id'])){
	$category_id = 0;
	$category_title = 'Все';
	$category_url = '';
}
if (empty($location['location_id'])){
	$location = mysqli_query($link,'select * from z_locations where location_id = '.$config['top_location_id']);
	$location = mysqli_fetch_assoc($location);
	$location_id = $location['location_id'];
}
if (empty($category_id)) $selected = 'selected';
else $selected = '';
$cats_form = '<option value="" '.$selected.' class="header">Все</option>';

$q_ = mysqli_query($link,'select * from z_categories where active = 1 and parent_category_id = 0 order by category_id');
while($q = mysqli_fetch_assoc($q_)){
	if ($category_id == $q['category_id']) $selected = 'selected';
	else $selected = '';
	$cats_form .= '<option value="'.$q['url'].'" '.$selected.' class="header">'.$q['title'].'</option>';
	$qq_ = mysqli_query($link,'select * from z_categories where active = 1 and parent_category_id = '.$q['category_id'].' order by category_id');
	while($qq = mysqli_fetch_assoc($qq_)){
		if ($category_id == $qq['category_id']) $selected = 'selected';
		else $selected = '';
		if (empty($qq['parent_category_id'])) $select_header = ' class="header" ';
		else $select_header = '';
		$cats_form .= '<option value="'.$qq['url'].'" '.$selected.' '.$select_header.'>'.$qq['title'].'</option>';
	}
}

$search_form = '
<div class="well well-sm" itemscope="itemscope" itemtype="http://schema.org/WebSite">
<link href="'.$config['site_url'].'" itemprop="url" />
	<div class="col-sm-4">
		<select class="form-control m-b" style="cursor:pointer;" id="search_category">
			'.$cats_form.'
		</select>
	</div>
	<div class="col-sm-4">
		<select class="form-control m-b" style="cursor:pointer;" id="search_location" data-modal-show="search_location">
			<option value="'.$location['url'].'" selected>'.$location['title'].'</option>
		</select>
	</div>

	<div data-modal="search_location" class="modal-bg">
		<div class="modal-body col-sm-6"><div class="modal-close"><i class="fa fa-close"></i></div>
		<div style="display:block;width:100%;position:relative;">
			<h5><b>Город или регион</b></h5>
			<input type="text" class="form-control" placeholder="Город, регион или Россия" id="search_location_input">
			<div style="height:160px"></div>
			<ul id="search_location_list"></ul>
			<div class="btn btn-primary r" onclick="search_location_input_save();">Сохранить</div>
		</div>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="btn btn-primary r" id="search_btn"><i class="fa fa-search"></i> Поиск</div>
	</div>

<div class="clear"></div>
</div>
';

$main_locations = '<div class="panel panel-default" style="margin-top: 0px;"><div class="panel-body"><span class="text-muted small">';
$q_= mysqli_query($link,'select * from z_locations where parent_location_id = '.$config['top_location_id'].' order by title');
while ($q = mysqli_fetch_assoc($q_)){
	$main_locations .= '<a class="text-nowrap" href="/'.$q['url'].'">'.$q['title'].'</a>, ';
}
$main_locations = mb_substr($main_locations,0,-2).'</span></div></div>';

$cats_out_noloc = 'Все категории: ';
$cats_out = 'Категории для <b>'.$location_info['names'][2].':</b> ';
$q_ = mysqli_query($link,'select * from z_categories where active = 1 and parent_category_id = 0 order by category_id');
while($q = mysqli_fetch_assoc($q_)){
	$cats_out .= '<a class="text-nowrap" href="/'.$location['url'].'/'.$q['url'].'">'.$q['title'].'</a>, ';
	$cats_out_noloc .= '<a class="text-nowrap" href="/'.$config['top_location_url'].'/'.$q['url'].'">'.$q['title'].'</a>, ';
	$qq_ = mysqli_query($link,'select * from z_categories where active = 1 and parent_category_id = '.$q['category_id'].' order by category_id');
	while($qq = mysqli_fetch_assoc($qq_)){
		$cats_out .= '<a class="text-nowrap" href="/'.$location['url'].'/'.$qq['url'].'">'.$qq['title'].'</a>, ';
		$cats_out_noloc .= '<a class="text-nowrap" href="/'.$config['top_location_url'].'/'.$qq['url'].'">'.$qq['title'].'</a>, ';
	}
}
$cats_out_noloc = '<div class="panel panel-default" style="margin-top: 0px;"><div class="panel-body"><span class="text-muted small">'.mb_substr($cats_out_noloc,0,-2).'</span></div></div>';
$cats_out = '<div class="panel panel-default" style="margin-top: 0px;"><div class="panel-body"><span class="text-muted small">'.mb_substr($cats_out,0,-2).'</span></div></div>';

if (!empty($ad_id)) $ml = $main_locations;
else $ml = '';
if (empty($location) or $location['location_id'] == $config['top_location_id']) $cats_out = '';
if ($PAGE == 'add-new' or $PAGE == 'reg' or $PAGE == 'login') $cats_out_noloc = '';

if (is_admin()){
	$ya_metrica = '';
	$google_anal = '';
}else{
	$ya_metrica = $config['yandex_metrica'];
	$google_anal = $config['google_anal'];
}

$footer = '
	'.$cats_out.'
	'.$cats_out_noloc.'
	'.$ml.'
   <div class="row">
      <div class="col-sm-4">
         <ul class="list-unstyled">
            <li><i class="fa fa-line-chart"></i>'.$config['footer'].'</li>
			'.$config['support'].'
            <li>'.$ya_metrica.'</li>
			<li>'.$google_anal.'</li>
			<li></li>
         </ul>
      </div>
   </div>
';

if (empty($canonical)){
	$canonical = $config['site_url'].$_SERVER['REQUEST_URI'];
}
if (empty($title)){
	$title = $config['domain_ru'];
}

if (is_user()){
	$myads_c = mysqli_query($link,'select count(1) c from z_users_ads ua join z_ads a on a.ad_id = ua.ad_id where a.active in (1,2) and ua.user_id = '.$user['user_id']);
	$myads_c = mysqli_fetch_assoc($myads_c);
	$myads_c = $myads_c['c'];
	
	$menu_login = '
	<a class="dropdown-toggle" href="/myads" title="Мои объявления" style="color:gray !important">
	<i class="fa fa-list-alt"></i> Мои объявления<span class="hidden-sm"></span>
	<span class="badge">'.$myads_c.'</span>
	</a>
	';
	$menu_reg = '
	<a class="dropdown-toggle" href="/settings" title="Дима" style="color:gray !important">
	<i class="fa fa-user"></i> '.htmlspecialchars($user['name']).'<span class="hidden-sm"></span>
	</a>';
}else{
	$menu_login = '<a href="/login"><div class="navbar-link"><i class="fa fa-user"></i> Войти</div></a>';
	$menu_reg = '<a href="/reg"><div class="navbar-link"><i class="fa fa-user-plus"></i> Зарегистрироваться</div></a>';
}

$menu = '
<header>
	<div class="navbar navbar-default">
	<div class="container-fluid">
	   <div class="navbar-header">
			<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse" type="button" onclick="top_menu_collapse()">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<span class="pull-left">
				<a class="navbar-brand" href="/" style="color:#337ab7;">
					<img src="/logo.png" style="position:absolute;width:40px;top:5px;" alt="'.$config['domain_ru'].'">
					<span style="padding-left:50px;">'.$config['domain_ru'].'</span>
				</a>
			</span>
		</div>
	<nav class="collapse navbar-collapse" id="top_navbar">
		  <ul class="nav navbar-nav navbar-right">
			
	<li class="nav navbar-nav">
	'.$menu_login.'
	</li>
	<li class="nav navbar-nav">
	'.$menu_reg.'
	</li>
	
			
<li class="nav navbar-nav">
	<div class="navbar-btn nav-font-color">
		<a data-no-turbolink="true" href="/add-new">
			<div class="btn btn-warning">
				<i class="fa fa-plus fa-lg"></i> Подать объявление бесплатно
			</div>
		</a>
	</div>	
 </li>

		  </ul>
	   </nav>
	</div>
	</div>
	<div class="notifications top-right"></div>
</header>
<h1 style="display:inline-block;">'.$title.'</h1>
';

$header = '
	<meta charset="utf-8" />
	<title>'.$title.'</title>
	<meta content="website" property="og:type" />
	<meta content="'.$config['domain_ru'].'" property="og:site_name" />
	
	<meta property="og:locale" content="ru_RU">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<link href="//mc.yandex.ru" rel="dns-prefetch" />
	<link href="//www.google-analytics.com" rel="dns-prefetch" />
	<link href="//www.googletagmanager.com" rel="dns-prefetch" />
	<link href="//informer.yandex.ru" rel="dns-prefetch" />
	<link href="//counter.yadro.ru" rel="dns-prefetch" />
	<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
	
	
	<link rel="canonical" href="'.$canonical.'"/>

	<link href="/css/asset.css" media="screen" rel="stylesheet" />
	<link href="/bootstrap/font-awesome/css/font-awesome.css" rel="stylesheet">
	<link href="/css/toastr.min.css" media="screen" rel="stylesheet" />
	<link href="/css/all.css?'.$config['version'].'" media="screen" rel="stylesheet" />
	<link href="/css/fotorama.min.css" media="screen" rel="stylesheet" />
	
	<script defer src="/js/jquery-3.3.1.min.js"></script>
	<script defer src="/js/toastr.min.js"></script>
	<script defer src="/js/all.js?'.$config['version'].'"></script>
	<script defer src="/js/fotorama.min.js"></script>
';

$form_reg = '
	<div class="form-group" data-group="name">
		<label class="col-sm-2 control-label"><i class="fa fa-user"></i> Моё имя</label>
		<div class="col-sm-4">
			<input class="form-control" id="reg_name" maxlength="50"  type="text" placeholder="Мое имя"/>
			<small class="help-block"></small>
		</div>
	</div>
	<div class="form-group" data-group="email">
		<label class="col-sm-2 control-label"><i class="fa fa-at"></i> Почта</label>
		<div class="col-sm-4">
			<input class="form-control" id="reg_email" maxlength="200"  type="text" placeholder="email@domain.com"/>
			<small class="help-block"></small>
		</div>
	</div>
	<div class="form-group" data-group="phone">
		<label class="col-sm-2 control-label"><i class="fa fa-phone"></i> Телефон</label>
		<div class="col-sm-4">
			<input class="form-control" id="reg_phone" maxlength="20"  type="text" placeholder="7 924 1234567"/>
			<small class="help-block"></small>
		</div>
	</div>
	<hr/>

	<div class="form-group" data-group="pass">
		<label class="col-sm-2 control-label"><i class="fa fa-lock"></i> Пароль</label>
		<div class="col-sm-4">
			<input class="form-control" id="reg_pass" maxlength="50"  type="text" placeholder="длина от 6 до 50 символов"/>
			<small class="help-block"></small>
		</div>
	</div>
';

online();