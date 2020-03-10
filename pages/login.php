<?php
if(!$HACKER) exit;

$title = 'Авторизация на '.$config['domain_ru'];
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

	<div class="form-group" data-group="email">
		<label class="col-sm-2 control-label"><i class="fa fa-at"></i> Почта</label>
		<div class="col-sm-4">
			<input class="form-control" id="login_email" maxlength="200"  type="text" placeholder="email@domain.com"/>
			<small class="help-block"></small>
		</div>
	</div>

	<div class="form-group" data-group="pass">
		<label class="col-sm-2 control-label"><i class="fa fa-lock"></i> Пароль</label>
		<div class="col-sm-4">
			<input class="form-control" id="login_pass" maxlength="50"  type="password" placeholder="пароль от аккаунта"/>
			<small class="help-block"></small>
		</div>
	</div>

	<div class="col-lg-offset-2">
		<button class="btn btn-primary btn-lg" id="login_btn" onclick="login($(this))">
			<i class="fa fa-save fa-lg"></i> Войти
		</button>
	</div>
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