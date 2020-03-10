<?php
$START = microtime(true);
ini_set('display_errors', 0);	
ini_set('error_reporting', E_WARNING);	
mb_internal_encoding("UTF-8");

$config['db']['host'] = '';	
$config['db']['user'] = '';	
$config['db']['pass'] = '';	
$config['db']['database'] = '';	

$link = mysqli_connect($config['db']['host'], $config['db']['user'], $config['db']['pass'], $config['db']['database']);
mysqli_query($link,'SET NAMES utf8mb4');
mysqli_query($link,'SET wait_timeout=186400');
mysqli_query($link,'SET interactive_timeout=186400');

if (!isset($SERVER_DOCUMENT_ROOT)) $SERVER_DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];

$config['adm_pass'] = '';	
$config['domain_ru'] = '';	
$config['domain'] = '';	
$config['protocol'] = '';	
$config['site_url'] = $config['protocol'].'://'.$config['domain'];
$config['gzencode_level'] = 9;
$config['top_location_id'] = ;
$config['top_location_url'] = '';
$config['version'] = 1;
$config['ads_per_page'] = 10; 
$config['ads_per_page_related'] = 10; 
$config['footer'] = '';	
$config['adminka'] = ''; 
$config['ad_time_up'] = 0; 
$config['ad_add_new_title'] = ''; 
$config['jpg_quality'] = 75;
$config['ad_fixed_count_main'] = 1;	
$config['yandex_metrica'] = '';
$config['support'] = '';
$config['google_anal'] = '';

$mysqli_transaction = false;

function block_assholes(){
	global $link,$_SERVER;
	$IP = $_SERVER['REMOTE_ADDR'];
	$U_A = $_SERVER['HTTP_USER_AGENT'];
	$q_ = mysqli_query($link,'select * from z_assholes');
	while ($q = mysqli_fetch_assoc($q_)){
		$ip = $q['ip'];
		$u_a = $q['u_a'];
		if (!empty($ip)){
			if (mb_strpos($IP,$ip) !== false){
				exit;
			}
		}
		if (!empty($u_a)){
			if (mb_strpos($U_A,$u_a) !== false){
				exit;
			}
		}
	}
	
}
block_assholes();


$q_ = mysqli_query($link,'select category_id,parent_category_id from z_categories where active = 1 order by category_id');
while($q = mysqli_fetch_assoc($q_)){
	if (!empty($q['parent_category_id'])) $cats_active[] = $q['category_id'];
}
$cats_active = implode(',',$cats_active);

function pass_hash($email,$pass=''){
	$result = hash('sha256',$email.'hashik_'.$pass);
	return $result;
}

