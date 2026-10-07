#!/bin/bash
# 利尔鑫官网 —— 本地开发环境一键管理
#
# 本机组件（均在用户目录，不污染系统）：
#   MySQL 8.4.4  ~/local/mysql        （官方 macOS ARM tarball）
#   PHP  8.3.x   ~/.local/php         （源码编译；若用 Homebrew 版会自动识别）
#   数据目录     ~/local/mysql-data
#
# 用法：
#   ./scripts/dev.sh start     启动 MySQL + PHP 服务
#   ./scripts/dev.sh stop      停止本地服务
#   ./scripts/dev.sh status    查看服务状态
#   ./scripts/dev.sh reset-db  重建本地数据库（清空留言，重置后台账号）
#   ./scripts/dev.sh log       查看 PHP 服务日志

set -u

SITE_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PORT="${LEX_PORT:-8899}"
DB_NAME="lex"
LOG_FILE="/tmp/lex-local-server.log"

MYSQL_BASE="$HOME/local/mysql"
MYSQL_DATA="$HOME/local/mysql-data"
SOCKET="/tmp/mysql.sock"

# PHP 可执行文件：按常见位置依次探测（源码编译版 / Herd / MAMP / Homebrew）
PHP_BIN=""
for candidate in \
    "$HOME/.local/php/bin/php" \
    "$HOME/.config/herd/bin/php" \
    "$HOME/Library/Application Support/Herd/bin/php" \
    "/Applications/Herd.app/Contents/Resources/php/bin/php" \
    "/Applications/MAMP/bin/php/php8.3.13/bin/php" \
    "/Applications/XAMPP/bin/php" \
    "$HOME/.homebrew/opt/php/bin/php" \
    "$HOME/.homebrew/bin/php"; do
    if [ -x "$candidate" ]; then PHP_BIN="$candidate"; break; fi
done
# 兜底：PATH 里的 php
[ -z "$PHP_BIN" ] && command -v php >/dev/null 2>&1 && PHP_BIN="$(command -v php)"

mysql_alive() {
    "$MYSQL_BASE/bin/mysqladmin" --socket="$SOCKET" ping 2>/dev/null | grep -q "alive"
}

ensure_mysql() {
    if mysql_alive; then
        echo "· MySQL 已在运行"
        return 0
    fi
    echo "· 启动 MySQL ..."
    # --mysql-native-password：本地 PHP 未编译 openssl，不支持 caching_sha2_password
    nohup "$MYSQL_BASE/bin/mysqld" \
        --datadir="$MYSQL_DATA" \
        --socket="$SOCKET" \
        --port=3306 \
        --mysqlx=0 \
        --skip-name-resolve \
        --mysql-native-password=ON > /tmp/mysqld.log 2>&1 &

    local i
    for i in $(seq 1 40); do
        mysql_alive && break
        sleep 1
    done
    mysql_alive && echo "· MySQL 就绪 ($("$MYSQL_BASE/bin/mysql" --socket="$SOCKET" -u root -N -e "SELECT VERSION();" 2>/dev/null))" \
        || { echo "✗ MySQL 启动失败，查看 /tmp/mysqld.log"; return 1; }
}

ensure_db() {
    local M="$MYSQL_BASE/bin/mysql"
    "$M" --socket="$SOCKET" -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
    "$M" --socket="$SOCKET" -u root -e "CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED WITH caching_sha2_password BY ''; GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;" 2>/dev/null
    # 注意：--default-character-set 不可省，否则 mysql CLI 默认 latin1 会把中文档双重编码
    "$M" --socket="$SOCKET" -u root --default-character-set=utf8mb4 "${DB_NAME}" < "${SITE_DIR}/database/schema.sql" 2>/dev/null
    if [ -f "${SITE_DIR}/database/seed_news.sql" ]; then
        "$M" --socket="$SOCKET" -u root --default-character-set=utf8mb4 "${DB_NAME}" < "${SITE_DIR}/database/seed_news.sql" 2>/dev/null
    fi
    if [ -f "${SITE_DIR}/database/seed_solutions.sql" ]; then
        "$M" --socket="$SOCKET" -u root --default-character-set=utf8mb4 "${DB_NAME}" < "${SITE_DIR}/database/seed_solutions.sql" 2>/dev/null
    fi
    if [ -f "${SITE_DIR}/database/seed_products.sql" ]; then
        "$M" --socket="$SOCKET" -u root --default-character-set=utf8mb4 "${DB_NAME}" < "${SITE_DIR}/database/seed_products.sql" 2>/dev/null
    fi
    echo "· 数据库 ${DB_NAME} / 表结构 / 初始资讯、行业方案与产品内容已就绪"
}

