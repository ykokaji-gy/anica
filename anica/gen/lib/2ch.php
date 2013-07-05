<?php
require_once 'common.inc';
/**
 * C2ch
 * 2chジェネレートクラス
 *
 * @autor ykokaji
 */

// test
$obj2ch = new C2ch();
$boardType = "buzz";
$boardIdList = array('anime', 'anime2');
//$boardIdList = array('livenhk', 'liveetv', 'liventv', 'livetbs', 'livecx');
$animeList = array(
    '俺の妹がこんなに可愛いわけがない。',
    'ローゼンメイデン',
    'とある科学の超電磁砲S',
    'Free！',
    'ダンガンロンパ',
    );
//var_dump($obj2ch->get2chBoardUrl($boardType));
//var_dump($obj2ch->get2chThreadList($boardType, $boardIdList));
//var_dump($obj2ch->get2chThreadContents($boardIdList));
var_dump($obj2ch->buzzCount('5', '2013/07/05 20:47:20'));

class C2ch
{


    /**
     * __construct
     *
     * コンスタラクタ
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
    }


    /**
     * get2chBoardUrl
     *
     * 2chのアニメbuzz系、実況系板のURLを取得してデータ保存する
     *
     * @access public
     * @param  string $boardType          buzz系 or 実況系
     * @return bool   true or false
     *
     */
    public function get2chBoardUrl($boardType)
    {
        // 2chのメニューを取得
        $html = file_get_contents(BBS_2CH_ALL_MENU_URL);
        if ($html === false) {
            return false;
        }

        // UTF8に変換
        $html = mb_convert_encoding($html, 'utf8', 'sjis-win');

        // リンクを配列に入れる
        preg_match_all('/<A HREF=.*>.*<\/A>/', $html, $linkList);

        // 多次元配列をシングルに
        $linkList = $linkList[0];

        // 2ch.netのリンクを抽出する
        $i = 0;
        foreach($linkList as $link){
            if(preg_match('{<A HREF=http:\/\/(.*).2ch.net\/(.*)\/>}',$link)){
                // URL部分とリンクの文字を取得　$res[$i][0]にURL　$res[$i][1]に板名
                if(preg_match_all('/<A HREF=(\S*)>(.*)<\/A>/',$link,$match,PREG_SET_ORDER)){
                    $res[$i][0] = $match[0][1];
                    $res[$i][1] = $match[0][2];
                    $i++;
                }
            }
        }
        $boardNum = count($res);

        // バズ or 実況
        if ($boardType == 'buzz') {
            $jsonPath = BUZZ_2CH_BOARD_URL_JSON_PATH;
        } else if ($boardType == 'jk') {
            $jsonPath = JK_2CH_BOARD_URL_JSON_PATH;
        }

        // ファイル存在チェック
        if (!file_exists($jsonPath)) {
            if (!touch($jsonPath)) {
                // file touch error
            }
        }

        // 既存の板(json)との整合性確認
        $json = file_get_contents($jsonPath, true);
        $objJson = json_decode($json);
        $updateBoardList = array();
        foreach ($objJson as $boardID => $boardUrl) {

            // 全板のリストから抽出
            $i = 1;
            foreach($res as $link){
                $name = $link[1];   // 板名
                $url = $link[0];    // URL
                // 板IDだけを独立して取得
                preg_match('{2ch.net/(.*)/$}',$url,$ch);
                $id = $ch[1];       // 板ID

                if ($boardID == $id) {
                    if ($boardUrl != $url) {
                        // URLが変わっていた場合
                        $boardUrl = $url;
                    }
                    $updateBoardList[$boardID] = $boardUrl;
                    break;
                } else {
                    $i++;
                }

                if ($i == $boardNum) {
                    // error mail
                }
            }
        }

        // json書き込み
        $updateBoardList = json_encode($updateBoardList);
        $fp = fopen($jsonPath, 'w');
        fwrite($fp, $updateBoardList);
        fclose($fp);

        return true;

    }