function guid(){
	if (function_exists('com_create_guid') === true){
		return trim(com_create_guid(), '{}');
	}
    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function is_user($hash = ''){
	global $link,$_COOKIE,$user,$config;
	$result = false;
	if (!empty($hash) or (empty($hash) and !empty($_COOKIE['hash']) and empty($user['user_id']))){
		if (empty($hash)){
			$hash = $_COOKIE['hash'];
			$reset_cookie = true;
		}
		$hash = mysqli_real_escape_string($link,pass_hash($hash));
		$user = mysqli_query($link,'select * from z_users where hash = "'.$hash.'"');
		$user = mysqli_fetch_assoc($user);
		if (!empty($user['user_id'])){
			$result = $user;
		}else{
			if ($reset_cookie) setcookie("hash", '', time() - 60*60*24*365, '/', $config['domain'], 1, 1);
		}
	}elseif(!empty($user['user_id'])){
		$result = $user;
	}
	return $result;
}
is_user();

$is_online_check = false;
function online(){
	global $link,$_SERVER,$ad_id,$is_online_check;
	if ($is_online_check) return;
	if (empty($ad_id)) $ad_id = 0;
	$url = mysqli_real_escape_string($link,$_SERVER['REQUEST_URI']);
	$ip = mysqli_real_escape_string($link,$_SERVER['REMOTE_ADDR']);
	$u_a = mysqli_real_escape_string($link,trim($_SERVER['HTTP_USER_AGENT']));
	$bot_id = 0;
	$http_response_code = http_response_code();
	$q = mysqli_query($link,'select * from z_bots where lower(trim(u_a)) like lower("%'.$u_a.'%") order by bot_id desc limit 1');
	$q = mysqli_fetch_assoc($q);
	if (!empty($q['bot_id'])){
		$bot_id = $q['bot_id'];
	}
	mysqli_query($link,'insert into z_views (ad_id,ip,u_a,t,url,bot_id,http_response_code) values ('.$ad_id.',"'.$ip.'","'.$u_a.'",'.time().',"'.$url.'",'.$bot_id.','.$http_response_code.')');
	$is_online_check = true;
}



function abs_img_src($url){
	global $config;
	$result = $url;
	$parse_url = parse_url($url);
	if (empty($parse_url['host'])){
		if (mb_substr($result,0,1) != '/') $url = '/'.$url;
		$result = $config['site_url'].$url;
	}
	return $result;
}

function is_admin(){
	global $_COOKIE,$config;
	if ($_COOKIE['adm_hash'] == md5($config['adm_pass'])) return true;
	else return false;
}

function replace_tel($tel){
	$result = preg_replace('/\D/', '', $tel);
	return $result;
}

function login($hash){
	global $config;
	setcookie("hash", $hash, time() + 60*60*24*365, '/', $config['domain'], 1, 1);
}

function is_tel($tel){
	$result = false;
	if (preg_match('/^\d+$/',$tel) and mb_strlen($tel) >= 11) $result = true;
	return $result;
}

function is_email($email){
	$result = false;
	$pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
	if (preg_match($pattern,$email)) $result = true;
	return $result;
}

function get_html($url, $timeout = 10, $headers = array()){
	$result = array();
	$r0 = 'error';
	$r1 = 'error';
	
	
	$ch = curl_init();
	if (count($headers) > 0){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_FAILONERROR, true);
	
	#curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
	#curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
	$data = curl_exec($ch);
	if ($data === false or curl_errno($ch)){
		$r0 = 'error';
		$r1 = curl_error($ch);
	}else{
		$r0 = 'ok';
		$r1 = $data;
	}
	curl_close($ch);
	$result[0] = $r0;
	$result[1] = $r1;
	return $result;
}

function is_process_running($pid, $command = ''){
	if (!empty($command)){
		$grep = '| grep "'.$command.'"';
		$result_num = 4;
	}else{
		$grep = '';
		$result_num = 2;
	}
	$result = shell_exec('ps -f -p '.$pid.' -o etime,command '.$grep.' | awk \'{print $1}\'');
	$result = trim(preg_replace('/ELAPSED/','',$result));
	
	if (empty($result)) $result = false;
	return $result;
}

function d($t = 0, $type = ''){
	if ($t == 0) $t = time();
	$result = '';
	if ($type == 'iso'){
		$result = date('c', $t);
	}else{
		$result = date('d.m.y в H:i', $t);
	}
	return $result;
}

function start_transaction(){
	global $link,$mysqli_transaction;
	if (!$mysqli_transaction)
		mysqli_query($link,'START TRANSACTION');
	$mysqli_transaction = true;
}

function commit(){
	global $link,$mysqli_transaction;
	if ($mysqli_transaction) mysqli_query($link,'commit');
	$mysqli_transaction = false;
}

function rollback(){
	global $link,$mysqli_transaction;
	if ($mysqli_transaction) mysqli_query($link,'rollback');
	$mysqli_transaction = false;
}

function x_log($fields){
	global $link;
	$t = time();
	$f = '';
	$v = '';
	if (is_array($fields)){
		$i = 1;
		foreach ($fields as $key => $val){
			$s = $i;
			if ($s == 1) $s = '';
			$f .= 'info'.$s.',';
			$v .= '"'.mysqli_real_escape_string($link,$val).'",';
			$i++;
		}
	}else{
		$f = 'info,';
		$v = '"'.mysqli_real_escape_string($link,$fields).'",';
	}
	mysqli_query($link,'insert into x_logs ('.$f.'t) values ('.$v.$t.')');
}

function number_end( $number, $titles ) {
    $cases = array( 2, 0, 1, 1, 1, 2 );
    return $titles[ ( $number % 100 > 4 && $number % 100 < 20 ) ? 2 : $cases[ min( $number % 10, 5 ) ] ];
}

function d_was($date){
    $stf      = 0;
    $cur_time = time();
    $diff     = $cur_time - $date;
	if ($diff <= 0) return 'только что';
 
    $seconds = array( 'сек.','сек.','сек.' );
    $minutes = array( 'мин.','мин.','мин.' );
    $hours   = array( 'ч.','ч.','ч.' );
    $days    = array( 'дн.','дн.','дн.' );
    $weeks   = array( 'нед.','нед.','нед.' );
    $months  = array( 'мес.','мес.','мес.' );
    $years   = array( 'г.','г.','г.' );
    $decades = array( 'дес.','дес.','дес.' );
 
    $phrase = array( $seconds, $minutes, $hours, $days, $weeks, $months, $years, $decades );
    $length = array( 1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600 );
 
    for ( $i = sizeof( $length ) - 1; ( $i >= 0 ) && ( ( $no = $diff / $length[ $i ] ) <= 1 ); $i -- ) {
        ;
    }
    if ( $i < 0 ) {
        $i = 0;
    }
    $_time = $cur_time - ( $diff % $length[ $i ] );
    $no    = floor( $no );
    $value = sprintf( "%d %s ", $no, number_end( $no, $phrase[ $i ] ) );
 
    if ( ( $stf == 1 ) && ( $i >= 1 ) && ( ( $cur_time - $_time ) > 0 ) ) {
        $value .= time_ago( $_time );
    }
 
    return $value;
}

function demon_type_info($demon_type_id){
	global $link;
	$demon_type = mysqli_query($link,'select * from x_demon_types where demon_type_id = '.$demon_type_id);
	$demon_type = mysqli_fetch_assoc($demon_type);
	$demon_type_info = json_decode($demon_type['info'],true);
	return $demon_type_info['params'];
}

function set_demon_type_info($demon_type_id,$arr=''){
	global $link;
	if (!empty($arr)){
		$demon_type = mysqli_query($link,'select * from x_demon_types where demon_type_id = '.$demon_type_id);
		$demon_type = mysqli_fetch_assoc($demon_type);
		$demon_type_info = json_decode($demon_type['info'],true);
		foreach ($arr as $key => $val){
			$demon_type_info['params'][$key] = $val;
		}
		$demon_type_info = mysqli_real_escape_string($link,json_encode_true($demon_type_info));
		mysqli_query($link,'update x_demon_types set info = "'.$demon_type_info.'" where demon_type_id = '.$demon_type_id);
	}
	return true;
}

function demon_end(){
	global $demon_id,$link;
	mysqli_query($link,'update x_demons set status = 2, t_e='.time().' where demon_id = '.$demon_id);
}

function json_encode_true($data){
	$result = defined('JSON_UNESCAPED_UNICODE') ? json_encode($data, JSON_UNESCAPED_UNICODE) : json_encode($data);
	return $result;
}

function translit($s) {
	$s = (string) $s;
	$s = strip_tags($s);
	$s = str_replace(array("\n", "\r"), " ", $s);
	$s = preg_replace("/\s+/", ' ', $s);
	$s = trim($s);
	$s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s);
	$s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
	$s = preg_replace("/[^0-9a-z-_ ]/i", "", $s);
	$s = str_replace(" ", "-", $s);
	return $s;
}

