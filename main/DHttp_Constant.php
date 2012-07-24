<?php
/**
 * ҵ���߼���صĳ����������ڴ�.
 *
 * ���ýӿڣ�����class������Ϊ�κ��඼����ʵ�ֱ��ӿڣ��Ӷ��Զ���ȡ��Щ������
 *
 * ͬʱ����Щ������Ҳ����ʵ�ϵĹ淶
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

    // http reponse status code,ֻ�ǳ��õĲ���
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

    // ���е�cookie key�������ڴˣ���ֹ���ã�DCore_ResponseҲʵ��������Ч��
    const
        COOKIE_SESSION_SERVERID = 'SERVERID',   // haproxy���õģ�����session sticky
        COOKIE_SESSION_USER = '_user',          // ��ǰ�ѵ�¼�û���session
        COOKIE_SESSION_WPRESENSE = 'wpresense', // web im
        COOKIE_SESSION_ONLINENUM = 'onlinenum', // ���ߺ�����
        COOKIE_SESSION_REF = '_ref';            // Ψһ��ʶһ���û��Ự

    const
        COOKIE_VID = '_vid',                    // log_kaixin001.js.gb ���õģ����ڷ���click stream
        COOKIE_REG_GOTO = 'wizard_goto',        // ���ע�����̺������ĸ�ҳ��
        COOKIE_UID = '_uid',                    // ��ǰ������������¼�û�uid������1��
        COOKIE_WPRESENCE = 'wpresence',         // DServerMan���ɵ�ֵ��CPresence�趨��cookie
        COOKIE_PREEMAIL = 'preemail';

    // Cross-site request forgery related
    const
        CSRF_TOKEN = 'csrf_token',
        CSRF_KEY = 'csrf_key';

    // request attribute related
    const
        REQ_ATTR_JSON = 'json';

    // ����ȫ���ض������url��������
    const
        REQ_PARAM_PAGE_START = 'start', // ͳһ�ķ�ҳ��pageNo����0��ʼ
        REQ_PARAM_PAGE_NUM = 'num', // ͳһ�ķ�ҳ��pageLimit
        REQ_PARAM_AJAX = '_ajax',
        REQ_PARAM_UID = 'uid', // ������uid
        REQ_PARAM_FROM = 'from', // Դurl������link click stream�������û���Ϊ
        REQ_PARAM_DEBUG_XHPROF = '_xhprof_'; // �����ڲ��Ի����´�xhprof���

    /**
     * �ڶ�GET��POSTֵ�Ϸ��Լ������У�������ַǷ��ģ�����Զ��Ѹò�����ֵ����Ϊ������.
     */
    const PARAM_INVALID_VALUE = '_changeMeIfPossible_';

    /**
     * �Ƿ������ⲿ��ַ����POST����?
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

    // ��֤�����
    const
        CAPTCHA_PARAM_KEYPE = 'keytype',
        CAPTCHA_PARAM_RCODE = 'rcode',          // ��֤����cache�е�keyֵ
        CAPTCHA_PARAM_CODE = 'code';            // �û������ֵ

    // ����url���
    const
        URL_ACCOUNT_DROPPED = '/s/alreadydel.html', // �ʺ���ɾ��
        URL_LOGOUT = '/login/logout.php', // �˳�
        URL_STRANGER_LIMITED = '/s/strangerlimit.html',
        URL_REFRESH_LIMITED = '/interface/limittip.php',
        URL_404 = '/interface/404.php',
        URL_BLACKED_OUT = '/interface/privacy.php'; // �������ں���ת��

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

    // isStar�ķ���ֵ
    const
        STAR_NONE = 0, // ��ͨ��
        STAR_PERSON = 1, // ����
        STAR_ORG = 2; // ����

}
