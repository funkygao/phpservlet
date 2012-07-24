<?php
/**
 * Application.
 *
 * ���Ǳ���ܹ淶�ĺ��ġ�
 *
 * ��ʵ������Ӧ�÷������淶���൱��java���servlet container���൱��python
 * ���app server����Ϊ����������������Ӧ����ͼ���м���������������γ��л������塣
 *
 * ��ģ�µĶ���
 * <ul>
 * <li>python WSGI</li>
 * <li>java servlet container and servlet spec</li>
 * </ul>
 *
 * ��ѭ�ù淶�Ĵ����ﲻҪʹ�����·�����
 * <ul>
 * <li>exit</li>
 * <li>die</li>
 * <li>header</li>
 * <li>echo</li>
 * </ul>
 *
 * �߼�ͼ��
 * <pre>
 *
 *                             user browser
 *                                |    |                 1
 *                                |    V                --- DHttp_Controller
 *                         status |    |               |
 *                         head   ^    |               | 1
 *                         body   |  php page          |--- DHttp_Request --- DHttp_Env
 *                                |    |               |                   |
 *                                |    |               |                   |- DHttp_UserAgent
 *   ---------------------        |    V new           |                   |
 *  | DHttp_ResultBuilder |       |    |  |            |                    - DHttp_Session
 *  |      execute        |       |    | run           |                    
 *   ---------------------        |    |               |                    
 *            |                   |    |               |                    
 *            |                   |    |               | 1                  
 *            |          http  -----------             |--- DHttp_Response --- DHttp_Header
 *  DHttp_ResultResolver-->---|           |��---------->|                    |
 *        |      |       body | DHttp_App |1  create   |                     - DHttp_Cookie
 *        |      |            |           |            | * 
 *        |      |             -----------             |--- DHttp_IMiddleware ----- CatchAll
 *        |      |                 |                   |                         |- Authentication
 *        |      |                 |                   | *                       |- Authorization             
 *        |      |           DHttp_Controller           --- hooks                |- SemiAccount
 *        V      ^                 |                          |                  |- Seo
 *   pull |      | result          | dispatch                 |- before          |- CaptchaGuard
 *   vars |      | name            V                          |- beforeDispatch  |- UserGuard
 *        |      |                 |                          |- afterDispatch   |- Profiler
 *        |      |                 |                           - afeter          |- Urlrewrite
 *        |      |       ------------------------------                          |- Tracker
 *        |       ---<--|      DHttp_Action            |                         |- RequestGuard
 *        |             |                              |                         |- PageCache
 *         ----->-------| pageMethod(req, res)->result |                         |- Degrade
 *                       ------------------------------                           - and more...
 *                                  |    |                                      
 *                             data ^    V call                                
 *                                  |    |                                    
 *                                 services                                  
 *                                    |                                       
 *              ---------------------------------------          
 *              |       |      |           |            |
 *            logger  model   kxi        .....         etc
 *                             |
 *                    ------------------------
 *                   |     |    |     |       |
 *                 DBMan  UO  mcache search  feed...
 *                     
 * </pre>
 *
 * ������Ҳ��һ���м����ֻ������м�������Ǹ�����������Ա����ķ��ϱ��淶
 * ��ҳ��Ӧ�ã����������{@link DHttp_Result}���ҳ����
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo front controller, ���м����call()�з���ֵ��result name, ��ͨ�м����action֮��������������кܶ๲�ԵĶ���������uid/uid, ��ȡ����biz level service
 *
 * @see http://rack.github.com/
 * @see http://en.wikipedia.org/wiki/Web_Server_Gateway_Interface
 *
 */
class DHttp_App implements DHttp_IMiddleware, DHttp_Constant
{

    const
        HOOK_BEFORE = 'kx.before',
        HOOK_BEFORE_DISPATCH = 'kx.before.dispatch',
        HOOK_AFTER_DISPATCH = 'kx.after.dispatch',
        HOOK_AFTER = 'kx.after';

    /**
     * @var DHttp_Controller
     */
    protected $_controller;

    /**
     * Ӧ�õ�����.
     *
     * @var DHttp_Config
     */
    protected $_config;

    /**
     *
     * @var DHttp_KxRequest
     */
    protected $_request;

    /**
     *
     * @var DHttp_Response
     */
    protected $_response;

    /**
     * ���е��м���б�.
     *
     * @var array[DHttp_Middleware]
     */
    protected $_middlewares;

    /**
     * @var DHttp_Action
     */
    protected $_action;

    /**
     * Hooks.
     *
     * @var array Ƕ������
     */
    protected $_hooks = array(
        self::HOOK_BEFORE            => array(array()),
        self::HOOK_BEFORE_DISPATCH   => array(array()),
        self::HOOK_AFTER_DISPATCH    => array(array()),
        self::HOOK_AFTER             => array(array()),
    );

