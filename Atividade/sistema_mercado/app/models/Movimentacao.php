<?php
require_once dirname(__FILE__) . '/../config/database.php';

/**
 * Model para gerenciamento de movimentações de estoque
 * Responsável por operações CRUD na tabela movimentacoes
 */
class Movimentacao {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registra uma nova movimentação de estoque usando transação PDO
     * @param array $dados
     * @return bool|string Retorna true se sucesso, ou mensagem de erro
     */
    public function registrar($dados) {
        try {
            // Validação básica
            if (empty($dados['produto_id']) || empty($dados['usuario_id']) || 
                empty($dados['tipo']) || empty($dados['quantidade']) || 
                empty($dados['data_movimentacao'])) {
                return "Dados obrigatórios não fornecidos";
            }

            if (!in_array($dados['tipo'], ['entrada', 'saida'])) {
                return "Tipo de movimentação inválido";
            }

            if ($dados['quantidade'] <= 0) {
                return "Quantidade deve ser maior que zero";
            }

            $this->db->beginTransaction();

            // Buscar produto para verificar estoque atual
            $sql = "SELECT estoque_atual, estoque_minimo, nome FROM produtos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $dados['produto_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$produto) {
                $this->db->rollBack();
                return "Produto não encontrado";
            }

            // Calcular novo estoque
            $quantidade = ($dados['tipo'] === 'entrada') ? $dados['quantidade'] : -$dados['quantidade'];
            $novoEstoque = $produto['estoque_atual'] + $quantidade;

            // Verificar se o estoque não ficará negativo
            if ($novoEstoque < 0) {
                $this->db->rollBack();
                return "Estoque insuficiente. Estoque atual: " . $produto['estoque_atual'];
            }

            // Registrar a movimentação
            $sql = "INSERT INTO movimentacoes (produto_id, usuario_id, tipo, quantidade, 
                    data_movimentacao, observacao) 
                    VALUES (:produto_id, :usuario_id, :tipo, :quantidade, :data_movimentacao, :observacao)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':produto_id', $dados['produto_id'], PDO::PARAM_INT);
            $stmt->bindParam(':usuario_id', $dados['usuario_id'], PDO::PARAM_INT);
            $stmt->bindParam(':tipo', $dados['tipo'], PDO::PARAM_STR);
            $stmt->bindParam(':quantidade', $dados['quantidade'], PDO::PARAM_INT);
            $stmt->bindParam(':data_movimentacao', $dados['data_movimentacao'], PDO::PARAM_STR);
            $stmt->bindParam(':observacao', $dados['observacao'], PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                $this->db->rollBack();
                return "Erro ao registrar movimentação";
            }

            // Atualizar estoque do produto
            $sql = "UPDATE produtos SET estoque_atual = :estoque WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estoque', $novoEstoque, PDO::PARAM_INT);
            $stmt->bindParam(':id', $dados['produto_id'], PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $this->db->rollBack();
                return "Erro ao atualizar estoque do produto";
            }

            $this->db->commit();

            // Verificar se o produto ficou com estoque baixo
            if ($novoEstoque < $produto['estoque_minimo']) {
                return "success_with_alert";
            }

            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao registrar movimentação: " . $e->getMessage());
            return "Erro interno do sistema";
        }
    }

    /**
     * Lista as últimas movimentações
     * @param int $limit Limite de registros
     * @param int $produtoId Filtrar por produto específico
     * @return array
     */
    public function listarUltimas($limit = 20, $produtoId = null) {
        try {
            $sql = "SELECT m.*, p.nome as produto_nome, p.categoria, u.nome as usuario_nome
                    FROM movimentacoes m
                    JOIN produtos p ON m.produto_id = p.id
                    JOIN usuarios u ON m.usuario_id = u.id";

            $params = [];

            if ($produtoId) {
                $sql .= " WHERE m.produto_id = :produto_id";
                $params[':produto_id'] = $produtoId;
            }

            $sql .= " ORDER BY m.data_registro DESC LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao listar movimentações: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca movimentações por período
     * @param string $dataInicio
     * @param string $dataFim
     * @param int $produtoId
     * @return array
     */
    public function buscarPorPeriodo($dataInicio, $dataFim, $produtoId = null) {
        try {
            $sql = "SELECT m.*, p.nome as produto_nome, p.categoria, u.nome as usuario_nome
                    FROM movimentacoes m
                    JOIN produtos p ON m.produto_id = p.id
                    JOIN usuarios u ON m.usuario_id = u.id
                    WHERE m.data_movimentacao BETWEEN :data_inicio AND :data_fim";

            $params = [
                ':data_inicio' => $dataInicio,
                ':data_fim' => $dataFim
            ];

            if ($produtoId) {
                $sql .= " AND m.produto_id = :produto_id";
                $params[':produto_id'] = $produtoId;
            }

            $sql .= " ORDER BY m.data_movimentacao DESC, m.data_registro DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar movimentações por período: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca uma movimentação específica
     * @param int $id
     * @return array|false
     */
    public function buscarPorId($id) {
        try {
            $sql = "SELECT m.*, p.nome as produto_nome, p.categoria, u.nome as usuario_nome
                    FROM movimentacoes m
                    JOIN produtos p ON m.produto_id = p.id
                    JOIN usuarios u ON m.usuario_id = u.id
                    WHERE m.id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar movimentação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calcula estatísticas de movimentação
     * @param int $dias Período em dias
     * @return array
     */
    public function estatisticasMovimentacao($dias = 30) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_movimentacoes,
                        SUM(CASE WHEN tipo = 'entrada' THEN quantidade ELSE 0 END) as total_entradas,
                        SUM(CASE WHEN tipo = 'saida' THEN quantidade ELSE 0 END) as total_saidas,
                        COUNT(DISTINCT produto_id) as produtos_movimentados
                    FROM movimentacoes 
                    WHERE data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':dias', $dias, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao calcular estatísticas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista produtos mais movimentados
     * @param int $limit
     * @param int $dias
     * @return array
     */
    public function produtosMaisMovimentados($limit = 10, $dias = 30) {
        try {
            $sql = "SELECT p.nome, p.categoria, 
                           COUNT(*) as total_movimentacoes,
                           SUM(CASE WHEN m.tipo = 'entrada' THEN m.quantidade ELSE 0 END) as total_entradas,
                           SUM(CASE WHEN m.tipo = 'saida' THEN m.quantidade ELSE 0 END) as total_saidas
                    FROM movimentacoes m
                    JOIN produtos p ON m.produto_id = p.id
                    WHERE m.data_movimentacao >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
                    GROUP BY p.id, p.nome, p.categoria
                    ORDER BY total_movimentacoes DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar produtos mais movimentados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Remove uma movimentação (reverter operação)
     * @param int $id
     * @return bool|string
     */
    public function reverter($id) {
        try {
            $this->db->beginTransaction();

            // Buscar a movimentação
            $movimentacao = $this->buscarPorId($id);
            if (!$movimentacao) {
                $this->db->rollBack();
                return "Movimentação não encontrada";
            }

            // Calcular quantidade para reverter
            $quantidadeReversa = ($movimentacao['tipo'] === 'entrada') 
                ? -$movimentacao['quantidade'] 
                : $movimentacao['quantidade'];

            // Buscar produto atual
            $sql = "SELECT estoque_atual FROM produtos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $movimentacao['produto_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            $produto = $stmt->fetch(PDO::FETCH_ASSOC);
            $novoEstoque = $produto['estoque_atual'] + $quantidadeReversa;

            // Verificar se não ficará negativo
            if ($novoEstoque < 0) {
                $this->db->rollBack();
                return "Não é possível reverter: estoque ficaria negativo";
            }

            // Atualizar estoque
            $sql = "UPDATE produtos SET estoque_atual = :estoque WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estoque', $novoEstoque, PDO::PARAM_INT);
            $stmt->bindParam(':id', $movimentacao['produto_id'], PDO::PARAM_INT);
            $stmt->execute();

            // Deletar movimentação
            $sql = "DELETE FROM movimentacoes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Erro ao reverter movimentação: " . $e->getMessage());
            return "Erro interno do sistema";
        }
    }
}
?>