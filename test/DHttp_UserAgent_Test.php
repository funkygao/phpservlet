<?php
/**
 *
 *
 * @package
 * @subpackage
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */

require_once('DHttp_TestBase.php');

class DHttp_UserAgent_Test extends DHttp_TestBase
{

    private $spiders;

    private $browserAgents;

    private $mobileAgents;

    protected function setUp()
    {
        $this->spiders = array(
            // 网易 yodao 有道
            'Mozilla/5.0 (compatible; YodaoBot/1.0; http://www.yodao.com/help/webmaster/spider/ ; )',
            // Yaodao其它
            'Mozilla/5.0 (compatible;YodaoBot-Reader/1.0;http://www.yodao.com/help/webmaster/spider/;1 subscriber;) ',
            // Google
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            // yahoo
            'Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp )',
            // Yahoo中国
            'Mozilla/5.0 (compatible; Yahoo! Slurp China; http://misc.yahoo.com.cn/help.html )',
            // baidu
            'Baiduspider+(+http://www.baidu.com/search/spider.htm)',
            // baidu代码
            'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322, Baidu-Transcoder/1.0.6.0, gate.baidu.com)',
            // msn
            'msnbot/1.1 (+http://search.msn.com/msnbot.htm) ',
            // sogou
            'Sogou Orion spider/3.0(+http://www.sogou.com/docs/help/webmasters.htm#07) ',
            // sogou2
            'Sogou web spider/3.0(+http://www.sogou.com/docs/help/webmasters.htm#07)',
            // QQsoso图片
            'Sosoimagespider+(+http://help.soso.com/soso-image-spider.htm) ',
        );

        $this->browserAgents = array(
            // IE 6-10
            'Mozilla/4.0 (compatible; MSIE 6.0b; Windows NT 5.1; DigExt)',
            'Mozilla/4.0 (compatible; U; MSIE 6.0; Windows NT 5.1) (Compatible; ; ; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)',
            'Mozilla/4.0 (compatible; MSIE 6.0b; Windows NT 5.0; YComp 5.0.0.0) (Compatible; ; ; Trident/4.0)',
            'Mozilla/4.0 (compatible; MSIE 6.1; Windows XP; .NET CLR 1.1.4322; .NET CLR 2.0.50727)',
            'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; c .NET CLR 3.0.04506; .NET CLR 3.5.30707; InfoPath.1; el-GR)',
            'Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.1; .NET CLR 1.1.4322; InfoPath.1)',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.    5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; Tablet PC 2.0; .NET4.0C; InfoPath.3)',
            'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; .NET CLR 2.7.58687; SLCC2; Media Center PC 5.0; Zune 3.4; Tablet PC 3.6; InfoPath.3)',
            'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 1.1.4322)',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; CIBA; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; Zune 4.7)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; FunWebProducts)',
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)',
            'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',

            // firefox
            'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20100101 Firefox/12.0',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.0.4) Gecko/2008102920 Firefox/3.0.4',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.12) Gecko/2008102920 Firefox/3.0.4',

            // safari
            ' Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-us) AppleWebKit/534.1+ (KHTML, like Gecko) Version/5.0 Safari/533.16',
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16',

            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; en-us) AppleWebKit/534.1+ (KHTML, like Gecko) ',
            'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.56 Safari/536.5',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.4 Safari/537.1',
            'Mozilla/5.0 (compatible; U; ABrowse 0.6; Syllable) AppleWebKit/420+ (KHTML, like Gecko)',
            'Mozilla/5.0 (compatible; MSIE 9.0; AOL 9.7; AOLBuild 4343.19; Windows NT 6.1; WOW64; Trident/5.0; FunWebProducts)',
            'Mozilla/4.08 (Charon; Inferno)',
            'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; SV1; Crazy Browser 9.0.04)',
            'Mozilla/5.0 (Windows; U; Windows NT 6.1; x64; fr; rv:1.9.2.13) Gecko/20101203 Firebird/3.6.13',
            'IBM WebExplorer /v0.94',
            'Mozilla/5.0 (compatible; IBrowse 3.0; AmigaOS4.0)',
            'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/533.1 (KHTML, like Gecko) Maxthon/3.0.8.2 Safari/533.1',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; Maxthon/3.0)',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; MyIE2; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0)',
            'NCSA Mosaic/3.0 (Windows 95)',
            'Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US) AppleWebKit/528.16 (KHTML, like Gecko, Safari/528.16) OmniWeb/v622.8.0.112941',
            'Opera/9.80 (Windows NT 6.1; U; es-ES) Presto/2.9.181 Version/12.00',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/534.55.3 (KHTML, like Gecko) Version/5.1.3 Safari/534.53.10',
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; TencentTraveler 4.0; Trident/4.0; SLCC1; Media Center PC 5.0; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30618)',
            'Wget/1.9+cvs-stable (Red Hat modified)',
            //'Lynx/2.8.8dev.3 libwww-FM/2.14 SSL-MM/1.4.1',

            // iPad
            'Apple-iPad',
            'Apple-iPad2C3',
            'Mozilla/5.0 (iPad; U; CPU OS 4_2_1 like Mac OS X; zh-cn) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148 Safari/6533.18.5',
            'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B367 Safari/531.21.10',
            'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.10',

            // WebOS HP Touchpad
            'Mozilla/5.0 (hp-tablet; Linux; hpwOS/3.0.0; U; en-US) AppleWebKit/534.6 (KHTML, like Gecko) wOSBrowser/233.70 Safari/534.6 TouchPad/1.0',
            'Mozilla/5.0 (hp-tablet; Linux; hpwOS/3.0.2; U; en-US) AppleWebKit/534.6 (KHTML, like Gecko) wOSBrowser/234.40.1 Safari/534.6 TouchPad/1.0',
        );

        $this->mobileAgents = array(
            'nokia/2.0',
            'symbian/2.0',
            'blackberry/2.0',
            'smartphone/2.0',
            'Nokia8310/1.0 (05.11) UP.Link/6.5.0.0.06.5.0.0.06.5.0.0.06.5.0.0.0',
            'Nokia2760/2.0 (06.82) Profile/MIDP-2.1 Configuration/CLDC-1.1',
            'Nokia3120Classic/2.0 (06.20) Profile/MIDP-2.1 Configuration/CLDC-1.1',
            'Mozilla/5.0 (SymbianOS/9.4; Series60/5.0 Nokia5230/40.0.003; Profile/MIDP-2.1 Configuration/CLDC-1.1 ) AppleWebKit/525 (KHTML, like Gecko) Version/3.0 BrowserNG/7.2.7.4 3gpp-gba',
            'Nokia5130c-2/2.0 (07.97) Profile/MIDP-2.1 Configuration/CLDC-1.1 nokia5130c-2/UC Browser7.5.1.77/69/351 UNTRUSTED/1.0',
            'Mozilla/4.1 (compatible; MSIE 5.0; Symbian OS; Nokia 6600;452) Opera 6.20 [en-US]',
            'Opera/9.80 (Mac OS X; Opera Mobi/38190; U; zh-cn) Presto/2.10.254 Version/12.00',
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0)',
            'Mozilla/5.0 (Linux; U; Android 4.0.3; de-ch; HTC Sensation Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
            'Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.346 Mobile Safari/534.11+',
            'SamsungI8910/SymbianOS/9.1 Series60/3.0',

            // Nokia N97
            'Mozilla/5.0 (SymbianOS/9.4; Series60/5.0 NokiaN97-1/20.0.019; Profile/MIDP-2.1 Configuration/CLDC-1.1) AppleWebKit/525 (KHTML, like Gecko) BrowserNG/7.1.18124',

            'NokiaE52-1/SymbianOS/9.1 Series60/3.0 3gpp-gba',
            'Mozilla/5.0 (SymbianOS/9.4; Series60/5.0 NokiaC6-00/20.0.042; Profile/MIDP-2.1 Configuration/CLDC-1.1; zh-hk) AppleWebKit/525 (KHTML, like Gecko) BrowserNG/7.2.6.9 3gpp-gba',
            'Opera/9.80 (Android 2.3.3; Linux; Opera Mobi/ADR-1111101157; U; es-ES) Presto/2.9.201 Version/11.50',
            //'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_7; en-us) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Safari/530.17 Skyfire/2.0',
            //'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; BOLT/2.340) AppleWebKit/530+ (KHTML, like Gecko) Version/4.0 Safari/530.17 UNTRUSTED/1.0 3gpp-gba',

            // Windows Phone Mango
            'Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; HTC; Titan)',

            // palm
            'Mozilla/5.0 (webOS/1.4.0; U; en-US) AppleWebKit/532.2 (KHTML, like Gecko) Version/1.0 Safari/532.2 Pre/1.0',
            'Mozilla/5.0 (webOS/Palm webOS 1.2.9; U; en-US) AppleWebKit/525.27.1 (KHTML, like Gecko) Version/1.0 Safari/525.27.1 Pixi/1.0',

            // iPhone
            'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3',
            // iPhone4
            'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_0 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8A293 Safari/6531.22.7',
            'Apple-iPhone3C1',
            'Apple-iPhone4C1',
            'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/4A93 Safari/419.3',
            // iPhone3
            'Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/1A542a Safari/419.3',

            // iPod
            ' Mozilla/5.0 (iPod; U; CPU iPhone OS 3_1_1 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Mobile/7C145',
            'Apple-iPod/501.347',

            // android
            'Mozilla/5.0 (Linux; U; Android 2.3.7; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.2.1; de-de; HTC_Wildfire_A3333 Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.2; fr-lu; HTC Legend Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.2.1; en-ca; LG-P505R Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.1-update1; es-mx; SonyEricssonE10a Build/2.0.A.0.504) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17',
            'Mozilla/5.0 (Linux; U; Android 1.6; ar-us; SonyEricssonX10i Build/R2BA026) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1',
            'Mozilla/5.0 (Linux; U; Android 2.2; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 1.6; en-gb; Dell Streak Build/Donut AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/ 525.20.1',
            'Mozilla/5.0 (Linux; U; Android 2.2; en-us; DROID2 GLOBAL Build/S273) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.2; en-gb; GT-P1000 Build/FROYO) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/5.0 (Linux; U; Android 2.1-update1; de-de; E10i Build/2.0.2.A.0.24) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17',
            'Mozilla/5.0 (Linux; U; Android 1.5; de-; sdk Build/CUPCAKE) AppleWebkit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1',
            'Mozilla/5.0 (Linux; U; Android 2.2; en-us; Nexus One Build/ FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows Phone OS 7.0; Trident/3.1; IEMobile/7.0; DELL; Venue Pro)',

            // BlackBerry
            'Mozilla/5.0 (BlackBerry; U; BlackBerry 9800; en) AppleWebKit/534.1+ (KHTML, like Gecko) Version/6.0.0.337 Mobile Safari/534.1+',

            // Android QQ浏览器 For android
            'MQQBrowser/26 Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; MB200 Build/GRJ22; CyanogenMod-7) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',

            // Android UC For android
            //'JUC (Linux; U; 2.3.7; zh-cn; MB200; 320*480) UCWEB7.9.3.103/139/999',

            // Android Firefox手机版Fennec
            'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:7.0a1) Gecko/20110623 Firefox/7.0a1 Fennec/7.0a1',

            // Android Opera Mobile
            'Opera/9.80 (Android 2.3.4; Linux; Opera Mobi/build-1107180945; U; en-GB) Presto/2.8.149 Version/11.10',

            // 百度手机浏览器
            'Mozilla/5.0 (Linux; U; Android 4.0.3; zh-cn; GT-I9000 Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',

            // Android Pad Moto Xoom
            //'Mozilla/5.0 (Linux; U; Android 3.0; en-us; Xoom Build/HRI39) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13',
        );
    }

    private function _mockUserAgent($arr)
    {
        return new DHttp_UserAgent($arr['HTTP_USER_AGENT']);
    }

    private function _testSpecifiedMobileAgent($uaAlike, $method)
    {
        foreach($this->browserAgents as $ua)
        {
            $userAgent = new DHttp_UserAgent($ua);
            if (stristr($ua, $uaAlike))
            {
                $this->assertTrue($userAgent->$method());
            }
            else
            {
                $this->assertFalse($userAgent->$method());
            }
        }

        foreach($this->spiders as $ua)
        {
            $userAgent = new DHttp_UserAgent($ua);
            $this->assertFalse($userAgent->$method());
        }

        foreach($this->browserAgents as $ua)
        {
            $userAgent = new DHttp_UserAgent($ua);
            $this->assertFalse($userAgent->$method());
        }
    }

    public function testIsFlashOrNot()
    {
        $userAgent = new DHttp_UserAgent('Shockwave Flash/12.3.1');
        $this->assertTrue($userAgent->isFlash());

        $userAgent = new DHttp_UserAgent('ie/12.3.1');
        $this->assertFalse($userAgent->isFlash());

        foreach($this->browserAgents as $ua)
        {
            $userAgent = new DHttp_UserAgent($ua);
            $this->assertFalse($userAgent->isFlash());
        }

        foreach($this->mobileAgents as $ua)
        {
            $userAgent = new DHttp_UserAgent($ua);
            $this->assertFalse($userAgent->isFlash());
        }

        foreach($this->spiders as $ua)
        {
            $userAgent = new DHttp_UserAgent($ua);
            $this->assertFalse($userAgent->isFlash());
        }
    }

    public function testIsIpadOrNot()
    {
        $r = $this->_mockUserAgent(array(
            'HTTP_USER_AGENT' => 'iPad/2.0',
        ));
        $this->assertTrue($r->isIpad());

        $r = $this->_mockUserAgent(array(
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPad; U; CPU OS 4_2_1 like Mac OS X; zh-cn) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148 Safari/6533.18.5'
        ));
        $this->assertTrue($r->isIpad());

        // 确认不能误判为iPad
        $r = $this->_mockUserAgent(array(
            'HTTP_USER_AGENT' => 'ie6/2.0',
        ));
        $this->assertFalse($r->isIpad());

        foreach($this->spiders as $ua)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertFalse($r->isIpad());
        }

        foreach($this->browserAgents as $ua)
        {
            if (CStr::contains($ua, 'ipad', true))
            {
                continue;
            }

            $r = $this->_mockUserAgent(array('HTTP_USER_AGENT' => $ua));
            $this->assertFalse($r->isIpad());
        }
    }

    public function testIsIphoneOrNot()
    {
        $this->_testSpecifiedMobileAgent('iphone', 'isIphone');
    }

    public function testIsIpodOrNot()
    {
        $this->_testSpecifiedMobileAgent('ipod', 'isIpod');
    }

    public function testIsAndroidOrNot()
    {
        $this->_testSpecifiedMobileAgent('android', 'isAndroid');
    }

    public function testIsMobileOrNot()
    {
        foreach ($this->mobileAgents as $ua)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertTrue($r->isMobile());
        }

        foreach ($this->browserAgents as $ua)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertFalse($r->isMobile());
        }

        // spiders are not mobile
        foreach($this->spiders as $ua)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertFalse($r->isMobile());
        }
    }

    public function testGetAndroidVersion()
    {
        $androids = array(
            'Mozilla/5.0 (Linux; U; Android 2.2.1; de-de; HTC_Wildfire_A3333 Build/FRG83D) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1' => '2.2',
            'Mozilla/5.0 (Linux; U; Android 2.2; fr-lu; HTC Legend Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1' => '2.2',
            'Mozilla/5.0 (Linux; U; Android 2.2.1; en-ca; LG-P505R Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1' => '2.2',
            'Mozilla/5.0 (Linux; U; Android 2.1-update1; es-mx; SonyEricssonE10a Build/2.0.A.0.504) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17' => '2.1',
            'Mozilla/5.0 (Linux; U; Android 1.6; ar-us; SonyEricssonX10i Build/R2BA026) AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1' => '1.6',
            'Mozilla/5.0 (Linux; U; Android 2.2; en-us; Nexus One Build/FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1' => '2.2',
            'Mozilla/5.0 (Linux; U; Android 1.6; en-gb; Dell Streak Build/Donut AppleWebKit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/ 525.20.1' => '1.6',
            'Mozilla/5.0 (Linux; U; Android 2.2; en-us; DROID2 GLOBAL Build/S273) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1' => '2.2',
            'Mozilla/5.0 (Linux; U; Android 2.2; en-gb; GT-P1000 Build/FROYO) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1' => '2.2',
            'Mozilla/5.0 (Linux; U; Android 2.1-update1; de-de; E10i Build/2.0.2.A.0.24) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17' => '2.1',
            'Mozilla/5.0 (Linux; U; Android 1.5; de-; sdk Build/CUPCAKE) AppleWebkit/528.5+ (KHTML, like Gecko) Version/3.1.2 Mobile Safari/525.20.1' => '1.5',
            'Mozilla/5.0 (Linux; U; Android 2.2; en-us; Nexus One Build/ FRF91) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1' => '2.2',
        );

        foreach($androids as $ua => $version)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertEquals($version, $r->getAndroidVersion());
        }

        foreach($this->browserAgents as $ua)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertFalse($r->getAndroidVersion());
        }
    }

    public function testIsNotSpiderOrNot()
    {
        foreach($this->spiders as $ua)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertTrue($r->isSpider());
        }

        $userAgents = array(
            'blackberry/2.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.52 Safari/536.5',
            'blackberry/2.0',
        );
        foreach ($userAgents as $ua)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertFalse($r->isSpider());
        }

        foreach($this->browserAgents as $ua)
        {
            $r = $this->_mockUserAgent(array('HTTP_USER_AGENT' => $ua));
            $this->assertFalse($r->isSpider());
        }

        foreach($this->mobileAgents as $ua)
        {
            $r = $this->_mockUserAgent(array('HTTP_USER_AGENT' => $ua));
            $this->assertFalse($r->isSpider());
        }
    }

    public function testIsMeeGo()
    {
        $r = $this->_mockUserAgent(array(
            'HTTP_USER_AGENT' => 'meego',
        ));
        $this->assertTrue($r->isMeeGo());

        foreach($this->browserAgents as $ua)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertFalse($r->isMeeGo());
        }
    }

    public function testIsUcWebClientOrNot()
    {
        $ucAgents = array(
            'UCWEB7.0.2.37/28/999',
            'ucweb',
            'NOKIA5700/UCWEB7.0.2.37/28/999',
            'Mozilla/4.0 (compatible; MSIE 6.0; ) Opera/UCWEB7.0.2.37/28/999',
        );
        foreach($ucAgents as $ua)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertTrue($r->isUcWebClient());
        }

        // 防止误判
        foreach($this->browserAgents as $ua)
        {
            $r = $this->_mockUserAgent(array(
                'HTTP_USER_AGENT' => $ua,
            ));
            $this->assertFalse($r->isUcWebClient());
        }
    }

    public function testGetPlatform()
    {
        $i = 0;
        $expected = array(
            'Windows', // 0
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Windows', // 10
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Apple',
            'Windows',
            'Apple', // 20
            'Windows',
            'Apple',
            'Windows',
            'Apple',
            'unknown',
            'Windows',
            'unknown',
            'Windows',
            'Windows',
            'unknown', // 30
            'unknown',
            'Windows',
            'Windows',
            'Windows',
            'Windows',
            'Apple',
            'Windows',
            'Apple',
            'Windows',
            'unknown', // 40
            'iPad',
            'iPad',
            'iPad',
            'iPad',
            'iPad',
            'Linux',
            'Linux',


        );
        foreach ($this->browserAgents as $ua)
        {
            $r = new DHttp_UserAgent($ua);

            $this->assertEquals($expected[$i], $r->getPlatform());

            $i ++;
        }
    }

}
