<?php
/**
 * 在线报价「参数因子」配置层
 *
 * 设计：
 *   - 默认参数写在本文件 quote_config_schema() 里（与 js/quote.js 的默认值一致）
 *   - 后台「报价参数」保存的覆盖值存在 quote_config 表（只存被改过的项）
 *   - 读取时 默认值 + 数据库覆盖 合并，输出给前台 api/quote_config.php
 *   - 数据库不可用时自动退回默认值，前台页面不会打不开
 */
if (!defined('LEX_APP')) {
    http_response_code(403);
    exit('Forbidden');
}
if (!function_exists('db')) {
    require __DIR__ . '/bootstrap.php';
}

/**
 * 参数定义（分组 → 项）
 * 每项：label 中文名 / unit 单位说明 / default 默认值 / step 步长
 */
function quote_config_schema(): array
{
    return [
        'base' => [
            'label' => '基础费用与规则',
            'desc'  => '最低消费、税率、快递费与页面取值限制',
            'items' => [
                'minBoard'     => ['label' => '板费最低消费', 'unit' => '元', 'default' => 300, 'step' => 10],
                'taxRate'      => ['label' => '发票税率', 'unit' => '0.13 = 13%', 'default' => 0.13, 'step' => 0.01],
                'shipFee'      => ['label' => '快递费', 'unit' => '元', 'default' => 25, 'step' => 1],
                'minQty'       => ['label' => '最小下单数量', 'unit' => 'pcs', 'default' => 5, 'step' => 1],
                'maxSize'      => ['label' => '单边最大尺寸', 'unit' => 'cm', 'default' => 120, 'step' => 1],
                'tipSmallSize' => ['label' => '小尺寸拼板提示阈值', 'unit' => 'cm', 'default' => 7.6, 'step' => 0.1],
                'weightFactor' => ['label' => '重量系数', 'unit' => 'kg/㎡（板厚 1.6mm）', 'default' => 2.4, 'step' => 0.1],
            ],
        ],
        'sqm' => [
            'label' => '每平米基准单价',
            'desc'  => '按层数计，单位 元/㎡',
            'items' => [
                1 => ['label' => '1 层', 'unit' => '元/㎡', 'default' => 320, 'step' => 10],
                2 => ['label' => '2 层', 'unit' => '元/㎡', 'default' => 460, 'step' => 10],
                4 => ['label' => '4 层', 'unit' => '元/㎡', 'default' => 980, 'step' => 10],
                6 => ['label' => '6 层', 'unit' => '元/㎡', 'default' => 1500, 'step' => 10],
                8 => ['label' => '8 层', 'unit' => '元/㎡', 'default' => 2100, 'step' => 10],
            ],
        ],
        'setup' => [
            'label' => '工程费',
            'desc'  => '按层数计，单位 元/款',
            'items' => [
                1 => ['label' => '1 层', 'unit' => '元/款', 'default' => 200, 'step' => 10],
                2 => ['label' => '2 层', 'unit' => '元/款', 'default' => 200, 'step' => 10],
                4 => ['label' => '4 层', 'unit' => '元/款', 'default' => 400, 'step' => 10],
                6 => ['label' => '6 层', 'unit' => '元/款', 'default' => 600, 'step' => 10],
                8 => ['label' => '8 层', 'unit' => '元/款', 'default' => 800, 'step' => 10],
            ],
        ],
        'material' => [
            'label' => '板材加价',
            'desc'  => '单位 元/㎡，可为 0',
            'items' => [
                'fr4a'     => ['label' => 'FR-4 A级', 'unit' => '元/㎡', 'default' => 0, 'step' => 10],
                'fr4kb'    => ['label' => 'FR-4 KB料', 'unit' => '元/㎡', 'default' => 10, 'step' => 10],
                'aluminum' => ['label' => '铝基板', 'unit' => '元/㎡', 'default' => 350, 'step' => 10],
                'rogers'   => ['label' => 'Rogers 高频', 'unit' => '元/㎡', 'default' => 1200, 'step' => 10],
            ],
        ],
        'copper' => [
            'label' => '铜厚加价',
            'desc'  => '单位 元/㎡',
            'items' => [
                1 => ['label' => '1 oz', 'unit' => '元/㎡', 'default' => 0, 'step' => 10],
                2 => ['label' => '2 oz', 'unit' => '元/㎡', 'default' => 150, 'step' => 10],
            ],
        ],
        'solder' => [
            'label' => '阻焊颜色加价',
            'desc'  => '单位 元/㎡，绿色通常为 0',
            'items' => [
                'green'       => ['label' => '绿色', 'unit' => '元/㎡', 'default' => 0, 'step' => 5],
                'white'       => ['label' => '白色', 'unit' => '元/㎡', 'default' => 10, 'step' => 5],
                'black'       => ['label' => '黑色', 'unit' => '元/㎡', 'default' => 10, 'step' => 5],
                'blue'        => ['label' => '蓝色', 'unit' => '元/㎡', 'default' => 10, 'step' => 5],
                'yellow'      => ['label' => '黄色', 'unit' => '元/㎡', 'default' => 10, 'step' => 5],
                'red'         => ['label' => '红色', 'unit' => '元/㎡', 'default' => 10, 'step' => 5],
                'matte-black' => ['label' => '哑光黑', 'unit' => '元/㎡', 'default' => 10, 'step' => 5],
            ],
        ],
        'silk' => [
            'label' => '字符加价',
            'desc'  => '单位 元/㎡',
            'items' => [
                'single' => ['label' => '单面字符', 'unit' => '元/㎡', 'default' => 0, 'step' => 5],
                'double' => ['label' => '双面字符', 'unit' => '元/㎡', 'default' => 5, 'step' => 5],
            ],
        ],
        'surface' => [
            'label' => '表面处理加价',
            'desc'  => '单位 元/㎡，负数表示优惠',
            'items' => [
                'hasl'    => ['label' => '有铅喷锡', 'unit' => '元/㎡', 'default' => 0, 'step' => 10],
                'hasl-lf' => ['label' => '无铅喷锡', 'unit' => '元/㎡', 'default' => 20, 'step' => 10],
                'enig'    => ['label' => '沉金（ENIG）', 'unit' => '元/㎡', 'default' => 180, 'step' => 10],
                'osp'     => ['label' => 'OSP 抗氧化', 'unit' => '元/㎡', 'default' => -5, 'step' => 5],
            ],
        ],
        'via' => [
            'label' => '过孔处理加价',
            'desc'  => '单位 元/㎡',
            'items' => [
                'tented' => ['label' => '过孔盖油', 'unit' => '元/㎡', 'default' => 0, 'step' => 5],
                'open'   => ['label' => '过孔开窗', 'unit' => '元/㎡', 'default' => 0, 'step' => 5],
                'filled' => ['label' => '过孔塞油', 'unit' => '元/㎡', 'default' => 20, 'step' => 5],
            ],
        ],
        'special' => [
            'label' => '特殊工艺加价',
            'desc'  => '单位 元/款（会乘以拼板款数）',
            'items' => [
                'half-hole'  => ['label' => '半孔工艺', 'unit' => '元/款', 'default' => 100, 'step' => 10],
                'bevel'      => ['label' => '斜边（金手指）', 'unit' => '元/款', 'default' => 150, 'step' => 10],
                'impedance'  => ['label' => '阻抗控制', 'unit' => '元/款', 'default' => 200, 'step' => 10],
                'bga'        => ['label' => 'BGA 封装', 'unit' => '元/款', 'default' => 50, 'step' => 10],
                'via-fill'   => ['label' => '树脂塞孔', 'unit' => '元/款', 'default' => 300, 'step' => 10],
                'edge-metal' => ['label' => '金属包边', 'unit' => '元/款', 'default' => 300, 'step' => 10],
            ],
        ],
        'report' => [
            'label' => '检验报告加价',
            'desc'  => '单位 元/款',
            'items' => [
                'none' => ['label' => '不需要', 'unit' => '元/款', 'default' => 0, 'step' => 10],
                'coc'  => ['label' => '出货检验报告 COC', 'unit' => '元/款', 'default' => 50, 'step' => 10],
                'fai'  => ['label' => '全尺寸报告 FAI', 'unit' => '元/款', 'default' => 150, 'step' => 10],
            ],
        ],
        'dateCode' => [
            'label' => '印周期加价',
            'desc'  => '单位 元/款',
            'items' => [
                'none' => ['label' => '不印', 'unit' => '元/款', 'default' => 0, 'step' => 10],
                'yw'   => ['label' => '年周（YYWW）', 'unit' => '元/款', 'default' => 30, 'step' => 10],
                'ymd'  => ['label' => '年月日（YYMMDD）', 'unit' => '元/款', 'default' => 30, 'step' => 10],
            ],
        ],
        'test' => [
            'label' => '测试费',
            'desc'  => '飞针按面积、电测按款数',
            'items' => [
                'flyingRate'   => ['label' => '飞针测试单价', 'unit' => '元/㎡', 'default' => 50, 'step' => 5],
                'flyingMin'    => ['label' => '飞针测试起步价', 'unit' => '元', 'default' => 150, 'step' => 10],
                'fixtureSmall' => ['label' => '电测（小批量）', 'unit' => '元/款', 'default' => 500, 'step' => 10],
                'fixtureLarge' => ['label' => '电测（大批量）', 'unit' => '元/款', 'default' => 1000, 'step' => 10],
                'fixtureArea'  => ['label' => '电测大批量面积阈值', 'unit' => '㎡', 'default' => 5, 'step' => 1],
            ],
        ],
        'tooling' => [
            'label' => '模具费',
            'desc'  => '仅「模冲」成型时计取',
            'items' => [
                'punchRate' => ['label' => '模冲模具系数', 'unit' => '元/cm（长+宽）', 'default' => 80, 'step' => 5],
                'punchMin'  => ['label' => '模冲模具起步价', 'unit' => '元', 'default' => 1500, 'step' => 50],
            ],
        ],
        'lead' => [
            'label' => '基准交期',
            'desc'  => '按层数计，单位 工作日',
            'items' => [
                1 => ['label' => '1 层', 'unit' => '工作日', 'default' => 3, 'step' => 1],
                2 => ['label' => '2 层', 'unit' => '工作日', 'default' => 4, 'step' => 1],
                4 => ['label' => '4 层', 'unit' => '工作日', 'default' => 6, 'step' => 1],
                6 => ['label' => '6 层', 'unit' => '工作日', 'default' => 8, 'step' => 1],
                8 => ['label' => '8 层', 'unit' => '工作日', 'default' => 10, 'step' => 1],
            ],
        ],
        'leadAdd' => [
            'label' => '交期追加',
            'desc'  => '在基准交期上叠加的工作日',
            'items' => [
                'enig'      => ['label' => '沉金工艺', 'unit' => '工作日', 'default' => 2, 'step' => 1],
                'impedance' => ['label' => '阻抗控制', 'unit' => '工作日', 'default' => 1, 'step' => 1],
                'punch'     => ['label' => '模冲成型', 'unit' => '工作日', 'default' => 3, 'step' => 1],
                'rogers'    => ['label' => 'Rogers 板材', 'unit' => '工作日', 'default' => 3, 'step' => 1],
                'aluminum'  => ['label' => '铝基板', 'unit' => '工作日', 'default' => 1, 'step' => 1],
                'qty1000'   => ['label' => '数量 ≥ 1000pcs', 'unit' => '工作日', 'default' => 2, 'step' => 1],
                'qty5000'   => ['label' => '数量 ≥ 5000pcs', 'unit' => '工作日', 'default' => 3, 'step' => 1],
            ],
        ],
    ];
}

