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
     * @param DHttp_Action $action ��ǰִ�е�action
     * @param string $value Json data
     *
     * @return string ׼����HTTP�����body
     */
    public function execute(DHttp_Action $action, $value = null)
    {
        $res = $action->getResponse();
        $res->contentType(self::CONTENT_TYPE_JSON, self::DEFAULT_CHARSET);

        if (is_null($value))
        {
            // û�дӲ������룬���request��ȡ
            $value = $action->getRequest()->getAttribute(self::REQ_ATTR_JSON);
        }

        // ת��
        $json = json_encode(DUtil_String::toUTF8($value));

        return $json;
    }

}
