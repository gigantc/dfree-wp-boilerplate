
# Block Folder Structure

This document explains how custom Gutenberg blocks are organized in this theme and how to decide where a new block belongs. The goal is consistency, clarity, and long-term scalability.

---


## layout/

Structural blocks that define **arrangement and structure**.
These blocks are reusable and largely content-agnostic.
This folder will likely contain the most items and is organized into subfolders.

### layout/split/
Two-column layout patterns.

**Examples**
- 50/50 media + content
- Content + content
- Asymmetrical splits (30/70, 40/60)

---

### layout/grid/
Repeated multi-column layouts.

**Examples**
- Card grids
- Three-across layouts
- Logo or image grids

---

### layout/carousel/
Generic, content-agnostic carousel logic.
This is the base “engine” used by more opinionated carousel blocks.

**Notes**
- Minimal styling
- No assumptions about content type
- Feature- or hero-specific carousels live elsewhere

---

### layout/stack/
Vertical repetition patterns.

**Examples**
- Stacked cards
- Alternating content sections
- Repeating content rows

---

### layout/accordion/
Generic accordion behavior.

**Notes**
- Handles expand/collapse behavior and accessibility
- No assumptions about content purpose
- FAQ or feature-specific accordions live in `feature/`

---

## hero/

Page entry blocks that appear at the top of a page.
These are high-impact, opinionated sections and typically appear once per page.

**Examples**
- Home page hero
- Standard page hero
- Hero carousel

---

## feature/

Storytelling and marketing-focused blocks.
These blocks combine structure, content expectations, and styling
to communicate specific messages or value.

Feature blocks may still be CMS-editable, but they are **purposeful** and **opinionated**.
When a block exists to drive a message or action, it belongs here.

**Examples**
- Feature split (media + text + CTA)
- Icon feature lists
- Stat or metric callouts
- Testimonials
- Case study previews
- FAQ accordions

---

## utility/

Helper blocks that support layout and editing rather than storytelling.

**Examples**
- Spacers
- Dividers
- Anchors / jump links
- Background wrappers
- Notices / alerts
- CTA bars

---

## misc/

Blocks that do not clearly fit into another category.

**Guidelines**
- Use sparingly
- Intended for experimental, legacy, or truly one-off blocks
- Any block placed here should include a note explaining why it cannot live elsewhere

---

## Current Block Tree

```
blocks/
├─ layout/
│  ├─ split/
│  │  ├─ media-content
│  │  └─ content-content
│  │
│  ├─ grid/
│  │  ├─ grid-triple
│  │  └─ grid-cards
│  │
│  ├─ carousel/
│  │  └─ carousel
│  │
│  ├─ accordion/
│  │  └─ accordion
│  │
│  └─ stack/
│     └─ card-stack
│
├─ hero/
│  ├─ hero-home
│  ├─ hero-page
│  └─ hero-carousel
│
├─ feature/
│  ├─ stats-impact
│  ├─ quote
│  ├─ carousel-testimonial
│  ├─ carousel-related-posts
│  ├─ callout-support
│  ├─ accordion-faq
│  └─ accordion-outcomes-list
│
├─ utility/
│  ├─ spacer
│  ├─ divider
│  ├─ anchor
│  ├─ alert
│  ├─ notice
│  └─ cta-bar
│
└─ misc/
```

---

## How to Decide Where a Block Goes

- If it defines **structure or layout** → `layout/`
- If it is **page entry content** → `hero/`
- If it tells a story or highlights value → `feature/`
- If it supports layout or editing → `utility/`
- If it truly does not fit anywhere else → `misc/` (with justification)

### Layout vs Feature (important)

A block being **CMS-controlled** does not automatically make it a `layout/` block.
The deciding factor is **intent and reusability**:

- Use `layout/` when the block is primarily **structural** and **content-agnostic** (a reusable pattern).
- Use `feature/` when the block is primarily **storytelling / conversion** and **opinionated** (a designed message).

**Quick test:** could this block be reused for a completely different purpose without feeling weird?
- If yes → `layout/`
- If no → `feature/`

### Example: Stats blocks

**Layout stats** (goes in `layout/`)  
A reusable “stats grid” pattern where editors control the numbers and labels.

Example path:
- `layout/stats/stats-grid`

**Feature stats** (goes in `feature/`)  
A stats block designed to communicate a specific narrative (e.g., “Impact,” “By the numbers,” “Outcomes”).
Even if editors can edit the values, it belongs in `feature/` if the structure and styling assume a specific message.

Example paths:
- `feature/feature-stats`
- `feature/stats-impact`