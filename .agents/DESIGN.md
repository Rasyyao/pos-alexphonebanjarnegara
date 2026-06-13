# Alex Phone Banjarnegara — Redesign Spec

> Handoff document for implementation. The original site
> (`alexphonebanjarnegara.com`) is a single-page promo for a local
> phone shop in Banjarnegara, Central Java. This redesign keeps the
> single-page structure and Indonesian copy but rebuilds the layout
> around a premium, "Apple-store-clean" aesthetic with a
> deep-blue + white palette.

---

## 1. Design principles

1. **Trust first.** This is a local konter selling new + used phones
   and doing repairs. Every section should communicate reliability —
   space, type hierarchy, and restraint do the heavy lifting; not
   gradients, badges, or AI-slop iconography.
2. **One quiet color.** Deep navy on near-white. Color is reserved
   for the brand mark, primary CTA, and the occasional state hint.
3. **Product is the hero.** Phones are shot clean against neutral
   backgrounds. Cards have generous whitespace; the device is
   centered and large.
4. **Indonesian voice.** Copy stays in Bahasa Indonesia. Keep it
   warm and direct ("Harga Murah Pelayanan Ramah"). No stiff
   marketing English.
5. **Single page, better rhythm.** Keep the long-scroll structure
   of the current site but introduce intentional pacing: tight
   service grid → roomy product grid → punchy promo → quiet
   testimonials → grounded footer.

---

## 2. Color tokens

| Token | Light | Dark | Usage |
|---|---|---|---|
| `--bg` | `#FFFFFF` | `#0A1428` | Page background |
| `--bg-soft` | `#F4F6FB` | `#0F1B33` | Section bands, product card surface |
| `--bg-elev` | `#FFFFFF` | `#162547` | Elevated cards |
| `--ink` | `#0A2540` | `#F4F6FB` | Primary text |
| `--ink-soft` | `#3D5374` | `#A8B6D1` | Secondary text |
| `--ink-mute` | `#7A8AA8` | `#6E7EA0` | Tertiary, captions, meta |
| `--line` | `#E4E9F2` | `#1E2D4E` | Hairline borders, dividers |
| `--brand` | `#0A2540` | `#FFFFFF` | Logo lockup, primary text on light |
| `--accent` | `#2563EB` | `#5B8DEF` | Primary CTA, link, focus ring |
| `--accent-ink` | `#FFFFFF` | `#0A1428` | Foreground on accent |
| `--success` | `#10806B` | `#3DCFAE` | "Open now" pip, WhatsApp pip |
| `--warn` | `#C2410C` | `#F6A56B` | Promo highlight (used sparingly) |

Tweak-driven themes (palette dropdown):

- **Navy** (default) — accent `#2563EB`
- **Indigo** — accent `#4F46E5`
- **Cool slate** (monochrome) — accent `#334155`
- **Warm** (alt) — keeps neutrals but switches accent to `#C2410C`

Saturation cap on neutrals: **≤ 0.02** in OKLCH. Whites are slightly
cool-toned (`oklch(99% 0.003 250)`).

---

## 3. Typography

Font stack pairs a clean grotesk for UI with a tighter display face
for hero numerals and section titles.

**Primary (UI + body):** `"Helvetica Neue", Helvetica, "Inter Tight",
system-ui, sans-serif`

**Display (h1, section labels):** same family at heavier weights —
tracking tightened (`-0.02em` for ≥48px).

**Mono (price, tags, store hours):** `"Satoshi", ui-monospace,
monospace` — used at small sizes only, for prices and the WIB time
strip.

Tweak font pairings:
1. Satoshi

### Scale (1.25 ratio, clamp-based)

| Token | Size (desktop) | Weight | Tracking | Use |
|---|---|---|---|---|
| `display-xl` | `clamp(48px, 7vw, 96px)` | 600 | -0.03em | Hero headline |
| `display-l` | `clamp(36px, 4.5vw, 64px)` | 600 | -0.02em | Section titles |
| `display-m` | `28px` | 600 | -0.01em | Card titles, h3 |
| `body-l` | `18px` | 400 | 0 | Hero subhead, lead paragraphs |
| `body` | `16px` | 400 | 0 | Default body |
| `body-s` | `14px` | 500 | 0 | Metadata, captions |
| `eyebrow` | `12px` | 600 | 0.12em UPPER | Section eyebrows |
| `mono-s` | `13px` | 500 | 0 | Prices, badges |

