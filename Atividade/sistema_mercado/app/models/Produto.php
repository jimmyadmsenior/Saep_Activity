<?php
require_once dirname(__FILE__) . '/../config/database.php';

/**
 * Model para gerenciamento de produtos
 * Responsável por operações CRUD na tabela produtos
 */
class Produto {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Lista todos os produtos com informações de status do estoque
     * @param string $busca Termo para buscar produtos
     * @param string $categoria Filtro por categoria
     * @return array
     */
    public function listarTodos($busca = '', $categoria = '') {
        try {
            $sql = "SELECT *, 
                    CASE 
                        WHEN estoque_atual = 0 THEN 'esgotado'
                        WHEN estoque_atual < estoque_minimo THEN 'baixo'
                        ELSE 'ok'
                    END as status_estoque
                    FROM produtos WHERE 1=1";

            $params = [];

            if (!empty($busca)) {
                $sql .= " AND (nome LIKE :busca OR codigo_barras LIKE :busca OR marca LIKE :busca)";
                $params[':busca'] = '%' . $busca . '%';
            }

            if (!empty($categoria)) {
                $sql .= " AND categoria = :categoria";
                $params[':categoria'] = $categoria;
            }

            $sql .= " ORDER BY nome";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao listar produtos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Implementação do Bubble Sort para ordenar produtos alfabeticamente
     * Conforme solicitado nos requisitos técnicos
     * @param array $produtos
     * @return array
     */
    public function ordenarProdutosBubbleSort($produtos) {
        $n = count($produtos);
        
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = 0; $j < $n - $i - 1; $j++) {
                if (strcasecmp($produtos[$j]['nome'], $produtos[$j + 1]['nome']) > 0) {
                    // Troca os elementos
                    $temp = $produtos[$j];
                    $produtos[$j] = $produtos[$j + 1];
                    $produtos[$j + 1] = $temp;
                }
            }
        }
        
        return $produtos;
    }

    /**
     * Busca um produto pelo ID
     * @param int $id
     * @return array|false
     */
    public function buscarPorId($id) {
        try {
            $sql = "SELECT * FROM produtos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar produto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cadastra um novo produto
     * @param array $dados
     * @return bool
     */
    public function cadastrar($dados) {
        try {
            // Validação básica
            if (empty($dados['nome']) || empty($dados['categoria'])) {
                return false;
            }

            // Verificar se código de barras já existe (se fornecido)
            if (!empty($dados['codigo_barras'])) {
                $sql = "SELECT id FROM produtos WHERE codigo_barras = :codigo_barras";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':codigo_barras', $dados['codigo_barras'], PDO::PARAM_STR);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    return false; // Código já existe
                }
            }

            $sql = "INSERT INTO produtos (nome, codigo_barras, categoria, marca, preco_custo, 
                    preco_venda, estoque_atual, estoque_minimo) 
                    VALUES (:nome, :codigo_barras, :categoria, :marca, :preco_custo, 
                    :preco_venda, :estoque_atual, :estoque_minimo)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nome', $dados['nome'], PDO::PARAM_STR);
            $stmt->bindParam(':codigo_barras', $dados['codigo_barras'], PDO::PARAM_STR);
            $stmt->bindParam(':categoria', $dados['categoria'], PDO::PARAM_STR);
            $stmt->bindParam(':marca', $dados['marca'], PDO::PARAM_STR);
            $stmt->bindParam(':preco_custo', $dados['preco_custo'], PDO::PARAM_STR);
            $stmt->bindParam(':preco_venda', $dados['preco_venda'], PDO::PARAM_STR);
            $stmt->bindParam(':estoque_atual', $dados['estoque_atual'], PDO::PARAM_INT);
            $stmt->bindParam(':estoque_minimo', $dados['estoque_minimo'], PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao cadastrar produto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza dados de um produto
     * @param int $id
     * @param array $dados
     * @return bool
     */
    public function atualizar($id, $dados) {
        try {
            // Verificar se código de barras já existe em outro produto
            if (!empty($dados['codigo_barras'])) {
                $sql = "SELECT id FROM produtos WHERE codigo_barras = :codigo_barras AND id != :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':codigo_barras', $dados['codigo_barras'], PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    return false; // Código já existe em outro produto
                }
            }

            $sql = "UPDATE produtos SET 
                    nome = :nome, 
                    codigo_barras = :codigo_barras, 
                    categoria = :categoria, 
                    marca = :marca, 
                    preco_custo = :preco_custo, 
                    preco_venda = :preco_venda, 
                    estoque_atual = :estoque_atual, 
                    estoque_minimo = :estoque_minimo
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nome', $dados['nome'], PDO::PARAM_STR);
            $stmt->bindParam(':codigo_barras', $dados['codigo_barras'], PDO::PARAM_STR);
            $stmt->bindParam(':categoria', $dados['categoria'], PDO::PARAM_STR);
            $stmt->bindParam(':marca', $dados['marca'], PDO::PARAM_STR);
            $stmt->bindParam(':preco_custo', $dados['preco_custo'], PDO::PARAM_STR);
            $stmt->bindParam(':preco_venda', $dados['preco_venda'], PDO::PARAM_STR);
            $stmt->bindParam(':estoque_atual', $dados['estoque_atual'], PDO::PARAM_INT);
            $stmt->bindParam(':estoque_minimo', $dados['estoque_minimo'], PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao atualizar produto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove um produto
     * @param int $id
     * @return bool
     */
    public function deletar($id) {
        try {
            $sql = "DELETE FROM produtos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao deletar produto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o estoque de um produto usando transação
     * @param int $produtoId
     * @param int $quantidade (positivo para entrada, negativo para saída)
     * @return bool
     */
    public function atualizarEstoque($produtoId, $quantidade) {
        try {
            $this->db->beginTransaction();

            // Buscar estoque atual
            $sql = "SELECT estoque_atual FROM produtos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
            $stmt->execute();
            
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$produto) {
                $this->db->rollBack();
                return false;
            }

            $novoEstoque = $produto['estoque_atual'] + $quantidade;

            // Verificar se o estoque não ficará negativo
            if ($novoEstoque < 0) {
                $this->db->rollBack();
                return false;
            }

            // Atualizar estoque
            $sql = "UPDATE produtos SET estoque_atual = :estoque WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estoque', $novoEstoque, PDO::PARAM_INT);
            $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar estoque: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna produtos com estoque baixo
     * @return array
     */
    public function produtosEstoqueBaixo() {
        try {
            $sql = "SELECT * FROM produtos WHERE estoque_atual < estoque_minimo ORDER BY estoque_atual";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar produtos com estoque baixo: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna todas as categorias disponíveis
     * @return array
     */
    public function listarCategorias() {
        try {
            $sql = "SELECT DISTINCT categoria FROM produtos ORDER BY categoria";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Erro ao listar categorias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se um código de barras já existe
     * @param string $codigoBarras
     * @param int $ignorarId ID do produto a ignorar na verificação
     * @return bool
     */
    public function codigoBarrasExiste($codigoBarras, $ignorarId = null) {
        try {
            $sql = "SELECT id FROM produtos WHERE codigo_barras = :codigo_barras";
            $params = [':codigo_barras' => $codigoBarras];

            if ($ignorarId) {
                $sql .= " AND id != :id";
                $params[':id'] = $ignorarId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar código de barras: " . $e->getMessage());
            return false;
        }
    }
}
?>