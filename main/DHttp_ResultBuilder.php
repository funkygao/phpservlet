<?php
/**
 * 所有result渲染的接口.
 *
 * 它把页面输出的任务，从{@link DHttp_Action}从解脱出来，由框架负责输出。
 *
 * 甚至，连模版的变量替换，也不用{@link DHttp_Action}操心了，都由框架完成
 *
 * <pre>
 *
 *               DHttp_App {result name: result type}
 *                  |   |
 *                  |   |-------<----------
 *                  V   V                  |
 *               1st|   |2nd               ^ http body
 *                  |   |                  |
 *      ----->------|    ------->------DHttp_ResultBuilder
 *      |           |                      |
 *      |       DHttp_Controller           |
 *      |           |                      V pull vars for template
 *      |           V dispatch             |
 *      |           |                      |
 *      ^       DHttp_Action -----<--------
 *      |           |
 *      |           V action_method()
 *      |           |
 *       -----<-----
 *       result name
 *
 * </pre>
 *
 * @category
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
interface DHttp_ResultBuilder extends DHttp_Result, DHttp_Constant
{
    /**
     * 根据{@link DHttp_Action}和模版文件得到该输出的内容.
     *
     * @param DHttp_Action $action 当前执行的action
     * @param string $value Result value
     *
     * @return string 准备给HTTP输出的body
     */
    public function execute(DHttp_Action $action, $value = null);

}
