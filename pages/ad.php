<?php
if(!$HACKER) exit;
$is_user = false;
if (is_user() and $ad['user_id'] == $user['user_id']){
	$is_user = true;
}
if (($ad['active'] == 0 and !is_admin()) or ($ad['active'] == 2 and (!$is_user and !is_admin()))){
	error(404);
	exit;
}
$location_info = json_decode($location['info'],true);
$location_id = $location['location_id'];
$category_id = $category['category_id'];

$canonical = $config['site_url'].$ad_url;
$title = htmlspecialchars($ad['title']);
$ad_info = json_decode(gzdecode($ad['info']),true);
$descr = htmlspecialchars($ad_info['description']);
$img = max_img_url($ad_info['images'][0]);
if (mb_strpos($img,'nophoto')!==false){
	$img = '';
}else{
	$img = '<meta content="'.$img.'" property="og:image" />';
}
$seller = htmlspecialchars($ad_info['seller']['name']);

if (!empty($ad_info['price']['value']) and !empty($ad_info['price']['metric'])){
	$price_out = 'Цена: <span class="formatRub loaded-format-rub">'.htmlspecialchars($ad_info['price']['value']).'</span>
	<meta content="'.htmlspecialchars($ad_info['price']['value']).'" itemprop="price" /><meta content="RUB" itemprop="priceCurrency" />'.htmlspecialchars($ad_info['price']['metric']).'</li>';
}else{
	$price_out = 'Цена: <span class="formatRub loaded-format-rub">'.htmlspecialchars($ad_info['price']['value']).'</span>
	<meta content="0" itemprop="price" /><meta content="RUB" itemprop="priceCurrency" /></li>';
}
$tel = tel_avito($ad_info['contacts']['list'][0]['value']['uri']);
if (!empty($tel)){
	$tel = '+'.$tel;
	$tel_out = '<!--noindex-->Телефон:<a href="tel://'.$tel.'">'.$tel.'</a><!--/noindex-->';
}else{
	$tel_out = '';
}
$geo_parent = $location['title'];
if ($location['parent_location_id'] != $config['top_location_id']){
	$parent_location = mysqli_query($link,'select * from z_locations where location_id = '.$location['parent_location_id']);
	$parent_location = mysqli_fetch_assoc($parent_location);
	$parent_location_info = json_decode($parent_location['info'],true);
	$geo_parent = $parent_location['title'];
}


$i = 0;
$images = '';
foreach ($ad_info['images'] as $key => $val){
	$i++;
	$alt = htmlspecialchars($ad['title'].' купить на '.$config['domain_ru'].' - фотография № '.$i);
	$images .= '<img itemprop="image" src="'.max_img_url($val).'" title="'.$alt.'" alt="'.$alt.'">';
}
	
$title .= ' купить на '.$config['domain_ru'];
$descr_meta = htmlspecialchars(mb_substr($ad_info['description'],0,200));
$geo_1 = $ad_info['coords']['lat'].', '.$ad_info['coords']['lng'];
$geo_2 = $ad_info['coords']['lat'].':'.$ad_info['coords']['lng'];

$div_fix = '';
if ($ad['t_fix'] > time()){
	$div_fix = '<div class="btn btn-success ad_fix_btn">Закрепленное до '.d($ad['t_fix']).'</div><br>';
}

ad_view($ad_id);


require_once($_SERVER['DOCUMENT_ROOT'].'/includes/all.php');
?>


<!DOCTYPE html>
<html lang="ru">
<head>
	<?=$header?>
	<meta content="<?=$title?>" property="og:title" />
	<?=$img?>
	<meta content="<?=$canonical?>" property="og:url" />
	<meta content="<?=$descr_meta?>" name="description" />
	<meta content="<?=$descr_meta?>" property="og:description" />
	
	<meta content="<?=$geo_parent?>, <?=$location['title']?>" name="geo.placename" />
	<meta content="RU-<?=$geo_parent?>" name="geo.region" />
	<meta content="<?=$geo_1?>" name="ICBM" />
	<meta content="<?=$geo_2?>" name="geo.position" />
	
