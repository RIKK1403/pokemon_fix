-- Migrasi Pokemon TCG App ke Supabase
-- Project: https://myaluqqfvzqxhmpmhifw.supabase.co
-- Run in Supabase SQL Editor

-- 1. Create tables (matching existing MySQL schema)
CREATE TABLE IF NOT EXISTS users (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  username TEXT UNIQUE NOT NULL,
  fullname TEXT,
  email TEXT,
  whatsapp TEXT,
  password_hash TEXT NOT NULL,
  join_date TIMESTAMPTZ DEFAULT NOW(),
  last_login TIMESTAMPTZ,
  is_active BOOLEAN DEFAULT true
);

CREATE TABLE IF NOT EXISTS listings (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  type TEXT CHECK (type IN ('direct', 'auction')) NOT NULL,
  card_name TEXT NOT NULL,
  set_name TEXT,
  rarity TEXT,
  condition TEXT,
  price BIGINT,
  start_price BIGINT,
  min_bid_increment BIGINT DEFAULT 10000,
  buy_now_price BIGINT,
  end_time TIMESTAMPTZ,
  platform TEXT,
  link TEXT,
  image TEXT,
  description TEXT,
  seller_id UUID REFERENCES users(id) ON DELETE CASCADE,
  date_created TIMESTAMPTZ DEFAULT NOW(),
  views INTEGER DEFAULT 0,
  bids_count INTEGER DEFAULT 0,
  bids_json JSONB DEFAULT '[]'::jsonb
);

CREATE TABLE IF NOT EXISTS reports (
  id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  listing_id UUID REFERENCES listings(id) ON DELETE CASCADE,
  reason TEXT NOT NULL,
  reporter_username TEXT NOT NULL,
  date_reported TIMESTAMPTZ DEFAULT NOW()
);

-- 2. Indexes for performance
CREATE INDEX idx_listings_seller ON listings(seller_id);
CREATE INDEX idx_listings_type_time ON listings(type, end_time);
CREATE INDEX idx_listings_date ON listings(date_created DESC);
CREATE INDEX idx_users_username ON users(username);

-- 3. RLS (Row Level Security) - optional for auth
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE listings ENABLE ROW LEVEL SECURITY;
ALTER TABLE reports ENABLE ROW LEVEL SECURITY;

-- 4. Migrate existing data from MySQL (run this after export/import)
-- INSERT INTO users SELECT uuid(), username, fullname, email, whatsapp, password_hash, join_date, last_login, is_active FROM mysql_export.users;
-- INSERT INTO listings SELECT uuid(), type, card_name, set_name, rarity, condition, price, start_price, min_bid_increment, buy_now_price, end_time, platform, link, image, description, seller_id, date_created, views, bids_count, bids_json FROM mysql_export.listings;
-- INSERT INTO reports SELECT uuid(), listing_id, reason, reporter_username, date_reported FROM mysql_export.reports;

-- 5. Test
SELECT 'Supabase migration ready! Add your 2 users/listings manually or import' as status;

-- API will use: POSTGRES_URL from Supabase dashboard

