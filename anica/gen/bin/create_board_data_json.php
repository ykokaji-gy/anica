<?php
require_once '/home/ykokaji/git/anica/gen/lib/common.inc';
// jsonにする

$a = array(
    'anime'    => 'http://toro.2ch.net/anime/',
    'anime2'   => 'http://ikura.2ch.net/anime2/',
    );

$a = json_encode($a);
$fp = fopen(BUZZ_2CH_BOARD_URL_JSON_PATH, 'w');
fwrite($fp, $a);
fclose($fp);
$json = file_get_contents(BUZZ_2CH_BOARD_URL_JSON_PATH,true);
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
$fp = fopen(JK_2CH_BOARD_URL_JSON_PATH, 'w');
fwrite($fp, $b);
fclose($fp);
$json = file_get_contents(JK_2CH_BOARD_URL_JSON_PATH,true);
var_dump(json_decode($json));

?>
