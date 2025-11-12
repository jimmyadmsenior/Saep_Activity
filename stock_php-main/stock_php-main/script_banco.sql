-- ============================================================================
-- ENTREGA 03 - SCRIPT DE CRIACAO E POPULACAO DO BANCO DE DADOS
-- Sistema de Gestao de Estoque de Biblioteca
-- Banco de Dados: biblioteca_db
-- ============================================================================

-- Remover banco se existir e criar novo
DROP DATABASE IF EXISTS biblioteca_db;
CREATE DATABASE biblioteca_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE biblioteca_db;

-- ============================================================================
-- CRIACAO DAS TABELAS
-- ============================================================================

-- Tabela de Usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Livros
CREATE TABLE livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(150) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    editora VARCHAR(100),
    ano_publicacao INT,
    categoria VARCHAR(50),
    estoque_atual INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 5,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Movimentacoes
CREATE TABLE movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livro_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao DATE NOT NULL,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacao TEXT,
    FOREIGN KEY (livro_id) REFERENCES livros(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- POPULACAO DAS TABELAS (MINIMO 3 REGISTROS CADA)
-- ============================================================================

-- Inserir Usuarios (senha: 123456)
INSERT INTO usuarios (nome, usuario, senha) VALUES
('Ana Silva Santos', 'ana.silva', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Carlos Eduardo Lima', 'carlos.lima', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Maria Fernanda Costa', 'maria.costa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserir Livros
INSERT INTO livros (titulo, autor, isbn, editora, ano_publicacao, categoria, estoque_atual, estoque_minimo) VALUES
('Dom Casmurro', 'Machado de Assis', '978-8535908774', 'Companhia das Letras', 1899, 'Literatura Brasileira', 12, 5),
('1984', 'George Orwell', '978-8535914849', 'Companhia das Letras', 1949, 'Ficcao Cientifica', 8, 5),
('O Pequeno Principe', 'Antoine de Saint-Exupery', '978-8522008731', 'Agir', 1943, 'Infantil', 15, 10),
('Clean Code', 'Robert C. Martin', '978-8576082675', 'Alta Books', 2008, 'Tecnologia', 4, 5),
('Memorias Postumas de Bras Cubas', 'Machado de Assis', '978-8535911664', 'Companhia das Letras', 1881, 'Literatura Brasileira', 7, 5),
('Harry Potter e a Pedra Filosofal', 'J.K. Rowling', '978-8532530787', 'Rocco', 1997, 'Fantasia', 20, 10),
('O Senhor dos Aneis', 'J.R.R. Tolkien', '978-8533613379', 'Martins Fontes', 1954, 'Fantasia', 10, 8),
('Algoritmos: Teoria e Pratica', 'Thomas H. Cormen', '978-8535236996', 'Campus', 2012, 'Tecnologia', 3, 5),
('A Revolucao dos Bichos', 'George Orwell', '978-8535909555', 'Companhia das Letras', 1945, 'Ficcao', 6, 5),
('O Codigo Da Vinci', 'Dan Brown', '978-8580414974', 'Arqueiro', 2003, 'Suspense', 9, 5);

-- Inserir Movimentacoes
INSERT INTO movimentacoes (livro_id, usuario_id, tipo, quantidade, data_movimentacao, observacao) VALUES
(1, 1, 'entrada', 10, '2024-01-15', 'Compra inicial de estoque'),
(1, 2, 'saida', 3, '2024-01-20', 'Emprestimo para alunos'),
(1, 1, 'entrada', 5, '2024-02-10', 'Reposicao de estoque'),
(2, 1, 'entrada', 10, '2024-01-15', 'Compra inicial de estoque'),
(2, 3, 'saida', 2, '2024-02-05', 'Emprestimo para professores'),
(3, 2, 'entrada', 15, '2024-01-10', 'Doacao da comunidade'),
(4, 1, 'entrada', 8, '2024-01-18', 'Compra para secao tecnica'),
(4, 2, 'saida', 4, '2024-02-12', 'Emprestimo para curso de programacao'),
(5, 3, 'entrada', 7, '2024-01-20', 'Compra inicial de estoque'),
(6, 1, 'entrada', 20, '2024-01-12', 'Alta demanda - estoque ampliado'),
(7, 2, 'entrada', 10, '2024-01-15', 'Compra inicial de estoque'),
(8, 1, 'entrada', 5, '2024-01-22', 'Compra para secao tecnica'),
(8, 3, 'saida', 2, '2024-02-08', 'Emprestimo para alunos'),
(9, 2, 'entrada', 8, '2024-01-16', 'Compra inicial de estoque'),
(9, 1, 'saida', 2, '2024-02-15', 'Emprestimo'),
(10, 3, 'entrada', 12, '2024-01-14', 'Compra inicial de estoque'),
(10, 2, 'saida', 3, '2024-02-11', 'Emprestimo para clube de leitura');

-- ============================================================================
-- VERIFICACAO DOS DADOS INSERIDOS
-- ============================================================================

-- Verificar usuarios cadastrados
SELECT 'USUARIOS CADASTRADOS:' AS '';
SELECT id, nome, usuario, data_cadastro FROM usuarios;

-- Verificar livros cadastrados
SELECT 'LIVROS CADASTRADOS:' AS '';
SELECT id, titulo, autor, isbn, categoria, estoque_atual, estoque_minimo FROM livros;

-- Verificar movimentacoes registradas
SELECT 'MOVIMENTACOES REGISTRADAS:' AS '';
SELECT m.id, l.titulo, u.nome AS responsavel, m.tipo, m.quantidade, m.data_movimentacao
FROM movimentacoes m
INNER JOIN livros l ON m.livro_id = l.id
INNER JOIN usuarios u ON m.usuario_id = u.id
ORDER BY m.data_registro DESC;

-- Verificar livros com estoque abaixo do minimo
SELECT 'ALERTAS - LIVROS COM ESTOQUE ABAIXO DO MINIMO:' AS '';
SELECT titulo, autor, estoque_atual, estoque_minimo,
       (estoque_minimo - estoque_atual) AS deficit
FROM livros
WHERE estoque_atual < estoque_minimo
ORDER BY deficit DESC;