function ad_add($info, $pars_id = 0, $avito_id = 0){
	global $link,$config;
	$locations_sql_0 = ''; $locations_sql_1 = '';
	$cats_sql_0 = ''; $cats_sql_1 = '';
	$i = 1;
	ksort($info['refs']['locations']);
	foreach ($info['refs']['locations'] as $key => $val){
		$locations_sql_0 .= ',location_id_'.$i;
		$locations_sql_1 .= ','.$key;
		$i++;
	}
	$i = 1;
	ksort($info['refs']['categories']);
	foreach ($info['refs']['categories'] as $key => $val){
		$cats_sql_0 .= ',category_id_'.$i;
		$cats_sql_1 .= ','.$key;
		$i++;
	}
	if (empty($info['user_id'])) $user_id = 0;
	else $user_id = $info['user_id'];
	$title = mysqli_real_escape_string($link,$info['title']);
	$category_id = $info['categoryId'];
	$location_id = $info['locationId'];
	$t = time();
	mysqli_query($link, 'insert into z_ads (pars_id,avito_id,user_id,title,category_id,location_id,views,t,t_creation,t_fix,active) values ('.$pars_id.','.$avito_id.','.$user_id.',"'.$title.'",'.$category_id.','.$location_id.',0,'.$t.','.$t.',0,1)');
	$ad_id = mysqli_insert_id($link);
	if ($ad_id > 0){
		$info_sql = mysqli_real_escape_string($link,gzencode(json_encode_true($info),$config['gzencode_level']));
		mysqli_query($link, 'insert into z_ads_info (ad_id,info) values ('.$ad_id.',"'.$info_sql.'")');
		mysqli_query($link, 'insert into z_ads_locations (ad_id'.$locations_sql_0.') values ('.$ad_id.$locations_sql_1.')');
		mysqli_query($link, 'insert into z_ads_categories (ad_id'.$cats_sql_0.') values ('.$ad_id.$cats_sql_1.')');
		if (!empty($user_id)){
			mysqli_query($link, 'insert into z_users_ads (user_id,ad_id) values ('.$user_id.','.$ad_id.')');
		}
	}
	return $ad_id;	
}

