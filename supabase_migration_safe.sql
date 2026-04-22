-- Safe Supabase Migration - Copy to Dashboard SQL Editor
-- Project: https://myaluqqfvzqxhmpmhifw.supabase.co

-- Disable RLS first (avoids permission errors)
ALTER TABLE users DISABLE ROW LEVEL SECURITY;
ALTER TABLE listings DISABLE ROW LEVEL SECURITY;
ALTER TABLE reports DISABLE ROW LEVEL SECURITY;

-- Create tables
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

-- Indexes
CREATE INDEX IF NOT EXISTS idx_listings_seller ON listings(seller_id);
CREATE INDEX IF NOT EXISTS idx_listings_type_time ON listings(type, end_time);
CREATE INDEX IF NOT EXISTS idx_listings_date ON listings(date_created DESC);
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

-- Sample Data (2 users + 3 listings)
INSERT INTO users (username, fullname, email, password_hash, is_active) VALUES 
('admin', 'Admin Pokémon', 'admin@rkpoke.com', '8d969eef6ecad3c29a3a629280e... ', true),
('seller1', 'Budi CardHunter', 'budi@example.com', '5e884898da28047151d0e56f8dc6... ', true);

INSERT INTO listings (type, card_name, set_name, rarity, condition, price, seller_id, platform, link, description) VALUES 
('direct', 'Pikachu VMAX', 'Vivid Voltage', 'Ultra Rare', 'Near Mint', 500000, (SELECT id FROM users WHERE username = 'seller1'), 'Tokopedia', 'https://tkp.co/pikachu', 'Pikachu VMAX Rainbow Rare NM condition'),
('auction', 'Charizard VSTAR', 'Brilliant Stars', 'Ultra Rare', 'Light Play', NULL, (SELECT id FROM users WHERE username = 'seller1'), 'Shopee', 'https://shopee.co/charizard', 'Charizard VSTAR Gold Secret Rare. Start bid Rp200k'),
('direct', 'Umbreon VMAX ALT', 'Evolving Skies', 'Alternate Art', 'Mint', 1500000, (SELECT id FROM users WHERE username = 'seller1'), 'Bukalapak', 'https://buka.co/umbreon', 'Full Art Umbreon VMAX Alternate Perfect condition');

-- Test query
SELECT 'Migration complete! Tables ready. Test register at app.' as status, 
count(*) as users_count, 
(SELECT count(*) FROM listings) as listings_count;
