<?php
/**
 * 浏览器种类的抽象和实现.
 *
 * @package http
 * @subpackage
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class DHttp_UserAgent
{

    const PLATFORM_UNKNOWN = 'unknown';

    const PLATFORM_WINDOWS = 'Windows';
    const PLATFORM_WINDOWS_CE = 'Windows CE';
    const PLATFORM_APPLE = 'Apple';
    const PLATFORM_LINUX = 'Linux';
    const PLATFORM_OS2 = 'OS/2';
    const PLATFORM_BEOS = 'BeOS';
    const PLATFORM_IPHONE = 'iPhone';
    const PLATFORM_IPOD = 'iPod';
    const PLATFORM_IPAD = 'iPad';
    const PLATFORM_BLACKBERRY = 'BlackBerry';
    const PLATFORM_NOKIA = 'Nokia';
    const PLATFORM_FREEBSD = 'FreeBSD';
    const PLATFORM_OPENBSD = 'OpenBSD';
    const PLATFORM_NETBSD = 'NetBSD';
    const PLATFORM_SUNOS = 'SunOS';
    const PLATFORM_OPENSOLARIS = 'OpenSolaris';
    const PLATFORM_ANDROID = 'Android';

    /**
     * @var string
     */
    private $_ua;

    /**
     * @var string
     */
    private $_platform;

    /**
     * @param string $ua 'User-Agent:' header value
     */
    public function __construct($ua)
    {
        $this->_ua = $ua;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        if (!is_null($this->_platform))
        {
            return $this->_platform;
        }

        if (stripos($this->_ua, 'windows') !== false)
        {
            $this->_platform = self::PLATFORM_WINDOWS;
        }
        else if (stripos($this->_ua, 'iPad') !== false)
        {
            $this->_platform = self::PLATFORM_IPAD;
        }
        else if (stripos($this->_ua, 'iPod') !== false)
        {
            $this->_platform = self::PLATFORM_IPOD;
        }
        else if (stripos($this->_ua, 'iPhone') !== false)
        {
            $this->_platform = self::PLATFORM_IPHONE;
        }
        elseif (stripos($this->_ua, 'mac') !== false)
        {
            $this->_platform = self::PLATFORM_APPLE;
        }
        elseif (stripos($this->_ua, 'android') !== false)
        {
            $this->_platform = self::PLATFORM_ANDROID;
        }
        elseif (stripos($this->_ua, 'linux') !== false)
        {
            $this->_platform = self::PLATFORM_LINUX;
        }
        else if (stripos($this->_ua, 'Nokia') !== false)
        {
            $this->_platform = self::PLATFORM_NOKIA;
        }
        else if (stripos($this->_ua, 'BlackBerry') !== false)
        {
            $this->_platform = self::PLATFORM_BLACKBERRY;
        }
        elseif (stripos($this->_ua, 'FreeBSD') !== false)
        {
            $this->_platform = self::PLATFORM_FREEBSD;
        }
        elseif (stripos($this->_ua, 'OpenBSD') !== false)
        {
            $this->_platform = self::PLATFORM_OPENBSD;
        }
        elseif (stripos($this->_ua, 'NetBSD') !== false)
        {
            $this->_platform = self::PLATFORM_NETBSD;
        }
        elseif (stripos($this->_ua, 'OpenSolaris') !== false)
        {
            $this->_platform = self::PLATFORM_OPENSOLARIS;
        }
        elseif (stripos($this->_ua, 'SunOS') !== false)
        {
            $this->_platform = self::PLATFORM_SUNOS;
        }
        elseif (stripos($this->_ua, 'OS\/2') !== false)
        {
            $this->_platform = self::PLATFORM_OS2;
        }
        elseif (stripos($this->_ua, 'BeOS') !== false)
        {
            $this->_platform = self::PLATFORM_BEOS;
        }
        elseif (stripos($this->_ua, 'win') !== false)
        {
            $this->_platform = self::PLATFORM_WINDOWS;
        }
        else
        {
            $this->_platform = self::PLATFORM_UNKNOWN;
        }

        return $this->_platform;
    }

    /**
     * Is this request coming from a mobile device?
     *
     * iPad不算
     *
     * @return bool
     */
    public final function isMobile()
    {
        return preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $this->_ua)
            || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
                substr($this->_ua, 0, 4));
    }

    /**
     *
     * @param string $likeWhat User agent in lowercase
     *
     * @return bool
     */
    private function _isUserAgentLike($likeWhat)
    {
        $ua = strtolower($this->_ua);
        return CStr::contains($ua, strtolower($likeWhat));
    }

    /**
     * 当前请求是否来自iPad?
     *
     * @return bool
     */
    public final function isIpad()
    {
        return $this->_isUserAgentLike('ipad');
    }

    /**
     *
     * @return bool
     */
    public final function isIpod()
    {
        return $this->_isUserAgentLike('ipod');
    }

    /**
     *
     * @return bool
     */
    public final function isAndroid()
    {
        return $this->_isUserAgentLike('android');
    }

    /**
     * 获取android客户端的版本号.
     *
     * 只返回major.minor version number
     * e.g.
     * <p>
     * 如果版本是2.1-update1，则返回2.1
     * 如果版本是2.2.1，则返回2.2
     * </p>
     *
     * @return string False if not android client request
     */
    public final function getAndroidVersion()
    {
        if (!$this->isAndroid())
        {
            return false;
        }

        preg_match('/android\s*(\d\.(\d)+)/i', $this->_ua, $matches);
        return $matches[1];
    }

    /**
     * 当前请求是否来自iPhone?
     *
     * @return bool
     */
    public final function isIphone()
    {
        return $this->_isUserAgentLike('iphone');
    }

    /**
     * Is this a Flash request?
     *
     * @return bool
     */
    public final function isFlash()
    {
        return $this->_isUserAgentLike('shockwave flash');
    }

    /**
     * @return bool
     */
    public final function isMeeGo()
    {
        return $this->_isUserAgentLike('MeeGo');
    }

    /**
     * @return bool
     */
    public final function isUcWebClient()
    {
        return $this->_isUserAgentLike('UCWEB');
    }

    /**
     * Is this request coming from a bot or spider?
     *
     * 目前支持：google, msn, yahoo, etc
     *
     * @return bool
     */
    public final function isSpider()
    {
        $ua = strtolower($this->_ua);
        $knownSpiders = array(
            'baiduspider', // 百度
            'googlebot', // Goolgle
            'yodaobot', // 网易有道
            'sosoimagespider', // QQsoso图片
            'sogou', // 搜狗
            'msnbot', // MSN
            'slurp', // Yahoo
            'baidu-transcoder', // 百度代码
            'bot',
            'splider',
        );

        foreach ($knownSpiders as $spider)
        {
            if (false !== strpos($ua, $spider))
            {
                return true;
            }
        }

        return false;
    }

}
