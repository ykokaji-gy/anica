<?php
$touchtime = '2013/07/05 20:16:24';
$now = date('U');
$m = date('U', strtotime($touchtime));

var_dump($now);
var_dump($m);

var_dump($now-$m);

?>