function time_end($decimal = 10){
	global $START;
	$time = round(microtime(true) - $START,$decimal);
	return $time;
}

function clear_dir($path){
	if (file_exists($path)){
		foreach (glob($path.'*') as $file){
			if (is_file($file)) unlink($file);
		}
	}
}

function ad_url($q){
	return $q['loc_url'].'/'.$q['cat_url'].'/'.translit($q['title']).'_'.$q['ad_id'];
}

function error($code){
	global $SERVER_DOCUMENT_ROOT,$config,$link;
	require_once($SERVER_DOCUMENT_ROOT.'/pages/error/error.php');
}

function max_img_url($arr){
	$max = 0;
	if (count($arr) <= 0){
		$result = '/img/nophoto.png';
	}else{
		foreach ($arr as $key => $val){
			if (intval($key) > $max){
				$max = intval($key);
				$result = $val;
			}
		}
	}
	return abs_img_src($result);
}

function time_late($t,$t2=0){
	if ($t2 == 0) $t2 = time();
	$result = '';
	$temp = '';
	$start_date = new DateTime(d($t2,'iso'));
	$since_start = $start_date->diff(new DateTime(d($t,'iso')));
	$days = $since_start->days;
	$h = $since_start->h;
	$i = $since_start->i;
	$s = $since_start->s;
	if ($days > 0) $temp .= $days.' дн. ';
	if ($h > 0) $temp .= $h.' ч. ';
	if ($i > 0) $temp .= $i.' м. ';
	if ($s > 0) $temp .= $s.' сек.';
	$result = $temp;
	return $result;
}