/**
 * 默认值（扁平：分组.项 => 值）
 */
function quote_config_defaults(): array
{
    static $flat = null;
    if ($flat !== null) {
        return $flat;
    }
    $flat = [];
    foreach (quote_config_schema() as $group => $def) {
        foreach ($def['items'] as $key => $item) {
            $flat[$group . '.' . $key] = $item['default'];
        }
    }
    return $flat;
}

/**
 * 当前生效参数：默认值 + 数据库覆盖
 */
function quote_config_all(): array
{
    static $merged = null;
    if ($merged !== null) {
        return $merged;
    }
    $merged = quote_config_defaults();
    try {
        $rows = db()->query('SELECT cfg_key, cfg_value FROM quote_config')->fetchAll();
        foreach ($rows as $r) {
            $key = (string) $r['cfg_key'];
            if (!array_key_exists($key, $merged)) {
                continue;                       // 忽略已下线或拼错的键
            }
            if (is_numeric((string) $r['cfg_value'])) {
                $merged[$key] = ((string) $r['cfg_value']) + 0;
            }
        }
    } catch (Throwable $ex) {
        // 表不存在或数据库异常时直接用默认值
    }
    return $merged;
}

/**
 * 取某个参数（找不到就用默认）
 */
function quote_cfg(string $key, $default = 0)
{
    $all = quote_config_all();
    return array_key_exists($key, $all) ? $all[$key] : $default;
}

