=========================
http 框架特点
=========================

:Author: Gao Peng <gaopeng@corp.kaixin001.com>
:Revision: $Id$

.. contents:: Table Of Contents
.. section-numbering::

设计特点
============================

- 很像struts2/pylons/rails/rose

- 面向对象 SOLID

- seperation of concerns，各司其职

- 面向接口，采用组合和注入，而非继承

  有继承，但都是为了提供helper method，而且层级不超过2

- 让使用者滥用的可能性降低

  滥用，我就哭叫！

- 松耦合，testable codes，readable code

- fully documented

- 支持插件，AOP

- 易于扩展

- 应用容器，统一规范，convention over configuration

- 没有使用一个magic method，IDE friendly，programmer friendly

- 充分的单元测试，over 2000 assertions

- no magic number

- 利用annotation机制简化开发

  减少代码量


功能特性
=======================

- xhprof on demand

- session

- request/response

- 不用担心headers already sent

- 不编写一行模版相关的代码，完全封装

- 不编写一行获取参数的代码，都使用annotation完成

  不再使用CInput

- 上下文信息

- 统一异常处理

- 安全处理，xss/csrf

- graceful degrade

- 统一的tracker，统一的xxx，因为插件机制


使用者
===========

- 虽然这个框架很多代码，我根本不需要关心

  我只关心action！ yes！！ only action！！！

  我根本不需要了解其内部机制，那是container理所应当做的。
  
  我只做分析请求，处理数据，然后告诉容器谁来展示结果，至于何时展示、如何展示，我不关心！

- 是有些限制的

  - 不能使用head()

  - 不能使用die/exit

- 开发者被分成2类

  #. action developer

  #. plugin developer


Drawbacks
=========

- DHttp_ContextUtil

- DHttp_Config

  会在后面去掉这个类，采用conventions over configuration

- forward feature not fully realized

- 目前还没有实现invlude virtual那样的组件机制


TODO's
======

- front controller, router

- urlrewrite


