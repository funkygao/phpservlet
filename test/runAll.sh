#!/bin/sh
#-----------------------------------------------------
#
# 一次性运行当前目录下所有单元测试
#
# 虽然'kxmake test'也可以完成，但那样执行时太严格了
# 好像它设置了：
# error_reporting(E_ALL | E_STRICT);
#
#-----------------------------------------------------

BLUE="\033[33;34m"
PURPLE="\033[33;35m"
RESET="\033[m"

function show_seperator() {
    for i in `seq 1 2`
    do
        echo
    done
}

# 运行当前目录下的所有单元测试用例
#================================
for testcase in `ls *_Test.php`:
do
    current="${PURPLE}${testcase}${RESET}"
    echo -e $current
    phpunit --colors $testcase

    show_seperator
done


# 运行所有子目录下的单元测试用例
#===============================
for testcase in `ls -F | grep /`:
do
    current="${PURPLE}${testcase}${RESET}"
    echo -e $current
    phpunit --colors --process-isolation $testcase

    show_seperator
done

