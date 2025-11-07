CREATE DATABASE saep_db;
USE saep_db;


-- Tabela de Usuários (Funcionários)
CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

-- Tabela de Livros
CREATE TABLE livro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    titulo VARCHAR(150) NOT NULL,
    edicao INT NOT NULL,
    genero VARCHAR(50) NOT NULL,
    estoque_minimo INT NOT NULL,
    estoque_atual INT NOT NULL
);

-- Tabela de Movimentações (Entradas e Saídas)
CREATE TABLE movimentacao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_livro INT,
    tipo ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    data DATETIME NOT NULL,
    id_usuario INT,
    FOREIGN KEY (id_livro) REFERENCES livro(id),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id)
);

-- População das tabelas

-- Usuários
INSERT INTO usuario (nome) VALUES
('João Silva'),
('Maria Souza'),
('Carlos Lima');

-- Livros
INSERT INTO livro (isbn, titulo, edicao, genero, estoque_minimo, estoque_atual) VALUES
('978-85-01-00001-1', 'Duna', 3, 'Ficção', 5, 10),
('978-85-01-00002-2', 'A metamorfose', 1, 'Ficção', 3, 7),
('978-85-01-00003-3', 'Drácula', 2, 'Terror', 4, 12);

-- Movimentações
INSERT INTO movimentacao (id_livro, tipo, quantidade, data, id_usuario) VALUES
(1, 'entrada', 5, '2025-11-01 10:00:00', 1),
(2, 'saida', 2, '2025-11-02 14:30:00', 2),
(3, 'entrada', 7, '2025-11-03 09:15:00', 3);