/* ============================================
   LIERXIN 利尔鑫 — SPA Router & Page Content
   Grand Corporate · PCB/PCBA Manufacturing
   ============================================ */

// ---- SVG icons ----
const icons = {
    pcb: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8" cy="8" r="1.5" fill="currentColor"/><circle cx="16" cy="8" r="1.5" fill="currentColor"/><circle cx="8" cy="16" r="1.5" fill="currentColor"/><circle cx="16" cy="16" r="1.5" fill="currentColor"/><path d="M8 8h8M8 16h8M8 8v8M16 8v8"/></svg>',
    layers: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 12l10 5 10-5M2 17l10 5 10-5"/></svg>',
    wave: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 12c2-4 4-4 6 0s4 4 6 0 4-4 6 0 4 4 6 0"/><path d="M2 18c2-4 4-4 6 0s4 4 6 0 4-4 6 0 4 4 6 0"/></svg>',
    chip: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="6" y="6" width="12" height="12" rx="1"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M1 9h3M1 15h3M20 9h3M20 15h3"/><rect x="10" y="10" width="4" height="4" rx="0.5"/></svg>',
    car: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 13l1.5-5C4.8 7.2 5.5 7 6 7h12c.5 0 1.2.2 1.5 1L21 13v5h-3v-2H6v2H3v-5z"/><circle cx="7" cy="16" r="1.5"/><circle cx="17" cy="16" r="1.5"/></svg>',
    factory: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 20V8l5 3V8l5 3V8l5 3V5h3v15H2z"/><path d="M6 20v-4M11 20v-4M16 20v-4"/></svg>',
    antenna: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 12.5a10 10 0 0 1 14 0M7.5 15a6 6 0 0 1 9 0"/><circle cx="12" cy="18" r="1.5" fill="currentColor"/><path d="M12 19v3"/></svg>',
    medical: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="6" width="18" height="14" rx="2"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M12 11v6M9 14h6"/></svg>',
    shield: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5l-8-3z"/></svg>',
    phone: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.5a16 16 0 0 0 6 6l1.1-1.1a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>',
    mail: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 6l10 7L22 6"/></svg>',
    location: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s-8-7-8-13a8 8 0 0 1 16 0c0 6-8 13-8 13z"/><circle cx="12" cy="9" r="3"/></svg>',
    arrow: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8h10M9 4l4 4-4 4"/></svg>',
    check: '<svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 9l3 3 7-7"/></svg>',
    users: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg>',
    building: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/><path d="M9 9v0M9 12v0M9 15v0M9 18v0"/></svg>',
    patent: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="10" cy="14" r="6"/><path d="M14.5 10.5L20 5M15 5h5v5"/></svg>',
    globe: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg>',
    test: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 2v6l-4 8a4 4 0 0 0 4 6h6a4 4 0 0 0 4-6l-4-8V2"/><path d="M7 2h10"/></svg>',
    truck: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 3h15v13H1zM16 8h4l3 3v5h-7"/><circle cx="5.5" cy="18.5" r="2"/><circle cx="18.5" cy="18.5" r="2"/></svg>',
    assembly: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 3v18M15 3v18M3 15h18"/><circle cx="6" cy="6" r="1" fill="currentColor"/><circle cx="12" cy="6" r="1" fill="currentColor"/><circle cx="18" cy="6" r="1" fill="currentColor"/></svg>',
    flex: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8c2 0 2 4 4 4s2-4 4-4 2 4 4 4 2-4 4-4 2 4 4 4"/><path d="M3 16c2 0 2 4 4 4s2-4 4-4 2 4 4 4 2-4 4-4 2 4 4 4"/></svg>',
    award: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="6"/><path d="M8.5 14L7 22l5-3 5 3-1.5-8"/></svg>',
    leaf: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11 20A7 7 0 0 1 9.8 6.5C15.5 5 19 7 21 2c1 2 2 5.5-1 9-2.5 3-5 4.5-9 9z"/><path d="M2 22c2-4 5-7 9-9"/></svg>',
    book: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4v16a2 2 0 0 0 2 2h14V2H6a2 2 0 0 0-2 2zM4 4a2 2 0 0 0-2 2v2h2M8 6h8M8 10h8M8 14h6"/></svg>',
    chart: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 3v18h18M8 14l3-3 4 4 5-6"/><path d="M8 14l3-3 4 4"/></svg>',
};

// ---- Elegant visual panel generator (replaces PCB pattern) ----
function generateElegantPanel() {
    return `
    <div style="text-align:center;padding:20px">
        <div style="font-size:56px;margin-bottom:16px;opacity:0.5">${icons.layers.replace('width="24"','width="56"')}</div>
        <div style="font-family:'Montserrat',sans-serif;font-size:20px;font-weight:800;letter-spacing:4px;color:var(--gold)">LIERXIN</div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:6px;letter-spacing:2px">利尔鑫</div>
    </div>`;
}

