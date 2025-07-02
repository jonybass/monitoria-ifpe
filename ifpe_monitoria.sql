-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS ifpe_monitoria;
USE ifpe_monitoria;

-- Tabela para alunos
CREATE TABLE IF NOT EXISTS alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela para monitores
CREATE TABLE IF NOT EXISTS monitores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela para professores (administradores)
CREATE TABLE IF NOT EXISTS professores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);


-- Tabela de matérias que o monitor irá ministrar
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_monitor INT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    turno VARCHAR(20),
    local VARCHAR(100),
    FOREIGN KEY (id_monitor) REFERENCES monitores(id)
);

-- Dias e horários das monitorias
CREATE TABLE IF NOT EXISTS horarios_monitoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    dia_semana VARCHAR(20),
    horario_inicio TIME,
    horario_fim TIME,
    FOREIGN KEY (id_materia) REFERENCES materias(id)
);

-- Fotos de comprovação
CREATE TABLE IF NOT EXISTS fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    id_monitor INT NOT NULL,
    caminho_foto VARCHAR(255) NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_materia) REFERENCES materias(id),
    FOREIGN KEY (id_monitor) REFERENCES monitores(id)
);


-- Arquivos de apoio
CREATE TABLE IF NOT EXISTS arquivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    caminho_arquivo VARCHAR(255),
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_materia) REFERENCES materias(id)
);
ALTER TABLE arquivos
ADD COLUMN id_monitor INT NOT NULL AFTER id_materia,
ADD COLUMN nome_arquivo VARCHAR(255) AFTER caminho_arquivo,
ADD FOREIGN KEY (id_monitor) REFERENCES monitores(id);

-- Alunos cadastrados em cada monitoria
CREATE TABLE IF NOT EXISTS alunos_monitoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    nome VARCHAR(100),
    email VARCHAR(100),
    FOREIGN KEY (id_materia) REFERENCES materias(id)
);

-- Frequência mensal para relatório
CREATE TABLE IF NOT EXISTS frequencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    data DATE,
    horario_inicio TIME,
    horario_fim TIME,
    atividades TEXT,
    FOREIGN KEY (id_materia) REFERENCES materias(id)
);

horarios_monitoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    dia_semana VARCHAR(20),
    horario_inicio TIME,
    horario_fim TIME,
    FOREIGN KEY (id_materia) REFERENCES materias(id)
)

-- Matérias e monitorias
CREATE TABLE materias_monitoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materia VARCHAR(100),
    dia VARCHAR(20),
    horario_inicio TIME,
    horario_fim TIME,
    local VARCHAR(100),
    monitor_id INT
);

-- Confirmações
CREATE TABLE confirmacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT,
    monitoria_id INT,
    confirmado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE alunos_materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_aluno INT NOT NULL,
    id_materia INT NOT NULL,
    UNIQUE KEY (id_aluno, id_materia),
    FOREIGN KEY (id_aluno) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materias(id) ON DELETE CASCADE
);

ALTER TABLE materias_monitoria ADD COLUMN ativo BOOLEAN DEFAULT 1;

SELECT * FROM monitores WHERE ativo = 1;

ALTER TABLE monitores ADD COLUMN ativo TINYINT(1) DEFAULT 1;

CREATE TABLE frequencias_alunos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_aluno INT NOT NULL,
  id_materia INT NOT NULL,
  data DATE NOT NULL,
  presente BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (id_aluno) REFERENCES alunos(id),
  FOREIGN KEY (id_materia) REFERENCES materias(id)
);

CREATE TABLE materiais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    tipo VARCHAR(100),
    caminho_arquivo VARCHAR(255),
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    ativo TINYINT(1) DEFAULT 1
);





-- corrijigo

-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS ifpe_monitoria;
USE ifpe_monitoria;

-- Tabela para alunos
CREATE TABLE IF NOT EXISTS alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1
);

-- Tabela para monitores
CREATE TABLE IF NOT EXISTS monitores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1
);

-- Tabela para professores (administradores)
CREATE TABLE IF NOT EXISTS professores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    ativo TINYINT(1) DEFAULT 1
);

-- Tabela de matérias que o monitor irá ministrar
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_monitor INT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    turno VARCHAR(20),
    local VARCHAR(100),
    FOREIGN KEY (id_monitor) REFERENCES monitores(id)
);

-- Dias e horários das monitorias
CREATE TABLE IF NOT EXISTS horarios_monitoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    dia_semana VARCHAR(20),
    horario_inicio TIME,
    horario_fim TIME,
    FOREIGN KEY (id_materia) REFERENCES materias(id)
);

-- Fotos de comprovação
CREATE TABLE IF NOT EXISTS fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    id_monitor INT NOT NULL,
    caminho_foto VARCHAR(255) NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_materia) REFERENCES materias(id),
    FOREIGN KEY (id_monitor) REFERENCES monitores(id)
);

-- Arquivos de apoio
CREATE TABLE IF NOT EXISTS arquivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    caminho_arquivo VARCHAR(255),
    nome_arquivo VARCHAR(255),
    id_monitor INT NOT NULL,
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_materia) REFERENCES materias(id),
    FOREIGN KEY (id_monitor) REFERENCES monitores(id)
);

-- Alunos cadastrados em cada monitoria
CREATE TABLE IF NOT EXISTS alunos_monitoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    nome VARCHAR(100),
    email VARCHAR(100),
    FOREIGN KEY (id_materia) REFERENCES materias(id)
);

-- Frequência mensal para relatório
CREATE TABLE IF NOT EXISTS frequencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT NOT NULL,
    data DATE,
    horario_inicio TIME,
    horario_fim TIME,
    atividades TEXT,
    FOREIGN KEY (id_materia) REFERENCES materias(id)
);

-- Matérias e monitorias
CREATE TABLE IF NOT EXISTS materias_monitoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materia VARCHAR(100),
    dia VARCHAR(20),
    horario_inicio TIME,
    horario_fim TIME,
    local VARCHAR(100),
    monitor_id INT,
    ativo BOOLEAN DEFAULT 1
);

-- Confirmações
CREATE TABLE IF NOT EXISTS confirmacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT,
    monitoria_id INT,
    confirmado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Relação alunos-materias
CREATE TABLE IF NOT EXISTS alunos_materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_aluno INT NOT NULL,
    id_materia INT NOT NULL,
    UNIQUE KEY (id_aluno, id_materia),
    FOREIGN KEY (id_aluno) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materias(id) ON DELETE CASCADE
);

-- Frequência dos alunos nas monitorias
CREATE TABLE IF NOT EXISTS frequencias_alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_aluno INT NOT NULL,
    id_materia INT NOT NULL,
    data DATE NOT NULL,
    presente BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_aluno) REFERENCES alunos(id),
    FOREIGN KEY (id_materia) REFERENCES materias(id)
);

-- Materiais de apoio
CREATE TABLE IF NOT EXISTS materiais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    tipo VARCHAR(100),
    caminho_arquivo VARCHAR(255),
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    ativo TINYINT(1) DEFAULT 1
);









