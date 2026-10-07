#!/usr/bin/env python3
"""
以脱离当前终端会话的方式启动本地服务（macOS 无 setsid 命令，用 os.setsid 实现）。

Bash 里 nohup 起来的后台进程会随 shell 会话一起被回收，
这里用 start_new_session=True 让 mysqld / php -S 真正独立于会话存在。
"""
import os
import subprocess
import sys

HOME = os.path.expanduser("~")
SITE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def spawn(argv, log_path, cwd=None):
    """启动一个脱离会话的常驻进程；已在运行则返回 None。"""
    log = open(log_path, "ab", buffering=0)
    p = subprocess.Popen(
        argv,
        cwd=cwd or SITE,
        stdin=subprocess.DEVNULL,
        stdout=log,
        stderr=subprocess.STDOUT,
        start_new_session=True,   # 关键：setsid，脱离调用方会话
        close_fds=True,
    )
    return p


def main():
    mysql_base = os.path.join(HOME, "local", "mysql")
    mysqld = os.path.join(mysql_base, "bin", "mysqld")
    mysqladmin = os.path.join(mysql_base, "bin", "mysqladmin")
    php = os.path.join(HOME, ".local", "php", "bin", "php")
    port = os.environ.get("LEX_PORT", "8899")

    # --- MySQL ---
    alive = subprocess.run(
        [mysqladmin, "--socket=/tmp/mysql.sock", "ping"],
        capture_output=True, text=True,
    ).stdout.strip()
    if "alive" in alive:
        print("· MySQL 已在运行")
    elif os.path.exists(mysqld):
        print("· 启动 MySQL ...")
        spawn([mysqld,
               "--datadir=" + os.path.join(HOME, "local", "mysql-data"),
               "--socket=/tmp/mysql.sock",
               "--port=3306", "--mysqlx=0", "--skip-name-resolve",
               # 本地 PHP 未编译 openssl，不支持 caching_sha2_password
               "--mysql-native-password=ON"],
              "/tmp/mysqld.log")
        import time
        for _ in range(40):
            time.sleep(1)
            out = subprocess.run([mysqladmin, "--socket=/tmp/mysql.sock", "ping"],
                                 capture_output=True, text=True).stdout
            if "alive" in out:
                print("· MySQL 就绪")
                break
        else:
            print("✗ MySQL 启动失败，查看 /tmp/mysqld.log")
            return 1
    else:
        print("✗ 未找到 mysqld：" + mysqld)
        return 1

    # --- PHP 内置服务器 ---
    import socket
    s = socket.socket()
    s.settimeout(0.5)
    port_busy = s.connect_ex(("127.0.0.1", int(port))) == 0
    s.close()

    if port_busy:
        print("· PHP 服务已在 %s 端口运行" % port)
    elif os.path.exists(php):
        print("· 启动 PHP 服务（端口 %s）..." % port)
        spawn([php, "-S", "127.0.0.1:" + port, "-t", SITE],
              "/tmp/lex-local-server.log")
        import time
        time.sleep(2)
        print("· PHP 服务已启动")
    else:
        print("✗ 未找到 PHP：" + php)
        return 1

    print("")
    print("✓ 本地环境已启动")
    print("  前台首页：http://localhost:%s/" % port)
    print("  在线留言：http://localhost:%s/index.html#/contact" % port)
    print("  管理后台：http://localhost:%s/admin/login.php   （admin / admin123）" % port)
    return 0


if __name__ == "__main__":
    sys.exit(main())