Line-height: 1.15 on display, 1.5 on body, 1.3 on card titles.
Use `text-wrap: pretty` on every paragraph and `text-wrap: balance`
on headings.

---

## 4. Layout system

- **Page max-width:** `1280px`, gutters `clamp(20px, 4vw, 56px)`.
- **Grid:** 12-col on ≥1024, 8-col on 640–1024, 4-col below.
- **Vertical rhythm:** sections separated by `clamp(72px, 10vw, 140px)`
  of top padding.
- **Section banding:** alternate `--bg` and `--bg-soft` to introduce
  rhythm without lines.

### Section order (top → bottom)

1. **Top utility strip** — open hours pip + WhatsApp link, 36px tall.
2. **Navigation** — logo (text lockup) left, links center, CTA right.
3. **Hero** — large display headline, lead subhead, two CTAs, single
   editorial product photograph right (or centered, depending on
   "Hero variant" tweak).
4. **Trust strip** — small monospaced row of brand names (Apple,
   Samsung, Xiaomi, Oppo, Vivo) with hairline separators. No logos —
   text only, intentionally understated.
5. **Services (3 cards)** — HP Baru & Bekas / Servis Kilat /
   Aksesoris. Large numbered (01 / 02 / 03), no decorative icons.
6. **Promo strip** — full-bleed dark navy band, single line of
   warm-toned copy + WhatsApp CTA. The only place warm color appears.
7. **Catalog grid** — "Paling Banyak Dicari". 4-up on desktop /
   2-up tablet / 1-up mobile. Each card: product photo placeholder,
   brand eyebrow, model name, monospaced "Mulai dari Rp …"
8. **Testimonials** — two quotes set as editorial pull-quotes, large
   serif-feel via tightened display weight. No avatars, no stars.
9. **CTA block** — "Mampir ke konter atau chat dulu" with WhatsApp +
   Shopee buttons side by side.
10. **Footer** — 3 columns: brand blurb, jam operasional, lokasi.
    Bottom bar with copyright + socials.

---

## 5. Component specs

### Buttons

- **Primary** — bg `--accent`, fg `--accent-ink`, radius `999px`,
  padding `14px 24px`, weight 500, no shadow. Hover: -8% lightness.
- **Secondary** — transparent, 1px border `--ink`, fg `--ink`, same
  radius/padding. Hover: bg `--ink`, fg `--bg`.
- **Ghost** — fg `--ink`, no border, padding `12px 16px`.
- **WhatsApp variant** — bg `--success`, fg white, leading pip.

Focus: 2px outline `--accent`, offset 3px.

### Cards (3 styles, switchable via Card style tweak)

- **Flat (default)** — bg `--bg-elev`, no border, no shadow; relies
  on whitespace. Hover lifts 2px and shows a hairline border.
- **Bordered** — 1px `--line`, no shadow, hover border `--ink`.
- **Shadowed** — `0 1px 2px rgba(10,37,64,.04), 0 12px 32px
  rgba(10,37,64,.06)`. Hover deepens shadow.

Radius: `20px` for product cards, `16px` for service cards,
`12px` for inline chips/buttons.

### Product card

```
┌───────────────────────────┐
│   [ product photo 4:5 ]   │  ← --bg-soft, centered device
│                           │
├───────────────────────────┤
│ APPLE          ★ Tersedia │  ← eyebrow + status
│ iPhone 15 Pro             │  ← display-m
│ 128GB · Garansi Resmi     │  ← body-s, --ink-soft
│ ─────────────────         │
│ Mulai dari                │  ← body-s, --ink-mute
│ Rp 14.999.000             │  ← mono-s, --ink, 18px
└───────────────────────────┘
```

### Service card

Number `01` at 96px in `--ink-soft` (alpha 0.15), title in display-m,
two-line body in `--ink-soft`. No icons.

### Testimonial

Quote in display-l weight 500, line-height 1.25, max-width 18ch.
Attribution below in mono-s `--ink-mute`, prefixed with `— `.

---

## 6. Imagery

- Product photography placeholders are **subtly-striped SVG blocks
  with a monospaced caption** noting what should be dropped there
  (e.g. `iphone-15-pro.png`). Never hand-draw device illustrations.
- Hero photograph is one editorial shot of a phone (any angle, clean
  background). Aspect ratio 4:5 portrait or 16:10 landscape depending
  on hero variant.
- No emoji. No abstract gradients. No stock-photo handshakes.

