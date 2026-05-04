-- MedePeso — Schema MySQL (base de dados: metabolic)
-- Tabelas usadas pelo backend Node.js, independentes das tabelas PHP existentes.

CREATE TABLE IF NOT EXISTS mp_users (
    id              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(191) UNIQUE NOT NULL,
    password        VARCHAR(255) NOT NULL,
    nome            VARCHAR(100),
    sexo            CHAR(1),
    idade           INT,
    altura          FLOAT,
    peso            FLOAT,
    atividade       VARCHAR(20),
    tipo_dieta      VARCHAR(20),
    come_ovos       TINYINT(1) DEFAULT 1,
    perfil_completo TINYINT(1) DEFAULT 0,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mp_pesos (
    id         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    data       DATE NOT NULL,
    peso       FLOAT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_data (user_id, data),
    FOREIGN KEY (user_id) REFERENCES mp_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mp_objetivos (
    id             INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    duracao_dias   INT NOT NULL,
    data_inicio    DATE NOT NULL,
    data_fim       DATE NOT NULL,
    peso_inicio    FLOAT NOT NULL,
    peso_alvo_min  FLOAT NOT NULL,
    peso_alvo_max  FLOAT NOT NULL,
    ativo          TINYINT(1) DEFAULT 1,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES mp_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mp_metricas (
    id         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    data       DATE NOT NULL,
    imc        FLOAT,
    tmb        FLOAT,
    tdee       FLOAT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_data (user_id, data),
    FOREIGN KEY (user_id) REFERENCES mp_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mp_refeicoes (
    id         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    data       DATE NOT NULL,
    tipo       VARCHAR(20),
    calorias   FLOAT,
    proteina   FLOAT,
    hidratos   FLOAT,
    gordura    FLOAT,
    fibra      FLOAT,
    descricao  TEXT,
    foto_url   TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES mp_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mp_atividades (
    id          INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    data        DATE NOT NULL,
    tipo        VARCHAR(50),
    duracao_min INT,
    calorias    FLOAT,
    passos      INT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES mp_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mp_cardapios (
    id           INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome         VARCHAR(255) NOT NULL,
    tipo         VARCHAR(20),
    calorias     FLOAT NOT NULL,
    proteina     FLOAT,
    hidratos     FLOAT,
    gordura      FLOAT,
    fibra        FLOAT,
    ingredientes TEXT,
    tipo_dieta   VARCHAR(20) DEFAULT 'todos',
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mp_planos_diarios (
    id                        INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id                   INT NOT NULL,
    data                      DATE NOT NULL,
    tmb                       FLOAT,
    calorias_pequeno_almoco   FLOAT,
    calorias_almoco           FLOAT,
    calorias_jantar           FLOAT,
    caminhada_manha_min       INT DEFAULT 15,
    caminhada_tarde_min       INT DEFAULT 15,
    calorias_caminhada        FLOAT,
    calorias_total_actividade FLOAT,
    deficit_previsto          FLOAT,
    created_at                DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_data (user_id, data),
    FOREIGN KEY (user_id) REFERENCES mp_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mp_avaliacoes_diarias (
    id                        INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id                   INT NOT NULL,
    data                      DATE NOT NULL,
    calorias_ingeridas        FLOAT DEFAULT 0,
    calorias_gastas_exercicio FLOAT DEFAULT 0,
    tmb                       FLOAT,
    saldo_calorico            FLOAT,
    estado                    VARCHAR(20),
    objetivo_cumprido         TINYINT(1),
    created_at                DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_data (user_id, data),
    FOREIGN KEY (user_id) REFERENCES mp_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cardápios base
INSERT IGNORE INTO mp_cardapios(nome,tipo,calorias,proteina,hidratos,gordura,fibra,ingredientes,tipo_dieta) VALUES
('Batido de proteína de soja com abacate','pequeno_almoco',380,32,28,14,8,'250ml leite soja magro, 30g proteína soja isolada, 50g abacate, 1 banana pequena','todos'),
('Iogurte grego com fruta e granola','pequeno_almoco',420,18,52,12,6,'200g iogurte grego natural, 150g fruta fresca, 30g granola sem açúcar','todos'),
('Aveia com leite vegetal e frutos secos','pequeno_almoco',460,15,58,16,9,'80g flocos aveia, 250ml leite soja, 20g nozes, 1 maçã','todos'),
('Sopa de legumes com leguminosas','almoco',280,14,38,6,12,'Cenoura, cebola, alho, feijão branco, espinafres, azeite','todos'),
('Salada mediterrânea com tofu','almoco',340,22,25,16,8,'200g tofu grelhado, alface, tomate, pepino, azeitonas, azeite limão','vegan'),
('Frango grelhado com legumes salteados','almoco',380,42,18,12,7,'180g frango grelhado, brócolo, cenoura, azeite, alho','omnivoro'),
('Batido de jantar nutritivo','jantar',320,28,22,12,5,'250ml leite soja magro, 30g proteína soja isolada, 50g abacate, 5g mel','todos'),
('Iogurte vegetal com fruta','jantar',280,12,35,8,4,'200g iogurte vegetal natural, 100g fruta, 20g amêndoas','todos');
