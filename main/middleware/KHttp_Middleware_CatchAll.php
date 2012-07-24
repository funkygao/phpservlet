<?php
/**
 * 捕获最终的所有异常，并显示系统繁忙的中间件.
 *
 * 主要是捕获中间层产生的异常，action的异常已经在{@link DHttp_App::call}里捕获了
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
            // 中间层出现了问题 TODO

        }
        catch (KHttp_Exception_Captcha $ex)
        {
            $action = $this->getAction();
            if (!($action instanceof KHttp_Action_CaptchaAware))
            {
                // 无照驾驶！忍无可忍，太气人了，不管了，就让用户看到我们的错误吧
                throw new KHttp_Exception_InvalidConfig('action must instanceof KHttp_Action_CaptchaAware');
            }

            $action->outputCaptchaError($ex);
        }
        catch (KHttp_Exception_InvalidConfig $ex)
        {
            // 程序员没有按照本规范进行开发action代码
            // TODO
        }
        catch (DHttp_Exception $ex)
        {
            // 内部抛出的具有特定语义的异常
        }
        catch (Exception $ex)
        {
            // unexpected exception

            // log
            $this->_getLogger()->error_log($ex->getMessage());

            // 统一输出500错误
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
