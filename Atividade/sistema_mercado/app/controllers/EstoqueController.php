<?php
require_once dirname(__FILE__) . '/LoginController.php';
require_once dirname(__FILE__) . '/../models/Produto.php';
require_once dirname(__FILE__) . '/../models/Movimentacao.php';

/**
 * Controller para gerenciamento de estoque
 */
class EstoqueController {
    private $produtoModel;
    private $movimentacaoModel;

    public function __construct() {
        // Verificar autenticação
        LoginController::verificarAutenticacao();
        
        $this->produtoModel = new Produto();
        $this->movimentacaoModel = new Movimentacao();
    }

    /**
     * Exibe a página de gestão de estoque
     */
    public function index() {
        // Buscar produtos ordenados alfabeticamente usando Bubble Sort
        $todosProdutos = $this->produtoModel->listarTodos();
        $produtos = $this->produtoModel->ordenarProdutosBubbleSort($todosProdutos);
        
        // Buscar últimas movimentações
        $ultimasMovimentacoes = $this->movimentacaoModel->listarUltimas(20);
        
        // Produtos com estoque baixo para alertas
        $produtosEstoqueBaixo = $this->produtoModel->produtosEstoqueBaixo();
        
        // Incluir a view
        include dirname(__FILE__) . '/../views/estoque/index.php';
    }