</head>
<body>
<div class="container"><div class="container_subwrapper">
<?=$menu?>
<ol class="breadcrumb" itemscope="itemscope" itemtype="http://schema.org/BreadcrumbList">
	
	<?php
	#крошки
		$i = 1;
		$breadcrumb = '';
		if (!empty($parent_location['location_id'])){
			$breadcrumb .= '
			<li class="small" itemprop="itemListElement" itemscope="itemscope" itemtype="http://schema.org/ListItem">
				<a href="/'.$parent_location['url'].'" itemprop="item">
					<span itemprop="name">'.$parent_location_info['names'][1].'</span>
				</a>
				<meta content="'.$i.'" itemprop="position" />
			</li>
			';
			$i++;
		}
		$breadcrumb .= '
		<li class="small" itemprop="itemListElement" itemscope="itemscope" itemtype="http://schema.org/ListItem">
			<a href="/'.$location['url'].'" itemprop="item">
				<span itemprop="name">'.$location_info['names'][1].'</span>
			</a>
			<meta content="'.$i.'" itemprop="position" />
		</li>
		';
		$i++;
		$breadcrumb .= '
		<li class="small" itemprop="itemListElement" itemscope="itemscope" itemtype="http://schema.org/ListItem">
			<a href="/'.$location['url'].'/'.$category['url'].'" itemprop="item">
				<span itemprop="name">'.$category['title'].' - '.$location_info['names'][1].'</span>
			</a>
			<meta content="'.$i.'" itemprop="position" />
		</li>
		';
		echo $breadcrumb;
	?>
   
	<li class="active">объявление № <?=$ad_id?></li>
</ol>
<?php
#пользователь
if (is_user() and $ad['user_id'] == $user['user_id']){
	$s = '<div class="ad_user"><h3>Это ваше объявление</h3>';
	if ($ad['active'] == 2) $s .= 'Объявление скрыто<br><div class="btn btn-sm btn-primary" onclick="user_ad($(this),\''.$ad['ad_id'].'\',\'ad_active\')">Опубликовать</div>';
	else $s .= '<div class="btn btn-sm btn-warning" onclick="user_ad($(this),\''.$ad['ad_id'].'\',\'ad_deactive\')">Снять с публикации</div>';
	$s .= '<br>';
	
	$t_up = $ad['t'] + $config['ad_time_up'] - time();
	if ($t_up < 0) $s .= '<div class="btn btn-sm btn-primary" onclick="user_ad($(this),\''.$ad['ad_id'].'\',\'ad_up\')">Поднять</div>';
	else $s .= '<div class="btn btn-sm btn-default disabled">Поднять через '.time_late(time()+$t_up).'</div>';
	$s .= '<br>';
	$s .= '</div>';
	echo $s;
}
#
#админ
if (is_admin()){
	$s = '<div class="ad_adminka"><h3>Админка</h3>';
	if ($ad['active'] == 2) $s .= 'Объявление скрыто<br><div class="btn btn-sm btn-primary" onclick="adminka($(this),\'act=ad_active&ad_id='.$ad_id.'\')">Опубликовать</div><br>';
	if ($ad['active'] == 1) $s .= '<div class="btn btn-sm btn-primary" onclick="adminka($(this),\'act=ad_deactive&ad_id='.$ad_id.'\')">Снять с публикации</div><br>';
	if ($ad['active'] == 0) $s .= 'Объявление удалено<br><div class="btn btn-sm btn-primary" onclick="adminka($(this),\'act=ad_recovery&ad_id='.$ad_id.'\')">Восстановить объяву</div><br>';
	else $s .= '<div class="btn btn-sm btn-primary" onclick="adminka($(this),\'act=ad_remove&ad_id='.$ad_id.'\')">Пометить как удаленное</div><br>';
	$s .= '<div class="btn btn-sm btn-primary" onclick="adminka($(this),\'act=ad_up&ad_id='.$ad_id.'\')">Поднять</div><br>';
	$s .= 'Время закрепа в минутах: <input type=text class="form-control" id="t_fix" style="width: 200px;" placeholder="-1 чтобы снять закреп">
		<div class="btn btn-sm btn-primary" onclick="adminka($(this),\'act=ad_fix&t_fix=\'+$(\'#t_fix\').val()+\'&ad_id='.$ad_id.'\')">Закрепить</div><br>';
	$s .= '</div>';
	echo $s;
}
#
?>
<div class="row" itemscope="" itemtype="http://schema.org/Product">
<div class="col-md-12 col-lg-5">
<meta content="<?=$ad_id?>" itemprop="productID" />
<meta content="<?=$cat_title?>" itemprop="model" />
<?=$div_fix?>
	<span class="small text-muted">
	<span style="display:none;" itemprop="name"><?=htmlspecialchars($ad['title'])?></span>
		<span class="time-container-action">Обновлено</span>
		<strong>
			<time class="smart_time" datetime="<?=d($ad['t'],'iso')?>"><?=d($ad['t'])?></time>
		</strong>
		Просмотров: всего
		<strong>
			<span title="Просмотры объявления за все время"><?=($ad['views']+1)?></span>
		</strong>
			</span>
	<div>
		<ul class="list-unstyled">
			<li class="lead" itemprop="offers" itemscope="" itemtype="http://schema.org/Offer">
			<?=$price_out?>
			<li>
				<div>
					<i class="fa fa-users"></i> <span><?=$seller?></span><br>
					<?=$tel_out?>
					<div class="small text-muted">Объявление № <?=$ad_id?> на сайте <?=$config['domain_ru']?></div>
				</div>
			</li>
			<li itemscope itemtype="http://schema.org/Place">Адрес:
				<?php
					$address = '<a href="/'.$location['url'].'" itemprop="name">'.$location_info['names'][1].'</a>				
						<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
							<meta itemprop="addressLocality" content="'.$location_info['names'][1].'" />
						</span>';
					if (!empty($tel)){
						$address .= '<meta itemprop="telephone" content="'.$tel.'" />';
					}
					if (!empty($parent_location['location_id'])){
						$address = '
						<a href="/'.$parent_location['url'].'" itemprop="name">'.$parent_location_info['names'][1].'</a>, 
						'.$address;
					}
					echo $address;
				?>
			</li>
			<li><b>Скажите продавцу, что нашли это объявление на <?=$config['domain_ru']?></b></li>
		</ul>
		<p itemprop="description" style="white-space: pre-line;"><?=$descr?></p>

	</div>