// ---- Page: Home ----
function renderHome() {
    return `
    <div class="page-enter">
        <!-- Hero -->
        <section class="hero">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="badge"><span class="dot"></span> 国家高新技术企业 · PCB/PCBA/方案研发一站式智造</div>
                    <h1>高端PCB与PCBA<br><span class="text-gold">精密智造解决方案</span></h1>
                    <p class="hero-desc">成立于2015年，集PCB线路板制造、PCBA制造及方案研发于一体，深圳、湖北、清远、江西四大生产基地协同运作，集团3500人团队，为全球客户提供从方案设计到批量交付的一站式定制服务。</p>
                    <div class="hero-buttons">
                        <a href="#/products" class="btn btn-primary">探索产品 ${icons.arrow}</a>
                        <a href="#/contact" class="btn btn-outline">获取报价</a>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat"><div><span class="num" data-count="60">0</span><span class="num-suffix">万㎡/月</span></div><div class="label">PCB总产能</div></div>
                        <div class="hero-stat"><div><span class="num" data-count="3500">0</span><span class="num-suffix">人</span></div><div class="label">集团员工</div></div>
                        <div class="hero-stat"><div><span class="num" data-count="28">0</span><span class="num-suffix">层</span></div><div class="label">最大层数</div></div>
                        <div class="hero-stat"><div><span class="num" data-count="20">0</span><span class="num-suffix">条</span></div><div class="label">SMT产线</div></div>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-panel">
                        <div class="geo-line h1"></div>
                        <div class="geo-line h2"></div>
                        <div class="geo-line h3"></div>
                        <div class="geo-line v1"></div>
                        <div class="panel-content">
                            <div class="panel-top">
                                <div class="pt-en">LIERXIN</div>
                                <div class="pt-cn">利尔鑫</div>
                            </div>
                        <div class="panel-mid">
                            <div class="pm-num" data-count="4">0</div>
                            <div class="pm-label">大生产基地协同</div>
                        </div>
                        <div class="panel-bottom">
                            <div class="pb-item"><span>SMT产线</span><span>20条</span></div>
                            <div class="pb-item"><span>装配线</span><span>5条</span></div>
                            <div class="pb-item"><span>技术人员占比</span><span>30%</span></div>
                            <div class="pb-item"><span>中国交付</span><span>72小时</span></div>
                        </div>
                        </div>
                    </div>
                    <div class="hero-accent ha-1">
                        <div class="ha-icon">${icons.shield}</div>
                        <div class="ha-text"><strong>IATF 16949</strong><span>车规认证</span></div>
                    </div>
                    <div class="hero-accent ha-2">
                        <div class="ha-icon">${icons.award}</div>
                        <div class="ha-text"><strong>60万㎡/月</strong><span>PCB总产能</span></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Overview -->
        <section class="section">
            <div class="section-title">
                <div class="eyebrow">CORE PRODUCTS</div>
                <h2>核心<span class="text-gold">产品体系</span></h2>
                <p>覆盖从常规多层板到高难度HDI、软硬结合板的全品类PCB/PCBA产品线</p>
            </div>
            <div class="card-grid">
                ${productsData.map(p => productCard(p)).join('')}
            </div>
        </section>

        <!-- Enterprise Stats -->
        <section class="stats-section">
            <div class="section" style="padding:80px 24px">
                <div class="section-title">
                    <div class="eyebrow">ENTERPRISE SCALE</div>
                    <h2>企业<span class="text-gold">实力</span></h2>
                    <p>用数据诠释利尔鑫的规模与实力</p>
                </div>
                <div class="stats-grid">
                    <div class="stat-block">
                        <div><span class="stat-num" data-count="60">0</span><span class="stat-suffix">万㎡/月</span></div>
                        <div class="stat-label">PCB总产能</div>
                    </div>
                    <div class="stat-block">
                        <div><span class="stat-num" data-count="3500">0</span><span class="stat-suffix">人</span></div>
                        <div class="stat-label">集团员工</div>
                    </div>
                    <div class="stat-block">
                        <div><span class="stat-num" data-count="30">0</span><span class="stat-suffix">%</span></div>
                        <div class="stat-label">专业技术人员占比</div>
                    </div>
                    <div class="stat-block">
                        <div><span class="stat-num" data-count="2015">0</span><span class="stat-suffix">年</span></div>
                        <div class="stat-label">公司成立</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Partners -->
        <section class="section">
            <div class="section-title">
                <div class="eyebrow">TRUSTED PARTNERS</div>
                <h2>合作<span class="text-gold">客户</span></h2>
                <p>与全球行业头部企业建立长期稳定的合作关系</p>
            </div>
            <div class="partners-grid">
                <div class="partner-badge">VinFast</div>
                <div class="partner-badge">蔚来汽车</div>
                <div class="partner-badge">吉利威睿</div>
                <div class="partner-badge">长安汽车</div>
                <div class="partner-badge">青山工业</div>
                <div class="partner-badge">拿森汽车</div>
                <div class="partner-badge">精华电子</div>
                <div class="partner-badge">日本大阪事务所</div>
            </div>
        </section>

        <!-- Global Market -->
        <section class="section" style="padding-top:0">
            <div class="section-title">
                <div class="eyebrow">GLOBAL MARKET</div>
                <h2>全球<span class="text-gold">市场布局</span></h2>
                <p>产品远销全球多个国家和地区，服务众多国际知名品牌</p>
            </div>
            <div class="market-grid">
                <div class="market-panel">
                    <h3>销售区域分布</h3>
                    <div class="mp-sub">Sales Area</div>
                    <div class="capability-bar">
                        <div class="cb-header"><span>中国</span><span>50%</span></div>
                        <div class="cb-track"><div class="cb-fill" data-width="50%"></div></div>
                    </div>
                    <div class="capability-bar">
                        <div class="cb-header"><span>东南亚</span><span>20%</span></div>
                        <div class="cb-track"><div class="cb-fill" data-width="20%"></div></div>
                    </div>
                    <div class="capability-bar">
                        <div class="cb-header"><span>欧洲</span><span>20%</span></div>
                        <div class="cb-track"><div class="cb-fill" data-width="20%"></div></div>
                    </div>
                    <div class="capability-bar">
                        <div class="cb-header"><span>美洲</span><span>5%</span></div>
                        <div class="cb-track"><div class="cb-fill" data-width="5%"></div></div>
                    </div>
                    <div class="capability-bar">
                        <div class="cb-header"><span>其他地区</span><span>5%</span></div>
                        <div class="cb-track"><div class="cb-fill" data-width="5%"></div></div>
                    </div>
                </div>
                <div class="market-panel">
                    <h3>PCBA行业应用占比</h3>
                    <div class="mp-sub">Application Area</div>
                    <div class="capability-bar">
                        <div class="cb-header"><span>无人机</span><span>43%</span></div>
                        <div class="cb-track"><div class="cb-fill" data-width="43%"></div></div>
                    </div>
                    <div class="capability-bar">
                        <div class="cb-header"><span>智能家居</span><span>16%</span></div>
                        <div class="cb-track"><div class="cb-fill" data-width="16%"></div></div>
                    </div>
                    <div class="capability-bar">
                        <div class="cb-header"><span>汽车医疗</span><span>12%</span></div>
                        <div class="cb-track"><div class="cb-fill" data-width="12%"></div></div>
                    </div>
                    <div class="capability-bar">
                        <div class="cb-header"><span>工控</span><span>12%</span></div>
                        <div class="cb-track"><div class="cb-fill" data-width="12%"></div></div>
                    </div>
                    <div class="capability-bar">
                        <div class="cb-header"><span>消费电子及其他</span><span>17%</span></div>
                        <div class="cb-track"><div class="cb-fill" data-width="17%"></div></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Certifications -->
        <section class="section" style="padding-top:0">
            <div class="section-title">
                <div class="eyebrow">QUALIFICATIONS</div>
                <h2>资质<span class="text-gold">认证</span></h2>
                <p>严格遵循国际标准，获多项权威认证与荣誉</p>
            </div>
            <div class="cert-grid">
                <div class="cert-card">
                    <div class="cert-icon">${icons.shield}</div>
                    <div class="cert-name">ISO 9001</div>
                    <div class="cert-desc">质量管理体系</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.leaf}</div>
                    <div class="cert-name">ISO 14001</div>
                    <div class="cert-desc">环境管理体系</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.car}</div>
                    <div class="cert-name">IATF 16949</div>
                    <div class="cert-desc">汽车行业质量体系</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.medical}</div>
                    <div class="cert-name">ISO 13485</div>
                    <div class="cert-desc">医疗器械质量体系</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.users}</div>
                    <div class="cert-name">ISO 45001</div>
                    <div class="cert-desc">职业健康安全管理体系</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.chart}</div>
                    <div class="cert-name">ISO 50001</div>
                    <div class="cert-desc">能源管理体系</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.book}</div>
                    <div class="cert-name">GJB 9001C</div>
                    <div class="cert-desc">武器装备质量管理体系</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.shield}</div>
                    <div class="cert-name">QC 080000</div>
                    <div class="cert-desc">有害物质控制体系</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.award}</div>
                    <div class="cert-name">UL</div>
                    <div class="cert-desc">美国安全认证</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.pcb}</div>
                    <div class="cert-name">CQC</div>
                    <div class="cert-desc">中国质量认证</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.globe}</div>
                    <div class="cert-name">AEO</div>
                    <div class="cert-desc">海关高级认证企业</div>
                </div>
            </div>
        </section>

        <!-- Process -->
        <section class="section" style="padding-top:0">
            <div class="section-title">
                <div class="eyebrow">SERVICE PROCESS</div>
                <h2>专业<span class="text-gold">服务流程</span></h2>
                <p>从概念设计到批量交付，一站式端到端服务</p>
            </div>
            <div class="process-steps">
                <div class="process-step">
                    <div class="ps-num">01</div>
                    <h4>概念设计</h4>
                    <p>深入理解客户需求，提供PCB设计与DFM可制造性分析</p>
                </div>
                <div class="process-step">
                    <div class="ps-num">02</div>
                    <h4>样品试制</h4>
                    <p>7-10天标准打样周期，支持无MOQ订单与加急</p>
                </div>
                <div class="process-step">
                    <div class="ps-num">03</div>
                    <h4>批量生产</h4>
                    <p>四大基地协同，智能数字化工厂，工业4.0标准高效量产</p>
                </div>
                <div class="process-step">
                    <div class="ps-num">04</div>
                    <h4>品质交付</h4>
                    <p>全流程自动化检测，QMS全流程品控，可追溯保障</p>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="cta-section">
            <h2>开启您的<span class="text-gold">智造合作</span>之旅</h2>
            <p>无论您需要标准产品还是一站式ODM/OEM定制方案，利尔鑫都能为您提供专业可靠的解决方案</p>
            <div class="cta-buttons">
                <a href="#/contact" class="btn btn-primary">联系我们 ${icons.arrow}</a>
                <a href="#/products" class="btn btn-outline">查看产品</a>
            </div>
        </section>
    </div>`;
}