    /**
     * Registra uma nova movimentação de estoque
     */
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=estoque');
            exit;
        }

        // Obter dados do usuário logado
        $usuarioLogado = LoginController::getUsuarioLogado();
        
        // Validar e processar dados
        $dados = $this->validarDadosMovimentacao($_POST, $usuarioLogado['id']);
        
        if ($dados === false) {
            header('Location: index.php?action=estoque');
            exit;
        }

        // Registrar movimentação
        $resultado = $this->movimentacaoModel->registrar($dados);
        
        if ($resultado === true) {
            LoginController::setFlashMessage('sucesso', 'Movimentação registrada com sucesso!');
        } elseif ($resultado === 'success_with_alert') {
            // Buscar dados do produto para o alerta
            $produto = $this->produtoModel->buscarPorId($dados['produto_id']);
            $mensagem = "Movimentação registrada com sucesso! ATENÇÃO: O produto '{$produto['nome']}' está com estoque baixo (Atual: {$produto['estoque_atual']}, Mínimo: {$produto['estoque_minimo']}).";
            LoginController::setFlashMessage('aviso', $mensagem);
        } else {
            LoginController::setFlashMessage('erro', $resultado);
        }
        
        header('Location: index.php?action=estoque');
        exit;
    }

    /**
     * Busca dados de um produto via AJAX
     */
    public function buscarProduto() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido']);
            exit;
        }

        $produto = $this->produtoModel->buscarPorId($id);
        
        if ($produto) {
            // Adicionar informação de status do estoque
            $produto['status_estoque'] = HomeController::getTextoStatusEstoque($produto);
            $produto['classe_status'] = HomeController::getClasseStatusEstoque($produto);
            
            header('Content-Type: application/json');
            echo json_encode($produto);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Produto não encontrado']);
        }
        exit;
    }

    /**
     * Lista movimentações com filtros via AJAX
     */
    public function listarMovimentacoes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit;
        }

        $produtoId = intval($_GET['produto_id'] ?? 0);
        $limit = intval($_GET['limit'] ?? 20);
        
        if ($produtoId > 0) {
            $movimentacoes = $this->movimentacaoModel->listarUltimas($limit, $produtoId);
        } else {
            $movimentacoes = $this->movimentacaoModel->listarUltimas($limit);
        }
        
        header('Content-Type: application/json');
        echo json_encode($movimentacoes);
        exit;
    }

    /**
     * Reverte uma movimentação de estoque
     */
    public function reverter() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=estoque');
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            LoginController::setFlashMessage('erro', 'ID da movimentação inválido.');
            header('Location: index.php?action=estoque');
            exit;
        }

        $resultado = $this->movimentacaoModel->reverter($id);
        
        if ($resultado === true) {
            LoginController::setFlashMessage('sucesso', 'Movimentação revertida com sucesso!');
        } else {
            LoginController::setFlashMessage('erro', $resultado);
        }
        
        header('Location: index.php?action=estoque');
        exit;
    }

    /**
     * Gera relatório de movimentações em CSV
     */
    public function relatorioCSV() {
        $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01'); // Primeiro dia do mês
        $dataFim = $_GET['data_fim'] ?? date('Y-m-d'); // Hoje
        $produtoId = intval($_GET['produto_id'] ?? 0);
        
        // Buscar movimentações
        $movimentacoes = $this->movimentacaoModel->buscarPorPeriodo($dataInicio, $dataFim, $produtoId > 0 ? $produtoId : null);
        
        // Definir headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=movimentacoes_' . date('Y-m-d') . '.csv');
        
        // Abrir output stream
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos do CSV
        fputcsv($output, [
            'ID', 'Produto', 'Categoria', 'Usuário', 'Tipo', 'Quantidade', 
            'Data Movimentação', 'Data Registro', 'Observação'
        ], ';');
        
        // Dados das movimentações
        foreach ($movimentacoes as $mov) {
            fputcsv($output, [
                $mov['id'],
                $mov['produto_nome'],
                $mov['categoria'],
                $mov['usuario_nome'],
                ucfirst($mov['tipo']),
                $mov['quantidade'],
                date('d/m/Y', strtotime($mov['data_movimentacao'])),
                date('d/m/Y H:i', strtotime($mov['data_registro'])),
                $mov['observacao']
            ], ';');
        }
        
        fclose($output);
        exit;
    }

    /**
     * Valida os dados da movimentação
     * @param array $dados
     * @param int $usuarioId
     * @return array|false
     */
    private function validarDadosMovimentacao($dados, $usuarioId) {
        $erros = [];

        // Sanitização básica
        $produto_id = intval($dados['produto_id'] ?? 0);
        $tipo = trim($dados['tipo'] ?? '');
        $quantidade = intval($dados['quantidade'] ?? 0);
        $data_movimentacao = trim($dados['data_movimentacao'] ?? '');
        $observacao = trim(htmlspecialchars($dados['observacao'] ?? '', ENT_QUOTES, 'UTF-8'));

        // Validações obrigatórias
        if ($produto_id <= 0) {
            $erros[] = 'Produto deve ser selecionado';
        }

        if (!in_array($tipo, ['entrada', 'saida'])) {
            $erros[] = 'Tipo de movimentação inválido';
        }

        if ($quantidade <= 0) {
            $erros[] = 'Quantidade deve ser maior que zero';
        }

        if (empty($data_movimentacao)) {
            $erros[] = 'Data da movimentação é obrigatória';
        } else {
            // Validar formato da data
            $dataObj = DateTime::createFromFormat('Y-m-d', $data_movimentacao);
            if (!$dataObj || $dataObj->format('Y-m-d') !== $data_movimentacao) {
                $erros[] = 'Data da movimentação inválida';
            }
            
            // Não permitir datas futuras
            if ($dataObj && $dataObj > new DateTime()) {
                $erros[] = 'Data da movimentação não pode ser futura';
            }
        }

        // Verificar se o produto existe
        if ($produto_id > 0) {
            $produto = $this->produtoModel->buscarPorId($produto_id);
            if (!$produto) {
                $erros[] = 'Produto não encontrado';
            } elseif ($tipo === 'saida' && $quantidade > $produto['estoque_atual']) {
                $erros[] = "Quantidade insuficiente em estoque. Disponível: {$produto['estoque_atual']}";
            }
        }

        // Se houver erros, exibir e retornar false
        if (!empty($erros)) {
            LoginController::setFlashMessage('erro', implode(', ', $erros));
            return false;
        }

        return [
            'produto_id' => $produto_id,
            'usuario_id' => $usuarioId,
            'tipo' => $tipo,
            'quantidade' => $quantidade,
            'data_movimentacao' => $data_movimentacao,
            'observacao' => $observacao
        ];
    }

    /**
     * Obtém estatísticas rápidas do estoque
     */
    public function estatisticas() {
        header('Content-Type: application/json');
        
        $estatisticas = [
            'movimentacao' => $this->movimentacaoModel->estatisticasMovimentacao(30),
            'produtos_mais_movimentados' => $this->movimentacaoModel->produtosMaisMovimentados(10, 30),
            'produtos_estoque_baixo' => count($this->produtoModel->produtosEstoqueBaixo())
        ];
        
        echo json_encode($estatisticas);
        exit;
    }

    /**
     * Atualização em lote do estoque mínimo
     */
    public function atualizarEstoqueMinimoLote() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=estoque');
            exit;
        }

        $categoria = trim($_POST['categoria'] ?? '');
        $novoMinimo = intval($_POST['novo_minimo'] ?? 0);

        if (empty($categoria) || $novoMinimo < 0) {
            LoginController::setFlashMessage('erro', 'Categoria e estoque mínimo são obrigatórios.');
            header('Location: index.php?action=estoque');
            exit;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $sql = "UPDATE produtos SET estoque_minimo = :novo_minimo WHERE categoria = :categoria";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':novo_minimo', $novoMinimo, PDO::PARAM_INT);
            $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
            $result = $stmt->execute();
            
            $produtosAtualizados = $stmt->rowCount();

            if ($result && $produtosAtualizados > 0) {
                LoginController::setFlashMessage('sucesso', "Estoque mínimo atualizado para {$produtosAtualizados} produtos da categoria {$categoria}.");
            } else {
                LoginController::setFlashMessage('aviso', 'Nenhum produto foi atualizado. Verifique se a categoria existe.');
            }
        } catch (PDOException $e) {
            error_log("Erro ao atualizar estoque mínimo em lote: " . $e->getMessage());
            LoginController::setFlashMessage('erro', 'Erro interno do sistema.');
        }

        header('Location: index.php?action=estoque');
        exit;
    }
}
?>