---

## 7. Spacing tokens

| Token | Value |
|---|---|
| `space-1` | 4px |
| `space-2` | 8px |
| `space-3` | 12px |
| `space-4` | 16px |
| `space-5` | 24px |
| `space-6` | 32px |
| `space-7` | 48px |
| `space-8` | 64px |
| `space-9` | 96px |
| `space-10` | 140px |

---

## 8. Motion

Restrained. Three rules only:

1. Hover transitions on cards/buttons: `transform 220ms ease,
   border-color 180ms ease, background 180ms ease`.
2. Section reveal on scroll: opacity 0→1 + translateY(12px→0),
   400ms ease-out, staggered 60ms per child. Disable when
   `prefers-reduced-motion`.
3. No looping animations, no carousels, no auto-playing video.

---

## 9. Tweaks exposed in prototype

| Tweak | Options | Default |
|---|---|---|
| Theme | Navy / Indigo / Cool slate / Warm | Navy |
| Light/Dark | toggle | Light |
| Font pairing | Helvetica / Manrope / Geist / IBM Plex | Helvetica |
| Card style | Flat / Bordered / Shadowed | Flat |
| Hero variant | Editorial right / Centered / Split full-bleed | Editorial right |

---

## 10. Accessibility

- All interactive elements have visible focus rings.
- Min text contrast 4.5:1; the accent blue on white passes AA at
  16px.
- Hit targets ≥ 44px on mobile.
- Reduced motion respected (see §8).
- Indonesian content is set with `lang="id"` on the root.

---

## 11. Implementation notes for Claude Code

- Build as a static single HTML page first; tokens go on `:root` so
  themes can swap by adding `[data-theme="indigo"]` etc.
- Dark mode is a `[data-theme-mode="dark"]` attribute on `<html>`,
  not a media query — so the user can override.
- Use semantic landmarks: `<header>`, `<main>`, `<section
  aria-labelledby>`, `<footer>`.
- Product cards should be `<article>`s with an `<h3>` model name
  and a `<dl>` for spec metadata (capacity, garansi).
