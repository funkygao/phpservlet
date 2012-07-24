<?php
/**
 * Application.
 *
 * 它是本框架规范的核心。
 *
 * 它实际上是应用服务器规范，相当于java里的servlet container，相当于python
 * 里的app server，因为，是它，把请求、响应、视图、中间件等整合起来，形成有机的整体。
 *
 * 它模仿的对象：
 * <ul>
 * <li>python WSGI</li>
 * <li>java servlet container and servlet spec</li>
 * </ul>
 *
 * 遵循该规范的代码里不要使用如下方法：
 * <ul>
 * <li>exit</li>
 * <li>die</li>
 * <li>header</li>
 * <li>echo</li>
 * </ul>
 *
 * 逻辑图：
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
 *  DHttp_ResultResolver-->---|           |◇---------->|                    |
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
 * 它本身也是一个中间件，只是这个中间件做的是负责启动程序员定义的符合本规范
 * 的页面应用，并负责根据{@link DHttp_Result}输出页面结果
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 *
 * @todo front controller, 让中间件的call()有返回值：result name, 打通中间件与action之间的桥梁，他们有很多共性的东西，例如uid/uid, 获取各种biz level service
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
     * 应用的配置.
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
     * 所有的中间件列表.
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
     * @var array 嵌套数组
     */
    protected $_hooks = array(
        self::HOOK_BEFORE            => array(array()),
        self::HOOK_BEFORE_DISPATCH   => array(array()),
        self::HOOK_AFTER_DISPATCH    => array(array()),
        self::HOOK_AFTER             => array(array()),
    );

    /**
     * 构造器.
     *
     * @param DHttp_Config $config 应用的配置信息
     * @param DHttp_Env $env
     */
    public function __construct(DHttp_Config $config, $env = null)
    {
        // 不这么一下，php5.3+会报notice error
        date_default_timezone_set('Asia/Shanghai');

        // 生成容器上下文
        $this->_config = $config;
        $this->_request = DHttp_ContextUtil::getKxRequest($env);
        $this->_response = DHttp_ContextUtil::getResponse(true);
        $this->_controller = new DHttp_Controller($this);

        // 需要预安装的中间件
        $this->_middlewares = array($this); // the tail
        foreach ($this->_config->getMiddlewareNames() as $name)
        {
            $middleware = 'KHttp_Middleware_' . ucfirst($name);
            $this->register(new $middleware());
        }
    }

    /**
     * 注册中间件.
     *
     * counterpart of interceptor in java struts2
     *
     * 越晚注册的中间件，越早被执行
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
     * 获取已经注册的所有中间件.
     *
     * 实际上不该暴露，但为了单元测试，不得不暴露出来，以便检验register()是否正确
     *
     * @return array List of {@link DHttp_Middleware}
     */
    public final function getMiddlewares()
    {
        return $this->_middlewares;
    }

    /**
     * 注册hook.
     *
     * @param string $name Hook name
     * @param mixed $callable A callable object
     * @param int $priority 越小越优先执行
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
     * 执行hook.
     *
     * @param string $name Hook name
     * @param mixed $hookArg 目前还没用上
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
     * 设置或取值当前应用的action.
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
     * 立即结束.
     *
     * 它会把当前上下文里的{@link DHttp_Response}立即输出给浏览器
     *
     * 供{@link DHttp_Action}使用.
     *
     * @throws KHttp_Exception_Stop
     */
    public final function stop()
    {
        throw new KHttp_Exception_Stop();
    }

    /**
     * 立即结束，并给浏览器发送状态码和正文.
     *
     * 它会覆盖现有上下文里的{@link DHttp_Response}对象
     *
     * 供{@link DHttp_Action}使用.
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
     * 页面重定向.
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
     * 转发给某个action处理请求.
     *
     * 注意与`redirect()`的不同：
     * 内部跳转和外部跳转的区别，前者在跳转时保留request/response信息
     *
     * 只能在action method里调用 !!!
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
     * 启动应用并把结果输出给浏览器.
     *
     * 它应该是应用程序的最后一行语句!
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

        // CatchAll永远是第一个中间件, head，洋葱卷的最外侧
        $this->register(new KHttp_Middleware_CatchAll());

        // 让CatchAll运行，它会带动后面的中间件运行
        $this->_middlewareHead()->call();

        // 输出响应给浏览器
        $this->_displayResponse();
    }

    /**
     * Tail of the middlewares of the application.
     *
     * 它的任务就是处理请求，执行最终页面的action，然后把结果输出给浏览器.
     *
     * 由于它总是最后一个执行的中间件，因此它就没有必要再把接力棒给下一个了
     *
     * hook机制、result输出，都是在此实现的
     */
    public function call()
    {
        try
        {
            $this->_invokeHook(self::HOOK_BEFORE);

            ob_start();

            $this->_invokeHook(self::HOOK_BEFORE_DISPATCH);

            // 让controller干活，执行action
            list($action, $resultName) = $this->_controller->dispatch();

            // 对result进行处理
            $resolver = new DHttp_ResultResolver($this->_config, $action, $resultName);
            $resultContent = $resolver->resolve();

            // 输出action结果
            $this->_response->write($resultContent);

            $this->_invokeHook(self::HOOK_AFTER_DISPATCH);

            /*
             * 输出output buffer，并关闭
             *
             * action里用echo/print_r等输出的，都在这里得到
             * 它们会放在$response->write()的后面
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
             * 这是action抛出的异常
             *
             * action里已有的输出完全抛弃
             */
            ob_end_clean();

            // 交给KHttp_Middleware_CatchAll处理
            throw $ex;
        }

    }

    /**
     * 包装php的header方法，便于单元测试.
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

        // 给浏览器发送响应头
        if (headers_sent() === false)
        {
            // status必须第一个输出
            $this->_header($this->_response->renderStatus(PHP_SAPI,
                $this->_request->getServerProcotol(), $status));

            // 输出其他的HTTP header
            foreach($header->toArray() as $headerLine)
            {
                $this->_header($headerLine, $replace = false);
            }
        }

        // 给浏览器发送响应正文
        echo $body;
    }

    /**
     * 打开annotation特性.
     *
     * 实际上就是require对应的文件，因为annotation机制目前不支持自动加载
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
