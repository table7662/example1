<?php
if(!$HACKER) exit;
$location_id = $location['location_id'];
$location_info = json_decode($location['info'],true);
if (empty($category['category_id'])){
	$category_id = 0;
	$category_title = 'Все';
	$category_url = '';
}else{	#если выбрана категория - проверка, активная ли категория
	$category_id = $category['category_id'];
	$q = mysqli_query($link,'select * from z_categories where active = 1 and category_id = '.$category_id);
	$q = mysqli_fetch_assoc($q);
	if (empty($q['category_id'])){
		error(404);
		exit;
	}
	$category_title = $category['title'];
	$category_url = '/'.$category['url'];
}

$page = intval($_GET['page']);
if ($page <= 0) $page = 1;
$offset = ($page-1) * $config['ads_per_page'];

#выбор объяв
if ($location_id == $config['top_location_id']){
	$sql_locs = '';
	$sql_locs_join = '';
}else{
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
}
if ($category_id == 0){
	$sql_cats = '';
	$sql_cats_join = '';
}else{
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
}
#закрепы
$ads_sql_fix = '
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
			and a.t_fix > '.time().'
			'.$sql_locs.'
			'.$sql_cats.'
		order by
			rand()
		limit
			'.$config['ad_fixed_count_main'].'
	) x
		join z_ads_info ai on ai.ad_id = x.ad_id
		join z_locations l on l.location_id = x.location_id
		join z_categories c on c.category_id = x.category_id
';

$ads_fix_ = mysqli_query($link,$ads_sql_fix);
$ads_c_now_fix = mysqli_num_rows($ads_fix_);
if ($ads_c_now_fix > 0){
	$ads_out_fix_ = ads_out($ads_fix_,true);
	$ads_fix_ids_sql = ' and a.ad_id not in ('.implode(',',$ads_out_fix_[1]).') ';
	$ads_out_fix = $ads_out_fix_[0].'<li style="margin-bottom:40px;"></li>';
}else{
	$ads_out_fix = '';
	$ads_fix_ids_sql = '';
}

#
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
			'.$ads_fix_ids_sql.'
			'.$sql_locs.'
			'.$sql_cats.'
		order by
			a.t desc
		limit
			'.$config['ads_per_page'].'
		offset
			'.$offset.'
	) x
		join z_ads_info ai on ai.ad_id = x.ad_id
		join z_locations l on l.location_id = x.location_id
		join z_categories c on c.category_id = x.category_id
';

$ads_ = mysqli_query($link,$ads_sql);
$ads_c_now = mysqli_num_rows($ads_);
$ads_out = $ads_out_fix.ads_out($ads_);
#
$index2 = empty($sql_locs_join) ? ' use index(category_id_2) ' : '';
$ads_sql_c = '
	select
		count(1) c
	from z_ads a
		'.$index2.'
		'.$sql_cats_join.'
		'.$sql_locs_join.'
	where 1=1
		and a.category_id in ('.$cats_active.')
		and a.active = 1
		'.$sql_locs.'
		'.$sql_cats.'
';

$q = mysqli_query($link,$ads_sql_c);
$q = mysqli_fetch_assoc($q);
$ads_c = $q['c'];
if ($ads_c_now <= 0 and $page > 1){
	http_response_code(404);
}

$title_page = '';
if ($page > 1 and $ads_c_now > 0){
	$title_page = ' страница № '.$page;
}
#echo time_end();

$ads_c_descr = $ads_c.' объявлени'.number_end($ads_c,array('е','я','й')).' в базе '.$config['domain_ru'];

#
$titles[35] = 'Животные в '.$location_info['names'][6].' - купить птиц, домашних и сельскохозяйственных животных на '.$config['domain_ru'];
$titles[89] = 'Собаки и щенки в '.$location_info['names'][6].' - купить щенка немецкой овчарки, хаски, лабрадора, чихуахуа, Джек Рассел терьера, цены на '.$config['domain_ru'];
$titles[90] = 'Коты, кошки, котята в '.$location_info['names'][6].' - купить купить вислоухого, шотландского, британского, бенгальского котенка, мейн-куна, цены на '.$config['domain_ru'];
$titles[91] = 'Купить попугаев, кур, голубей в '.$location_info['names'][6].' продажа птиц на '.$config['domain_ru'];
$titles[92] = 'Аквариумы в '.$location_info['names'][6].' - купить рыбок, фильтры и насосы для аквариума на '.$config['domain_ru'];
$titles[93] = 'Экзотические животные и зоотовары для них в '.$location_info['names'][6].' - купить необычных домашних животных на '.$config['domain_ru'];
$titles[94] = 'Корма для собак и кошек в '.$location_info['names'][6].' - купить клетки, домики и переноски категории Товары для животных на '.$config['domain_ru'];
$titles[0] = $config['domain_ru'].' — бесплатные объявления в '.$location_info['names'][6].' — Объявления на сайте '.$config['domain'];
$titles[0] = $titles[35];

