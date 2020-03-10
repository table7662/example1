<?php
if(!$HACKER) exit;

$title = 'Регистрация на '.$config['domain_ru'];
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

<?=$form_reg?>

	<div class="col-lg-offset-2">
		<button class="btn btn-primary btn-lg" id="reg_btn" onclick="reg($(this))">
			<i class="fa fa-save fa-lg"></i> Регистрация
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