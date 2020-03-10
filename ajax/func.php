<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/config.php');

function reg($name,$email,$phone,$pass){
	global $link,$_SERVER;
	$result = array();
	$name = trim($name);
	$email = trim($email);
	$phone = replace_tel($phone);
	if (!preg_match('/^[a-zа-я0-9\s]{2,50}+$/ui',$name)){
		$result['errors']['name'] = 'Только анг., рус. буквы, цифры, пробелы, длина 2-50 симв.';
	}
	if (!is_email($email)){
		$result['errors']['email'] = 'Некорректный email';
	}
	if (!is_tel($phone)){
		$result['errors']['phone'] = 'Некорректный телефон';
	}
	if (strlen($pass) > 50 or strlen($pass) < 6){
		$result['errors']['pass'] = 'Длина пароля от 6 до 50 символов';
	}
	
	if (empty($result['errors'])){
		$user = mysqli_query($link,'select * from z_users where tel="'.$phone.'" or email = "'.$email.'" limit 1');
		$user = mysqli_fetch_assoc($user);
		if (empty($user['user_id'])){
			$hash = pass_hash($email,$pass);
			$hash_ = pass_hash($hash);
			$name = mysqli_real_escape_string($link,$name);
			$email = mysqli_real_escape_string($link,$email);
			$phone = mysqli_real_escape_string($link,$phone);
			$pass = mysqli_real_escape_string($link,$pass);
			$ip = mysqli_real_escape_string($link,$_SERVER['REMOTE_ADDR']);
			mysqli_query($link,'insert into z_users (pass,hash,name,email,tel,ip,t) values ("'.$pass.'","'.mysqli_real_escape_string($link,$hash_).'","'.$name.'","'.$email.'","'.$phone.'","'.$ip.'",'.time().')');
			$result['success'] = 'ok';
			$result['hash'] = $hash;
		}else{
			if ($phone == $user['tel']) $result['errors']['phone'] = 'Телефон уже зарегистрирован';
			if ($email == $user['email']) $result['errors']['email'] = 'Email уже зарегистрирован';
		}
	}
	return $result;
}

