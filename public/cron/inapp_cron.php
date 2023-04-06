<?php
//mail("nidhi@ninehertzindia.com","test","test message");
$data = file_get_contents('http://dev9server.com/cena-dev/api/v1/cron-payment-success');
$data = file_get_contents('http://dev9server.com/cena-dev/api/v1/cron-expire-free-subscription');
echo $data; 
die;
?>