start_php() {
    if lsof -nP -iTCP:"${PORT}" -sTCP:LISTEN >/dev/null 2>&1; then
        echo "· PHP 服务已在 ${PORT} 端口运行"
        return 0
    fi
    if [ ! -x "$PHP_BIN" ]; then
        echo "✗ 找不到 PHP（${PHP_BIN}），请先安装 PHP"
        return 1
    fi
    echo "· 启动 PHP 服务（端口 ${PORT}）..."
    (cd "${SITE_DIR}" && nohup "$PHP_BIN" -S "127.0.0.1:${PORT}" -t "${SITE_DIR}" >"${LOG_FILE}" 2>&1 &)
    sleep 2
    return 0
}

case "${1:-start}" in
    start)
        [ -f "${SITE_DIR}/.local" ] || touch "${SITE_DIR}/.local"
        # 用 Python 的 setsid 启动：bash 里 nohup 的进程会随 shell 会话被回收
        if command -v python3 >/dev/null 2>&1 && [ -f "${SITE_DIR}/scripts/dev_launch.py" ]; then
            python3 "${SITE_DIR}/scripts/dev_launch.py" || exit 1
        else
            ensure_mysql || exit 1
            start_php
            echo ""
            echo "✓ 本地环境已启动"
            echo "  前台首页：http://localhost:${PORT}/"
            echo "  在线留言：http://localhost:${PORT}/index.html#/contact"
            echo "  管理后台：http://localhost:${PORT}/admin/login.php   （admin / admin123）"
        fi
        # 顺带确保表结构存在（幂等）
        mysql_alive && ensure_db >/dev/null
        ;;
    stop)
        pkill -f "php -S 127.0.0.1:${PORT}" 2>/dev/null && echo "· PHP 服务已停止" || echo "· PHP 服务未运行"
        "$MYSQL_BASE/bin/mysqladmin" --socket="$SOCKET" -u root shutdown 2>/dev/null && echo "· MySQL 已停止" || echo "· MySQL 未运行"
        ;;
    status)
        printf "%-12s " "MySQL:"; mysql_alive && echo "运行中" || echo "未运行"
        printf "%-12s " "PHP:"; lsof -nP -iTCP:"${PORT}" -sTCP:LISTEN >/dev/null 2>&1 && echo "运行中 (${PORT})" || echo "未运行"
        if mysql_alive; then
            "$MYSQL_BASE/bin/mysql" --socket="$SOCKET" -u root -e "SELECT COUNT(*) AS 留言数 FROM ${DB_NAME}.messages;" 2>/dev/null
        fi
        ;;
    reset-db)
        ensure_mysql || exit 1
        "$MYSQL_BASE/bin/mysql" --socket="$SOCKET" -u root -e "DROP DATABASE IF EXISTS \`${DB_NAME}\`;" 2>/dev/null
        echo "· 已删除旧库"
        ensure_db
        echo "· 后台账号已重置：admin / admin123"
        ;;
    log)
        tail -n 40 "${LOG_FILE}" 2>/dev/null || echo "暂无日志"
        ;;
    *)
        echo "用法：$0 [start|stop|status|reset-db|log]"
        ;;
esac
