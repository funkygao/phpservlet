<?php
/**
 *
 *
 * @category
 * @package http
 * @subpackage result
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
class KHttp_Result_Json implements DHttp_ResultBuilder
{

    /**
     *
     * @param DHttp_Action $action 当前执行的action
     * @param string $value Json data
     *
     * @return string 准备给HTTP输出的body
     */
    public function execute(DHttp_Action $action, $value = null)
    {
        $res = $action->getResponse();
        $res->contentType(self::CONTENT_TYPE_JSON, self::DEFAULT_CHARSET);

        if (is_null($value))
        {
            // 没有从参数传入，则从request里取
            $value = $action->getRequest()->getAttribute(self::REQ_ATTR_JSON);
        }

        // 转码
        $json = json_encode(DUtil_String::toUTF8($value));

        return $json;
    }

}
