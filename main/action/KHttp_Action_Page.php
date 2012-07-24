<?php
/**
 * 开心网页面的action.
 *
 * 增加了常用的页面模版变量的取值功能
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
     * 统一的分页的pageNo.
     *
     * @KHttp_Annotation_Param(type='int', default=0)
     */
    public $start;

    /**
     * 统一的分页的pageLimit.
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
     * 把常用的模版变量封装成数组.
     *
     * 这样性能更好(减少了反射方法调用次数)，而且代码简洁很多
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
                'rcode'   => rand(0, 100), // 用于强制刷新页面：&t={{$app.ctx.rcode}}
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
                'type'       => $this->_isStar(), // 帐号类型
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
     * 获取当前用户要显示的组件列表.
     *
     * @return array Map
     */
    protected function _getAppData()
    {

    }

    /**
     * 页面标题.
     *
     * @return string
     */
    protected abstract function _getTitle();

}
