-- Extensões
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Utilizadores
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    nome TEXT,
    sexo TEXT CHECK(sexo IN ('M','F')),
    idade INT,
    altura FLOAT,
    peso FLOAT,
    atividade TEXT CHECK(atividade IN ('sedentario','moderado','ativo')),
    tipo_dieta TEXT CHECK(tipo_dieta IN ('omnivoro','vegetariano','vegan','carnivoro')),
    come_ovos BOOLEAN DEFAULT true,
    perfil_completo BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Histórico de peso
CREATE TABLE IF NOT EXISTS pesos (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    data DATE NOT NULL,
    peso FLOAT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Objectivos
CREATE TABLE IF NOT EXISTS objetivos (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    duracao_dias INT NOT NULL CHECK(duracao_dias IN (30,90,180)),
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    peso_inicio FLOAT NOT NULL,
    peso_alvo_min FLOAT NOT NULL,
    peso_alvo_max FLOAT NOT NULL,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Métricas calculadas (histórico)
CREATE TABLE IF NOT EXISTS metricas (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    data DATE NOT NULL,
    imc FLOAT,
    tmb FLOAT,
    tdee FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Refeições
CREATE TABLE IF NOT EXISTS refeicoes (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    data DATE NOT NULL,
    tipo TEXT CHECK(tipo IN ('pequeno_almoco','almoco','jantar')),
    calorias FLOAT,
    proteina FLOAT,
    hidratos FLOAT,
    gordura FLOAT,
    fibra FLOAT,
    descricao TEXT,
    foto_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Actividades físicas
CREATE TABLE IF NOT EXISTS atividades (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    data DATE NOT NULL,
    tipo TEXT,
    duracao_min INT,
    calorias FLOAT,
    passos INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cardápios / sugestões de refeições
CREATE TABLE IF NOT EXISTS cardapios (
    id SERIAL PRIMARY KEY,
    nome TEXT NOT NULL,
    tipo TEXT CHECK(tipo IN ('pequeno_almoco','almoco','jantar')),
    calorias FLOAT NOT NULL,
    proteina FLOAT,
    hidratos FLOAT,
    gordura FLOAT,
    fibra FLOAT,
    ingredientes TEXT,
    tipo_dieta TEXT DEFAULT 'todos',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Planos diários
CREATE TABLE IF NOT EXISTS planos_diarios (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    data DATE NOT NULL,
    tmb FLOAT,
    calorias_pequeno_almoco FLOAT,
    calorias_almoco FLOAT,
    calorias_jantar FLOAT,
    caminhada_manha_min INT DEFAULT 15,
    caminhada_tarde_min INT DEFAULT 15,
    calorias_caminhada FLOAT,
    calorias_total_actividade FLOAT,
    deficit_previsto FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Avaliações diárias
CREATE TABLE IF NOT EXISTS avaliacoes_diarias (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    data DATE NOT NULL,
    calorias_ingeridas FLOAT DEFAULT 0,
    calorias_gastas_exercicio FLOAT DEFAULT 0,
    tmb FLOAT,
    saldo_calorico FLOAT,
    estado TEXT CHECK(estado IN ('deficit','equilibrio','excesso')),
    objetivo_cumprido BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir cardápios base
INSERT INTO cardapios (nome, tipo, calorias, proteina, hidratos, gordura, fibra, ingredientes, tipo_dieta) VALUES
('Batido de proteína de soja com abacate', 'pequeno_almoco', 380, 32, 28, 14, 8, '250ml leite soja magro, 30g proteína soja isolada, 50g abacate, 1 banana pequena', 'todos'),
('Iogurte grego com fruta e granola', 'pequeno_almoco', 420, 18, 52, 12, 6, '200g iogurte grego natural, 150g fruta fresca, 30g granola sem açúcar', 'todos'),
('Aveia com leite vegetal e frutos secos', 'pequeno_almoco', 460, 15, 58, 16, 9, '80g flocos aveia, 250ml leite soja, 20g nozes, 1 maçã', 'todos'),
('Sopa de legumes com leguminosas', 'almoco', 280, 14, 38, 6, 12, 'Cenoura, cebola, alho, feijão branco, espinafres, azeite', 'todos'),
('Salada mediterrânea com tofu', 'almoco', 340, 22, 25, 16, 8, '200g tofu grelhado, alface, tomate, pepino, azeitonas, azeite limão', 'vegan'),
('Frango grelhado com legumes salteados', 'almoco', 380, 42, 18, 12, 7, '180g frango grelhado, brócolo, cenoura, azeite, alho', 'omnivoro'),
('Batido de jantar nutritivo', 'jantar', 320, 28, 22, 12, 5, '250ml leite soja magro, 30g proteína soja isolada, 50g abacate, 5g mel', 'todos'),
('Iogurte vegetal com fruta', 'jantar', 280, 12, 35, 8, 4, '200g iogurte vegetal natural, 100g fruta, 20g amêndoas', 'todos');
