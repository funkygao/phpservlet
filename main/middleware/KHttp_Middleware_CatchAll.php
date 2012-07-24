<?php
/**
 * �������յ������쳣������ʾϵͳ��æ���м��.
 *
 * ��Ҫ�ǲ����м��������쳣��action���쳣�Ѿ���{@link DHttp_App::call}�ﲶ����
 *
 * @package http
 * @subpackage middleware
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo _outputErrorPage, kxi_Exception
 */
class KHttp_Middleware_CatchAll extends DHttp_Middleware
{

    public function call()
    {
        try
        {
            $this->_callNextMiddleware();
        }
        catch (kxi_Exception $ex)
        {
            // �м����������� TODO

        }
        catch (KHttp_Exception_Captcha $ex)
        {
            $action = $this->getAction();
            if (!($action instanceof KHttp_Action_CaptchaAware))
            {
                // ���ռ�ʻ�����޿��̣�̫�����ˣ������ˣ������û��������ǵĴ����
                throw new KHttp_Exception_InvalidConfig('action must instanceof KHttp_Action_CaptchaAware');
            }

            $action->outputCaptchaError($ex);
        }
        catch (KHttp_Exception_InvalidConfig $ex)
        {
            // ����Աû�а��ձ��淶���п���action����
            // TODO
        }
        catch (DHttp_Exception $ex)
        {
            // �ڲ��׳��ľ����ض�������쳣
        }
        catch (Exception $ex)
        {
            // unexpected exception

            // log
            $this->_getLogger()->error_log($ex->getMessage());

            // ͳһ���500����
            $res = $this->getResponse();
            $res->status(self::SC_INTERNAL_SERVER_ERROR);
            $res->body($this->_outputErrorPage($ex));
        }
    }

    private function _outputErrorPage(Exception $ex)
    {
        //echo $ex->getMessage();
    }

}
