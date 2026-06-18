<?php
$f3=require_once '../fatfree-master/f3_lib_3.6.4/base.php';
//$f3=require_once '/home/ec2-user/external/fatfree-master/f3_lib_3.5/base.php';

$f3->config('config_prod.ini.php');
$f3->config('routes.ini.php');

/*600 seconds = 10min refresh cache*/
//$f3->route('GET /','PrepsController->index',600);

$f3->run();