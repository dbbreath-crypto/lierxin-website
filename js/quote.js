/**
 * 在线报价系统（独立页面 quote.html，不依赖主站 app.js / 不进主导航）
 * 功能：参数选择 → 实时估算价格与交期 → 提交询价入库
 */
(function () {
    'use strict';

    // ---------- 主题继承主站选择 ----------
    try {
        if (localStorage.getItem('lierxin-theme') === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    } catch (e) { /* 忽略隐私模式异常 */ }

    // ---------- 价格模型 ----------
    // 默认值：与后台「报价参数」里的系统默认一致；
    // 页面启动后会拉取 api/quote_config.php 覆盖（后台改过的值优先），拉取失败则用这里的默认值。
    var DEFAULT_CFG = {
        sqm:      { 1: 320, 2: 460, 4: 980, 6: 1500, 8: 2100 },      // 每平米基准单价
        setup:    { 1: 200, 2: 200, 4: 400, 6: 600, 8: 800 },        // 工程费（每款）
        material: { fr4a: 0, fr4kb: 10, aluminum: 350, rogers: 1200 }, // 每平米加价
        copper:   { 1: 0, 2: 150 },                                   // 每平米加价
        solder:   { green: 0, white: 10, black: 10, blue: 10, yellow: 10, red: 10, 'matte-black': 10 },
        silk:     { single: 0, double: 5 },
        surface:  { hasl: 0, 'hasl-lf': 20, enig: 180, osp: -5 },
        via:      { tented: 0, open: 0, filled: 20 },
        special:  { 'half-hole': 100, bevel: 150, impedance: 200, bga: 50, 'via-fill': 300, 'edge-metal': 300 },
        report:   { none: 0, coc: 50, fai: 150 },
        dateCode: { none: 0, yw: 30, ymd: 30 },
        test:     { flyingRate: 50, flyingMin: 150, fixtureSmall: 500, fixtureLarge: 1000, fixtureArea: 5 },
        tooling:  { punchRate: 80, punchMin: 1500 },
        leadBase: { 1: 3, 2: 4, 4: 6, 6: 8, 8: 10 },                 // 基准交期（工作日）
        leadAdd:  { enig: 2, impedance: 1, punch: 3, rogers: 3, aluminum: 1, qty1000: 2, qty5000: 3 },
        minBoard: 300,      // 板费 + 工艺附加最低消费
        taxRate:  0.13,
        shipFee:  25,
        limits:   { minQty: 5, maxSize: 120, tipSmallSize: 7.6, weightFactor: 2.4 }
    };

    var CFG = JSON.parse(JSON.stringify(DEFAULT_CFG));

    /** 用后台配置覆盖默认值（只覆盖数字，缺失项保留默认） */
    function applyCfg(src) {
        if (!src || typeof src !== 'object') { return; }
        function merge(dst, part) {
            for (var k in part) {
                if (!Object.prototype.hasOwnProperty.call(part, k)) { continue; }
                var v = part[k];
                if (v !== null && typeof v === 'object') {
                    if (dst[k] && typeof dst[k] === 'object') { merge(dst[k], v); }
                } else {
                    var n = parseFloat(v);
                    dst[k] = isFinite(n) ? n : dst[k];
                }
            }
        }
        merge(CFG, src);
    }

    function $(id) { return document.getElementById(id); }
    function num(id, fallback) {
        var v = parseFloat($(id).value);
        return isFinite(v) ? v : fallback;
    }
    function money(n) {
        return '￥' + (Math.round(n * 100) / 100).toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function collect() {
        var specials = [];
        var boxes = $('fSpecial').querySelectorAll('input[type=checkbox]');
        for (var i = 0; i < boxes.length; i++) {
            if (boxes[i].checked) specials.push(boxes[i].value);
        }
        return {
            length:    Math.min(Math.max(num('fLength', 10), 1), CFG.limits.maxSize),
            width:     Math.min(Math.max(num('fWidth', 10), 1), CFG.limits.maxSize),
            qty:       Math.max(Math.floor(num('fQty', 100)), CFG.limits.minQty),
            variety:   Math.min(Math.max(Math.floor(num('fVariety', 1)), 1), 50),
            layer:     parseInt($('fLayer').value, 10),
            material:  $('fMaterial').value,
            thickness: parseFloat($('fThickness').value),
            copper:    $('fCopper').value,
            solder:    $('fSolder').value,
            silk:      $('fSilk').value,
            surface:   $('fSurface').value,
            via:       $('fVia').value,
            test:      $('fTest').value,
            shape:     $('fShape').value,
            special:   specials,
            dateCode:  $('fDateCode').value,
            report:    $('fReport').value,
            invoice:   $('fInvoice').value === '1',
            ship:      $('fShip').value === '1',
            delivery:  $('fDelivery').value
        };
    }

    function calc(p) {
        var areaOne = (p.length / 100) * (p.width / 100);       // 单片 ㎡
        var area = areaOne * p.qty;                             // 总面积 ㎡
        var weight = area * (p.thickness / 1.6) * CFG.limits.weightFactor;   // 约重 kg

        // 每平米单价 = 层数基准 + 板材 + 铜厚 + 阻焊 + 字符 + 表面处理 + 过孔
        var addSqm = CFG.material[p.material]
            + CFG.copper[p.copper]
            + CFG.solder[p.solder]
            + CFG.silk[p.silk]
            + CFG.surface[p.surface]
            + CFG.via[p.via];
        var unitSqm = CFG.sqm[p.layer] + addSqm;

        var board = area * CFG.sqm[p.layer];                    // 基础板费
        var extraSqm = area * addSqm;                           // 工艺附加（按面积）
        if (board + extraSqm < CFG.minBoard) {
            // 不足最低消费时补足，体现在附加费里
            extraSqm += CFG.minBoard - (board + extraSqm);
        }

        // 按款数计的附加
        var extraOrder = 0;
        for (var i = 0; i < p.special.length; i++) {
            extraOrder += (CFG.special[p.special[i]] || 0) * p.variety;
        }
        extraOrder += (CFG.dateCode[p.dateCode] || 0) * p.variety;
        extraOrder += (CFG.report[p.report] || 0) * p.variety;
        var extra = extraSqm + extraOrder;

        // 工程费（每款）
        var setup = CFG.setup[p.layer] * p.variety;

        // 测试费
        var test = 0;
        if (p.test === 'flying') {
            test = Math.max(area * CFG.test.flyingRate, CFG.test.flyingMin);
        } else if (p.test === 'fixture') {
            test = (area >= CFG.test.fixtureArea ? CFG.test.fixtureLarge : CFG.test.fixtureSmall) * p.variety;
        }

        // 模具费
        var tooling = 0;
        if (p.shape === 'punch') {
            tooling = Math.max((p.length + p.width) * CFG.tooling.punchRate, CFG.tooling.punchMin);
        }

        var subtotal = board + extra + setup + test + tooling;
        var tax = p.invoice ? subtotal * CFG.taxRate : 0;
        var ship = p.ship ? CFG.shipFee : 0;
        var total = subtotal + tax + ship;

        // 交期
        var days = CFG.leadBase[p.layer] || 6;
        var la = CFG.leadAdd;
        if (p.surface === 'enig') days += la.enig;
        if (p.special.indexOf('impedance') !== -1) days += la.impedance;
        if (p.shape === 'punch') days += la.punch;
        if (p.material === 'rogers') days += la.rogers;
        if (p.material === 'aluminum') days += la.aluminum;
        if (p.qty >= 5000) days += la.qty5000;
        else if (p.qty >= 1000) days += la.qty1000;

        return {
            area: area, weight: weight, unitSqm: unitSqm,
            board: board, extra: extra, setup: setup,
            test: test, tooling: tooling, tax: tax, ship: ship,
            total: total, unit: total / p.qty,
            lead: days + '-' + (days + 2) + ' 个工作日'
        };
    }

    function note(p) {
        var tips = [];
        var minSide = CFG.limits.tipSmallSize;
        if (p.delivery === 'single' && (p.length < minSide || p.width < minSide)) {
            tips.push('单片出货尺寸小于 ' + minSide + '×' + minSide + 'cm，生产效率低，建议做拼板。');
        }
        if (p.qty % 5 !== 0) {
            tips.push('下单数量需为 5 的倍数，提交后我们会按最接近的合规数量确认。');
        }
        if (p.material === 'aluminum' && p.layer > 2) {
            tips.push('铝基板通常为单/双面板，多层需求建议转人工报价。');
        }
        if (p.material === 'fr4kb' && !(p.layer === 2 || p.layer === 4)) {
            tips.push('KB 料适用于 2 层或 4 层板，其他层数将按 FR-4 A级核算。');
        }
        if (p.surface === 'enig' && Math.max(p.length, p.width) > 65) {
            tips.push('尺寸大于 65cm 时沉金工艺需工程评估，建议转人工报价。');
        }
        if (p.test === 'flying' && area10(p)) {
            tips.push('订单面积达到 10㎡ 时不建议使用飞针测试，已按规则保留，审核时可能调整。');
        }
        if (!tips.length) {
            tips.push('提示：单片出货尺寸小于 ' + minSide + '×' + minSide + 'cm 建议做拼板，效率更高、价格更优。');
        }
        return tips.join('<br>');
    }

    function area10(p) {
        return (p.length / 100) * (p.width / 100) * p.qty >= 10;
    }

    var lastResult = null;
    var lastParams = null;

    function render() {
        var p = lastParams = collect();
        var r = lastResult = calc(p);

        $('sArea').textContent   = r.area.toFixed(3) + ' ㎡';
        $('sWeight').textContent = r.weight.toFixed(2) + ' kg';
        $('sSqm').textContent    = money(r.unitSqm);
        $('sBoard').textContent  = money(r.board);
        $('sSetup').textContent  = money(r.setup);
        $('sTest').textContent   = money(r.test);
        $('sTooling').textContent = money(r.tooling);
        $('sExtra').textContent  = money(r.extra);
        $('sTax').textContent    = money(r.tax);
        $('sShip').textContent   = money(r.ship);
        $('sTotal').textContent  = (Math.round(r.total * 100) / 100).toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('sUnit').textContent   = (Math.round(r.unit * 100) / 100).toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('sLead').textContent   = r.lead;
        $('sNote').innerHTML     = note(p);
    }

    // ---------- 绑定 ----------
    var ids = ['fLength', 'fWidth', 'fQty', 'fVariety', 'fLayer', 'fMaterial', 'fDelivery', 'fThickness',
               'fCopper', 'fSolder', 'fSilk', 'fSurface', 'fVia', 'fTest', 'fShape',
               'fDateCode', 'fReport', 'fInvoice', 'fShip'];
    ids.forEach(function (id) {
        var el = $(id);
        if (!el) return;
        el.addEventListener('input', render);
        el.addEventListener('change', render);
    });
    var boxes = $('fSpecial').querySelectorAll('input[type=checkbox]');
    for (var i = 0; i < boxes.length; i++) {
        boxes[i].addEventListener('change', render);
    }

    // 验证码刷新
    var capImg = $('qCaptchaImg');
    function refreshCaptcha() {
        capImg.src = 'api/captcha.php?' + Date.now();
    }
    capImg.addEventListener('click', refreshCaptcha);

    // 提交
    $('quoteForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var status = $('qStatus');
        var btn = $('qSubmit');

        var name = $('qName').value.trim();
        var phone = $('qPhone').value.trim();
        var captcha = $('qCaptcha').value.trim();

        if (name.length < 2) { status.className = 'q-status is-error'; status.textContent = '请填写姓名（至少 2 个字）'; return; }
        if (!/^[0-9+\-\s()（）]{6,30}$/.test(phone)) { status.className = 'q-status is-error'; status.textContent = '请填写有效的联系电话'; return; }
        if (!captcha) { status.className = 'q-status is-error'; status.textContent = '请输入验证码'; return; }

        btn.disabled = true;
        btn.textContent = '提交中…';
        status.className = 'q-status';
        status.textContent = '';

        var fd = new FormData();
        fd.append('name', name);
        fd.append('phone', phone);
        fd.append('email', $('qEmail').value.trim());
        fd.append('company', $('qCompany').value.trim());
        fd.append('remark', $('qRemark').value.trim());
        fd.append('captcha', captcha);
        fd.append('params', JSON.stringify(lastParams));
        fd.append('estimate_total', lastResult.total.toFixed(2));
        fd.append('estimate_unit', lastResult.unit.toFixed(2));
        fd.append('lead_time', lastResult.lead);

        fetch('api/quote.php', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.ok) {
                    status.className = 'q-status is-ok';
                    status.textContent = d.message;
                    $('qCaptcha').value = '';
                    refreshCaptcha();
                } else {
                    status.className = 'q-status is-error';
                    status.textContent = d.message || '提交失败，请稍后重试';
                    refreshCaptcha();
                }
            })
            .catch(function () {
                status.className = 'q-status is-error';
                status.textContent = '网络异常，请稍后重试';
            })
            .then(function () {
                btn.disabled = false;
                btn.textContent = '提交询价';
            });
    });

    // 平滑滚动到联系方式
    $('qJumpForm').addEventListener('click', function (e) {
        e.preventDefault();
        var form = $('quoteForm');
        if (form && form.scrollIntoView) {
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    render();

    // ---------- 拉取后台参数 ----------
    // 先用内置默认值渲染一版，保证接口异常时页面依然可用；
    // 拿到后台「报价参数」后覆盖并重算。
    function loadConfig() {
        if (typeof fetch !== 'function') { return; }
        fetch('api/quote_config.php', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d && d.ok && d.data) {
                    applyCfg(d.data);
                    render();
                }
            })
            .catch(function () { /* 拉取失败则沿用默认参数 */ });
    }

    loadConfig();
})();
