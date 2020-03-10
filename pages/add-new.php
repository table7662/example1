<?php
if(!$HACKER) exit;

$title = $config['ad_add_new_title'].' на '.$config['domain_ru'];
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
<div class="form-horizontal">
<div class="form form-horizontal form-new-add">
	<div class="form-group" data-group="category">
		<label class="col-sm-2 control-label"><i class="fa fa-tag"></i> Категория</label>
		<div class="col-sm-6">
			<select class="form-control m-b" style="cursor:pointer;" id="add_category">
				<?php
				$s = '';
				$q_ = mysqli_query($link,'select * from z_categories where parent_category_id != 0 and active = 1 order by parent_category_id');
				while ($q = mysqli_fetch_assoc($q_)){
					$s .= '<option value="'.$q['category_id'].'">'.$q['title'].'</option>';
				}
				echo $s;
				?>
			</select>
			<small class="help-block"></small>
		</div>
	</div>
	<div class="form-group" data-group="title">
		<label class="col-sm-2 control-label"><i class="fa fa-tag"></i> Название</label>
		<div class="col-sm-6">
			<input class="form-control" id="add_title" maxlength="100"  type="text" placeholder="кратко суть"/>
			<small class="help-block"></small>
		</div>
	</div>
	<div class="form-group" data-group="description">
		<label class="col-sm-2 control-label"><i class="fa fa-bullhorn"></i> Описание</label>
		<div class="col-sm-6">
			<textarea class="form-control" id="add_description" placeholder="опишите подробнее" rows="6" maxlength="4000"></textarea>
			<small class="help-block"></small>
		</div>
	</div>
	<div class="form-group" data-group="price">
		<label class="col-sm-2 control-label"><i class="fa fa-tag"></i> Цена</label>
		<div class="col-sm-2">
			<input class="form-control" id="add_price1" maxlength="12"  type="text" style="padding-right:10px" placeholder="1000"/>
			<i class="fa fa-rub" style="position:absolute;right:22px;top:7px;font-size:20px;"></i>
			<label style="cursor:pointer;"><input class="user_name_changer_action" id="add_price2" name="price" type="checkbox"> БЕСПЛАТНО</label>
			<small class="help-block"></small>
		</div>
	</div>
	<div class="form-group" data-group="photo">
		<label class="col-sm-2 control-label"><i class="fa fa-camera-retro fa-lg"></i> Фото</label>
		<div class="col-sm-8">
			<form enctype="multipart/form-data" method="POST" onsubmit="photos_upload(event,$(this))">
				<label class="btn btn-success fileinput-button">
				<input onchange="$(this).parent().parent().submit()" type="file" accept="image/*" name="photo[]" class="hide" multiple>
				<i class="i fa fa-image"></i> <span>добавить</span>
				</label>
				<small class="help-block" style="display:block">разрешенные форматы: .jpg .jpeg .gif .png</small>
			</form>
			<div class="add_photos_div" id="add_photos_div"></div>
		</div>
	</div>
	<div class="form-group" data-group="location1">
		<label class="col-sm-2 control-label"><i class="fa fa-map"></i> Регион</label>
		<div class="col-sm-4">
			<select class="form-control m-b" style="cursor:pointer;" id="add_location1">
				<option value="0" selected>Выберите регион</option>
				<?php
					$s = '';
					$q_ = mysqli_query($link,'select * from z_locations where parent_location_id = '.$config['top_location_id'].' order by title');
					while ($q = mysqli_fetch_assoc($q_)){
						$s .= '<option value="'.$q['location_id'].'">'.$q['title'].'</option>';
					}
					echo $s;
				?>
			</select>
			<small class="help-block"></small>
		</div>
	</div>
	<div class="form-group" data-group="location2">
		<label class="col-sm-2 control-label"><i class="fa fa-map-marker"></i> Нас. пункт</label>
		<div class="col-sm-4">
			<select class="form-control m-b" style="cursor:pointer;" id="add_location2" disabled></select>
			<small class="help-block"></small>
		</div>
	</div>
	
	<hr />
<?php
if (!is_user()){
	echo $form_reg;
}
?>
	<div class="col-lg-offset-2">
		<button class="btn btn-primary btn-lg" id="add_new_btn" onclick="add_new($(this))">
			<i class="fa fa-plus fa-lg"></i> Создать
		</button>
	</div>
</div>
</div></div>

</div>

<footer>
<hr>
<?=$footer?>
<hr>
</footer>
</div>
</body>
</html>