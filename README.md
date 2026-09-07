# 利尔鑫 LIERXIN 企业官网

深圳利尔鑫实业有限公司官方网站 —— 高端 PCB / PCBA 一站式智造。

## 在线访问

https://dbbreath-crypto.github.io/lierxin-website/

## 技术栈

纯静态站点，**无后端、无数据库、无构建步骤**：

- `index.html` — 页面骨架
- `js/app.js` — hash 路由 SPA，所有页面内容在此渲染
- `css/style.css` — 全部样式，含暗色 / 浅色双主题（CSS 变量）
- `images/` — 站点素材（hero 背景、产品、新闻、行业方案配图）

图片素材均来自 [Pexels](https://www.pexels.com/)，CC0 协议，免费商用免署名。

## 本地预览

```bash
cd topreach-site
python3 -m http.server 8899
```

浏览器打开 http://localhost:8899/

注意：必须通过 HTTP 服务访问，直接双击 `index.html` 用 `file://` 打开会因浏览器安全策略导致部分资源加载失败。

## 主题切换

默认暗色主题。导航栏右上角的月亮 / 太阳图标可切换浅色主题，选择保存在 `localStorage`（键名 `lierxin-theme`），刷新后保持。

样式改动请优先使用 CSS 变量，不要写死颜色值，否则会破坏双主题适配。

## 部署

站点为纯静态，可托管到任意静态空间。推送到 GitHub 后，GitHub Pages 会自动发布（Settings → Pages → Deploy from a branch → `main` / `/root`）。

```bash
git add -A
git commit -m "说明改动内容"
git push origin main
```

推送后 GitHub Pages 需 1~2 分钟完成构建。

## 内容维护

全站文案集中在 `js/app.js`，主要数据区块：

- `productsData` — 六大产品线的介绍与规格参数
- `newsItems` — 新闻中心列表
- `solutionsData` — 行业方案卡片
- `renderContact()` — 联系页信息与表单

公司真实数据源自《深圳利尔鑫实业有限公司》官方介绍资料（2025-05 版）。