// ---- Product Data ----
const productsData = [
    {
        id: 'hdi',
        title: 'HDI线路板',
        en: 'HDI BOARD',
        shortDesc: '1-3阶任意层HDI高密度互连板，微孔高密度设计，满足智能终端、通信设备等小型化需求。',
        features: ['任意层HDI', '微盲埋孔', '高密度布线'],
        icon: 'chip',
        detail: {
            tag: 'HDI BOARD',
            heading: 'HDI高密度互连线路板',
            desc: '1-3阶任意层HDI板，采用激光微孔技术，实现更高密度的线路布局，满足智能手机、可穿戴设备等小型化、高性能需求。',
            list: [
                '1-3阶任意层HDI互连结构',
                '激光微孔，最小孔径50μm',
                '最小线宽线距 2/2mil',
                '高密度布线，满足空间紧凑设计',
                '适用于5G终端、智能穿戴、AI硬件等场景'
            ],
            specs: [
                { value: '1-3阶', name: 'HDI阶数' },
                { value: '50μm', name: '最小微孔径' },
                { value: '2/2mil', name: '最小线宽/线距' },
                { value: '任意层', name: '互连结构' },
                { value: 'mSAP', name: '精细线路工艺' },
                { value: '7-10天', name: '打样周期' }
            ]
        }
    },
    {
        id: 'multilayer',
        title: '多层PCB板',
        en: 'MULTILAYER PCB',
        shortDesc: '1-28层刚性多层印制电路板，工艺成熟稳定，覆盖汽车电子、工控、通信等各领域。',
        features: ['1-28层', '高可靠性', '车规级品控'],
        icon: 'layers',
        detail: {
            tag: 'MULTILAYER PCB',
            heading: '多层印制电路板',
            desc: '1-28层刚性多层PCB，覆盖双面多层、HDI、铝基板、铜基板、高频高速、金属基板全品类，广泛应用于汽车电子、工业控制、网络通讯、安防、医疗等领域。支持埋铜块、分级金手指、混压等特殊工艺。',
            list: [
                '1-28层刚性多层板，全规格覆盖',
                '最小线宽线距 50/50μm，最大纵横比16:1',
                '阻抗控制精度±8%，满足高信号完整性要求',
                '板厚范围0.25-6.0mm，最大拼板600×860mm',
                '执行IPC Class 2/3标准，生益、KB等知名基材'
            ],
            specs: [
                { value: '1-28层', name: '板层范围' },
                { value: '50/50μm', name: '最小线宽/线距' },
                { value: '±8%', name: '阻抗控制精度' },
                { value: '0.25-6.0mm', name: '板厚范围' },
                { value: '16:1', name: '通孔纵横比' },
                { value: 'IPC 2/3', name: '执行标准' }
            ]
        }
    },
    {
        id: 'pcba',
        title: 'PCBA贴装组装',
        en: 'PCBA ASSEMBLY',
        shortDesc: '20条SMT线+5条装配线+4条DIP线，从贴片到焊接、检测的全流程组装服务，支持ODM/OEM定制。',
        features: ['20条SMT线', '全流程检测', '一站式服务'],
        icon: 'assembly',
        detail: {
            tag: 'PCBA ASSEMBLY',
            heading: 'PCBA贴装组装',
            desc: 'PCBA基地位于深圳沙井，配备20条SMT线体、5条装配线、4条DIP线及4台波峰焊机，从贴片到焊接、检测的全流程组装服务，覆盖无人机、智能家居、汽车医疗、工控等应用领域。',
            list: [
                '20条SMT线体 + 5条装配线 + 4条DIP线',
                '配备IPX7防水测试、震动测试、高压测试等可靠性试验设备',
                '从BOM代采到整机功能测试端到端服务',
                '支持ODM/OEM一站式定制方案',
                '无人机类应用占比达43%，行业经验深厚'
            ],
            specs: [
                { value: '20条', name: 'SMT线体' },
                { value: '5条', name: '装配线' },
                { value: '4条', name: 'DIP线' },
                { value: '深圳沙井', name: '基地位置' },
                { value: 'ODM/OEM', name: '定制模式' },
                { value: '43%', name: '无人机应用占比' }
            ]
        }
    },
    {
        id: 'rigid-flex',
        title: '软硬结合板',
        en: 'RIGID-FLEX PCB',
        shortDesc: '柔性电路与刚性电路结合，实现三维空间布线，满足可穿戴、医疗等空间受限场景。',
        features: ['三维布线', '轻量化', '高可靠性'],
        icon: 'flex',
        detail: {
            tag: 'RIGID-FLEX PCB',
            heading: '软硬结合板',
            desc: '将柔性电路与刚性电路结合于一体，实现三维空间布线与折叠安装，满足可穿戴设备、医疗内窥镜、无人机等空间受限场景的高可靠性需求。',
            list: [
                '刚性与柔性电路一体化设计',
                '三维空间布线，节省内部空间',
                '优异的弯折耐久性与信号完整性',
                '轻量化，满足终端小型化趋势',
                '适用于医疗、消费电子、无人机等场景'
            ],
            specs: [
                { value: '一体化', name: '刚柔结合' },
                { value: '3D', name: '空间布线' },
                { value: '10万次', name: '弯折耐久' },
                { value: '多层', name: '柔性层' },
                { value: 'PI', name: '基材类型' },
                { value: '8-12天', name: '打样周期' }
            ]
        }
    },
    {
        id: 'high-freq',
        title: '高频高速板',
        en: 'HIGH-FREQ PCB',
        shortDesc: '高频高速材料混压工艺，适用于5G通信、汽车雷达等高频信号完整性要求场景。',
        features: ['高频混压', '低损耗', '阻抗±8%'],
        icon: 'wave',
        detail: {
            tag: 'HIGH-FREQ PCB',
            heading: '高频高速板',
            desc: '采用高频高速材料混压工艺，具备优异的高频电气性能，广泛应用于5G通信、汽车雷达、高速服务器等领域。支持HDI软硬结合、Anylayer HDI等先进结构。',
            list: [
                '高频高速材料混压工艺成熟',
                '阻抗控制精度±8%，信号完整性保障',
                '支持高速服务器板、Anylayer HDI结构',
                '最小BGA间距0.40mm，高密度设计支持',
                '适用于5G终端、雷达、高速传输场景'
            ],
            specs: [
                { value: '±8%', name: '阻抗控制精度' },
                { value: '混压', name: '工艺支持' },
                { value: 'Anylayer', name: 'HDI结构' },
                { value: '0.40mm', name: '最小BGA间距' },
                { value: '0.05mm', name: '最小激光盲孔' },
                { value: '5G/雷达', name: '适用领域' }
            ]
        }
    },
    {
        id: 'special',
        title: '特殊工艺板',
        en: 'SPECIAL PCB',
        shortDesc: '金属基板、厚铜板、埋铜块等特殊工艺定制，满足大功率与特殊应用需求。',
        features: ['金属基板', '厚铜4-6oz', '埋铜块'],
        icon: 'pcb',
        detail: {
            tag: 'SPECIAL PCB',
            heading: '特殊工艺板',
            desc: '提供金属基板（铝基板/铜基板/IMS）、厚铜板（4-6oz）、埋铜块、控深钻、背钻等特殊工艺定制产品，满足大功率电源、新能源BMS、汽车电驱等高散热、大电流应用场景的严苛需求。',
            list: [
                '金属基板：铝基板、铜基板、金属基板IMS',
                '厚铜板：4-6oz厚铜箔，外层铜厚最高6oz',
                '埋铜块、分级金手指、控深钻、背钻工艺',
                '江西基地POFV/VIPPO工艺，服务海外客户',
                '服务青山工业汽车电驱、吉利威睿BMS等客户'
            ],
            specs: [
                { value: '4-6oz', name: '厚铜规格' },
                { value: '铝/铜/IMS', name: '金属基板' },
                { value: '6.0mm', name: '最大板厚' },
                { value: '埋铜块', name: '特殊工艺' },
                { value: '大功率', name: '适用场景' },
                { value: '10年+', name: '产品寿命' }
            ]
        }
    }
];