function ads_out($q_,$ids_return=false){
	global $link,$config,$user;
	$result = '';
	while ($q = mysqli_fetch_assoc($q_)){
		$ad_url = '/'.ad_url($q);
		$title = htmlspecialchars($q['title']);
		$ad_info = json_decode(gzdecode($q['info']),true);
		$descr = htmlspecialchars(mb_substr($ad_info['description'],0,200));
		$img = max_img_url($ad_info['images'][0]);
		$seller = htmlspecialchars($ad_info['seller']['name']);
		$price = htmlspecialchars($ad_info['price']['value']);
		if (!empty($ad_info['price']['metric'])) $metric = htmlspecialchars($ad_info['price']['metric']);
		else $metric = '';
		$location = mysqli_query($link,'select * from z_locations where location_id = '.$q['location_id']);
		$location = mysqli_fetch_assoc($location);
		$location_info = json_decode($location['info'],true);
		if ($q['parent_location_id'] != $config['top_location_id']){
			$parent_location = mysqli_query($link,'select * from z_locations where location_id = '.$q['parent_location_id']);
			$parent_location = mysqli_fetch_assoc($parent_location);
			$parent_location_info = json_decode($parent_location['info'],true);
			$par_loc = '<a href="/'.$parent_location['url'].'/'.$q['cat_url'].'">'.$parent_location_info['names'][6].'</a>, ';
		}else{
			$par_loc = '';
		}
		$col_lg = 1;
		#закреп
		$div_fix = '';
		$ad_fix = '';
		if ($q['t_fix'] > time()){
			$col_lg = 2;
			$ad_fix = ' ad_fix ';
			$div_fix = '<div class="btn btn-success ad_fix_btn">Закрепленное до '.d($q['t_fix']).'</div><br>';
		}
		#
		#юзер
		$btn_pub = '';
		$ad_dis = '';
		$btn_up = '';
		$ad_my = '';
		if (!empty($q['user_id']) and !empty($user['user_id']) and $user['user_id'] == $q['user_id']){
			$ad_my = ' ad_my ';
			$t_up = $q['t'] + $config['ad_time_up'] - time();
			if ($t_up < 0){
				$btn_up = '<div class="btn btn-sm btn-primary" onclick="user_ad($(this),\''.$q['ad_id'].'\',\'ad_up\')">Поднять</div>';
			}else{
				$btn_up = '<div class="btn btn-sm btn-default disabled">Поднять через '.time_late(time()+$t_up).'</div>';
			}
			if ($q['active'] == 2){
				$btn_pub = '<div class="btn btn-sm btn-primary" onclick="user_ad($(this),\''.$q['ad_id'].'\',\'ad_active\')">Опубликовать</div>';
				$ad_dis = ' ad_dis ';
			}else{
				$btn_pub = '<div class="btn btn-sm btn-warning" onclick="user_ad($(this),\''.$q['ad_id'].'\',\'ad_deactive\')">Снять с публикации</div>';
			}
			$col_lg = 3;
		}
		#
		
		$result .= '
			<li class="media clearfix adv_item adv_item_even1 '.$ad_disy.$ad_fix.$ad_my.'">
			<div class="col-lg-'.$col_lg.' col-md-1 col-sm-12 col-xs-12 text-muted small">
				<div class="row">
					<span class="col-md-12 col-md-2 col-xs-12 media-heading">
						<time class="smart_time" datetime="'.d($q['t'],'iso').'">
							'.d($q['t']).' мск.
						</time>
					</span>
					'.$div_fix.'
				</div>
				'.$btn_pub.'
				'.$btn_up.'
			</div>
			<div class="col-lg-2 col-md-3 col-sm-3 media-heading fake_href_action loaded" style="overflow: hidden;" data-show-ad="'.$ad_url.'">
				<img class="img-zoom lazy_action" style="height: 90px;" src="'.$img.'" title="'.$title.'" alt="'.$title.'">
			</div>
			<div class="col-lg-5 col-md-5 col-sm-6 col-xs-12">
				<a class="header_adv_short" href="'.$ad_url.'">
					'.$title.'
				</a>
				<div>
					<span class="lead"><span class="formatRub loaded-format-rub">'.$price.'</span> '.$metric.'</span>
					<div class="text-location"><a href="/'.$config['top_location_url'].'/'.$q['cat_url'].'">'.$q['cat_title'].'</a> в '.$par_loc.'<a href="/'.$q['loc_url'].'/'.$q['cat_url'].'">'.$location_info['names'][6].'</a></div>
					<div class="small text-muted text-trim text-comment tooltip_comment_action tooltip_comment_loaded">
						'.$descr.'
					</div>
					
				</div>
			</div>
			<div class="col-lg-2 col-md-3 col-sm-3 col-xs-6">
				<div class="trim-name"><i class="fa fa-user"></i> '.$seller.'</div>
			</div>
			<div class="col-lg-2 col-md-3 col-sm-3 col-xs-6">
				<button class="btn btn-success btn-xs" data-show-ad="'.$ad_url.'"><i class="fa fa-phone"></i> Показать телефон</button>
			</div>
		</li>
	';
	$ids[] = $q['ad_id'];
	}
	
	if ($ids_return){
		$s = $result;
		$result = array();
		$result[0] = $s;
		$result[1] = $ids;
	}
	return $result;
}

function redirect($url){
	global $config;
	header('Location: '.$url);
	exit;
}

function tel_avito($tel){
	if (mb_strpos($tel,'%2B') !== false) $result = mb_substr($tel,mb_strpos($tel,'%2B')+3);
	else $result = replace_tel($tel);
	return $result;
}

function ad_view($ad_id,$views = 1){
	global $link;
	mysqli_query($link,'update z_ads set views = views + '.$views.' where ad_id = '.$ad_id);
}







