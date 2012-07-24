<?php
/**
 * 业务逻辑相关的常量都定义在此.
 *
 * 采用接口，而非class，是因为任何类都可以实现本接口，从而自动获取这些常量咯
 *
 * 同时，这些常量，也是事实上的规范
 *
 * @category
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
interface DHttp_Constant
{

    // http reponse status code,只是常用的部分
    const
        SC_OK = 200,
        SC_CREATED = 201,
        SC_NON_AUTHORITATIVE_INFORMATION = 203,
        SC_NO_CONTENT = 204,

        SC_MOVED_PERMANENTLY = 301,
        SC_MOVED_TEMPORARILY = 302,
        SC_NOT_MODIFIED = 304,

        SC_BAD_REQUEST = 400,
        SC_UNAUTHORIZED = 401,
        SC_FORBIDDEN = 403,
        SC_NOT_FOUND = 404,
        SC_METHOD_NOT_ALLOWED = 405,

        SC_INTERNAL_SERVER_ERROR = 500,
        SC_NOT_IMPLEMENTED = 501,
        SC_SERVICE_UNAVAILABLE = 503,
        SC_HTTP_VERSION_NOT_SUPPORTED = 505;

    // response header keywords
    const
        HDR_LOCATION = 'Location',
        HDR_SET_COOKIE = 'Set-Cookie',
        HDR_CONTENT_LENGTH = 'Content-Length',
        HDR_LAST_MODIFIED = 'Last-Modified',
        HDR_EXPIRES = 'Expires',
        HDR_CACHE_CONTROL = 'Cache-Control',
        HDR_PRAGMA = 'Pragma',
        HDR_ETAG = 'ETag',
        HDR_CONTENT_TYPE = 'Content-Type';

    const
        DEFAULT_CHARSET = 'UTF-8',
        DEFAULT_CONTENT_TYPE = 'text/html';

    const
        CONTENT_TYPE_HTML = 'text/html',
        CONTENT_TYPE_XML = 'text/xml',
        CONTENT_TYPE_JS = 'text/javascript',
        CONTENT_TYPE_TXT = 'text/plain',
        CONTENT_TYPE_STREAM = 'application/octet-stream',
        CONTENT_TYPE_JSON = 'application/json';

    // 所有的cookie key都定义在此，防止滥用，DCore_Response也实现了类似效果
    const
        COOKIE_SESSION_SERVERID = 'SERVERID',   // haproxy设置的，用于session sticky
        COOKIE_SESSION_USER = '_user',          // 当前已登录用户的session
        COOKIE_SESSION_WPRESENSE = 'wpresense', // web im
        COOKIE_SESSION_ONLINENUM = 'onlinenum', // 在线好友数
        COOKIE_SESSION_REF = '_ref';            // 唯一标识一个用户会话

    const
        COOKIE_VID = '_vid',                    // log_kaixin001.js.gb 设置的，用于分析click stream
        COOKIE_REG_GOTO = 'wizard_goto',        // 完成注册流程后，跳到哪个页面
        COOKIE_UID = '_uid',                    // 当前浏览器的最近登录用户uid，保存1年
        COOKIE_WPRESENCE = 'wpresence',         // DServerMan生成的值，CPresence设定的cookie
        COOKIE_PREEMAIL = 'preemail';

    // Cross-site request forgery related
    const
        CSRF_TOKEN = 'csrf_token',
        CSRF_KEY = 'csrf_key';

    // request attribute related
    const
        REQ_ATTR_JSON = 'json';

    // 具有全局特定含义的url参数名称
    const
        REQ_PARAM_PAGE_START = 'start', // 统一的分页的pageNo，从0开始
        REQ_PARAM_PAGE_NUM = 'num', // 统一的分页的pageLimit
        REQ_PARAM_AJAX = '_ajax',
        REQ_PARAM_UID = 'uid', // 被访者uid
        REQ_PARAM_FROM = 'from', // 源url，用于link click stream，分析用户行为
        REQ_PARAM_DEBUG_XHPROF = '_xhprof_'; // 用于在测试环境下打开xhprof输出

    /**
     * 在对GET、POST值合法性检查过程中，如果发现非法的，则会自动把该参数的值设置为本常量.
     */
    const PARAM_INVALID_VALUE = '_changeMeIfPossible_';

    /**
     * 是否允许外部地址来的POST请求?
     */
    const BIZ_EXTERNAL_POST_ENABLED = 'http.post.externl.enabled';

    // module names
    const
        MODULE_REG = 'reg',
        MODULE_LOGIN = 'login',
        MODULE_HOME = 'home',
        MODULE_VIDEO = 'video',
        MODULE_GAME = 'game',
        MODULE_FLASHGAME = 'flashgame',
        MODULE_TAEMBUY = 'teambuy',
        MODULE_NAVIGATOR = 'navigate';

    // 验证码相关
    const
        CAPTCHA_PARAM_KEYPE = 'keytype',
        CAPTCHA_PARAM_RCODE = 'rcode',          // 验证码在cache中的key值
        CAPTCHA_PARAM_CODE = 'code';            // 用户输入的值

    // 常用url相关
    const
        URL_ACCOUNT_DROPPED = '/s/alreadydel.html', // 帐号已删除
        URL_LOGOUT = '/login/logout.php', // 退出
        URL_STRANGER_LIMITED = '/s/strangerlimit.html',
        URL_REFRESH_LIMITED = '/interface/limittip.php',
        URL_404 = '/interface/404.php',
        URL_BLACKED_OUT = '/interface/privacy.php'; // 被人拉黑后跳转到

    // annotation related
    const
        ANNOTATION_OGNL = 'KHttp_Annotation_Ognl',
        ANNOTATION_PARAM = 'KHttp_Annotation_Param',
        ANNOTATION_POSTONLY = 'KHttp_Annotation_PostOnly';

    // friendship
    const
        FRIEND_NONE = 0,
        FRIEND_IDOL = 1,
        FRIEND_FANS = 2,
        FRIEND_MUTUAL = 3;

    // isStar的返回值
    const
        STAR_NONE = 0, // 普通人
        STAR_PERSON = 1, // 名人
        STAR_ORG = 2; // 机构

}
