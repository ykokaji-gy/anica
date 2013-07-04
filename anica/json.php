<?php
// jsonにする

$a = array(
    'anime'    => 'http://toro.2ch.net/anime/',
    'anime2'   => 'http://ikura.2ch.net/anime2/',
    );

$a = json_encode($a);
$fp = fopen('gen/data/board/buzz_2chURL.json', 'w');
fwrite($fp, $a);
fclose($fp);
$json = file_get_contents('gen/data/board/buzz_2chURL.json',true);
var_dump(json_decode($json));

$b = array(
    'livenhk' => 'http://hayabusa2.2ch.net/livenhk/',
    'liveetv' => 'http://hayabusa2.2ch.net/liveetv/',
    'liventv' => 'http://hayabusa2.2ch.net/liventv/',
    'livetbs' => 'http://hayabusa2.2ch.net/livetbs/',
    'livecx' => 'http://hayabusa2.2ch.net/livecx/',
    'liveanb' => 'http://hayabusa2.2ch.net/liveanb/',
    'livetx' => 'http://hayabusa2.2ch.net/livetx/',
    'weekly' => 'http://hayabusa2.2ch.net/weekly/',
    'liveanime' => 'http://hayabusa.2ch.net/liveanime/',
    );

$b = json_encode($b);
$fp = fopen('gen/data/board/jk_2chURL.json', 'w');
fwrite($fp, $b);
fclose($fp);
$json = file_get_contents('gen/data/board/jk_2chURL.json',true);
var_dump(json_decode($json));

?>