function productCard(product) {
    return `
    <a href="#/products/${product.id}" class="tech-card">
        <div class="card-visual" style="background-image:url('images/products/${product.id}.jpg')"></div>
        <div class="card-body">
            <div class="card-tag">${product.en}</div>
            <h3 class="card-title">${product.title}</h3>
            <p class="card-desc">${product.shortDesc}</p>
            <div class="card-features">${product.features.map(f => `<span class="card-feature">${f}</span>`).join('')}</div>
            <span class="card-link">查看详情 ${icons.arrow}</span>
        </div>
    </a>`;
}

function solutionCard(title, desc, img) {
    return `
    <div class="solution-card">
        <div class="sol-visual">
            <div class="sol-visual-img" style="background-image:url('images/solutions/${img}')"></div>
        </div>
        <h3>${title}</h3>
        <p>${desc}</p>
    </div>`;
}

// ---- Page: Products ----
function renderProducts() {
    return `
    <div class="page-enter">
        <div class="product-hero" style="background-image:url('images/products-hero.jpg')">
            <div class="eyebrow">PRODUCT CENTER</div>
            <h1>产品<span class="text-gold">中心</span></h1>
            <p>利尔鑫提供覆盖HDI线路板、多层PCB、PCBA贴装组装、软硬结合板、高频高速板等全品类产品，满足各行业从样品到量产的多样化需求。</p>
        </div>

        <section class="section" style="padding-top:60px">
            <div class="card-grid">
                ${productsData.map(p => productCard(p)).join('')}
            </div>
        </section>

        <!-- Production Capability -->
        <section class="section" style="padding-top:0">
            <div class="section-title">
                <div class="eyebrow">CAPABILITY</div>
                <h2>制程<span class="text-gold">能力</span></h2>
                <p>行业领先的工艺参数边界，覆盖常规到高难度全品类订单</p>
            </div>
            <div class="product-specs" style="margin-top:0">
                <div class="spec-card"><div class="spec-value">24层</div><div class="spec-name">最大层数（量产）</div></div>
                <div class="spec-card"><div class="spec-value">0.25~6.0<span style="font-size:14px">mm</span></div><div class="spec-name">板厚范围</div></div>
                <div class="spec-card"><div class="spec-value">1/3~6<span style="font-size:14px">oz</span></div><div class="spec-name">外层铜厚范围</div></div>
                <div class="spec-card"><div class="spec-value">0.15<span style="font-size:14px">mm</span></div><div class="spec-name">最小通孔孔径</div></div>
                <div class="spec-card"><div class="spec-value">50/50<span style="font-size:14px">μm</span></div><div class="spec-name">最小线宽/线距</div></div>
                <div class="spec-card"><div class="spec-value">600×860<span style="font-size:14px">mm</span></div><div class="spec-name">最大拼板尺寸（量产）</div></div>
                <div class="spec-card"><div class="spec-value">16:1</div><div class="spec-name">通孔电镀纵横比</div></div>
                <div class="spec-card"><div class="spec-value">±8%</div><div class="spec-name">阻抗控制精度</div></div>
            </div>
            <div style="margin-top:48px">
                <h3 style="text-align:center;font-size:20px;font-weight:700;margin-bottom:8px">特殊工艺能力</h3>
                <p style="text-align:center;font-size:13px;color:var(--text-muted);margin-bottom:24px;letter-spacing:2px">SPECIAL PROCESS</p>
                <div class="tag-list">
                    <span class="tag">长短金手指</span>
                    <span class="tag">分级金手指</span>
                    <span class="tag">控深机械钻孔</span>
                    <span class="tag">背钻</span>
                    <span class="tag">填孔电镀</span>
                    <span class="tag">机械盲埋孔</span>
                    <span class="tag">HDI</span>
                    <span class="tag">埋铜块</span>
                    <span class="tag">多层混压</span>
                    <span class="tag">POFV盖孔电镀</span>
                    <span class="tag">软硬结合</span>
                </div>
            </div>
            <div class="market-grid" style="margin-top:48px">
                <div class="market-panel">
                    <h3>材料选择</h3>
                    <div class="mp-sub">Material Type</div>
                    <p style="font-size:14px;color:var(--text-secondary);line-height:1.9">采用国际知名基材（生益、KB等），覆盖普通TG、中TG、高TG、中损耗、低损耗全系列板材，符合IPC Class 2/3标准，通过UL、RoHS、REACH认证，确保高温高湿极端环境下的稳定性。</p>
                </div>
                <div class="market-panel">
                    <h3>表面处理</h3>
                    <div class="mp-sub">Surface Finish</div>
                    <p style="font-size:14px;color:var(--text-secondary);line-height:1.9">沉金、沉锡、沉银、镀金、无铅喷锡、OSP、沉金+OSP复合工艺；阻焊颜色支持绿色、哑绿、红色、黄色、蓝色、黑色、白色等多种选择。</p>
                </div>
            </div>
        </section>

        <!-- Product Cases -->
        <section class="section" style="padding-top:0">
            <div class="section-title">
                <div class="eyebrow">PRODUCT CASES</div>
                <h2>典型<span class="text-gold">产品案例</span></h2>
                <p>来自汽车电子、储能工控、通信、消费电子等领域的真实交付案例</p>
            </div>
            <div class="case-grid">
                <div class="case-card">
                    <div class="case-cat">汽车电子</div>
                    <h4>汽车移动电源（VinFast）</h4>
                    <div class="case-spec"><strong>6层通孔板</strong> · TG170 · 铜厚 3/3oz<br>服务越南电动车龙头企业 VinFast</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">汽车电子</div>
                    <h4>电源管理模块 BMS（吉利威睿）</h4>
                    <div class="case-spec"><strong>6层通孔板</strong> · TG170 · 铜厚 1/1oz+黄金胶<br>动力电池管理系统核心组件</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">汽车电子</div>
                    <h4>OBC高效充电模组（精华电子）</h4>
                    <div class="case-spec"><strong>4层通孔板</strong> · TG150 · 铜厚 4/4oz<br>动力电池龙头企业产品</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">汽车电子</div>
                    <h4>车载数据中心 T-BOX（精华电子）</h4>
                    <div class="case-spec"><strong>8层1阶HDI板</strong> · TG170 · 1/1oz+埋孔/盲孔<br>ASK终端产品</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">汽车电子</div>
                    <h4>AR/VR主机（蔚来汽车）</h4>
                    <div class="case-spec"><strong>12层通孔板</strong> · TG170 · 铜厚 1/1oz<br>3D模拟应用场景</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">汽车电子</div>
                    <h4>线控转向传感器（拿森汽车）</h4>
                    <div class="case-spec"><strong>4层软硬结合板</strong> · TG170<br>1/1oz + FCCL/CVL 结构</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">汽车电子</div>
                    <h4>线控转向控制（长安汽车）</h4>
                    <div class="case-spec"><strong>4层半软板</strong> · TG170<br>1/1oz + 挠性PP+环氧胶</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">汽车电子</div>
                    <h4>汽车电驱（青山工业）</h4>
                    <div class="case-spec"><strong>1层铝基板</strong> · TG150 · 2oz导热3W<br>耐压2500VDC</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">消费电子</div>
                    <h4>高速吹风机 / 电动牙刷</h4>
                    <div class="case-spec">个人护理类产品PCB/PCBA<br>研产一体化批量交付</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">消费电子</div>
                    <h4>扫地机 / SSD移动硬盘</h4>
                    <div class="case-spec">智能家居与存储类产品<br>高可靠性多层板方案</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">工控储能</div>
                    <h4>控制器主板 / 逆变器板</h4>
                    <div class="case-spec">工业控制与电源转换场景<br>高稳定性批量供货</div>
                </div>
                <div class="case-card">
                    <div class="case-cat">工控储能</div>
                    <h4>混合储能电源 / 高压变频器</h4>
                    <div class="case-spec">大功率储能应用<br>厚铜工艺承载大电流</div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="cta-section">
            <h2>需要<span class="text-gold">定制方案</span>？</h2>
            <p>告诉我们的工程团队您的需求，获取专业PCB/PCBA定制方案与报价</p>
            <div class="cta-buttons">
                <a href="#/contact" class="btn btn-primary">获取定制报价 ${icons.arrow}</a>
                <a href="#/solutions" class="btn btn-outline">查看行业方案</a>
            </div>
        </section>
    </div>`;
}

