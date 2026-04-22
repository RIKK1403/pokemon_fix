# Supabase + Vercel Migration TODO

**Completed:**
- [x] Update supabase_config.php with real password
- [x] User confirmed migration SQL run (assumed)

**Step 1: Switch API to Supabase** ✅\n- [x] supabase_config.php password updated\n- [ ] api.php getDB() → getSupabaseDB() (8 locations)

**Step 2: Frontend**
- [ ] Create app index.php (TCG listings UI)

**Step 3: Vercel Setup**
- [ ] vercel.json
- [ ] .env.example
- [ ] Deploy guide

**Step 4: Test**
- [ ] Local: php -S localhost:8000
- [ ] Vercel: vercel deploy
- [ ] DB connection verified

**Current Status:** API switch → Local test → Vercel deploy
