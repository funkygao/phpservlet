<?php
/**
 * �м���Ľӿ�.
 *
 * �����м����Ҫʵ�ֱ��ӿ�
 *
 * ÿ���м���Ŀ�����
 * <ul>
 * <li>setApp</li>
 * <li>setNext</li>
 * <li>call</li>
 * </ul>
 *
 *
 * ��������о���
 * <pre>
 *
 *     ----------------------------------------
 *    |                        CatchAll        |
 *    |  --------------------------------------|
 *    | |                      Profiler        |
 *    | |  ------------------------------------|
 *    | | |                    MiddlewareN...  |    
 *    | | |  ----------------------------------|
 *    | | | |                  MiddlewareN-1   |
 *    | | | |  --------------------------------|
 *    | | | | |                App --> Action  |
 *    | | | |  --------------------------------|
 *    | | | |                                  |
 *    | | |  ----------------------------------|
 *    | | |                                    |
 *    | |  ------------------------------------|
 *    | |                                      |
 *    |  --------------------------------------|
 *    |                                        |
 *     ----------------------------------------
 *
 * </pre>
 *
 * @category http
 * @package http
 * @version $Id:$
 * @author gaopeng <gaopeng@corp.kaixin001.com>
 *
 * vim: set sw=4 ts=4 et:
 */
interface DHttp_IMiddleware
{

    /**
     * �м��������ִ���߼�.
     *
     * ��app���ȡrequest/response�������Ը�����ֵ���Ӷ�������Ϊ��
     * ���⣬������ͨ��{@link DHttp_App}��hook��ʵ�ָ���Χ����
     * ���ڵ���Ϊ
     *
     * ���������Ƿ������һ���м����������������������ʾֱ�������
     */
    public function call();

}