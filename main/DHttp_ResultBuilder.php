<?php
/**
 * ����result��Ⱦ�Ľӿ�.
 *
 * ����ҳ����������񣬴�{@link DHttp_Action}�ӽ��ѳ������ɿ�ܸ��������
 *
 * ��������ģ��ı����滻��Ҳ����{@link DHttp_Action}�����ˣ����ɿ�����
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
     * ����{@link DHttp_Action}��ģ���ļ��õ������������.
     *
     * @param DHttp_Action $action ��ǰִ�е�action
     * @param string $value Result value
     *
     * @return string ׼����HTTP�����body
     */
    public function execute(DHttp_Action $action, $value = null);

}
