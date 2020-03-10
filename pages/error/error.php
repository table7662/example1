<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/config.php');
if (empty($code)) $code = http_response_code();
http_response_code($code);


$title = 'Ошибка '.$code.' '.$config['domain_ru'];
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
<?=$search_form?>
<?=$main_locations?>
<br><br>
<h2 class="btn btn-default btn-lg" onclick="history.back();"><<= Вернуться назад</h2>

</div>

<footer>
<hr>
<?=$footer?>
<hr>
</footer>
</div>
</body>
</html>