- WhatsApp links should deep-link to
  `https://wa.me/6289674886141?text=` with a URL-encoded prefilled
  message per CTA (e.g. "Halo Alex Phone, saya tertarik dengan
  iPhone 15 Pro").
- Image placeholders: `<svg>` with diagonal stripe pattern + caption
  `<text>` element. Replace with real product shots later.
- Keep the prototype within `index.html`; no build step required.

---

## 12. Admin / Dashboard page — design benchmarks

The storefront above is the **public** face. The admin panel is where
Alex (and staff) manage stock, orders, servis tickets, and promos. It
reuses the same tokens (§2–3) but follows a denser, utility-first
spec. Use these benchmarks as acceptance criteria — a screen is "done"
when it hits the targets below, not when it merely looks nice.

### 12.1 Layout shell

| Element | Spec | Benchmark |
|---|---|---|
| Sidebar (nav) | Fixed left, `240px` wide (collapses to `64px` icon-rail < 1024px) | Persists across routes; active item uses `--accent` left-border 3px + `--bg-soft` fill |
| Topbar | `56px` tall, sticky, holds search + notifications + account | Global search reachable in ≤ 1 click; `⌘K` opens command palette |
| Content max-width | `1440px`, gutter `24px` | Tables never exceed viewport without horizontal scroll affordance |
| Density mode | Comfortable (default) / Compact toggle | Compact reduces row height `52px → 40px` |

The admin shell does **not** use the floating pill nav or the
storefront's ambient decoration — those are marketing flourishes.
Admin chrome is flat, square-cornered (radius `10px` max on panels),
and quiet.

### 12.2 Core screens (build in this priority order)

1. **Dashboard / Ringkasan** — KPI row + recent activity.
2. **Produk / Stok** — data table, the workhorse screen.
3. **Pesanan** (orders) — list → detail drawer.
4. **Servis** (repair tickets) — kanban by status.
5. **Promo** — list + simple form.
6. **Pengaturan** (settings) — account, store hours, staff.

### 12.3 KPI / stat cards (Dashboard)

```
┌────────────────────────────┐
│ STOK AKTIF            ↗ 4%  │  ← label (eyebrow) + delta pill
│ 248 unit                    │  ← value, 32px, --font-ui 600
│ vs. 238 minggu lalu         │  ← caption, --ink-mute
└────────────────────────────┘
```

- **Benchmark:** max **4 KPIs** in the top row. More than 4 = noise.
  Each must answer a question Alex actually asks ("berapa stok?",
  "omzet hari ini?", "tiket servis pending?", "pesanan baru?").
- Delta pill: green `--success` for good direction, `--warn` for bad.
  Never both colors competing in one card.
- No sparkline unless it changes a decision. No donut charts for
  2-value splits — use a single labeled bar.

### 12.4 Data tables (the most important admin component)

This is where admin UIs live or die. Benchmarks:

| Aspect | Benchmark |
|---|---|
| Row height | 52px comfortable / 40px compact; vertical-center all cells |
| Header | Sticky on scroll; `--bg-soft` fill; `--ink-mute` 12px uppercase mono labels |
| Zebra | **No** zebra striping — use 1px `--line` row dividers instead |
| Hover | Whole row tints `--bg-soft`; row actions fade in on the right |
| Numeric cols | Right-aligned, tabular `--font-mono`, currency as `Rp 1.499.000` |
| Empty state | Illustrated + one primary action ("Tambah produk pertama") — never a blank table |
| Loading | Skeleton rows (not a spinner) matching final column widths |
| Pagination | Footer bar: "Menampilkan 1–20 dari 248" + page controls; default page size 20 |
| Bulk select | Checkbox col appears only after first selection or via header checkbox; bulk action bar slides up from bottom |
| Sort | Click header to sort; show ▲/▼; only one active sort at a time |
| Sticky first col | Product name column sticks when scrolling wide tables horizontally |

**Stock-specific:** show a status chip per row — `Ready` (green),
`Menipis` (warn, ≤ 3 unit), `Habis` (muted/strikethrough). Low-stock
rows get a `--warn` left accent.

### 12.5 Forms (add/edit product, promo, etc.)

- **Layout:** single column, `max-width 560px`, label **above** input
  (never floating labels — they hurt scannability for staff).
- Input height `44px`, radius `10px`, 1px `--line` border, focus ring
  `--accent`.
- Group related fields under `--font-mono` section headers
  ("Detail Unit", "Harga & Stok", "Garansi").
- **Benchmark:** primary action ("Simpan") is always bottom-right and
  **sticky** if the form scrolls; destructive actions ("Hapus") are
  visually separated (left side, ghost-red), never adjacent to save.
- Inline validation on blur, not on every keystroke. Error text sits
  directly below the field in `--warn`, 13px.
- Money inputs prefix `Rp` inside the field, format thousands with `.`
  on blur.

### 12.6 Status & feedback

| Pattern | Spec |
|---|---|
| Toast | Bottom-right, auto-dismiss 4s, max 1 visible; success = `--success` pip, error = `--warn` |
| Confirm destructive | Modal with explicit confirm for irreversible deletes; default-focus the **cancel** button |
| Servis ticket status | `Antri` → `Dikerjakan` → `Selesai` → `Diambil` — fixed color per stage, used consistently in kanban + badges |
| Save state | Show "Tersimpan" inline near the primary button, fade after 2s — don't toast every save |

### 12.7 Admin-specific benchmarks (acceptance criteria)

- **Three-click rule:** any common task (add stock, mark order paid,
  close a servis ticket) reachable in ≤ 3 clicks from the dashboard.
- **Keyboard:** `⌘K` command palette; `/` focuses table search;
  arrow-keys + Enter navigate table rows.
- **No horizontal scroll** on the shell at ≥ 1280px; tables may scroll
  internally but the page never does.
- **Information density:** a stock table should show ≥ 12 rows above
  the fold at 1080p in compact mode.
- **Latency cues:** every async action shows optimistic UI or a
  skeleton within 100ms; nothing silently hangs.
- **Color discipline:** `--accent` is reserved for primary actions and
  active nav only. Status colors (`--success`/`--warn`) never used for
  decoration. Greyscale carries 90% of the UI.
- **Mobile:** admin is desktop-first but must stay usable at 768px —
  tables collapse to stacked cards, sidebar becomes a drawer.

### 12.8 Reference bar (what "good" looks like)

Benchmark the admin against the clarity of **Linear** (keyboard-first,
quiet chrome), the table ergonomics of **Stripe Dashboard** (dense but
legible, great empty/loading states), and the form discipline of
**Shopify admin** (label-above, sticky save, grouped sections). Do
**not** copy their visuals — match their *standards*, dressed in the
Alex Phone navy token set.
