<?php
#==============================================================================
# 2ch URL
#==============================================================================
// menuURL
define('BBS_2CH_ALL_MENU_URL', 'http://menu.2ch.net/bbsmenu.html');

#==============================================================================
# ディレクトリ定義
#==============================================================================
// bace
define('BASE_DIR', '/home/ykokaji/git/anica/');

// genディレクトリ
define('GEN_DIR', BASE_DIR . 'gen/');
// gen lib ディレクトリ
define('GEN_LIB_DIR', GEN_DIR . 'lib/');
// gen data ディレクトリ
define('GEN_DATA_DIR', GEN_DIR . 'data/');
// gen data 板情報格納ディレクトリ
define('GEN_DATA_BOARD_DIR', GEN_DATA_DIR . 'board/');
// gen data スレ情報格納ディレクトリ
define('GEN_DATA_THREAD_DIR', GEN_DATA_DIR . 'thread/');
// datファイル格納ディレクトリ
define('GEN_DATA_DAT_DIR', GEN_DATA_DIR . 'dat/');


// 板 buzz系 json
define('BUZZ_2CH_BOARD_URL_JSON_PATH', GEN_DATA_BOARD_DIR . 'buzz.json');
// 板 実況系 json
define('JK_2CH_BOARD_URL_JSON_PATH',   GEN_DATA_BOARD_DIR . 'jk.json');

// 共通ログディレクトリ


?>