// ---- Page: Product Detail ----
function renderProductDetail(id) {
    const idx = productsData.findIndex(p => p.id === id);
    if (idx === -1) return renderProducts();
    const p = productsData[idx];
    const d = p.detail;
    const reverse = idx % 2 === 1;
    const prevP = productsData[(idx - 1 + productsData.length) % productsData.length];
    const nextP = productsData[(idx + 1) % productsData.length];

    return `
    <div class="page-enter">
        <div class="product-hero" style="background-image:url('images/products-hero.jpg')">
            <div class="eyebrow">${d.tag}</div>
            <h1>${p.title}</h1>
            <p>${d.desc}</p>
        </div>

        <div class="detail-nav">
            <a href="#/products" class="back-link">${icons.arrow.replace('M3 8h10M9 4l4 4-4 4', 'M13 8H3M7 4L3 8l4 4')} 返回产品列表</a>
        </div>

        <!-- Feature Block -->
        <section class="section" style="padding-top:0">
            <div class="feature-block${reverse ? ' reverse' : ''}">
                <div class="feature-visual">${generateElegantPanel()}</div>
                <div class="feature-text">
                    <div class="ft-tag">${d.tag}</div>
                    <h3>${d.heading}</h3>
                    <p>${d.desc}</p>
                    <ul class="feature-list">
                        ${d.list.map(item => `<li>${item}</li>`).join('')}
                    </ul>
                </div>
            </div>
        </section>

        <!-- Spec Cards -->
        <section class="section" style="padding-top:0">
            <div class="section-title" style="margin-bottom:36px">
                <div class="eyebrow">SPECIFICATIONS</div>
                <h2>产品<span class="text-gold">规格参数</span></h2>
            </div>
            <div class="product-specs" style="margin-top:0">
                ${d.specs.map(s => `<div class="spec-card"><div class="spec-value">${s.value}</div><div class="spec-name">${s.name}</div></div>`).join('')}
            </div>
        </section>

        <!-- Related Products -->
        <section class="section" style="padding-top:40px">
            <div class="section-title" style="margin-bottom:36px">
                <div class="eyebrow">MORE PRODUCTS</div>
                <h2>其他<span class="text-gold">产品</span></h2>
            </div>
            <div class="card-grid">
                ${productCard(prevP)}
                ${productCard(nextP)}
            </div>
        </section>

        <!-- CTA -->
        <section class="cta-section">
            <h2>需要<span class="text-gold">定制方案</span>？</h2>
            <p>告诉我们的工程团队您的需求，获取专业PCB/PCBA定制方案与报价</p>
            <div class="cta-buttons">
                <a href="#/contact" class="btn btn-primary">获取定制报价 ${icons.arrow}</a>
                <a href="#/solutions" class="btn btn-outline">查看行业方案</a>
            </div>
        </section>
    </div>`;
}

// ---- Page: Solutions ----
function renderSolutions() {
    const solutionsData = [
        { img: 'automotive.jpg', icon: icons.car, title: '汽车电子', desc: '车规级PCB方案，通过IATF 16949认证，应用于ECU控制单元、BMS电池管理、车载娱乐系统、汽车雷达等核心模块。' },
        { img: 'telecom.jpg', icon: icons.antenna, title: '通讯设备', desc: '5G/4G通信基站及射频模块PCB方案，高频高速材料，低损耗传输，保障信号完整性与通信质量。' },
        { img: 'industrial.jpg', icon: icons.factory, title: '工业控制', desc: '高可靠性工业控制板，耐恶劣环境，长期稳定运行，应用于PLC、变频器、伺服驱动器等工业自动化设备。' },
        { img: 'medical.jpg', icon: icons.medical, title: '医疗电子', desc: '通过ISO 13485医疗体系认证，精密监测诊断设备PCB方案，包括超声、监护仪、血氧仪等专业医疗设备。' },
        { img: 'consumer.jpg', icon: icons.chip, title: '消费电子', desc: '面向智能手机、可穿戴设备、智能家居等消费类产品的高密度PCB/PCBA方案，满足轻薄化、高性能需求。' },
        { img: 'drone.jpg', icon: icons.truck, title: '无人机与新能源', desc: '无人机飞控板、新能源BMS与逆变器PCB方案，厚铜板承载大电流，满足高功率与轻量化双重需求。' },
    ];
    const solutionsHtml = solutionsData.map(s => `
                <div class="solution-card">
                    <div class="sol-visual">
                        <div class="sol-visual-img" style="background-image:url('images/solutions/${s.img}')"></div>
                    </div>
                    <h3>${s.title}</h3>
                    <p>${s.desc}</p>
                </div>`).join('');

    return `
    <div class="page-enter">
        <div class="product-hero" style="background-image:url('images/solutions-hero.jpg')">
            <div class="eyebrow">INDUSTRY SOLUTIONS</div>
            <h1>行业<span class="text-gold">解决方案</span></h1>
            <p>深耕多元应用场景，为各行业提供专业、可靠的PCB/PCBA解决方案，助力客户产品创新与产业升级。</p>
        </div>

        <section class="section">
            <div class="solutions-grid">
                ${solutionsHtml}
            </div>
        </section>

        <!-- Feature: Auto -->
        <section class="section" style="padding-top:0">
            <div class="feature-block">
                <div class="feature-visual">${generateElegantPanel()}</div>
                <div class="feature-text">
                    <div class="ft-tag">AUTOMOTIVE</div>
                    <h3>汽车电子解决方案</h3>
                    <p>利尔鑫为汽车电子领域提供符合车规标准的PCB解决方案，产品涵盖ECU控制单元、BMS电池管理、车载信息娱乐系统、毫米波雷达等核心模块，确保在严苛的汽车使用环境下长期可靠运行。</p>
                    <ul class="feature-list">
                        <li>通过 IATF 16949 汽车行业质量体系认证</li>
                        <li>抗振动设计，机械可靠性测试通过</li>
                        <li>厚铜方案承载大电流，适用于BMS系统</li>
                        <li>高频材料支持77GHz车载雷达与ADAS</li>
                        <li>服务VinFast、蔚来、吉利威睿、长安汽车等头部车企</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Feature: Comm -->
        <section class="section" style="padding-top:0">
            <div class="feature-block reverse">
                <div class="feature-visual">${generateElegantPanel()}</div>
                <div class="feature-text">
                    <div class="ft-tag">COMMUNICATION</div>
                    <h3>通讯设备解决方案</h3>
                    <p>面向5G通信基站、射频前端、光模块等应用场景，提供高频高速PCB解决方案。采用低损耗高频材料，确保信号完整性，满足高速数据传输需求。</p>
                    <ul class="feature-list">
                        <li>低介电常数与低损耗因子材料</li>
                        <li>多层高频混压工艺</li>
                        <li>精密阻抗控制，精度±8%</li>
                        <li>支持77GHz毫米波雷达应用</li>
                        <li>服务精华电子（ASK终端）等通信行业头部企业</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="cta-section">
            <h2>寻找适合您行业的<span class="text-gold">PCB方案</span>？</h2>
            <p>我们的工程团队随时为您提供专业的行业解决方案咨询</p>
            <div class="cta-buttons">
                <a href="#/contact" class="btn btn-primary">咨询方案 ${icons.arrow}</a>
                <a href="#/products" class="btn btn-outline">查看产品</a>
            </div>
        </section>
    </div>`;
}