/**
 * 转成前台 JS 用的嵌套结构
 */
function quote_config_js(): array
{
    $c = quote_config_all();
    $pick = static function (array $keys, string $group) use ($c): array {
        $out = [];
        foreach ($keys as $k) {
            $flat = $group . '.' . $k;
            $out[(string) $k] = array_key_exists($flat, $c) ? (float) $c[$flat] : 0.0;
        }
        return $out;
    };

    return [
        'sqm'      => $pick([1, 2, 4, 6, 8], 'sqm'),
        'setup'    => $pick([1, 2, 4, 6, 8], 'setup'),
        'material' => $pick(['fr4a', 'fr4kb', 'aluminum', 'rogers'], 'material'),
        'copper'   => $pick([1, 2], 'copper'),
        'solder'   => $pick(['green', 'white', 'black', 'blue', 'yellow', 'red', 'matte-black'], 'solder'),
        'silk'     => $pick(['single', 'double'], 'silk'),
        'surface'  => $pick(['hasl', 'hasl-lf', 'enig', 'osp'], 'surface'),
        'via'      => $pick(['tented', 'open', 'filled'], 'via'),
        'special'  => $pick(['half-hole', 'bevel', 'impedance', 'bga', 'via-fill', 'edge-metal'], 'special'),
        'report'   => $pick(['none', 'coc', 'fai'], 'report'),
        'dateCode' => $pick(['none', 'yw', 'ymd'], 'dateCode'),
        'test'     => $pick(['flyingRate', 'flyingMin', 'fixtureSmall', 'fixtureLarge', 'fixtureArea'], 'test'),
        'tooling'  => $pick(['punchRate', 'punchMin'], 'tooling'),
        'leadBase' => $pick([1, 2, 4, 6, 8], 'lead'),
        'leadAdd'  => $pick(['enig', 'impedance', 'punch', 'rogers', 'aluminum', 'qty1000', 'qty5000'], 'leadAdd'),
        'minBoard' => (float) quote_cfg('base.minBoard', 300),
        'taxRate'  => (float) quote_cfg('base.taxRate', 0.13),
        'shipFee'  => (float) quote_cfg('base.shipFee', 25),
        'limits'   => [
            'minQty'       => (float) quote_cfg('base.minQty', 5),
            'maxSize'      => (float) quote_cfg('base.maxSize', 120),
            'tipSmallSize' => (float) quote_cfg('base.tipSmallSize', 7.6),
            'weightFactor' => (float) quote_cfg('base.weightFactor', 2.4),
        ],
    ];
}

