<?php
require_once dirname(__FILE__) . '/LoginController.php';
require_once dirname(__FILE__) . '/../models/Produto.php';
require_once dirname(__FILE__) . '/../models/Movimentacao.php';

/**
 * Controller da página principal (home)
 */
class HomeController {
    private $produtoModel;
    private $movimentacaoModel;

    public function __construct() {
        // Verificar autenticação
        LoginController::verificarAutenticacao();
        
        $this->produtoModel = new Produto();
        $this->movimentacaoModel = new Movimentacao();
    }

    /**
     * Exibe a página inicial com dashboard
     */
    public function index() {
        // Obter dados para o dashboard
        $dadosDashboard = $this->obterDadosDashboard();
        
        // Incluir a view da home
        include dirname(__FILE__) . '/../views/home.php';
    }

    /**
     * Coleta dados estatísticos para o dashboard
     * @return array
     */
    private function obterDadosDashboard() {
        try {
            // Produtos com estoque baixo
            $produtosEstoqueBaixo = $this->produtoModel->produtosEstoqueBaixo();
            
            // Últimas movimentações
            $ultimasMovimentacoes = $this->movimentacaoModel->listarUltimas(10);
            
            // Estatísticas de movimentação dos últimos 30 dias
            $estatisticasMovimentacao = $this->movimentacaoModel->estatisticasMovimentacao(30);
            
            // Produtos mais movimentados
            $produtosMaisMovimentados = $this->movimentacaoModel->produtosMaisMovimentados(5, 30);
            
            // Contar produtos por categoria
            $produtosPorCategoria = $this->contarProdutosPorCategoria();
            
            // Status geral do estoque
            $statusEstoque = $this->obterStatusEstoque();
            
            return [
                'produtos_estoque_baixo' => $produtosEstoqueBaixo,
                'ultimas_movimentacoes' => $ultimasMovimentacoes,
                'estatisticas_movimentacao' => $estatisticasMovimentacao,
                'produtos_mais_movimentados' => $produtosMaisMovimentados,
                'produtos_por_categoria' => $produtosPorCategoria,
                'status_estoque' => $statusEstoque
            ];
        } catch (Exception $e) {
            error_log("Erro ao obter dados do dashboard: " . $e->getMessage());
            return [
                'produtos_estoque_baixo' => [],
                'ultimas_movimentacoes' => [],
                'estatisticas_movimentacao' => [],
                'produtos_mais_movimentados' => [],
                'produtos_por_categoria' => [],
                'status_estoque' => []
            ];
        }
    }

    /**
     * Conta produtos por categoria
     * @return array
     */
    private function contarProdutosPorCategoria() {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT categoria, COUNT(*) as total FROM produtos GROUP BY categoria ORDER BY categoria";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao contar produtos por categoria: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém status geral do estoque
     * @return array
     */
    private function obterStatusEstoque() {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT 
                        COUNT(*) as total_produtos,
                        SUM(CASE WHEN estoque_atual = 0 THEN 1 ELSE 0 END) as esgotados,
                        SUM(CASE WHEN estoque_atual > 0 AND estoque_atual < estoque_minimo THEN 1 ELSE 0 END) as estoque_baixo,
                        SUM(CASE WHEN estoque_atual >= estoque_minimo THEN 1 ELSE 0 END) as estoque_ok,
                        SUM(estoque_atual) as total_itens_estoque
                    FROM produtos";
            
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter status do estoque: " . $e->getMessage());
            return [
                'total_produtos' => 0,
                'esgotados' => 0,
                'estoque_baixo' => 0,
                'estoque_ok' => 0,
                'total_itens_estoque' => 0
            ];
        }
    }

    /**
     * Retorna dados em JSON para requisições AJAX
     */
    public function ajax() {
        header('Content-Type: application/json');
        
        $acao = $_GET['acao'] ?? '';
        
        switch ($acao) {
            case 'dashboard':
                $dados = $this->obterDadosDashboard();
                echo json_encode($dados);
                break;
                
            case 'estoque_baixo':
                $produtos = $this->produtoModel->produtosEstoqueBaixo();
                echo json_encode($produtos);
                break;
                
            case 'ultimas_movimentacoes':
                $limit = intval($_GET['limit'] ?? 10);
                $movimentacoes = $this->movimentacaoModel->listarUltimas($limit);
                echo json_encode($movimentacoes);
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['erro' => 'Ação não encontrada']);
        }
        exit;
    }

    /**
     * Formata valores monetários para exibição
     * @param float $valor
     * @return string
     */
    public static function formatarMoeda($valor) {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    /**
     * Formata data para exibição
     * @param string $data
     * @return string
     */
    public static function formatarData($data) {
        return date('d/m/Y', strtotime($data));
    }

    /**
     * Formata data e hora para exibição
     * @param string $dataHora
     * @return string
     */
    public static function formatarDataHora($dataHora) {
        return date('d/m/Y H:i', strtotime($dataHora));
    }

    /**
     * Retorna a classe CSS baseada no status do estoque
     * @param array $produto
     * @return string
     */
    public static function getClasseStatusEstoque($produto) {
        if ($produto['estoque_atual'] == 0) {
            return 'danger'; // Esgotado
        } elseif ($produto['estoque_atual'] < $produto['estoque_minimo']) {
            return 'warning'; // Estoque baixo
        } else {
            return 'success'; // Estoque OK
        }
    }

    /**
     * Retorna o texto do status do estoque
     * @param array $produto
     * @return string
     */
    public static function getTextoStatusEstoque($produto) {
        if ($produto['estoque_atual'] == 0) {
            return 'Esgotado';
        } elseif ($produto['estoque_atual'] < $produto['estoque_minimo']) {
            return 'Estoque Baixo';
        } else {
            return 'Estoque OK';
        }
    }
}
?>