// ---- Page: About ----
function renderAbout() {
    return `
    <div class="page-enter">
        <div class="product-hero" style="background-image:url('images/about-hero.jpg')">
            <div class="eyebrow">ABOUT US</div>
            <h1>关于<span class="text-gold">利尔鑫</span></h1>
            <p>全球工业客户首选的PCB&模块化解决方案伙伴——集PCB线路板制造、PCBA制造及方案研发于一体，四大生产基地协同运作，为全球客户提供一站式ODM/OEM定制解决方案。</p>
        </div>

        <!-- Company Intro -->
        <section class="section">
            <div class="feature-block">
                <div class="feature-visual" style="height:400px">
                    <div style="text-align:center;padding:24px">
                        <div style="font-size:64px;margin-bottom:16px;opacity:0.5">${icons.building.replace('width="24"','width="64"')}</div>
                        <div style="font-family:'Montserrat',sans-serif;font-size:28px;font-weight:700;letter-spacing:4px;color:var(--gold)">LIERXIN</div>
                        <div style="font-size:15px;color:var(--text-secondary);margin-top:8px">深圳利尔鑫实业有限公司</div>
                        <div style="font-size:13px;color:var(--text-muted);margin-top:4px">Founded 2015 · Shenzhen</div>
                    </div>
                </div>
                <div class="feature-text">
                    <div class="ft-tag">COMPANY PROFILE</div>
                    <h3>公司简介</h3>
                    <p>深圳利尔鑫实业有限公司成立于2015年04月，位于广东省深圳市宝安区，是集PCB线路板制造、PCBA制造及方案研发于一体的国家高新技术企业，旗下设三大业务模块，母公司拥有智能数字化工厂，实现工业4.0生产，为客户提供从方案开发设计到生产、ODM、OEM的一站式定制服务。</p>
                    <p>集团员工3500人，专业技术人员占30%；PCB总产能达60万平米/月，产品覆盖1-28层板、HDI、厚铜、高频高速板全品类；深圳、湖北、清远、江西四大生产基地协同运作，并在日本大阪设有营业事务所；服务半径覆盖72小时中国交付、15天欧美达货。</p>
                    <ul class="feature-list">
                        <li>国家高新技术企业认证</li>
                        <li>IATF 16949 / ISO 9001 / ISO 13485 / GJB 9001C 体系认证</li>
                        <li>海关AEO高级认证企业</li>
                        <li>PCB总产能60万平米/月</li>
                        <li>四大生产基地 + 日本大阪营业事务所</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Capabilities -->
        <section class="section" style="padding-top:0">
            <div class="section-title">
                <div class="eyebrow">CORE CAPABILITIES</div>
                <h2>核心<span class="text-gold">能力</span></h2>
                <p>以专业能力构建竞争壁垒，以品质赢得客户信赖</p>
            </div>
            <div style="max-width:720px;margin:0 auto" class="capability-bars">
                <div class="capability-bar">
                    <div class="cb-header"><span>全品类PCB制造能力</span><span>96%</span></div>
                    <div class="cb-track"><div class="cb-fill" data-width="96%"></div></div>
                </div>
                <div class="capability-bar">
                    <div class="cb-header"><span>智能化生产与品控</span><span>98%</span></div>
                    <div class="cb-track"><div class="cb-fill" data-width="98%"></div></div>
                </div>
                <div class="capability-bar">
                    <div class="cb-header"><span>HDI工艺能力</span><span>92%</span></div>
                    <div class="cb-track"><div class="cb-fill" data-width="92%"></div></div>
                </div>
                <div class="capability-bar">
                    <div class="cb-header"><span>高频高速技术</span><span>90%</span></div>
                    <div class="cb-track"><div class="cb-fill" data-width="90%"></div></div>
                </div>
                <div class="capability-bar">
                    <div class="cb-header"><span>一站式交付能力</span><span>95%</span></div>
                    <div class="cb-track"><div class="cb-fill" data-width="95%"></div></div>
                </div>
            </div>
        </section>

        <!-- R&D -->
        <!-- Corporate Culture -->
        <section class="section">
            <div class="section-title">
                <div class="eyebrow">CORPORATE CULTURE</div>
                <h2>企业<span class="text-gold">文化</span></h2>
                <p>以匠心筑梦，携手全球客户共创辉煌未来</p>
            </div>
            <div class="cert-grid" style="margin-bottom:28px">
                <div class="cert-card">
                    <div class="cert-icon">${icons.globe}</div>
                    <div class="cert-name">企业愿景</div>
                    <div class="cert-desc">引领线路板行业，以创新和诚信服务全球</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.award}</div>
                    <div class="cert-name">企业使命</div>
                    <div class="cert-desc">以卓越产品服务客户，以成长平台成就员工，以绿色科技回馈社会</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.users}</div>
                    <div class="cert-name">核心价值观</div>
                    <div class="cert-desc">客户第一 · 团队合作 · 拥抱变化 · 诚信 · 激情 · 敬业</div>
                </div>
            </div>
            <div class="cert-grid">
                <div class="cert-card">
                    <div class="cert-icon">${icons.check}</div>
                    <div class="cert-name">深圳总部</div>
                    <div class="cert-desc">总部、研发中心、PCB/PCBA工厂，距宝安国际机场30分钟车程</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.factory}</div>
                    <div class="cert-name">湖北/清远基地</div>
                    <div class="cert-desc">车规级品控，大批量汽车电子，工业4.0自动化工厂，厚铜板、汽车BMS</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.patent}</div>
                    <div class="cert-name">江西基地</div>
                    <div class="cert-desc">国际化环保合规，中批量高要求，海外客户为主，小批量定制快速响应</div>
                </div>
                <div class="cert-card">
                    <div class="cert-icon">${icons.shield}</div>
                    <div class="cert-name">日本大阪</div>
                    <div class="cert-desc">营业事务所，辐射东亚市场，本地化客户服务</div>
                </div>
            </div>
        </section>

        <!-- R&D -->
        <section class="section" style="padding-top:0">
            <div class="section-title">
                <div class="eyebrow">R&D INNOVATION</div>
                <h2>研发<span class="text-gold">创新</span></h2>
            </div>
            <div class="feature-block reverse">
                <div class="feature-visual" style="height:380px">
                    <div style="text-align:center;padding:24px">
                        <div style="font-size:64px;margin-bottom:16px;opacity:0.5">${icons.patent.replace('width="24"','width="64"')}</div>
                        <div style="font-family:'Montserrat',sans-serif;font-size:56px;font-weight:700;background:linear-gradient(135deg,var(--gold),var(--gold-light));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">30%</div>
                        <div style="font-size:15px;color:var(--text-secondary)">专业技术人员占比</div>
                    </div>
                </div>
                <div class="feature-text">
                    <div class="ft-tag">R&D & SOLUTION</div>
                    <h3>方案研发体系</h3>
                    <p>利尔鑫第三大业务板块——方案研发，专注于通信、工控监测、智能宠物类产品领域，凭借深厚行业经验与前沿技术，为客户提供涵盖研发、设计、加工的一站式产品应用解决方案，从方案定制到产品落地全流程保障。</p>
                    <ul class="feature-list">
                        <li>通信与工控模块：中/小功率无线信号收发器、8通道高速AD检测模块</li>
                        <li>无线通信网络优化设备：多运营商多频段信号放大单元</li>
                        <li>智能安防监控、监测系统设计及工程应用</li>
                        <li>智能宠物类：GPS定位器、宠物AI记录仪、智能鱼缸等</li>
                        <li>深圳/东莞研发团队，从单板加工到整机测试全周期把控</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Manufacturing -->
        <section class="section" style="padding-top:0">
            <div class="section-title">
                <div class="eyebrow">SMART MANUFACTURING</div>
                <h2>智能<span class="text-gold">制造</span></h2>
            </div>
            <div class="feature-block">
                <div class="feature-visual" style="height:380px">
                    <div style="text-align:center;padding:24px">
                        <div style="font-size:64px;margin-bottom:16px;opacity:0.5">${icons.factory.replace('width="24"','width="64"')}</div>
                        <div style="font-family:'Montserrat',sans-serif;font-size:56px;font-weight:700;background:linear-gradient(135deg,var(--gold),var(--gold-light));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">60万㎡/月</div>
                        <div style="font-size:15px;color:var(--text-secondary)">PCB总产能</div>
                    </div>
                </div>
                <div class="feature-text">
                    <div class="ft-tag">FACTORY</div>
                    <h3>精益制造体系</h3>
                    <p>母公司拥有智能数字化工厂，实现工业4.0生产。深圳总部厂房7000平米（研发中心技术支持），湖北/清远工厂车规级品控（35万平米/月），江西工厂国际化环保合规（15万平米/月），PCBA基地位于深圳沙井，配备20条SMT线体、5条装配线、4条DIP线。</p>
                    <ul class="feature-list">
                        <li>四大生产基地：深圳/湖北/清远/江西协同运作</li>
                        <li>PCB总产能60万平米/月，1-28层全覆盖</li>
                        <li>湖北/清远基地支持厚铜4-6oz、埋铜块、HDI&RF</li>
                        <li>江西基地POFV/VIPPO/金属基板IMS，服务海外客户</li>
                        <li>HALT高加速寿命测试、热循环、CAF耐离子迁移测试</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Global Service -->
        <section class="section" style="padding-top:0">
            <div class="section-title">
                <div class="eyebrow">GLOBAL SERVICE</div>
                <h2>全球<span class="text-gold">服务</span></h2>
            </div>
            <div class="feature-block reverse">
                <div class="feature-visual" style="height:380px">
                    <div style="text-align:center;padding:24px">
                        <div style="font-size:64px;margin-bottom:16px;opacity:0.5">${icons.globe.replace('width="24"','width="64"')}</div>
                        <div style="font-family:'Montserrat',sans-serif;font-size:56px;font-weight:700;background:linear-gradient(135deg,var(--gold),var(--gold-light));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">全球化</div>
                        <div style="font-size:15px;color:var(--text-secondary)">服务网络</div>
                    </div>
                </div>
                <div class="feature-text">
                    <div class="ft-tag">GLOBAL NETWORK</div>
                    <h3>全球服务网络</h3>
                    <p>利尔鑫的业务网络覆盖中国50%、东南亚20%、欧洲20%、美洲5%的全球市场，在日本大阪设有营业事务所。本地化服务团队24小时技术响应，提供"一站式"服务，减少客户沟通成本。</p>
                    <ul class="feature-list">
                        <li>服务VinFast、蔚来、吉利威睿、长安等头部车企</li>
                        <li>中国50%、东南亚20%、欧洲20%、美洲5%市场分布</li>
                        <li>72小时中国交付，15天欧美达货</li>
                        <li>从方案开发设计到ODM/OEM量产一站式定制</li>
                        <li>供应链全链条可控，确保交付时效与品控</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Timeline -->
        <section class="section" style="padding-top:0">
            <div class="section-title">
                <div class="eyebrow">MILESTONES</div>
                <h2>发展<span class="text-gold">历程</span></h2>
            </div>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-year">2015</div>
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>公司成立</h4>
                        <p>深圳利尔鑫实业有限公司在深圳宝安区成立，开启PCB线路板制造之路，逐步建立PCBA制造与方案研发业务板块。</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-year">2023</div>
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>工艺升级</h4>
                        <p>18层通孔板量产能力成熟，2阶HDI、铜基板、埋铜块、内外4oz厚铜等工艺相继落地。</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-year">2024</div>
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>能力扩展</h4>
                        <p>通孔软硬结合板、3阶HDI、铝基板、6oz厚铜等特殊工艺实现量产，车规级品控体系持续完善。</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-year">2025</div>
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h4>高端突破</h4>
                        <p>Anylayer HDI、高速服务器板、高频混压板、HDI软硬结合板等高端产品工艺全面突破，服务全球头部客户。</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="cta-section">
            <h2>与利尔鑫<span class="text-gold">携手共赢</span></h2>
            <p>以专业能力与品质承诺，为您的产品创新保驾护航</p>
            <div class="cta-buttons">
                <a href="#/contact" class="btn btn-primary">联系我们 ${icons.arrow}</a>
                <a href="#/products" class="btn btn-outline">查看产品</a>
            </div>
        </section>
    </div>`;
}

