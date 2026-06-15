CREATE DATABASE IF NOT EXISTS unidades_conservacao
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE unidades_conservacao;

CREATE TABLE IF NOT EXISTS instituicao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS municipio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    estado CHAR(2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS unidade_conservacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    data_criacao DATE DEFAULT NULL,
    descricao TEXT DEFAULT NULL,
    imagem TEXT DEFAULT NULL,
    instituicao_id INT DEFAULT NULL,
    CONSTRAINT fk_unidade_instituicao
        FOREIGN KEY (instituicao_id)
        REFERENCES instituicao(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS unidade_municipio (
    unidade_id INT NOT NULL,
    municipio_id INT NOT NULL,
    PRIMARY KEY (unidade_id, municipio_id),
    CONSTRAINT fk_unidade_municipio_unidade
        FOREIGN KEY (unidade_id)
        REFERENCES unidade_conservacao(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_unidade_municipio_municipio
        FOREIGN KEY (municipio_id)
        REFERENCES municipio(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comunicacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT NOT NULL,
    data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    email VARCHAR(150) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 0,
    unidade_id INT DEFAULT NULL,
    CONSTRAINT fk_comunicacao_unidade
        FOREIGN KEY (unidade_id)
        REFERENCES unidade_conservacao(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO instituicao (nome, email)
SELECT * FROM (
    SELECT 'IMA - Instituto do Meio Ambiente', 'contato@ima.sc.gov.br'
    UNION ALL
    SELECT 'ICMBio', 'contato@icmbio.gov.br'
) AS dados
WHERE NOT EXISTS (
    SELECT 1 FROM instituicao
);

INSERT INTO municipio (nome, estado)
SELECT * FROM (
    SELECT 'Florianopolis', 'SC'
    UNION ALL
    SELECT 'Bombinhas', 'SC'
    UNION ALL
    SELECT 'Palhoca', 'SC'
    UNION ALL
    SELECT 'Imbituba', 'SC'
) AS dados
WHERE NOT EXISTS (
    SELECT 1 FROM municipio
);

INSERT INTO unidade_conservacao (nome, data_criacao, descricao, imagem, instituicao_id)
SELECT * FROM (
    SELECT
        'Parque Estadual da Serra do Tabuleiro',
        '1975-11-01',
        'Unidade de conservacao localizada em Santa Catarina com rica biodiversidade.',
        'https://images.unsplash.com/photo-1506744038136-46273834b3fb',
        1
    UNION ALL
    SELECT
        'Reserva Biologica Marinha do Arvoredo',
        '1990-03-12',
        'Area de preservacao marinha localizada no litoral catarinense.',
        'https://images.unsplash.com/photo-1507525428034-b723cf961d3e',
        2
    UNION ALL
    SELECT
        'Parque Estadual do Rio Vermelho',
        '2007-05-24',
        'Area de preservacao ambiental com vegetacao nativa.',
        'https://images.unsplash.com/photo-1441974231531-c6227db76b6e',
        1
    UNION ALL
    SELECT
        'APA da Baleia Franca',
        '2000-09-14',
        'Area de protecao ambiental para preservacao da baleia franca.',
        'https://hsproweb.com.br/baleiafranca/wp-content/uploads/2024/02/logo-completo2.png',
        2
) AS dados
WHERE NOT EXISTS (
    SELECT 1 FROM unidade_conservacao
);

INSERT INTO unidade_municipio (unidade_id, municipio_id)
SELECT * FROM (
    SELECT 1, 3
    UNION ALL
    SELECT 2, 2
    UNION ALL
    SELECT 3, 1
    UNION ALL
    SELECT 4, 4
) AS dados
WHERE NOT EXISTS (
    SELECT 1 FROM unidade_municipio
);
