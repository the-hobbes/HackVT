<?php
$s = '2012-01-01';
for($i = 0; $i < 365; $i++){
	echo $s . '<br />';
	$s = date("Y-m-d",strtotime("+1 day", strtotime($s)));
}
echo $s;

echo implode(explode("-",'2012-01-01'));

?>

