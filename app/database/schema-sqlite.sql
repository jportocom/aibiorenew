-- MedePeso — Schema SQLite
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    email           TEXT UNIQUE NOT NULL,
    password        TEXT NOT NULL,
    nome            TEXT,
    sexo            TEXT CHECK(sexo IN ('M','F')),
    idade           INTEGER,
    altura          REAL,
    peso            REAL,
    atividade       TEXT CHECK(atividade IN ('sedentario','moderado','ativo')),
    tipo_dieta      TEXT CHECK(tipo_dieta IN ('omnivoro','vegetariano','vegan','carnivoro')),
    come_ovos       INTEGER DEFAULT 1,
    perfil_completo INTEGER DEFAULT 0,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pesos (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    data       DATE NOT NULL,
    peso       REAL NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, data)
);

CREATE TABLE IF NOT EXISTS objetivos (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id        INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    duracao_dias   INTEGER NOT NULL CHECK(duracao_dias IN (30,90,180)),
    data_inicio    DATE NOT NULL,
    data_fim       DATE NOT NULL,
    peso_inicio    REAL NOT NULL,
    peso_alvo_min  REAL NOT NULL,
    peso_alvo_max  REAL NOT NULL,
    ativo          INTEGER DEFAULT 1,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS metricas (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    data       DATE NOT NULL,
    imc        REAL,
    tmb        REAL,
    tdee       REAL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, data)
);

CREATE TABLE IF NOT EXISTS refeicoes (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    data       DATE NOT NULL,
    tipo       TEXT CHECK(tipo IN ('pequeno_almoco','almoco','jantar')),
    calorias   REAL,
    proteina   REAL,
    hidratos   REAL,
    gordura    REAL,
    fibra      REAL,
    descricao  TEXT,
    foto_url   TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS atividades (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    data        DATE NOT NULL,
    tipo        TEXT,
    duracao_min INTEGER,
    calorias    REAL,
    passos      INTEGER,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cardapios (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    nome        TEXT NOT NULL,
    tipo        TEXT CHECK(tipo IN ('pequeno_almoco','almoco','jantar')),
    calorias    REAL NOT NULL,
    proteina    REAL,
    hidratos    REAL,
    gordura     REAL,
    fibra       REAL,
    ingredientes TEXT,
    tipo_dieta  TEXT DEFAULT 'todos',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS planos_diarios (
    id                       INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id                  INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    data                     DATE NOT NULL,
    tmb                      REAL,
    calorias_pequeno_almoco  REAL,
    calorias_almoco          REAL,
    calorias_jantar          REAL,
    caminhada_manha_min      INTEGER DEFAULT 15,
    caminhada_tarde_min      INTEGER DEFAULT 15,
    calorias_caminhada       REAL,
    calorias_total_actividade REAL,
    deficit_previsto         REAL,
    created_at               DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, data)
);

CREATE TABLE IF NOT EXISTS avaliacoes_diarias (
    id                    INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id               INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    data                  DATE NOT NULL,
    calorias_ingeridas    REAL DEFAULT 0,
    calorias_gastas_exercicio REAL DEFAULT 0,
    tmb                   REAL,
    saldo_calorico        REAL,
    estado                TEXT CHECK(estado IN ('deficit','equilibrio','excesso')),
    objetivo_cumprido     INTEGER,
    created_at            DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, data)
);

-- Cardápios base
INSERT OR IGNORE INTO cardapios(nome,tipo,calorias,proteina,hidratos,gordura,fibra,ingredientes,tipo_dieta) VALUES
('Batido de proteína de soja com abacate','pequeno_almoco',380,32,28,14,8,'250ml leite soja magro, 30g proteína soja isolada, 50g abacate, 1 banana pequena','todos'),
('Iogurte grego com fruta e granola','pequeno_almoco',420,18,52,12,6,'200g iogurte grego natural, 150g fruta fresca, 30g granola sem açúcar','todos'),
('Aveia com leite vegetal e frutos secos','pequeno_almoco',460,15,58,16,9,'80g flocos aveia, 250ml leite soja, 20g nozes, 1 maçã','todos'),
('Sopa de legumes com leguminosas','almoco',280,14,38,6,12,'Cenoura, cebola, alho, feijão branco, espinafres, azeite','todos'),
('Salada mediterrânea com tofu','almoco',340,22,25,16,8,'200g tofu grelhado, alface, tomate, pepino, azeitonas, azeite limão','vegan'),
('Frango grelhado com legumes salteados','almoco',380,42,18,12,7,'180g frango grelhado, brócolo, cenoura, azeite, alho','omnivoro'),
('Batido de jantar nutritivo','jantar',320,28,22,12,5,'250ml leite soja magro, 30g proteína soja isolada, 50g abacate, 5g mel','todos'),
('Iogurte vegetal com fruta','jantar',280,12,35,8,4,'200g iogurte vegetal natural, 100g fruta, 20g amêndoas','todos');