    /**
     * ������.
     *
     * @param DHttp_Config $config Ӧ�õ�������Ϣ
     * @param DHttp_Env $env
     */
    public function __construct(DHttp_Config $config, $env = null)
    {
        // ����ôһ�£�php5.3+�ᱨnotice error
        date_default_timezone_set('Asia/Shanghai');

        // ��������������
        $this->_config = $config;
        $this->_request = DHttp_ContextUtil::getKxRequest($env);
        $this->_response = DHttp_ContextUtil::getResponse(true);
        $this->_controller = new DHttp_Controller($this);

        // ��ҪԤ��װ���м��
        $this->_middlewares = array($this); // the tail
        foreach ($this->_config->getMiddlewareNames() as $name)
        {
            $middleware = 'KHttp_Middleware_' . ucfirst($name);
            $this->register(new $middleware());
        }
    }

    /**
     * ע���м��.
     *
     * counterpart of interceptor in java struts2
     *
     * Խ��ע����м����Խ�类ִ��
     *
     * After register middleware:
     * <pre>
     * [1]
     * [2->1]
     * [3->2->1]
     * </pre>
     *
     * @param DHttp_Middleware $middleware
     */
    public final function register(DHttp_Middleware $middleware)
    {
        $middleware->setApp($this);
        $middleware->setNext($this->_middlewareHead());
        array_unshift($this->_middlewares, $middleware);
    }

    /**
     * ��ȡ�Ѿ�ע��������м��.
     *
     * ʵ���ϲ��ñ�¶����Ϊ�˵�Ԫ���ԣ����ò���¶�������Ա����register()�Ƿ���ȷ
     *
     * @return array List of {@link DHttp_Middleware}
     */
    public final function getMiddlewares()
    {
        return $this->_middlewares;
    }

    /**
     * ע��hook.
     *
     * @param string $name Hook name
     * @param mixed $callable A callable object
     * @param int $priority ԽСԽ����ִ��
     *
     * @throws InvalidArgumentException
     */
    public final function hook($name, $callable, $priority = 10)
    {
        $priority = (int)$priority;

        if (!isset($this->_hooks[$name]))
        {
            $this->_hooks[$name] = array(array());
        }

        if (!is_callable($callable))
        {
            throw new InvalidArgumentException("$callable is not callable");
        }

        $this->_hooks[$name][$priority][] = $callable;
    }

    /**
     * ִ��hook.
     *
     * @param string $name Hook name
     * @param mixed $hookArg Ŀǰ��û����
     *
     * @return mixed
     */
    private function _invokeHook($name, $hookArg = null)
    {
        if (empty($this->_hooks[$name]))
        {
            return;
        }

        // Sort by priority, low to high, if there's more than one priority
        if (count($this->_hooks[$name]) > 1)
        {
            ksort($this->_hooks[$name]);
        }

        foreach ($this->_hooks[$name] as $priority)
        {
            if (empty($priority))
            {
                continue;
            }

            foreach ($priority as $callable)
            {
                $hookArg = call_user_func($callable, $hookArg);
            }
        }

        return $hookArg;
    }

    /**
     * @return DHttp_Middleware
     */
    private function _middlewareHead()
    {
        return $this->_middlewares[0];
    }

    /**
     *
     * @return DHttp_KxRequest
     */
    public final function request()
    {
        return $this->_request;
    }

    /**
     *
     * @return DHttp_Response
     */
    public final function response()
    {
        return $this->_response;
    }

    /**
     * @return DHttp_Config
     */
    public final function config()
    {
        return $this->_config;
    }

    /**
     * @return DHttp_Controller
     */
    public final function controller()
    {
        return $this->_controller;
    }

    /**
     * ���û�ȡֵ��ǰӦ�õ�action.
     *
     * @param DHttp_Action|null $action
     * @return DHttp_Action|DHttp_App
     */
    public final function action($action = null)
    {
        if (is_null($action))
        {
            // getter
            return $this->_action;
        }
        else
        {
            $this->_action = $action;

            return $this;
        }
    }

    /**
     * ��������.
     *
     * ����ѵ�ǰ���������{@link DHttp_Response}��������������
     *
     * ��{@link DHttp_Action}ʹ��.
     *
     * @throws KHttp_Exception_Stop
     */
    public final function stop()
    {
        throw new KHttp_Exception_Stop();
    }