$TYPE = $_GET['type'];
switch($TYPE){
	case 'get_locations':
		$s = '';
		$query = mysqli_real_escape_string($link,$_GET['q']);
		$q_ = mysqli_query($link,'
			select
				x.*
			from (
				select
					l.title
					,l.url
					,lp.title parent_title
				from z_locations l
					left join z_locations lp on lp.location_id = l.parent_location_id and lp.location_id != '.$config['top_location_id'].'
				where 1=1
					and l.title like "%'.$query.'%"
				order by
					l.title
				limit 5
			) x
			union
			select
				title
				,url
				,null
			from z_locations
			where location_id = '.$config['top_location_id'].'
		');
		while ($q = mysqli_fetch_assoc($q_)){
			if (!empty($q['parent_title'])) $ss = '<span class="parent">'.$q['parent_title'].'</span>';
			else $ss = '';
			$s .= '<li data-location="'.$q['title'].'" data-location_url="'.$q['url'].'">'.$q['title'].$ss.'</li>';
		}
		echo $s;
	exit;
	case 'reg':
		if (is_user()) exit;
		$name = $_POST['name'];
		$email = $_POST['email'];
		$phone = $_POST['phone'];
		$pass = $_POST['pass'];
		
		$result = reg($name,$email,$phone,$pass);
		if ($result['success'] == 'ok'){
			ob_clean();
			login($result['hash']);
			$result['location'] = '/myads';
		}
		print_r(json_encode_true($result));
	exit;
	case 'login':
		if (is_user()) exit;
		$result = array();
		$email = trim($_POST['email']);
		$pass = $_POST['pass'];
		if (!is_email($email)){
			$result['errors']['email'] = 'Некорректный email';
		}
		if (strlen($pass) > 50 or strlen($pass) < 6){
			$result['errors']['pass'] = 'Длина пароля от 6 до 50 символов';
		}
		if (empty($result['errors'])){
			$hash = pass_hash($email,$pass);
			if (is_user($hash)){
				$result['success'] = 'ok';
				$result['location'] = '/myads';
				ob_clean();
				login($hash);
			}else{
				$result['errors']['pass'] = 'Неверный email и/или пароль';
			}
		}
		
		print_r(json_encode_true($result));
	exit;
	case 'add_location1':
		$result = '<option value="0" selected>Выберите нас.пункт</option>';
		$location_id = intval($_GET['location_id']);
		if (!empty($location_id)){
			$q_ = mysqli_query($link,'select * from z_locations where parent_location_id = '.$location_id.' order by title');
			while ($q = mysqli_fetch_assoc($q_)){
				$result .= '<option value="'.$q['location_id'].'">'.$q['title'].'</option>';
			}
		}
		echo $result;
	exit;
	case 'add_photos_upload':
		$result = array('result'=>'error','msg'=>'ошибка при загрузке');
		$files = $_FILES;
		$user_id = 0;
		if (is_user()) $user_id = $user['user_id'];
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' and !empty($files)){
			foreach ($files['photo']['tmp_name'] as $index => $tmp_name){
				if (!empty($tmp_name) and is_uploaded_file($tmp_name)){
					$img_type = exif_imagetype($tmp_name);
					if ($img_type == IMAGETYPE_GIF or $img_type == IMAGETYPE_PNG or $img_type == IMAGETYPE_JPEG){
						switch ($img_type){
							case IMAGETYPE_JPEG:
								$image = imagecreatefromjpeg($tmp_name);
							break;
							case IMAGETYPE_GIF:
								$image = imagecreatefromgif($tmp_name);
								$bg = imagecreatetruecolor(imagesx($image), imagesy($image));
								imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
								imagealphablending($bg, TRUE);
								imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
								imagedestroy($image);
								$image = $bg;
							break;
							case IMAGETYPE_PNG:
								$image = imagecreatefrompng($tmp_name);
								$bg = imagecreatetruecolor(imagesx($image), imagesy($image));
								imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
								imagealphablending($bg, TRUE);
								imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
								imagedestroy($image);
								$image = $bg;
							break;
						}
						$photo_name = guid();
						$photo_url = '/img/photos/'.$photo_name.'.jpg';
						$photo_path = $SERVER_DOCUMENT_ROOT.$photo_url;
						imagejpeg($image, $photo_path, $config['jpg_quality']);
						imagedestroy($image);
						if (file_exists($photo_path)){
							$upload_hash = hash('sha256',$photo_name.'protected');
							$info = array();
							$info['ip'] = $_SERVER['REMOTE_ADDR'];
							$info['u_a'] = $_SERVER['HTTP_USER_AGENT'];
							$info['photo_url'] = $photo_url;
							$info = mysqli_real_escape_string($link,json_encode_true($info));
							mysqli_query($link,'insert into z_uploads (upload_hash,info,user_id,ad_id,t) values ("'.mysqli_real_escape_string($link,$upload_hash).'","'.$info.'",'.$user_id.',0,'.time().')');
							if (mysqli_affected_rows($link) > 0){
								$result['photos'][$upload_hash] = $photo_url;
							}
						}
					}
				}
			}
		}
		if (!empty($result['photos'])){
			$result['result'] = 'ok';
			unset($result['msg']);
		}
		print_r(json_encode_true($result));
	exit;
	case 'add_new':
		$result = array();
		start_transaction();
		$category_id = intval($_POST['category']);
		$title = trim($_POST['title']);
		$description = trim($_POST['description']);
		$price = $_POST['price'];
		$parent_location_id = intval($_POST['location1']);
		$location_id = intval($_POST['location2']);
		$photos = $_POST['photos'];
		
		$name = $_POST['name'];
		$email = $_POST['email'];
		$phone = $_POST['phone'];
		$pass = $_POST['pass'];
		
		$category = mysqli_query($link,'select * from z_categories where active = 1 and category_id = '.$category_id.' and category_id != 0 and parent_category_id != 0');
		$category = mysqli_fetch_assoc($category);
		if (empty($category['category_id'])){
			$result['errors']['category'] = 'Выберите категорию';
		}
		if (mb_strlen($title) > 100 or mb_strlen($title) < 3){
			$result['errors']['title'] = 'Длина заголовка 3 - 100 символов';
		}
		if (mb_strlen($description) > 4000 or mb_strlen($description) < 10){
			$result['errors']['description'] = 'Описание 10 - 40000 символов';
		}
		if ($price != 'free' and intval($price) == 0){
			$result['errors']['price'] = 'Некорректная цена';
		}
		$parent_location = mysqli_query($link,'select * from z_locations where location_id = '.$parent_location_id.' and parent_location_id = '.$config['top_location_id']);
		$parent_location = mysqli_fetch_assoc($parent_location);
		$parent_location_info = json_decode($parent_location['info'],true);
		if (empty($parent_location['location_id'])){
			$result['errors']['location1'] = 'Выберите регион';
		}
		$location = mysqli_query($link,'select * from z_locations where location_id = '.$location_id.' and parent_location_id = '.$parent_location['location_id']);
		$location = mysqli_fetch_assoc($location);
		if (empty($location['location_id']) and !$parent_location_info['hasDirections']){
			$result['errors']['location2'] = 'Выберите нас. пункт';
		}	
		if (empty($result['errors'])){
			if (!is_user()){	#не зареган, пытаемся зарегать
				$reg = reg($name,$email,$phone,$pass);
				if ($reg['success'] == 'ok'){	#зарегали
					$result['hash'] = $reg['hash'];
					is_user($reg['hash']);
				}else{
					$result['errors' ]= $reg['errors'];
				}
			}
		}
		if (empty($result['errors'])){	#все ок добавляем объяву
			$info = array();
			$info['refs']['locations'][$parent_location['location_id']]['name'] = $parent_location['title'];
			if (!empty($location['location_id'])) $info['refs']['locations'][$location['location_id']]['name'] = $location['title'];
			$parent_category = mysqli_query($link,'select * from z_categories where category_id = '.$category['parent_category_id']);
			$parent_category = mysqli_fetch_assoc($parent_category);
			$info['refs']['categories'][$parent_category['category_id']]['name'] = $parent_category['title'];
			$info['refs']['categories'][$category['category_id']]['name'] = $category['title'];
			$info['user_id'] = $user['user_id'];
			$info['title'] = $title;
			$info['categoryId'] = $category['category_id'];
			$info['locationId'] = !empty($location['location_id']) ? $location['location_id'] : $parent_location['location_id'];
			$info['description'] = $description;
			#фотки
			$photos = explode(',', $photos);
			$images = array();
			foreach ($photos as $key => $val){
				$upload_hash = mysqli_real_escape_string($link,$val);
				$q = mysqli_query($link,'select * from z_uploads where upload_hash = "'.$upload_hash.'"');
				$q = mysqli_fetch_assoc($q);
				if (!empty($q['upload_hash'])){
					$upload_info = json_decode($q['info'],true);
					$photo_url = $upload_info['photo_url'];
					$size = getimagesize($SERVER_DOCUMENT_ROOT.$photo_url);
					$size = $size[0].'x'.$size[1];
					$images[][$size] = $photo_url;
				}
			}
			#
			$info['images'] = $images;
			$info['seller']['name'] = $user['name'];
			$info['contacts']['list'][0]['value']['uri'] = $user['tel'];
			if ($price == 'free'){
				$price = 'Бесплатно';
				$metric = '';
			}else{
				$price = intval($price);
				$metric = 'руб.';
			}
			$info['price']['value'] = $price;
			$info['price']['metric'] = $metric;
			$location_info = json_decode($location['info'],true);
			$info['coords']['lat'] = $location_info['coords']['lat'];
			$info['coords']['lng'] = $location_info['coords']['lng'];
			$ad_id = ad_add($info);
			if ($ad_id <= 0){	#не добавилась объява какая-то ошибка, откатываем и регистрацию тоже
				rollback();
				$result['hash'] = '';
				$result['location'] = '/add-new';
			}else{	#все ок добавили объяву
				foreach ($photos as $key => $val){
					$upload_hash = mysqli_real_escape_string($link,$val);
					mysqli_query($link,'update z_uploads set user_id = '.$user['id'].', ad_id = '.$ad_id.' where upload_hash = "'.$upload_hash.'"');
				}
				$result['success'] = 'ok';
				$result['location'] = '/s/s/s_'.$ad_id;	#залепа
			}
			if (!empty($result['hash'])){	#логиним если только что зарегали
				ob_clean();
				login($result['hash']);
			}
		}
		commit();
		
		print_r(json_encode_true($result));
	exit;
	case 'settings_save':
		if (!is_user()) exit;
		$result = array();
		$name = trim($_POST['name']);
		$email = trim($_POST['email']);
		$phone = replace_tel($_POST['phone']);
		$pass_old = $_POST['pass_old'];
		$pass_new = $_POST['pass_new'];
		

		if (!preg_match('/^[a-zа-я0-9\s]{2,50}+$/ui',$name)){
			$result['errors']['name'] = 'Только анг., рус. буквы, цифры, пробелы, длина 2-50 симв.';
		}
		if (!is_email($email)){
			$result['errors']['email'] = 'Некорректный email';
		}
		if (!is_tel($phone)){
			$result['errors']['phone'] = 'Некорректный телефон';
		}
		if (!empty($pass_new) or !empty($pass_old)){
			if (strlen($pass_old) > 50 or strlen($pass_old) < 6){
				$result['errors']['pass_old'] = 'Длина пароля от 6 до 50 символов';
			}
			if (strlen($pass_new) > 50 or strlen($pass_new) < 6){
				$result['errors']['pass_new'] = 'Длина пароля от 6 до 50 символов';
			}
			if ($pass_new == $pass_old){
				$result['errors']['pass_new'] = 'Новый пароль должен отличаться';
			}
		}
		
		if (empty($result['errors'])){
			$user2 = mysqli_query($link,'select * from z_users where (tel="'.$phone.'" or email = "'.$email.'") and user_id != '.$user['user_id'].' limit 1');
			$user2 = mysqli_fetch_assoc($user2);
			if (empty($user2['user_id'])){
				$hash = pass_hash($email,$user['pass']);
				$pass = $user['pass'];
				if (!empty($pass_new) or !empty($pass_old)){	#проверяем и меняем пароль
					$hash = pass_hash($email,$pass_old);
					if (is_user($hash)){	#старый пароль верный
						$hash = pass_hash($email,$pass_new);
						$pass = $pass_new;
					}else{
						$result['errors']['pass_old'] = 'Неверный старый пароль';
					}
				}
				if (empty($result['errors'])){
					$hash_ = pass_hash($hash);
					$name = mysqli_real_escape_string($link,$name);
					$email = mysqli_real_escape_string($link,$email);
					$phone = mysqli_real_escape_string($link,$phone);
					$pass = mysqli_real_escape_string($link,$pass);
					$ip = mysqli_real_escape_string($link,$_SERVER['REMOTE_ADDR']);
					mysqli_query($link,'update z_users set pass = "'.$pass.'", hash = "'.mysqli_real_escape_string($link,$hash_).'", name = "'.$name.'", email = "'.$email.'", tel = "'.$phone.'", ip = "'.$ip.'", t = '.time());
					if (mysqli_affected_rows($link) > 0){
						$result['success'] = 'ok';
						$result['msg'] = 'Сохранено';
						ob_clean();
						login($hash);
					}else{
						$result['errors']['s'] = 'ошибка';
						$result['msg'] = 'Ошибка! Обновите страницу';
						ob_clean();
						login($hash);
					}
				}
			}else{
				if ($phone == $user2['tel']) $result['errors']['phone'] = 'Телефон уже зарегистрирован';
				if ($email == $user2['email']) $result['errors']['email'] = 'Email уже зарегистрирован';
			}
		}
		
		print_r(json_encode_true($result));
	exit;
	case 'ad_active':
	case 'ad_deactive':
		if (!is_user()) exit;
		$result = array();
		$ad_id = intval($_POST['ad_id']);
		$active = 1;
		if ($TYPE == 'ad_deactive') $active = 2;
		if (!empty($ad_id)){
			$ad = mysqli_query($link,'select ad_id from z_ads where ad_id = '.$ad_id.' and user_id = '.$user['user_id'].' and active != 0');
			$ad = mysqli_fetch_assoc($ad);
			if (!empty($ad['ad_id'])){
				mysqli_query($link,'update z_ads set active = '.$active.' where ad_id = '.$ad_id);
			}
		}
		$result['location'] = '';
		print_r(json_encode_true($result));
	exit;
	case 'ad_up':
		if (!is_user()) exit;
		$result = array();
		$ad_id = intval($_POST['ad_id']);
		if (!empty($ad_id)){
			$ad = mysqli_query($link,'select ad_id from z_ads where ad_id = '.$ad_id.' and user_id = '.$user['user_id'].' and active != 0');
			$ad = mysqli_fetch_assoc($ad);
			if (!empty($ad['ad_id'])){
				$t = time();
				$t_up = $ad['t'] + $config['ad_time_up'] - $t;
				if ($t_up < 0){
					mysqli_query($link,'update z_ads set t = '.$t.' where ad_id = '.$ad_id);
					$result['location'] = '';
				}else{
					$result['result'] = 'error';
					$result['msg'] = 'Поднять можно через '.time_late($t+$t_up);
				}
			}
		}
		
		print_r(json_encode_true($result));
	exit;
	case 'adminka':
		if (!is_admin()) exit;
		$result = array();
		$act = $_POST['act'];
		switch ($act){
			case 'ad_active':
			case 'ad_deactive':
			case 'ad_recovery':
			case 'ad_remove':
				$ad_id = intval($_POST['ad_id']);
				if ($act == 'ad_active') $active = 1;
				if ($act == 'ad_deactive') $active = 2;
				if ($act == 'ad_recovery') $active = 1;
				if ($act == 'ad_remove') $active = 0;
				mysqli_query($link,'update z_ads set active = '.$active.' where ad_id = '.$ad_id);
				$result['location'] = '';
				$result['result'] = 'success';
				$result['msg'] = 'Статус '.$active;
			break;
			case 'ad_up':
				$ad_id = intval($_POST['ad_id']);
				mysqli_query($link,'update z_ads set t = '.time().' where ad_id = '.$ad_id);
				$result['result'] = 'success';
				$result['msg'] = 'Поднято';
			break;
			case 'ad_fix':
				$ad_id = intval($_POST['ad_id']);
				$t_fix = intval($_POST['t_fix']);
				if (!empty($t_fix)){
					$t = time()+$t_fix*60;
					mysqli_query($link,'update z_ads set t_fix = '.$t.' where ad_id = '.$ad_id);
					#$result['location'] = '';
					$result['result'] = 'success';
					$result['msg'] = 'Закреплено до '.d($t);
				}
			break;
		}
		print_r(json_encode_true($result));
	exit;
}
























