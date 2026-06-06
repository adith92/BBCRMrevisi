#!/bin/bash
# ============================================================
# DEPLOY SCRIPT — Golden Bird CRM v7.5 Kanban Pipeline
# Jalankan dari dalam folder: golden-bird-crm/
# ============================================================

set -e  # stop on any error

echo "📦 [1/6] Install npm dependencies..."
npm install

echo "🔨 [2/6] Build Vite assets..."
npm run build

echo "🧪 [3/6] Run Pest tests..."
./vendor/bin/pest --stop-on-failure

echo "✅ [4/6] All tests passed. Staging git commit..."
git add -A
git status

echo ""
echo "📝 [5/6] Committing..."
git commit -m "feat(kanban): Kanban Pipeline v7.5

- Vite + Tailwind compiled (buang CDN)
- Layout refactor: app.blade.php 642 → 66 baris
- Sidebar + topbar + flash sebagai Blade components
- Kanban board full drag-drop via SortableJS
- Inline edit modal (title, nilai, close date, pax, notes)
- 360° view modal (4 tab: Info, Aktivitas, Approval, Linked)
- moveStage endpoint (PATCH JSON) dengan PipelineService validation
- quickUpdate endpoint (PATCH JSON) untuk inline edit
- view360 endpoint (GET JSON) dengan eager-loaded relations
- Activity log otomatis setiap drag-drop
- Lost reason dialog saat drag ke Kalah
- Toast notification real-time
- Role-scoped (sales hanya lihat deal sendiri)
- Responsive mobile-first
- 20 test cases di KanbanTest.php
- nixpacks.toml + render.yaml updated untuk npm build"

echo ""
echo "🚀 [6/6] Push ke remote..."
git push

echo ""
echo "✅ Done! Tunggu Railway/Render auto-deploy selesai."
echo "   Monitor di: https://dashboard.render.com atau https://railway.app"
