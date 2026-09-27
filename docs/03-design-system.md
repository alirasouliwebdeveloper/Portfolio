# 03 — Design system

Machine-readable version: `design/tokens.json`. Define these as CSS variables in `globals.css` and map them into the Tailwind theme (Tailwind v4: `@theme`; v3: `theme.extend`). Components use theme names, never raw values.

## Colors (dark theme only)
| Token | Hex | Use |
|---|---|---|
| `bg` | #0A0B1A | page background |
| `bg-alt` | #0D0F22 | alternating sections |
| `bg-footer` | #080916 | footer |
| `surface` | #10122A | cards |
| `surface-input` | #0F1128 | inputs, code card, dropzone |
| `surface-strong` | #14153A | open FAQ item, highlighted tier |
| `border` | #262A4D | card borders |
| `border-input` | #2B2E57 | inputs, outline chips |
| `divider` | #22254A | lines inside cards |
| `line` | #191B34 | header/footer borders |
| `chip` | #1B1942 | tag/chip background |
| `chip-text` | #D3CCFF | tag text |
| `tile` | #2C2A63 | icon tile background |
| `accent` | #6D5DF5 | primary (buttons, active states) |
| `accent-2` | #8272FF | gradient end for primary buttons |
| `accent-soft` | #8F80FF | name in hero, quote icon |
| `eyebrow` | #9C8FFF | eyebrow labels |
| `link` | #A99CFF | inline text links, "Read Article" |
| `icon-soft` | #B1A6FF | icons in soft tiles |
| `text` | #F4F5FA | headings, primary text |
| `text-2` | #C9CBE0 | body in cards, labels, nav |
| `muted` | #A7AAC6 | paragraphs |
| `dim` | #8E91B0 | meta, captions |
| `success` | #4ADE80 (fg) / #16A34A (solid) | upload ok, Turnstile, checks |
| `danger` | #F87171 (fg) / #7F2D3A (border) | errors |
| `star` | #F5B84A | rating stars |

Primary button: `linear-gradient(90deg, accent, accent-2)`, white text. Outline button: 1px `text-2` border, white text.

## Typography
Inter 400/500/600/700; JetBrains Mono 400 for code. Headings weight 600, letter-spacing −0.02em for H1, −0.01em for H2.

| Style | Desktop | Tablet | Mobile | Line height |
|---|---|---|---|---|
| Hero H1 | 68 | 52 | 40 | 1.08 |
| Page H1 | 56–62 | 44–46 | 34 | 1.1–1.15 |
| Section H2 | 36 | 30 | 26 | 1.25 |
| Card title | 19–22 | 18–20 | 18–19 | 1.35–1.4 |
| Lead | 19 | 18 | 16 | 1.65 |
| Body | 16 | 16 | 15 | 1.7 |
| Article body | 18 | 17.5 | 16.5 | 1.85 |
| Eyebrow | 13 / 600 / +0.1em / uppercase | 13 | 12 | — |
| Meta / caption | 13–14 | 13–14 | 13 | — |

## Layout & spacing
- Breakpoints: mobile < 768, tablet 768–1279, desktop ≥ 1280. Designs are drawn at 390, 834 and 1920.
- Container: `max-width: 1440px; margin-inline: auto; padding-inline: 20px (mobile) / 40px (tablet) / 0 when viewport ≥ 1520 (use 40px below that)`.
- Section padding-block: 96 / 72 / 56.
- Grid gaps: 32 / 24 / 20. Card padding: 32 / 28 / 24.
- Columns: post/project grids 3 / 2 / 1; stack & process 4 / 2 / 1; testimonials 3 / 1 / 1.

## Radii & effects
Buttons, inputs: 8px · Cards: 12px · Large cards, images: 14–16px · Chips/pills: 999px · Tags: 6px · Icon tiles: 22% of size · Portrait arch: `border-radius: 50% 50% 20px 20px / (width/2) (width/2) 20px 20px` (top fully round).
Shadows only on floating elements (code card, floating metric card): `0 20px 40px rgba(0,0,0,.4)`.

## Components
- **Icon tile:** square, 56 / 52 / 48, background `tile` (or `chip` with `icon-soft` icon for secondary tiles, or `accent` for emphasis). Icon = 52% of tile, stroke 1.8, round caps. Use one icon set (e.g. Lucide) mapped to the icon names stored in the API.
- **Buttons:** height 52 / 50; padding-inline 26; 16px / 500; icon after label, 14–18px. Full width on mobile in hero and form submit.
- **Chips (filters):** pill, 10×18 padding, 14px; active = `accent` fill; others 1px `border-input`.
- **Post card:** 16:10 cover with category tag top-start, meta, title (link), excerpt, "Read Article" link pinned to bottom.
- **FAQ item:** 12px radius; closed = `surface` + `border`; open = `surface-strong` + border #4A43A8; toggle = 36px circle (`accent` minus when open, `border` plus when closed).
- **Uploader rows:** file tile 42px, name (ellipsis), status line, remove button 36px. Error rows use a `danger` border.
- **Focus:** 2px `accent` outline with 2px offset on all interactive elements (not drawn in the design — add it).
- Motion: only for user actions (accordion, menu, hover color). No scroll-triggered fade-ins.

## Assets
`assets/images/` contains the portrait cutout (`ali-portrait.png`, real) and **mock** project screenshots and blog covers rendered for the design. Seed them into the media library so pages aren't empty; replace before launch.
