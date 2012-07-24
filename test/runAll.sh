#!/bin/sh
#-----------------------------------------------------
#
# һ�������е�ǰĿ¼�����е�Ԫ����
#
# ��Ȼ'kxmake test'Ҳ������ɣ�������ִ��ʱ̫�ϸ���
# �����������ˣ�
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

# ���е�ǰĿ¼�µ����е�Ԫ��������
#================================
for testcase in `ls *_Test.php`:
do
    current="${PURPLE}${testcase}${RESET}"
    echo -e $current
    phpunit --colors $testcase

    show_seperator
done


# ����������Ŀ¼�µĵ�Ԫ��������
#===============================
for testcase in `ls -F | grep /`:
do
    current="${PURPLE}${testcase}${RESET}"
    echo -e $current
    phpunit --colors --process-isolation $testcase

    show_seperator
done

