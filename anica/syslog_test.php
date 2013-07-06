<?php
/**
 * syslogクラス
 * /home/y/logs/yjfort/syslog にログが吐かれる
 *
 * @access static
 * @param string $sMessage ログに吐くメッセージ
 *
 * @author $Author: ykokaji $
 * @date $Date: 2011-11-09 11:04:25 $
 **/

class Tset_Syslog
{

    // システムが利用できなくなった（ぐらい緊急度が高い）
    static public function emerg($sMessage)
    {
        openlog("", LOG_PID, LOG_LOCAL7);
        syslog(LOG_EMERG, "[$sMessage]");
        closelog();
    }

    // 直ちに修正されるべき状態
    static public function alert($sMessage)
    {
        openlog("", LOG_PID, LOG_LOCAL7);
        syslog(LOG_ALERT, "[$sMessage]");
        closelog();
    }

    // 緊急状態
    static public function crit($sMessage)
    {
        openlog("", LOG_PID, LOG_LOCAL7);
        syslog(LOG_CRIT, "[$sMessage]");
        closelog();
    }

    // 標準エラー
    static public function err($sMessage)
    {
        openlog("", LOG_PID, LOG_LOCAL7);
        syslog(LOG_ERR, "[$sMessage]");
        closelog();
    }

    // 警告メッセージ
    static public function warning($sMessage)
    {
        openlog("", LOG_PID, LOG_LOCAL7);
        syslog(LOG_WARNING, "[$sMessage]");
        closelog();
    }

    // お知らせ
    static public function notice($sMessage)
    {
        openlog("", LOG_PID, LOG_LOCAL7);
        syslog(LOG_NOTICE, "[$sMessage]");
        closelog();
    }

    // 情報メッセージ
    static public function info($sMessage)
    {
        openlog("", LOG_PID, LOG_LOCAL7);
        syslog(LOG_INFO, "[$sMessage]");
        closelog();
    }

    // デバックメッセージ
    static public function debug($sMessage)
    {
        openlog("", LOG_PID, LOG_LOCAL7);
        syslog(LOG_DEBUG, "[$sMessage]");
        closelog();
    }

}


// 使用例
Tset_Syslog::debug('test message');

// ログ吐き出し例
//Nov 9 19:53:28 <local7.debug> dev22-jail05 [2133]: [test message]

?>
