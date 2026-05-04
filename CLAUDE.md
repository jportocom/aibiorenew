# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Bio AI Renew** — a nutrition and weight-management web app. Marketing/static pages live at the repo root; the interactive app lives under `app/`. The parent `/var/www/CLAUDE.md` covers the overall portal conventions (language, commit format, inline styles, etc.).

## Architecture

```
aibiorenew/
├── index.html, missao.html, metodo.html …   # Static marketing pages
├── estudos/                                 # Educational article pages
├── area-pessoal.html                        # Login/register page (JWT)
├── app/
│   ├── frontend/                            # Authenticated app screens
│   │   ├── dashboard.html
│   │   ├── perfil.html
│   │   ├── registos.html
│   │   └── js/
│   │       ├── api.js        # Shared fetch wrapper + token helpers
│   │       ├── dashboard.js
│   │       └── perfil.js
│   ├── backend/              # Node.js/Express API (port 3000)
│   │   ├── server.js         # Entry point
│   │   ├── config.js         # Port + JWT config
│   │   ├── db.js             # pg Pool (PostgreSQL)
│   │   ├── middleware/auth.js # JWT verification
│   │   ├── routes/           # One file per resource
│   │   └── services/         # Pure business logic (calc, plan, avaliacao)
│   └── database/
│       ├── schema.sql         # PostgreSQL schema (reference)
│       ├── schema-mysql.sql   # MySQL variant
│       └── init.js
└── *.php                     # Legacy PHP management interface
```

Shared frontend assets (CSS, loginbar) live at `/var/www/assets/` and are referenced as `/assets/style.css`.

## Backend

**Stack:** Node.js + Express + PostgreSQL (`metabolic` DB, user `mpo`)

**Run/manage:**
```bash
pm2 list                    # Check status (name: medepeso-api)
pm2 restart medepeso-api    # After backend changes
pm2 logs medepeso-api       # Live logs
```

**Local dev (from `app/backend/`):**
```bash
npm run dev    # nodemon — auto-restarts on change
npm start      # node server.js
```

**Health check:** `curl http://localhost:3000/health`

**API routes:**
- `POST /auth/login`, `POST /auth/register`
- `GET|PUT /user/profile`
- `GET /calc` — returns IMC, TMB, TDEE
- `GET|POST /peso`, `/objetivos`, `/refeicoes`, `/atividades`, `/cardapios`
- `GET /plano/hoje`, `GET /plano/avaliacao`

## Database

PostgreSQL DB: `metabolic`, user: `mpo`. Connect: `PGPASSWORD=P0rtugal psql -U mpo -d metabolic`

**Dual-table user system** — this is the most important non-obvious architectural detail:
- `utilizadores` — legacy PHP table. Login checks `status='1'` here first.
- `mp_users` — Node.js app table (prefixed `mp_`). Created/synced on login.
- All Node.js backend tables are prefixed `mp_`: `mp_users`, `mp_pesos`, `mp_objetivos`, `mp_refeicoes`, `mp_atividades`, `mp_cardapios`, `mp_planos_diarios`, `mp_avaliacoes_diarias`, `mp_metricas`.

**Auth flow on login:** verify against `utilizadores` → auto-create `mp_users` row if missing → issue JWT.

**Passwords:** `utilizadores` stores plain text (legacy import); `mp_users` uses bcrypt. The auth route handles both transparently.

## Frontend Auth

JWT stored as `mp_token` in `localStorage`. On 401, `api.js` clears token and redirects to `/dev/medepeso/area-pessoal.html`. The `API_BASE` points to `http://ai.jporto.com` (proxied to port 3000).

## Services (pure functions, no DB)

- `calcService.js` — Mifflin-St Jeor formula → IMC, TMB, TDEE
- `planService.js` — Generates daily meal targets from TMB; 45% breakfast, 35% lunch, ≤400 kcal dinner
- `avaliacaoService.js` — Daily caloric balance: `deficit | equilibrio | excesso`

## Environment

Copy `app/backend/.env.example` to `.env`. Required vars: `PORT`, `JWT_SECRET`, `DB_HOST`, `DB_USER`, `DB_NAME`, `DB_PASSWORD`, `DB_PORT`.

## Validation

No test suite. Validate backend changes with `curl` against `localhost:3000`. Validate frontend by opening HTML files in a browser.
