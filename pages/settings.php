<?php
if(!$HACKER) exit;

$title = 'Настройки учетной записи '.$config['domain_ru'];
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

	<div class="form-group" data-group="name">
		<label class="col-sm-2 control-label"><i class="fa fa-user"></i> Моё имя</label>
		<div class="col-sm-4">
			<input class="form-control" id="settings_name" maxlength="50"  type="text" placeholder="Мое имя" value="<?=htmlspecialchars($user['name'])?>"/>
			<small class="help-block"></small>
		</div>
	</div>
	<div class="form-group" data-group="email">
		<label class="col-sm-2 control-label"><i class="fa fa-at"></i> Почта</label>
		<div class="col-sm-4">
			<input class="form-control" id="settings_email" maxlength="200"  type="text" placeholder="mymail@domen.com" value="<?=htmlspecialchars($user['email'])?>"/>
			<small class="help-block"></small>
		</div>
	</div>
	<div class="form-group" data-group="phone">
		<label class="col-sm-2 control-label"><i class="fa fa-phone"></i> Телефон</label>
		<div class="col-sm-4">
			<input class="form-control" id="settings_phone" maxlength="20"  type="text" placeholder="7 924 1234567"  value="<?=htmlspecialchars($user['tel'])?>"/>
			<small class="help-block"></small>
		</div>
	</div>
	<hr/>
	
			<div class="form-group" data-group="pass_old">
				<label class="col-sm-2 control-label"><i class="fa fa-lock"></i> Старый пароль</label>
				<div class="col-sm-4">
					<input class="form-control" id="settings_pass_old" maxlength="50"  type="password" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="старый пароль от аккаунта"/>
					<small class="help-block"></small>
				</div>
			</div>
			<div class="form-group" data-group="pass_new">
				<label class="col-sm-2 control-label"><i class="fa fa-lock"></i> Новый пароль</label>
				<div class="col-sm-4">
					<input class="form-control" id="settings_pass_new" maxlength="50"  type="text" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="мин. 6 символов"/>
					<small class="help-block"></small>
				</div>
			</div>
				<div class="col-lg-offset-2">
		<button class="btn btn-primary btn-lg" id="settings_save_btn" onclick="settings_save($(this))">
			<i class="fa fa-save fa-lg"></i> Сохранить
		</button>
	</div>
	
	<br><br>
	<a href="/logout.php"><i class="fa fa-sign-out-alt"></i> Выйти из аккаута</a>
</div>
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