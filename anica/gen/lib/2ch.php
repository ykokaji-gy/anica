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
$animeNameList = array(
    'a:001' => array('俺の妹がこんなに可愛いわけがない。', '俺妹', 'tbs'),
    'a:002' => array('ローゼンメイデン', 'ローゼン'),
    'a:003' => array('とある科学の超電磁砲S', 'レールガン', 'とある'),
    'a:004' => array('Free！'),
    'a:005' => array('ダンガンロンパ'),
    );
//var_dump($obj2ch->get2chBoardUrl($boardType));
//var_dump($obj2ch->get2chThreadList($boardType, $boardIdList));
//var_dump($obj2ch->get2chThreadContents($boardIdList, $animeNameList));



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
     * get2chBoardUredList->{'threadName'}
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

                    // スレURL生成
                    $threadDatUrl = $json->{$boardId} . 'dat/' . $threadId . '.dat';

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
                        'threadId'      => $threadId,
                        'threadDatUrl'  => $threadDatUrl,
                        'threadName'    => $threadName,
                        'num'           => $num
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
     * 2chのアニメbuzz系・実況系スレッドの内容を取得、勢いからデータ生成
     *
     * @access public
     * @param  array  $boardIdList        板のID
     * @param  array  $animeNameList      アニメ名や略称などのリスト（anime idがサブキー）
     * @param  int    $borderLineNum      勢いのある板の境界値
     * @return bool   true or false
     *
     */
    public function get2chThreadContents($boardIdList, $animeNameList, $borderLineNum=0)
    {
        mb_regex_encoding("UTF-8");
        $animeThreadList = array();

        // アニメ作品スレッド抽出
        foreach ($boardIdList as $boardId) {
            // 板ごとにjson読み込み
            $threadDataJsonPath = GEN_DATA_THREAD_DIR . $boardId . '.json';
            // ファイル存在チェック
            if (!file_exists($threadDataJsonPath)) {
                // not file exists
                break;
            }
            $threadJson = file_get_contents($threadDataJsonPath, true);
            $threadJson = json_decode($threadJson);
            // スレッド名に検索対象アニメ名・略称があるかcheck
            foreach ($threadJson as $threadList) {
                foreach ($animeNameList as $animeId => $animeWordList) {
                    foreach ($animeWordList as $animeWord) {
                        // スレ絞り込み
                        if (mb_eregi($animeWord, $threadList->{'threadName'})) {
                            $animeThreadList[$animeId][] = array(
                                'threadName'   => $threadList->{'threadName'},
                                'threadDatUrl' => $threadList->{'threadDatUrl'},
                                'num'          => $threadList->{'num'}
                            );
                            // 同じ作品でスレが重複しないようにbreak
                            break;
                        }
                    }
                }
            }
        }

        $threadContentLsit = array();
        // アニメ毎にスレッドのdatURLからスレが立った時間取得
        foreach ($animeThreadList as $animeID => $animeThreadInfoList) {
            foreach ($animeThreadInfoList as $key => $animeThreadInfo) {
                $tmp = $this->curlRequest2ch($animeThreadInfo['threadDatUrl']);

                // 過去ログに落ちてるのは除外
                if ($tmp['touchTime'] != 0) {
                    // 時間とレス数から勢い取得
                    $tmp['ikioi'] = $this->buzzCount($animeThreadInfo['num'], $tmp['touchTime']);
                    //$threadContentLsit[$animeID][$key] = $tmp;
                    $tmp['animeID'] = $animeID;
                    $tmp['num'] = $animeThreadInfo['num'];
                    $threadContentLsit[] = $tmp;
                }
            }
        }

        // 勢い順にソート
        foreach ($threadContentLsit as $key => $threadContent) {
            $key_id[$key] = $threadContent['ikioi'];
        }
        array_multisort($key_id, SORT_DESC, $threadContentLsit);

        return $threadContentLsit;
    }


    /**
     * buzzCount
     *
     * 2chの勢いを計算
     *
     * @access public
     * @param  string $num       スレッドのレス数
     * @param  int    $touchTime スレッドの立った時間（エポック時間からの通算秒数）
     * @return string $count  勢い数
     */
    public function buzzCount($num, $touchTime)
    {
        // 今の時間
        $now = date('U');
        // スレがたってからの経過時間
        $time = (int)$now - (int)$touchTime;
        // 勢い計算
        $count = (int)$num * 86400 / (int)$time;

        // 書き込み数5未満→勢最大勢い10　書き込み数10未満→勢最大勢い100に調整
        if ($num < 5 && $count >= 10) {
            $count = 10;
        } else if ($num < 10 && $count >= 100) {
            $count = 100;
        }

        return $count;
    }


    /**
     * curlRequest2chDat
     *
     * 2chDatファイル専用のcurlリクエスト用
     *
     * @access public
     * @param  string $datUrl             datファイルのURL
     * @return array  $threadContentLsit  スレ内容と新規取得か差分取得かのフラグ
     */
    public function curlRequest2ch($datUrl)
    {
        // datのファイル名
        preg_match('/\d{1,}.dat$/', $datUrl, $datNameList);
        // datの格納場所
        $logFilePath = GEN_DATA_DAT_DIR . $datNameList[0];

        $data = '';
        $flag = '0';
        $ch = curl_init();

        // dat取得用header生成
        $header[] = 'User-Agent: Monazilla/1.00 gyao.yahoo.co.jp';
        if(file_exists($logFilePath)){
            $time = filemtime($logFilePath);
            $mod = date("r", $time - 3600 * 9);
            $byte = filesize($logFilePath);
            $header[] = 'If-Modified-Since: '.$mod;
            $header[] = 'Range: bytes='.$byte.'-';
        } else {
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
            $flag = 'new';
        }
        $header[] = 'Connection: close';

        // curlいろいろ
        curl_setopt($ch, CURLOPT_URL, $datUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FILETIME, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_FILETIME, 1);
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        // dat取得
        $data = curl_exec($ch);
        //$data = mb_convert_encoding($data, 'utf8', 'sjis-win');
        // httpcode取得
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // Last-Modified取得
        $mod  = curl_getinfo($ch, CURLINFO_FILETIME);
        // Last-Modifiedをタイムスタンプに変換
        $t = strtotime($mod);
        if($t !== FALSE){
            $mod = $t;
        }
        if ($data != '' && $flag != 'new') {
            $flag = 'update';
        }

        // とりあえずerrorを取得
        $error = curl_error($ch);
        curl_close($ch);

        // datファイルをそのまま保存（差分取得用
        if ($flag != "0") {
            $fp = fopen($logFilePath, 'a');
            fwrite($fp, $data);
            fclose($fp);
        }

        // スレッドの立った時間を取得
        $fp = fopen($logFilePath, 'r');
        while (!feof($fp)) {
            $touchTime = 0;
            $threadName = '';
            // 1行ごとに正規表現使って分解
            $ikioiLine = fgets($fp);
            $ikioiLine = mb_convert_encoding($ikioiLine, 'utf8', 'sjis-win');
            $LineList = explode('<>', $ikioiLine);
            if (empty($LineList[2])) {
                continue;
            }
            $date = mb_substr($LineList[2], 0, 10);
            $time = mb_substr($LineList[2], 16, 8);
            $touchTime = date('U', strtotime($date . ' ' . $time));
            $threadName = $LineList[4];
            if ($touchTime !== FALSE || $touchTime != 0) {
                break;
            } else {
                // もし時間が不正な場合は初期化
                $touchTime = 0;
            }
        }
        fclose($fp);

        // スレッドURL
        $datUrlParse = explode('/', $datUrl);
        $threadId = explode('.', $datUrlParse[5]);
        $threadUrl = 'http://' . $datUrlParse[2] . '/test/read.cgi/' . $datUrlParse[3] . '/' . $threadId[0] . '/';


        // スレッド名、スレッドレス内容、更新フラグ、スレッドの立った時間
        $threadContentLsit = array(
                'threadName' => trim($threadName),
                'threadContents' => mb_convert_encoding($data, 'utf8', 'sjis-win'),
                'updateFlag' => $flag,
                'touchTime' => $touchTime,
                'threadUrl' => $threadUrl,);

        return $threadContentLsit;

    }

}




?>
