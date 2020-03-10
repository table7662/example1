<?php
$HACKER = TRUE;
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/config.php');
$URL = $_GET['url'];
$URLS = explode('/',$URL);
$arr = explode('&',parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));
foreach($arr as $key => $val){
	$s = explode('=',$val);
	$_GET[$s[0]] = $s[1];
}











if($URLS[0] == 'reg'){	#рега
	if (is_user()){
		redirect('/');
		exit;
	}
	$PAGE = $URLS[0];
	require_once($SERVER_DOCUMENT_ROOT.'/pages/'.$PAGE.'.php');
	exit;
}elseif($URLS[0] == 'login'){	#логин
	if (is_user()){
		redirect('/');
		exit;
	}
	$PAGE = $URLS[0];
	require_once($SERVER_DOCUMENT_ROOT.'/pages/'.$PAGE.'.php');
	exit;
}elseif($URLS[0] == 'settings'){	#настройки
	if (!is_user()){
		redirect('/');
		exit;
	}
	$PAGE = $URLS[0];
	require_once($SERVER_DOCUMENT_ROOT.'/pages/'.$PAGE.'.php');
	exit;
}elseif($URLS[0] == 'myads'){	#мои объявы
	if (!is_user()){
		redirect('/');
		exit;
	}
	$PAGE = $URLS[0];
	require_once($SERVER_DOCUMENT_ROOT.'/pages/'.$PAGE.'.php');
	exit;
}elseif($URLS[0] == 'add-new'){	#добавить объяву
	is_user();
	$PAGE = $URLS[0];
	require_once($SERVER_DOCUMENT_ROOT.'/pages/'.$PAGE.'.php');
	exit;
}elseif (($URLS[0]=='' or $URLS[0]!='') and $URLS[1]==''){	#вся страна или локация
	$location_url = $URLS[0];
	if ($location_url == '') $location_url = $config['top_location_url'];
	$location = mysqli_query($link,'select * from z_locations where url = "'.mysqli_real_escape_string($link,$location_url).'"');
	$location = mysqli_fetch_assoc($location);
	if (!empty($location['location_id'])){
		$PAGE = 'main';
		require_once($SERVER_DOCUMENT_ROOT.'/pages/main.php');
		exit;
	}
}elseif($URLS[0]!='' and $URLS[1]!='' and $URLS[2]==''){	#локация и категория
	$location_url = $URLS[0];
	$category_url = $URLS[1];
	$location = mysqli_query($link,'select * from z_locations where url = "'.mysqli_real_escape_string($link,$location_url).'"');
	$location = mysqli_fetch_assoc($location);
	if (!empty($location['location_id'])){
		$category = mysqli_query($link,'select * from z_categories where url = "'.mysqli_real_escape_string($link,$category_url).'"');
		$category = mysqli_fetch_assoc($category);
		if (!empty($category['category_id'])){
			$PAGE = 'main';
			require_once($SERVER_DOCUMENT_ROOT.'/pages/main.php');
			exit;
		}
	}
}elseif($URLS[0]!='' and $URLS[1]!='' and $URLS[2]!=''){	#объява
	$location_url = $URLS[0];
	$category_url = $URLS[1];
	$ad_id = intval(mb_substr($URLS[2],mb_strrpos($URLS[2],'_')+1));
	if (!empty($ad_id)){
		$location = mysqli_query($link,'select * from z_locations where url = "'.mysqli_real_escape_string($link,$location_url).'"');
		$location = mysqli_fetch_assoc($location);
		$category = mysqli_query($link,'select * from z_categories where url = "'.mysqli_real_escape_string($link,$category_url).'"');
		$category = mysqli_fetch_assoc($category);
		$ad = mysqli_query($link,'
			select
				a.*,
				ai.info,
				l.url loc_url,
				l.title loc_title,
				c.url cat_url,
				c.title cat_title
			from z_ads a
				join z_ads_info ai on ai.ad_id = a.ad_id
				join z_locations l on l.location_id = a.location_id
				join z_categories c on c.category_id = a.category_id
			where 1=1
				and a.ad_id = '.$ad_id.'
		');
		$ad = mysqli_fetch_assoc($ad);
		$ad_url = '/'.ad_url($ad);
		if (!empty($ad['ad_id'])){
			if ($_SERVER['REQUEST_URI'] !== $ad_url){
				redirect($config['site_url'].$ad_url);
			}
			$PAGE = 'ad';
			require_once($SERVER_DOCUMENT_ROOT.'/pages/ad.php');
			exit;
		}
	}
}

error(404);
exit;