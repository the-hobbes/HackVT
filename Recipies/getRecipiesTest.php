<?php

require_once('getRecipies.php');

$recipies = new GetRecipies();
$recipies->set_ingredients(array('raspberries'));
$recipies->query_recipe();
?>