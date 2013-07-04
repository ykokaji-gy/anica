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
$boardtype = "buzz";
$boardList = array('anime', 'anime2');
//var_dump($obj2ch->get2chBoardUrl($boardtype));
var_dump($obj2ch->get2chThreadUrl($boardType, $boardIdList));
//var_dump($obj2ch->get2chThreadContents($boardList));


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
            $jsonPath = BUZZ_2CH_URL_JSON_PATH;
        } else if ($boardType == 'jk') {
            $jsonPath = JK_2CH_URL_JSON_PATH;
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
                    var_dump($boardID);
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
     * get2chThreadUrl
     *
     * 2chのアニメbuzz系、実況系スレッドのURLを取得してデータ保存する
     *
     * @access public
     * @param  string $boardType          buzz系 or 実況系
     * @param  string $boardIdList        掲示板ID
     * @return bool   true or false
     *
     */
    public function get2chThreadUrl($boardType, $boardIdList = array())
    {
        if ($boardType ) {
        }
        return "hoge";
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

}




?>
