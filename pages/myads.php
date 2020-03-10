<?php
if(!$HACKER) exit;

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
			join z_users_ads ua on ua.ad_id = a.ad_id
			join z_users u on u.user_id = ua.user_id
		where 1=1
			and a.category_id in ('.$cats_active.')
			and a.active in (1,2)
			and u.user_id = '.$user['user_id'].'
		order by
			a.t desc
	) x
		join z_ads_info ai on ai.ad_id = x.ad_id
		join z_locations l on l.location_id = x.location_id
		join z_categories c on c.category_id = x.category_id
';
$ads_ = mysqli_query($link,$ads_sql);
$ads_c_now = mysqli_num_rows($ads_);
$ads_out = ads_out($ads_);

$title = 'Мои объявления на '.$config['domain_ru'];
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/all.php');
?>


<!DOCTYPE html>
<html lang="ru">
<head>
	<?=$header?>
</head>
<body>

<div class="container"><div class="container_subwrapper">
<?=$menu?>

<div class="well well-sm">
<ul class="list-unstyled">
<?php
if ($ads_c_now<=0) echo '<h2>Вы не подавали объявлений</h2><br><a style="color: white !important;" class="btn btn-warning" href="/add-new"><i class="fa fa-plus fa-lg"></i> Подать объявление бесплатно</a>';
?>
<?=$ads_out?>
</ul>
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