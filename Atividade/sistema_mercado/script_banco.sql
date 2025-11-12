-- Script de criação do banco de dados para Sistema de Gestão de Estoque
-- Sistema: Gestão de Estoque para Mercado
-- Data: 2025-11-12

-- Criação do banco de dados
DROP DATABASE IF EXISTS mercado_db;
CREATE DATABASE mercado_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mercado_db;

-- Tabela de usuários
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de produtos
CREATE TABLE produtos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    codigo_barras VARCHAR(50) UNIQUE,
    categoria VARCHAR(50) NOT NULL,
    marca VARCHAR(50),
    preco_custo DECIMAL(10,2),
    preco_venda DECIMAL(10,2),
    estoque_atual INT NOT NULL DEFAULT 0,
    estoque_minimo INT NOT NULL DEFAULT 10,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de movimentações de estoque
CREATE TABLE movimentacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    produto_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    quantidade INT NOT NULL,
    data_movimentacao DATE NOT NULL,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacao TEXT,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inserção de usuários de teste
-- Senha: 123456 (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
INSERT INTO usuarios (nome, usuario, senha) VALUES
('Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Maria Silva', 'maria', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('João Santos', 'joao', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Ana Costa', 'ana', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserção de produtos de teste
INSERT INTO produtos (nome, codigo_barras, categoria, marca, preco_custo, preco_venda, estoque_atual, estoque_minimo) VALUES
-- Alimentos
('Arroz Tipo 1 5kg', '7891234567001', 'Alimentos', 'Tio João', 12.50, 18.90, 25, 10),
('Feijão Preto 1kg', '7891234567002', 'Alimentos', 'Camil', 4.20, 6.50, 30, 15),
('Macarrão Espaguete 500g', '7891234567003', 'Alimentos', 'Barilla', 2.80, 4.20, 40, 20),
('Óleo de Soja 900ml', '7891234567004', 'Alimentos', 'Liza', 3.50, 5.80, 8, 10),
('Açúcar Cristal 1kg', '7891234567005', 'Alimentos', 'União', 2.90, 4.50, 35, 15),

-- Bebidas
('Refrigerante Cola 2L', '7891234567006', 'Bebidas', 'Coca-Cola', 3.20, 5.99, 22, 12),
('Suco de Laranja 1L', '7891234567007', 'Bebidas', 'Del Valle', 2.80, 4.80, 18, 10),
('Água Mineral 1.5L', '7891234567008', 'Bebidas', 'Crystal', 1.20, 2.50, 45, 20),

-- Higiene
('Sabonete 90g', '7891234567009', 'Higiene', 'Dove', 1.80, 3.20, 28, 15),
('Shampoo 400ml', '7891234567010', 'Higiene', 'Pantene', 8.50, 14.90, 12, 8),
('Pasta de Dente 90g', '7891234567011', 'Higiene', 'Colgate', 2.50, 4.50, 20, 10),

-- Limpeza
('Detergente Líquido 500ml', '7891234567012', 'Limpeza', 'Ypê', 1.20, 2.80, 32, 18),
('Água Sanitária 1L', '7891234567013', 'Limpeza', 'Q-Boa', 1.50, 3.20, 15, 12),

-- Hortifruti
('Banana Nanica kg', '7891234567014', 'Hortifruti', 'Fazenda Verde', 2.50, 4.90, 5, 8),
('Tomate kg', '7891234567015', 'Hortifruti', 'Fazenda Verde', 3.20, 5.80, 3, 5),

-- Padaria
('Pão Francês kg', '7891234567016', 'Padaria', 'Padaria Central', 4.50, 8.90, 12, 10),
('Bolo de Chocolate', '7891234567017', 'Padaria', 'Padaria Central', 8.00, 15.00, 6, 5),

-- Açougue
('Carne Bovina kg', '7891234567018', 'Açougue', 'Frigorífico São Paulo', 18.00, 32.90, 15, 8),
('Frango Inteiro kg', '7891234567019', 'Açougue', 'Sadia', 8.50, 14.90, 20, 10),

-- Outros
('Pilhas AA', '7891234567020', 'Outros', 'Duracell', 5.00, 9.90, 25, 15);

-- Inserção de movimentações de teste
INSERT INTO movimentacoes (produto_id, usuario_id, tipo, quantidade, data_movimentacao, observacao) VALUES
-- Entradas iniciais de estoque
(1, 1, 'entrada', 50, '2025-11-01', 'Estoque inicial'),
(2, 1, 'entrada', 60, '2025-11-01', 'Estoque inicial'),
(3, 1, 'entrada', 80, '2025-11-01', 'Estoque inicial'),
(4, 1, 'entrada', 30, '2025-11-01', 'Estoque inicial'),
(5, 1, 'entrada', 70, '2025-11-01', 'Estoque inicial'),
(6, 1, 'entrada', 40, '2025-11-01', 'Estoque inicial'),
(7, 1, 'entrada', 35, '2025-11-01', 'Estoque inicial'),
(8, 1, 'entrada', 80, '2025-11-01', 'Estoque inicial'),

-- Saídas (vendas)
(1, 2, 'saida', 25, '2025-11-05', 'Venda balcão'),
(2, 2, 'saida', 30, '2025-11-05', 'Venda balcão'),
(3, 3, 'saida', 40, '2025-11-06', 'Venda atacado'),
(4, 2, 'saida', 22, '2025-11-06', 'Venda balcão'),
(5, 3, 'saida', 35, '2025-11-07', 'Venda balcão'),
(6, 2, 'saida', 18, '2025-11-07', 'Venda balcão'),
(7, 4, 'saida', 17, '2025-11-08', 'Venda balcão'),
(8, 2, 'saida', 35, '2025-11-08', 'Venda atacado'),

-- Movimentações mais recentes
(14, 3, 'saida', 3, '2025-11-09', 'Produto próximo ao vencimento'),
(15, 2, 'saida', 2, '2025-11-10', 'Venda balcão'),
(9, 2, 'entrada', 50, '2025-11-10', 'Reposição estoque'),
(10, 1, 'entrada', 20, '2025-11-11', 'Reposição estoque'),
(11, 3, 'saida', 8, '2025-11-11', 'Venda balcão'),
(12, 4, 'saida', 15, '2025-11-12', 'Venda balcão');

-- Queries de validação
SELECT 'Usuários cadastrados:' as info;
SELECT id, nome, usuario, data_cadastro FROM usuarios;

SELECT 'Produtos por categoria:' as info;
SELECT categoria, COUNT(*) as quantidade FROM produtos GROUP BY categoria;

SELECT 'Produtos com estoque baixo (abaixo do mínimo):' as info;
SELECT nome, categoria, estoque_atual, estoque_minimo 
FROM produtos 
WHERE estoque_atual < estoque_minimo
ORDER BY estoque_atual;

SELECT 'Últimas 10 movimentações:' as info;
SELECT m.id, p.nome as produto, u.nome as usuario, m.tipo, m.quantidade, 
       m.data_movimentacao, m.observacao
FROM movimentacoes m
JOIN produtos p ON m.produto_id = p.id
JOIN usuarios u ON m.usuario_id = u.id
ORDER BY m.data_registro DESC
LIMIT 10;

SELECT 'Total de produtos por status de estoque:' as info;
SELECT 
    CASE 
        WHEN estoque_atual = 0 THEN 'Esgotado'
        WHEN estoque_atual < estoque_minimo THEN 'Estoque Baixo'
        ELSE 'Estoque OK'
    END as status_estoque,
    COUNT(*) as quantidade
FROM produtos
GROUP BY status_estoque;