// ---- Page: News ----
function renderNews() {
    const newsItems = [
        { date: '2026-08-20', tag: '企业动态', img: 'news-1.jpg', title: '利尔鑫2026年半年度经营总结会圆满召开', excerpt: '回顾上半年发展成果，部署下半年重点工作，利尔鑫持续推进智能制造与全球化布局。' },
        { date: '2026-07-15', tag: '技术前沿', img: 'news-2.jpg', title: '利尔鑫任意层HDI工艺再升级，最小孔径达50μm', excerpt: '在HDI微孔激光钻孔工艺上取得新突破，为高密度互连提供更优解决方案，满足5G终端需求。' },
        { date: '2026-06-28', tag: '合作动态', img: 'news-3.jpg', title: '利尔鑫深化与VinFast合作，共推电动车PCB方案', excerpt: '为越南电动车龙头VinFast批量供应汽车移动电源6层通孔板，利尔鑫持续为全球头部车企提供高品质产品。' },
        { date: '2026-05-30', tag: '资质荣誉', img: 'news-4.jpg', title: '利尔鑫体系认证再添多项权威背书', excerpt: '公司通过GJB 9001C武器装备质量管理、QC080000有害物质控制及海关AEO高级认证，资质体系行业领先。' },
        { date: '2026-04-18', tag: '展会动态', img: 'news-5.jpg', title: '利尔鑫亮相2026国际电子电路展览会', excerpt: '利尔鑫携最新HDI、高频高速PCB及PCBA一站式方案亮相展会，吸引众多客户交流洽谈。' },
        { date: '2026-03-22', tag: '行业洞察', img: 'news-6.jpg', title: '新能源汽车PCB需求增长，厚铜板方案助力BMS', excerpt: '深入分析新能源车PCB需求趋势，利尔鑫厚铜板方案为BMS电池管理系统提供可靠保障。' },
        { date: '2026-02-10', tag: '企业动态', img: 'news-7.jpg', title: '四大基地产能持续爬坡，PCB月产能达60万平米', excerpt: '深圳、湖北、清远、江西四大生产基地协同运作，PCB总产能达60万平米/月，72小时中国交付、15天欧美达货。' },
        { date: '2026-01-15', tag: '技术前沿', img: 'news-8.jpg', title: '利尔鑫PCB方案助力医疗监护仪与内窥镜创新', excerpt: '通过ISO 13485医疗体系认证，为医疗设备提供高可靠性PCB方案，助力智慧医疗发展。' },
        { date: '2025-12-20', tag: '资质荣誉', img: 'news-9.jpg', title: '利尔鑫通过ISO 50001能源管理体系认证', excerpt: '在能源管理与绿色制造方面取得突出成绩，践行"以绿色科技回馈社会"的企业使命。' },
    ];

    return `
    <div class="page-enter">
        <div class="product-hero" style="background-image:url('images/news-hero.jpg')">
            <div class="eyebrow">NEWS CENTER</div>
            <h1>资讯<span class="text-gold">中心</span></h1>
            <p>企业动态、技术前沿、行业洞察与合作资讯，助您全面了解利尔鑫最新发展。</p>
        </div>

        <section class="section">
            <div class="news-grid">
                ${newsItems.map(item => `
                <div class="news-card">
                    <div class="news-visual" style="background-image:url('images/news/${item.img}')">
                        <div class="news-date-badge">${item.date}</div>
                    </div>
                    <div class="news-body">
                        <div class="news-tag">${item.tag}</div>
                        <h3 class="news-title">${item.title}</h3>
                        <p class="news-excerpt">${item.excerpt}</p>
                    </div>
                </div>`).join('')}
            </div>
        </section>

        <section class="cta-section">
            <h2>关注利尔鑫<span class="text-gold">最新动态</span></h2>
            <p>如需了解更多产品与技术信息，欢迎随时与我们联系</p>
            <div class="cta-buttons">
                <a href="#/contact" class="btn btn-primary">联系我们 ${icons.arrow}</a>
                <a href="#/about" class="btn btn-outline">了解公司</a>
            </div>
        </section>
    </div>`;
}

