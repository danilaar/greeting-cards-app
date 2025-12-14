-- Создание базы данных (выполнить вручную)
-- CREATE DATABASE greeting_cards;

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица шаблонов
CREATE TABLE IF NOT EXISTS templates (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(20) NOT NULL CHECK (type IN ('card', 'invitation')),
    html_content TEXT NOT NULL,
    css_content TEXT,
    created_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Таблица созданных открыток/приглашений
CREATE TABLE IF NOT EXISTS user_cards (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    template_id INTEGER REFERENCES templates(id) ON DELETE SET NULL,
    title VARCHAR(200),
    html_content TEXT NOT NULL,
    css_content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Вставка тестового админа (пароль: admin123)
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@example.com', '$2y$12$TMY6V98ldnEFKTmE6fuONuxU0BLaSviuwHokJ/qjm5A2CQ6yhaLGO', 'admin')
ON CONFLICT (username) DO NOTHING;

-- Вставка тестового пользователя (пароль: user123)
INSERT INTO users (username, email, password, role) 
VALUES ('user', 'user@example.com', '$2y$12$Oy.bj3aMZkIXdbCgBDit5OUuofzxu0exI//k.7JdRIzA5sUpbzjzO', 'user')
ON CONFLICT (username) DO NOTHING;

-- Примеры шаблонов (вставляются только если их еще нет)
DO $$
DECLARE
    admin_id INTEGER;
BEGIN
    SELECT id INTO admin_id FROM users WHERE username = 'admin' LIMIT 1;
    
    IF NOT EXISTS (SELECT 1 FROM templates WHERE name = 'Поздравление с Днем Рождения') THEN
        INSERT INTO templates (name, type, html_content, css_content, created_by) 
        VALUES (
            'Поздравление с Днем Рождения',
            'card',
            '<div class="birthday-card"><h1>С Днем Рождения!</h1><p class="message">Желаю счастья, здоровья и успехов во всех начинаниях!</p><p class="signature">С уважением</p></div>',
            '.birthday-card { text-align: center; padding: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; } .birthday-card h1 { font-size: 2.5em; margin-bottom: 20px; } .birthday-card .message { font-size: 1.3em; margin: 30px 0; } .birthday-card .signature { font-size: 1.1em; margin-top: 40px; }',
            admin_id
        );
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM templates WHERE name = 'Приглашение на праздник') THEN
        INSERT INTO templates (name, type, html_content, css_content, created_by) 
        VALUES (
            'Приглашение на праздник',
            'invitation',
            '<div class="invitation-card"><h1>Приглашение</h1><p class="event">Приглашаем вас на праздник!</p><p class="date">Дата: 25 декабря 2024</p><p class="time">Время: 18:00</p><p class="place">Место: Ресторан "Восток"</p><p class="rsvp">Будем рады видеть вас!</p></div>',
            '.invitation-card { text-align: center; padding: 50px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); } .invitation-card h1 { font-size: 2.8em; margin-bottom: 30px; text-transform: uppercase; } .invitation-card .event { font-size: 1.5em; margin: 25px 0; font-weight: bold; } .invitation-card .date, .invitation-card .time, .invitation-card .place { font-size: 1.2em; margin: 15px 0; } .invitation-card .rsvp { font-size: 1.3em; margin-top: 30px; font-style: italic; }',
            admin_id
        );
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM templates WHERE name = 'Новогодняя открытка') THEN
        INSERT INTO templates (name, type, html_content, css_content, created_by) 
        VALUES (
            'Новогодняя открытка',
            'card',
            '<div class="newyear-card"><h1>С Новым Годом!</h1><p class="wish">Пусть новый год принесет много радости, счастья и исполнения всех желаний!</p><p class="year">2025</p></div>',
            '.newyear-card { text-align: center; padding: 60px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; border-radius: 20px; position: relative; } .newyear-card::before { content: "❄"; position: absolute; top: 20px; left: 20px; font-size: 2em; opacity: 0.5; } .newyear-card::after { content: "❄"; position: absolute; top: 20px; right: 20px; font-size: 2em; opacity: 0.5; } .newyear-card h1 { font-size: 3em; margin-bottom: 30px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); } .newyear-card .wish { font-size: 1.4em; margin: 40px 0; line-height: 1.6; } .newyear-card .year { font-size: 4em; font-weight: bold; margin-top: 30px; color: #ffd700; }',
            admin_id
        );
    END IF;
END $$;

