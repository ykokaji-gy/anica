<?php
require_once '/home/ykokaji/git/anica/gen/lib/common.inc';

// 中身みたいjsonのパス
$jsonPath = '/home/ykokaji/git/anica/gen/data/thread/anime2.json';

$json = file_get_contents($jsonPath, true);
var_dump(json_decode($json));


?>