// ---- Page: Contact ----
function renderContact() {
    return `
    <div class="page-enter">
        <div class="product-hero" style="background-image:url('images/contact-hero.jpg')">
            <div class="eyebrow">CONTACT US</div>
            <h1>联系<span class="text-gold">我们</span></h1>
            <p>无论您需要产品咨询、技术支持还是合作洽谈，利尔鑫的专业团队随时为您服务。</p>
        </div>

        <section class="section">
            <div class="contact-grid">
                <div class="contact-info-card">
                    <h3 style="font-family:var(--font-serif);font-size:22px;margin-bottom:32px">联系方式</h3>
                    <div class="contact-info-item">
                        <div class="ci-icon">${icons.phone}</div>
                        <div class="ci-content">
                            <h5>联系电话</h5>
                            <p>191 6877 4494 / 138 2999 8264</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="ci-icon">${icons.mail}</div>
                        <div class="ci-content">
                            <h5>电子邮箱</h5>
                            <p>selina@szlierpcb.com（康总）<br>vicky@szlierpcb.com（颜总）</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="ci-icon">${icons.location}</div>
                        <div class="ci-content">
                            <h5>公司地址</h5>
                            <p>深圳市宝安区新桥街道新二社区南岭路23号C栋</p>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="ci-icon">${icons.truck}</div>
                        <div class="ci-content">
                            <h5>交付时效</h5>
                            <p>72小时中国交付 · 15天欧美达货</p>
                        </div>
                    </div>
                </div>

                <div class="contact-form-card">
                    <h3 style="font-family:var(--font-serif);font-size:22px;margin-bottom:32px">在线留言</h3>
                    <form onsubmit="submitForm(event)">
                        <div class="form-row">
                            <div class="form-group">
                                <label>您的姓名 *</label>
                                <input type="text" class="form-input" placeholder="请输入您的姓名" required>
                            </div>
                            <div class="form-group">
                                <label>联系电话 *</label>
                                <input type="tel" class="form-input" placeholder="请输入手机号" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>电子邮箱</label>
                            <input type="email" class="form-input" placeholder="请输入邮箱地址">
                        </div>
                        <div class="form-group">
                            <label>公司名称</label>
                            <input type="text" class="form-input" placeholder="请输入公司名称">
                        </div>
                        <div class="form-group">
                            <label>咨询产品</label>
                            <select class="form-input">
                                <option value="">请选择咨询产品</option>
                                <option>HDI线路板</option>
                                <option>多层PCB板</option>
                                <option>PCBA贴装组装</option>
                                <option>软硬结合板</option>
                                <option>高频高速板</option>
                                <option>其他ODM/OEM定制</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>留言内容 *</label>
                            <textarea class="form-textarea" placeholder="请描述您的需求..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">提交留言 ${icons.arrow}</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- Map -->
        <section class="section" style="padding-top:0">
            <div class="map-card" style="text-align:center;padding:64px 24px">
                <div style="font-size:48px;margin-bottom:16px;opacity:0.5">${icons.location.replace('width="20"','width="48"')}</div>
                <h3 style="font-family:var(--font-serif);font-size:20px;margin-bottom:8px">总部位置</h3>
                <p style="color:var(--text-secondary);font-size:15px">深圳市宝安区新桥街道新二社区南岭路23号C栋</p>
                <p style="color:var(--text-muted);font-size:13px;margin-top:8px">距深圳宝安国际机场仅30分钟车程</p>
            </div>
        </section>
    </div>`;
}

// ---- Form submit handler ----
function submitForm(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const original = btn.innerHTML;
    btn.innerHTML = '提交成功 ✓';
    btn.style.opacity = '0.8';
    setTimeout(() => {
        e.target.reset();
        btn.innerHTML = original;
        btn.style.opacity = '';
    }, 2000);
}

// ---- Router ----
const routes = {
    '/': { render: renderHome, title: '首页' },
    '/products': { render: renderProducts, title: '产品中心' },
    '/solutions': { render: renderSolutions, title: '行业方案' },
    '/about': { render: renderAbout, title: '关于我们' },
    '/news': { render: renderNews, title: '资讯中心' },
    '/contact': { render: renderContact, title: '联系我们' },
};

function router() {
    const hash = location.hash.slice(1) || '/';
    let route, title;
    
    // Handle product detail: /products/{id}
    if (hash.startsWith('/products/')) {
        const productId = hash.replace('/products/', '');
        const product = productsData.find(p => p.id === productId);
        if (product) {
            route = { render: () => renderProductDetail(productId) };
            title = product.title;
        } else {
            route = routes['/products'];
            title = '产品中心';
        }
    } else {
        route = routes[hash] || routes['/'];
        title = route.title;
    }

    const app = document.getElementById('app');
    app.innerHTML = route.render();
    app.classList.remove('page-enter');
    void app.offsetWidth;
    app.classList.add('page-enter');

    document.title = `LIERXIN 利尔鑫 | ${title} — 高端PCB/PCBA一站式智造专家`;

    document.querySelectorAll('.nav-link').forEach(link => {
        const linkHash = (link.getAttribute('href') || '').replace(/^#/, '');
        // Highlight "产品中心" for both /products and /products/*
        const isActive = linkHash === hash || (linkHash === '/products' && hash.startsWith('/products'));
        link.classList.toggle('active', isActive);
    });

    document.getElementById('navMenu').classList.remove('open');
    document.getElementById('navToggle').classList.remove('open');

    window.scrollTo({ top: 0, behavior: 'instant' });
    animateCounters();
    animateBars();
    handleScroll();
}

// ---- Counter Animation ----
function animateCounters() {
    const counters = document.querySelectorAll('[data-count]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.count);
                const duration = 1600;
                const start = performance.now();
                function update(now) {
                    const elapsed = now - start;
                    const progress = Math.min(elapsed / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const current = Math.floor(eased * target);
                    el.textContent = current.toLocaleString();
                    if (progress < 1) requestAnimationFrame(update);
                    else el.textContent = target.toLocaleString();
                }
                requestAnimationFrame(update);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.3 });
    counters.forEach(c => observer.observe(c));
}

// ---- Capability Bars Animation ----
function animateBars() {
    const bars = document.querySelectorAll('.cb-fill[data-width]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                setTimeout(() => { entry.target.style.width = entry.target.dataset.width; }, 200);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    bars.forEach(b => { b.style.width = '0%'; observer.observe(b); });
}

// ---- Scroll Handlers ----
function handleScroll() {
    const navbar = document.getElementById('navbar');
    const scrollTop = document.getElementById('scrollTop');
    if (window.scrollY > 50) navbar.classList.add('scrolled');
    else navbar.classList.remove('scrolled');
    if (window.scrollY > 400) scrollTop.classList.add('show');
    else scrollTop.classList.remove('show');
}

// ---- Init ----
window.addEventListener('hashchange', router);
window.addEventListener('scroll', handleScroll);

// ---- Theme Switch (dark / light) ----
// 默认深色；用户选择后写入 localStorage 记忆
const THEME_KEY = 'lierxin-theme';
function applyTheme(theme) {
    if (theme === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
}
function initTheme() {
    try {
        const saved = localStorage.getItem(THEME_KEY);
        applyTheme(saved === 'light' ? 'light' : 'dark');
    } catch (e) { applyTheme('dark'); }

    const btn = document.getElementById('themeToggle');
    if (!btn) return;
    btn.addEventListener('click', () => {
        const isLight = document.documentElement.getAttribute('data-theme') === 'light';
        const next = isLight ? 'dark' : 'light';
        applyTheme(next);
        try { localStorage.setItem(THEME_KEY, next); } catch (e) {}
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    navToggle.addEventListener('click', () => {
        navToggle.classList.toggle('open');
        navMenu.classList.toggle('open');
    });
    document.getElementById('scrollTop').addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    if (!location.hash) location.hash = '#/';
    router();
});