/**
 * 保存后台提交的参数（只接受 schema 里存在且为数字的项）
 *
 * @param array $values 扁平数组 [ 'sqm.2' => '500', ... ]
 * @return int 实际保存的项数
 */
function quote_config_save(array $values): int
{
    $defaults = quote_config_defaults();
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO quote_config (cfg_key, cfg_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE cfg_value = VALUES(cfg_value)'
    );
    $del = $pdo->prepare('DELETE FROM quote_config WHERE cfg_key = ?');

    $saved = 0;
    foreach ($values as $key => $value) {
        $key = (string) $key;
        if (!array_key_exists($key, $defaults)) {
            continue;                            // 非法的键直接丢弃
        }
        if (is_array($value) || !is_numeric((string) $value)) {
            continue;                            // 非数字丢弃
        }
        $num = (float) $value;
        if (!is_finite($num) || $num < -100000 || $num > 1000000) {
            continue;
        }
        // 与默认值一致时不写库，保持「只存覆盖项」
        if (abs($num - (float) $defaults[$key]) < 1e-9) {
            $del->execute([$key]);
            continue;
        }
        $stmt->execute([$key, rtrim(rtrim(number_format($num, 4, '.', ''), '0'), '.')]);
        $saved++;
    }
    return $saved;
}

/**
 * 恢复默认（清空所有覆盖项）
 */
function quote_config_reset(): void
{
    try {
        db()->exec('DELETE FROM quote_config');
    } catch (Throwable $ex) {
        // 忽略
    }
}

/**
 * 最后一次修改时间
 */
function quote_config_updated_at(): string
{
    try {
        $v = db()->query('SELECT MAX(updated_at) FROM quote_config')->fetchColumn();
        return $v ? (string) $v : '';
    } catch (Throwable $ex) {
        return '';
    }
}