    /**
     * �����������������������״̬�������.
     *
     * ���Ḳ���������������{@link DHttp_Response}����
     *
     * ��{@link DHttp_Action}ʹ��.
     *
     * @param int $statusCode HTTP status code
     * @param string $body HTTP body content
     *
     * @throws KHttp_Exception_Stop
     */
    public final function halt($statusCode, $body = '')
    {
        if (ob_get_level() !== 0)
        {
            ob_clean();
        }

        $this->_response->status($statusCode);
        $this->_response->body($body);

        $this->stop();
    }

    /**
     * ҳ���ض���.
     *
     * @param string $url
     * @param int $status
     */
    public final function redirect($url, $status = self::SC_MOVED_TEMPORARILY)
    {
        $this->_response->redirectFinal($url, $status);

        $this->halt($status);
    }

    /**
     * ת����ĳ��action��������.
     *
     * ע����`redirect()`�Ĳ�ͬ��
     * �ڲ���ת���ⲿ��ת������ǰ������תʱ����request/response��Ϣ
     *
     * ֻ����action method����� !!!
     *
     * @param string $actionClass
     * @param string $actionMethod
     *
     * @return string Result name
     */
    public final function forward($actionClass, $actionMethod)
    {
        return $this->_controller->forward($actionClass, $actionMethod);
    }

    /**
     * ����Ӧ�ò��ѽ������������.
     *
     * ��Ӧ����Ӧ�ó�������һ�����!
     *
     * @param string $actionMethodOrFilename Virtual action method name or __FILE__
     *
     * @return void
     */
    public final function run($actionMethodOrFilename = null)
    {
        if ($actionMethodOrFilename)
        {
            $this->_config->setActionMethod($actionMethodOrFilename);
        }

        // CatchAll��Զ�ǵ�һ���м��, head����о�������
        $this->register(new KHttp_Middleware_CatchAll());

        // ��CatchAll���У��������������м������
        $this->_middlewareHead()->call();

        // �����Ӧ�������
        $this->_displayResponse();
    }

    /**
     * Tail of the middlewares of the application.
     *
     * ����������Ǵ�������ִ������ҳ���action��Ȼ��ѽ������������.
     *
     * �������������һ��ִ�е��м�����������û�б�Ҫ�ٰѽ���������һ����
     *
     * hook���ơ�result����������ڴ�ʵ�ֵ�
     */
    public function call()
    {
        try
        {
            $this->_invokeHook(self::HOOK_BEFORE);

            ob_start();

            $this->_invokeHook(self::HOOK_BEFORE_DISPATCH);

            // ��controller�ɻִ��action
            list($action, $resultName) = $this->_controller->dispatch();

            // ��result���д���
            $resolver = new DHttp_ResultResolver($this->_config, $action, $resultName);
            $resultContent = $resolver->resolve();

            // ���action���
            $this->_response->write($resultContent);

            $this->_invokeHook(self::HOOK_AFTER_DISPATCH);

            /*
             * ���output buffer�����ر�
             *
             * action����echo/print_r������ģ���������õ�
             * ���ǻ����$response->write()�ĺ���
             */
            $this->_response->write(ob_get_clean());

            $this->_invokeHook(self::HOOK_AFTER);
        }
        catch (KHttp_Exception_Stop $ex)
        {
            $this->_response->write(ob_get_clean());
        }
        catch (Exception $ex)
        {
            /*
             * ����action�׳����쳣
             *
             * action�����е������ȫ����
             */
            ob_end_clean();

            // ����KHttp_Middleware_CatchAll����
            throw $ex;
        }

    }

    /**
     * ��װphp��header���������ڵ�Ԫ����.
     *
     * @param string $line
     * @param bool $replace
     */
    private function _header($line, $replace = true)
    {
        if ($this->_request->isCli())
        {
            echo $line . "\n";
        }
        else
        {
            header($line, $replace);
        }

    }

    private function _displayResponse()
    {
        list($status, $header, $body) = $this->_response->finalize();

        // �������������Ӧͷ
        if (headers_sent() === false)
        {
            // status�����һ�����
            $this->_header($this->_response->renderStatus(PHP_SAPI,
                $this->_request->getServerProcotol(), $status));

            // ���������HTTP header
            foreach($header->toArray() as $headerLine)
            {
                $this->_header($headerLine, $replace = false);
            }
        }

        // �������������Ӧ����
        echo $body;
    }

    /**
     * ��annotation����.
     *
     * ʵ���Ͼ���require��Ӧ���ļ�����Ϊannotation����Ŀǰ��֧���Զ�����
     */
    public static function enableAnnotationFeature()
    {
        static $fileLoaded = false;
        if ($fileLoaded)
        {
            return;
        }

        include_once(dirname(__FILE__) . '/../base/annotation/annotations.php');
        $fileLoaded = true;
    }

}