$descrs[35] = 'Объявления о продаже домашних и сельскохозяйственных животных, зоотовары: собаки, кошки, птицы, рыбки, грызуны по доступным ценам в '.$location_info['names'][6].' '.$ads_c.' объявлений. Купите качественные корма и товары для животных недорого на '.$config['domain_ru'];
$descrs[89] = 'Объявления о продаже взрослых собак и щенков: немецкие овчарки, лабрадоры, хаски, чихуахуа, Джек Рассел терьеры, бульдоги, шпицы по доступным ценам в '.$location_info['names'][6].' '.$ads_c.' объявления. Купите породистого щенка недорого на '.$config['domain_ru'];
$descrs[90] = 'Объявления о продаже взрослых кошек и котят: шотландские, вислоухие, британские, бенгальские, персидские коты, мейн-куны по доступным ценам в '.$location_info['names'][6].' '.$ads_c.' объявление. Купите породистого котенка недорого на '.$config['domain_ru'];
$descrs[91] = 'Объявления о продаже птиц: курицы-несушки, волнистые попугайчики, попугаи Жако, голуби, вороны по доступным ценам в '.$location_info['names'][6].' '.$ads_c.' объявлений. Купите породистую птицу недорого на '.$config['domain_ru'];
$descrs[92] = 'Объявления о продаже аквариумных рыбок: гуппи, розовые данио, неоны, сомики, меченосцы, барбусы по доступным ценам в '.$location_info['names'][6].' '.$ads_c.' объявлений. Купите рыб для аквариума недорого на '.$config['domain_ru'];
$descrs[93] = 'Объявления о продаже экзотических животных и товаров для них по доступным ценам в '.$location_info['names'][6].' '.$ads_c.' объявлений. Купите необычных животных, корма и зоотовары для них недорого на '.$config['domain_ru'];
$descrs[94] = 'Объявления о продаже товаров для животных: корма для собак, щенков, кошек, котят, ошейники, переноски, машинки для стрижки, поводки, шлейки, фурминаторы по доступным ценам в '.$location_info['names'][6].' '.$ads_c.' объявления. Купите зоотовары недорого на '.$config['domain_ru'];
$descrs[0] = $descrs[35];
#

$title = $titles[$category_id].$title_page;
$descr = $descrs[$category_id];

if (empty($category['title']))	$keywords = 'бесплатно, объявления, '.$location_info['names'][1];
else $keywords = 'бесплатно, объявления, '.$category['title'].', '.$location_info['names'][1];

if ($category_id != 0) $cu = '/'.$category['url'];
else $cu = '';
if (!empty($category_id) or $location_id != $config['top_location_id']) $lu = '/'.$location['url'];
else $lu = '';
$canonical = $config['site_url'].$lu.$cu;

require_once($_SERVER['DOCUMENT_ROOT'].'/includes/all.php');
?>


<!DOCTYPE html>
<html lang="ru">
<head>
	<?=$header?>
	<meta content="<?=$title?>" property="og:title" />
	<meta content="<?=$config['site_url']?>/logo.png" property="og:image" />
	<meta content="<?=$config['site_url'].$_SERVER['REQUEST_URI']?>" property="og:url" />
	<meta content="<?=$descr?>" name="description" />
	<meta content="<?=$descr?>" property="og:description" />
	<meta content="<?=$keywords?>" name="keywords" />

</head>
<body>
<div class="container"><div class="container_subwrapper">
<?=$menu?>
	<br><span style="font-size:17px;"><?=$ads_c_descr?></span>
	<?=$search_form?>
	<?=$main_locations?>

	
	<?php
	function temp_228($location_id,$category_url){
		global $link;
		$result = '';
		$q_= mysqli_query($link,'select * from z_locations where parent_location_id = '.$location_id.' order by title');
		$c = mysqli_num_rows($q_);
		if ($c > 0){
			while ($q = mysqli_fetch_assoc($q_)){
				$location_info = json_decode($q['info'],true);
				$result .= '<a href="/'.$q['url'].$category_url.'">'.$location_info['names'][1].'</a>, '.temp_228($q['location_id'],$category_url);
			}
		}
		return mb_substr($result,0,-2);
	}
	$s = '';
	if ($location_id != $config['top_location_id']) $s = temp_228($location_id,$category_url);
	if (!empty($s)) $s = ': '.$s;

	echo '
	<div class="panel panel-default" style="margin-top: 0px;">
		<div class="panel-body">
		<span class="text-muted small">
		<b></b>
		<b>Объявления <a href="/'.$config['top_location_url'].$category_url.'">'.$category_title.'</a> в <a href="/'.$location['url'].'">'.$location_info['names'][6].'</a></b>'.$s.'
	</span></div></div>
';
	?>
	
	
<?php
if ($ads_c_now <= 0){
	$ads_out = '<h3>Нет объявлений</h3><div class="btn btn-default btn-lg" onclick="history.back();"><= Назад</div>';
}else{
	$pagination = '<div class="row"><div class="col-xs-12"><ul class="pagination pull-left">';
	if ($page > 1) $pagination .= '<li><a href="/'.$location_url.$category_url.'?page='.($page-1).'">«</a></li>';
	for ($i = $page-5; $i <= $page+5; $i++){
		if ($i <= 0) continue;
		if ($i*$config['ads_per_page'] > $ads_c+$config['ads_per_page']) break;
		$li = '<li><a href="/'.$location_url.$category_url.'?page='.$i.'">'.$i.'</a></li>';
		if ($i == $page) $li = '<li class="disabled"><span class="selected">'.$i.'</span></li>';
		$pagination .= $li;
	}
	if ($offset+$config['ads_per_page'] < $ads_c) $pagination .= '<li><a href="/'.$location_url.$category_url.'?page='.($page+1).'">»</a></li>';
	$pagination .= '</ul></div></div>';
	$ads_out = '<p class="text-muted small"><i class="fa fa-list"></i> Показано объявлений <b>'.($offset+1).'-'.($offset+$config['ads_per_page']).'</b> (страница №<b>'.$page.'</b>).</p>
	<ul class="list-unstyled">
	'.$ads_out.'
	</ul>
	'.$pagination;
}
echo $ads_out;
?>
</div>

<footer>
<hr>
<?=$footer?>
<hr>
</footer>

</div>
</body>
</html>