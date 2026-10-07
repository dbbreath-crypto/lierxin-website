-- 利尔鑫官网后台 数据库结构
-- 本地初始化：mysql -u root -e "source database/schema.sql" 导入前先 CREATE DATABASE
-- 服务器初始化：mysql -u lex -p lex < database/schema.sql
-- 说明：均为 IF NOT EXISTS，可重复执行，不会覆盖已有数据

CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL COMMENT '姓名',
  `phone` VARCHAR(30) NOT NULL COMMENT '联系电话',
  `email` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '电子邮箱',
  `company` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '公司名称',
  `product` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '咨询产品',
  `content` TEXT NOT NULL COMMENT '留言内容',
  `ip` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '来源IP',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '浏览器标识',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否已读 0未读 1已读',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_read` (`is_read`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='在线留言';

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL COMMENT '管理员账号',
  `password_hash` VARCHAR(255) NOT NULL COMMENT '密码哈希',
  `last_login_at` DATETIME NULL COMMENT '最后登录时间',
  `last_login_ip` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='后台管理员';

CREATE TABLE IF NOT EXISTS `news` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '自定义标识，用于幂等导入',
  `title` VARCHAR(200) NOT NULL COMMENT '标题',
  `tag` VARCHAR(30) NOT NULL DEFAULT '' COMMENT '分类：企业动态/技术前沿/资质荣誉 等',
  `cover` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '封面图路径，如 images/news/news-1.jpg',
  `excerpt` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '摘要',
  `content` MEDIUMTEXT NULL COMMENT '正文（富文本 HTML，保存时过白名单）',
  `published_date` DATE NOT NULL COMMENT '发布日期',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '状态 1已发布 0草稿',
  `sort_weight` INT NOT NULL DEFAULT 0 COMMENT '排序权重，越大越靠前',
  `views` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览量',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_status_date` (`status`, `published_date`),
  KEY `idx_sort` (`sort_weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='资讯中心';

CREATE TABLE IF NOT EXISTS `solutions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '自定义标识，用于幂等导入',
  `title` VARCHAR(200) NOT NULL COMMENT '行业名称，如 汽车电子',
  `en_title` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '英文标识，如 AUTOMOTIVE',
  `cover` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '封面图路径，如 images/solutions/automotive.jpg',
  `summary` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '列表页摘要',
  `highlights` TEXT NULL COMMENT '方案亮点，每行一条',
  `content` MEDIUMTEXT NULL COMMENT '方案详情正文（富文本 HTML，保存时过白名单）',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '状态 1已发布 0草稿',
  `sort_weight` INT NOT NULL DEFAULT 0 COMMENT '排序权重，越大越靠前',
  `views` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览量',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_status_sort` (`status`, `sort_weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='行业方案';

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '自定义标识，用于幂等导入',
  `title` VARCHAR(200) NOT NULL COMMENT '产品名称，如 HDI线路板',
  `en_title` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '英文标识，如 HDI BOARD',
  `cover` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '封面图路径，如 images/products/hdi.jpg',
  `short_desc` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '列表页简介',
  `features` TEXT NULL COMMENT '卡片标签，每行一条',
  `detail_tag` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '详情页顶部英文标签',
  `detail_heading` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '详情页小标题',
  `detail_desc` TEXT NULL COMMENT '详情页描述段落',
  `detail_list` TEXT NULL COMMENT '详情页要点，每行一条',
  `specs` TEXT NULL COMMENT '规格参数，每行一条，格式：数值|名称',
  `content` MEDIUMTEXT NULL COMMENT '补充说明（富文本 HTML，可选）',
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '状态 1已发布 0草稿',
  `sort_weight` INT NOT NULL DEFAULT 0 COMMENT '排序权重，越大越靠前',
  `views` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览量',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_slug` (`slug`),
  KEY `idx_status_sort` (`status`, `sort_weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='产品中心';

CREATE TABLE IF NOT EXISTS `quotes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL COMMENT '联系人',
  `phone` VARCHAR(30) NOT NULL COMMENT '联系电话',
  `email` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '邮箱',
  `company` VARCHAR(120) NOT NULL DEFAULT '' COMMENT '公司名称',
  `params` MEDIUMTEXT NULL COMMENT '报价参数 JSON（尺寸/层数/工艺等）',
  `estimate_total` DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT '系统估算总价（元）',
  `estimate_unit` DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT '系统估算单片价（元）',
  `lead_time` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '预计交期文案',
  `remark` TEXT NULL COMMENT '备注',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '处理状态 0待联系 1已联系 2已成交 3已关闭',
  `admin_note` TEXT NULL COMMENT '后台备注',
  `ip` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '来源IP',
  `user_agent` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '浏览器标识',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_status` (`status`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='在线报价询价';

CREATE TABLE IF NOT EXISTS `quote_config` (
  `cfg_key` VARCHAR(60) NOT NULL COMMENT '参数键，格式 分组.项，如 sqm.2 / test.flyingMin',
  `cfg_value` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '参数值（数字，覆盖 inc/quote_config.php 里的默认值）',
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cfg_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='在线报价参数（仅存被覆盖的项）';

-- 本地开发环境默认管理员：admin / admin123（仅限本机，切勿用于生产）
INSERT INTO `admins` (`username`, `password_hash`)
SELECT 'admin', '$2b$10$jCpWpbTPwcck.RWy0a58suXfsPe/ts5vJfx4Reh/j03.c0tahDehK'
WHERE NOT EXISTS (SELECT 1 FROM `admins` WHERE `username` = 'admin');