    /**
     * get2chThreadList
     *
     * 2chのアニメbuzz系、実況系スレッドの情報を取得してデータ保存する
     *
     * @access public
     * @param  string $boardType          buzz系 or 実況系
     * @param  string $boardIdList        掲示板ID
     * @return bool   true or false
     *
     */
    public function get2chThreadList($boardType, $boardIdList = array())
    {

        if ($boardType == 'buzz') {
            $jsonPath = BUZZ_2CH_BOARD_URL_JSON_PATH;
        } else if ($boardType == 'jk') {
            $jsonPath = JK_2CH_BOARD_URL_JSON_PATH;
        }

        $json = file_get_contents($jsonPath, 'true');
        $json = json_decode($json);
        // 板ごとに回す
        foreach ($boardIdList as $boardId) {
            // 板のスレッド一覧
            $subjectUrl = $json->{$boardId} . 'subject.txt';
            $threadList = fopen($subjectUrl, 'r');

            if ($threadList) {
                $i = 0;
                $threadDataList = array();

                // スレッド一覧をあるだけ回す
                while (!feof($threadList)) {
                    $i++;
                    // 1行ずつ読む
                    $threadLine = fgets($threadList);
                    // UTF-8変換
                    $threadLine = mb_convert_encoding($threadLine, 'utf8', 'sjis-win');

                    // スレIDの取得
                    $threadIdNum = mb_strpos($threadLine, '.dat<>');
                    $threadId    = mb_substr($threadLine, 0, $threadIdNum);

                    // レス数の取得
                    $last  = mb_strrpos($threadLine, ')') - 1;        // 最後に)の出る場所
                    $first = mb_strrpos($threadLine, ' (') + 1;       // 最後に(の出る場所
                    $n     = $last - $first;
                    $num   = mb_substr($threadLine, $first + 1, $n);

                    // スレ名取得
                    $name       = $first - 7 - $threadIdNum;                       // 7は「.dat<>」の文字数
                    $threadName = mb_substr($threadLine, $threadIdNum + 6, $name); // 6は「.dat<>」の文字数

                    // jsonに保存するデータ
                    $threadDataList[$i] = array(
                        'threadId'   => $threadId,
                        'threadName' => $threadName,
                        'num'        => $num
                        );

                }

                // 板ごとにjsonに書き込み
                $threadDataJsonPath = GEN_DATA_THREAD_DIR . $boardId . '.json';
                // ファイル存在チェック
                if (!file_exists($threadDataJsonPath)) {
                    if (!touch($threadDataJsonPath)) {
                        // file touch error
                    }
                }
                $threadDataList = json_encode($threadDataList);
                $fp = fopen($threadDataJsonPath, 'w');
                fwrite($fp, $threadDataList);
                fclose($fp);

            } else {
                // fopen error
                return false;
            }
        }
        return true;
    }

    /**
     * get2chThreadContents
     *
     * 2chのアニメ実況系スレッドの内容を取得、データ生成
     *
     * @access public
     * @param  string $boardList        実況板のID
     * @return bool   true or false
     *
     */
    public function get2chThreadContents($boardList)
    {
        
        return "hoge";
    }

    /**
     * 
     *
     * 2chのアニメbuzz系、実況系スレッドの勢いを取得
     *
     * @access public
     * @param  string $boardtype        buzz系 or 実況系
     * @return array  $ikioiList        アニメ毎の勢い
     *
     */
    public function get2chThreadIkioi($boardtype)
    {
        return "hoge";
    }

    /**
     * buzzCount
     *
     * 2chの勢いを計算
     *
     * @access public
     * @param  string $num       スレッドのレス数
     * @param  string $touchTime スレッドの立った時間（YYYY/MM/dd hh:mm:ss）
     * @return string $count  勢い数
     */
    public function buzzCount($num, $touchTime)
    {
        // スレたった時間
        $touchTime = date('U', strtotime($touchTime));
        // 今の時間
        $now = date('U');
        // スレがたってからの経過時間
        $time = (int)$now - (int)$touchTime;
        // 勢い計算
        $num = 40;
        $time = 4000;
        $count = (int)$num * 86400 / (int)$time;

        // 書き込み数5未満→勢最大勢い10　書き込み数10未満→勢最大勢い100に調整
        if ($num < 5 && $count >= 10) {
            $count = 10;
        } else if ($num < 10 && $count >= 100) {
            $count = 100;
        }

        return $count;
    }

}




?>
