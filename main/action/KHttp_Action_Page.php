<?php
/**
 * ������ҳ���action.
 *
 * �����˳��õ�ҳ��ģ�������ȡֵ����
 *
 * <pre>
 *
 *                     ---------------------
 *                     DHttp_Action
 *
 *                     http protocol related
 *                     ---------------------
 *                          ^
 *                          |
 *                          |
 *                     ------------------------
 *                     KHttp_Action_Base
 *
 *                     user and service related
 *                     ------------------------
 *                          ^
 *                          |
 *                          |
 *                     --------------------------
 *                     KHttp_Action_Page
 *
 *                     page template vars related
 *                     --------------------------
 *
 * </pre>
 *
 * @package http
 * @subpackage action
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo title/seo metas/middleware manipulate html header
 */
abstract class KHttp_Action_Page extends KHttp_Action_Base
{

    /**
     * ͳһ�ķ�ҳ��pageNo.
     *
     * @KHttp_Annotation_Param(type='int', default=0)
     */
    public $start;

    /**
     * ͳһ�ķ�ҳ��pageLimit.
     *
     * @KHttp_Annotation_Param(type='int', default=10)
     */
    public $num;

    /**
     * @param mixed $paramValue
     *
     * @return bool
     */
    protected final function _isParamValid($paramValue)
    {
        return $this->getController()->isParamValid($paramValue);
    }

    /**
     * �ѳ��õ�ģ�������װ������.
     *
     * �������ܸ���(�����˷��䷽�����ô���)�����Ҵ�����ܶ�
     *
     * <code>
     * {{$app.host.image}}
     * {{$app._user.isAbTester}}
     * {{$app.js.IM}}
     * </code>
     *
     * @KHttp_Annotation_Ognl(name='app')
     *
     * @return array
     */
    public final function getPage()
    {
        $userInfo = $this->_userInfo();
        return array(
            'page'   => array(
                'charset' => SYS_CHARSET,
                'rcode'   => rand(0, 100), // ����ǿ��ˢ��ҳ�棺&t={{$app.ctx.rcode}}
                'title'   => $this->_getTitle(),
            ),

            'host'   => array(
                'image'  => IMG1_HOST,
                'pic'    => PIC_HOST,
                'static' => STC_HOST,
                'www'    => WWW_HOST,
            ),

            '_user'  => array(
                'isAbTester' => DUtil_TestUsers::isTestUser($this->_uid) ? 1 : 0,
                'uid'        => $this->_uid,
                'realName'   => $this->_userInfo()->getFirst('real_name'),
                'icon50'     => CIcon::getUserIcon(
                    $this->_uid,
                    50,
                    $userInfo->getFirst('logo'),
                    $userInfo->getFirst('gender')
                ),
                'type'       => $this->_isStar(), // �ʺ�����
                'imWeb'      => KBiz_Cache_LCache::getImWeb($this->_uid),
            ),

            'user'   => array(
                'uid'        => $this->uid,
            ),

            'js'     => array(
                'IM'       => 'js/apps/im/IM.js',
                'lazyStat' => 'js/log_kaixin001.js',
            ),

            'jscode' => array(
                'checkStat'=> $this->_getCheckStatJsCode(),
            ),

            'app'    => array(
                'groups' => CHtmlHead::outputAppGroupList($this->_getAppData()),
            ),

            'ads'    => array(
                'cpmVersion' => CKxPlatConst::CPM_JS_VER,
                'cpcVersion' => CKxPlatConst::CPC_JS_VER,
            ),

        );

    }

    private function _getCheckStatJsCode()
    {
        $js = '';
        if (($this->_uid == $this->uid)
            && ($this->_uid % 64 == 26)
            && $this->getRequest()->isHomePage()
        )
        {
            $js = '$j.post("/home/checkstat.php", {pars:""}, function(data) {}, "json");';
        }

        return $js;
    }

    /**
     * ��ȡ��ǰ�û�Ҫ��ʾ������б�.
     *
     * @return array Map
     */
    protected function _getAppData()
    {

    }

    /**
     * ҳ�����.
     *
     * @return string
     */
    protected abstract function _getTitle();

}