</div>
<div class="col-md-12 col-lg-7">
<div class="fotorama"
	data-allowfullscreen="native"
	data-nav="thumbs"
	data-loop="true"
>
<?=$images?>
</div>
</div>
<div class="col-xs-12 col-lg-12">
<hr>
	<span class="header_adv_long">Похожие объявления <i class="fa fa-arrow-down"></i></span><div class="clear"></div><br>
	<?php
	#выбор объяв
	$sql_locs = '
	and (
		al.location_id_1 = '.$location_id.'
		or al.location_id_2 = '.$location_id.'
		or al.location_id_3 = '.$location_id.'
		or al.location_id_4 = '.$location_id.'
		or al.location_id_5 = '.$location_id.'
		or al.location_id_6 = '.$location_id.'
	)
	';
	$sql_locs_join = 'join z_ads_locations al on al.ad_id = a.ad_id';

	$sql_cats = '
	and (
		ac.category_id_2 = '.$category_id.'
		or ac.category_id_1 = '.$category_id.'
		or ac.category_id_3 = '.$category_id.'
		or ac.category_id_4 = '.$category_id.'
		or ac.category_id_5 = '.$category_id.'
		or ac.category_id_6 = '.$category_id.'
	)
	';
	$sql_cats_join = 'join z_ads_categories ac on ac.ad_id = a.ad_id';

	$ads_sql = '
		select
			x.*,
			ai.info,
			l.url loc_url,
			l.parent_location_id,
			l.title loc_title,
			c.url cat_url,
			c.title cat_title
		from (
			select
				a.*
			from z_ads a
				'.$sql_cats_join.'
				'.$sql_locs_join.'
			where 1=1
				and a.category_id in ('.$cats_active.')
				and a.active = 1
				'.$sql_locs.'
				'.$sql_cats.'
				and a.ad_id != '.$ad_id.'
			order by
				a.t desc
			limit
				'.$config['ads_per_page_related'].'
		) x
			join z_ads_info ai on ai.ad_id = x.ad_id
			join z_locations l on l.location_id = x.location_id
			join z_categories c on c.category_id = x.category_id
	';
	$ads_ = mysqli_query($link,$ads_sql);
	$ads_c_now = mysqli_num_rows($ads_);
	$ads_out = ads_out($ads_);
	#
	if ($ads_c_now > 0)	echo '<ul class="list-unstyled">'.$ads_out.'</ul>';
	else echo '<h3>Нет похожих объявлений</h3>';
	?>
</div>
</div>
</div>

<footer>
<hr>
<?=$footer?>
<hr>
</footer>

</div>
</body>
</html>