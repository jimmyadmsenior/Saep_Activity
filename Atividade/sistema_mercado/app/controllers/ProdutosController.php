<?php
require_once dirname(__FILE__) . '/LoginController.php';
require_once dirname(__FILE__) . '/../models/Produto.php';

/**
 * Controller para gerenciamento de produtos
 */
class ProdutosController {
    private $produtoModel;

    public function __construct() {
        // Verificar autenticação
        LoginController::verificarAutenticacao();
        
        $this->produtoModel = new Produto();
    }

    /**
     * Lista todos os produtos com filtros de busca
     */
    public function listar() {
        $busca = trim($_GET['busca'] ?? '');
        $categoria = $_GET['categoria'] ?? '';
        
        // Buscar produtos
        $produtos = $this->produtoModel->listarTodos($busca, $categoria);
        
        // Buscar categorias para o filtro
        $categorias = $this->produtoModel->listarCategorias();
        
        // Incluir a view
        include dirname(__FILE__) . '/../views/produtos/listar.php';
    }

    /**
     * Cadastra um novo produto
     */
    public function cadastrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Processar o cadastro
            $dados = $this->validarDadosProduto($_POST);
            
            if ($dados === false) {
                header('Location: index.php?action=produtos');
                exit;
            }

            $resultado = $this->produtoModel->cadastrar($dados);
            
            if ($resultado) {
                LoginController::setFlashMessage('sucesso', 'Produto cadastrado com sucesso!');
            } else {
                LoginController::setFlashMessage('erro', 'Erro ao cadastrar produto. Verifique se o código de barras já não existe.');
            }
            
            // Redirect para evitar reenvio do formulário
            header('Location: index.php?action=produtos');
            exit;
        }
        
        // Se não é POST, redireciona para listagem
        header('Location: index.php?action=produtos');
        exit;
    }

    /**
     * Edita um produto existente
     */
    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                LoginController::setFlashMessage('erro', 'ID do produto inválido.');
                header('Location: index.php?action=produtos');
                exit;
            }

            // Processar a edição
            $dados = $this->validarDadosProduto($_POST, $id);
            
            if ($dados === false) {
                header('Location: index.php?action=produtos');
                exit;
            }

            $resultado = $this->produtoModel->atualizar($id, $dados);
            
            if ($resultado) {
                LoginController::setFlashMessage('sucesso', 'Produto atualizado com sucesso!');
            } else {
                LoginController::setFlashMessage('erro', 'Erro ao atualizar produto. Verifique se o código de barras já não existe em outro produto.');
            }
            
            header('Location: index.php?action=produtos');
            exit;
        }
        
        // Se não é POST, redireciona para listagem
        header('Location: index.php?action=produtos');
        exit;
    }

    /**
     * Exclui um produto
     */
    public function excluir() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                LoginController::setFlashMessage('erro', 'ID do produto inválido.');
                header('Location: index.php?action=produtos');
                exit;
            }

            $resultado = $this->produtoModel->deletar($id);
            
            if ($resultado) {
                LoginController::setFlashMessage('sucesso', 'Produto excluído com sucesso!');
            } else {
                LoginController::setFlashMessage('erro', 'Erro ao excluir produto. Pode haver movimentações associadas.');
            }
            
            header('Location: index.php?action=produtos');
            exit;
        }
        
        // Se não é POST, redireciona para listagem
        header('Location: index.php?action=produtos');
        exit;
    }

    /**
     * Busca um produto por ID via AJAX
     */
    public function buscarPorId() {
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
            header('Content-Type: application/json');
            echo json_encode($produto);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Produto não encontrado']);
        }
        exit;
    }

    /**
     * Valida os dados do produto
     * @param array $dados
     * @param int|null $id Para edição
     * @return array|false
     */
    private function validarDadosProduto($dados, $id = null) {
        $erros = [];

        // Sanitização básica
        $nome = trim(htmlspecialchars($dados['nome'] ?? '', ENT_QUOTES, 'UTF-8'));
        $codigo_barras = trim(htmlspecialchars($dados['codigo_barras'] ?? '', ENT_QUOTES, 'UTF-8'));
        $categoria = trim(htmlspecialchars($dados['categoria'] ?? '', ENT_QUOTES, 'UTF-8'));
        $marca = trim(htmlspecialchars($dados['marca'] ?? '', ENT_QUOTES, 'UTF-8'));
        $preco_custo = floatval($dados['preco_custo'] ?? 0);
        $preco_venda = floatval($dados['preco_venda'] ?? 0);
        $estoque_atual = intval($dados['estoque_atual'] ?? 0);
        $estoque_minimo = intval($dados['estoque_minimo'] ?? 10);

        // Validações obrigatórias
        if (empty($nome)) {
            $erros[] = 'Nome do produto é obrigatório';
        }

        if (empty($categoria)) {
            $erros[] = 'Categoria é obrigatória';
        }

        if ($preco_custo < 0) {
            $erros[] = 'Preço de custo deve ser maior ou igual a zero';
        }

        if ($preco_venda < 0) {
            $erros[] = 'Preço de venda deve ser maior ou igual a zero';
        }

        if ($estoque_atual < 0) {
            $erros[] = 'Estoque atual deve ser maior ou igual a zero';
        }

        if ($estoque_minimo < 0) {
            $erros[] = 'Estoque mínimo deve ser maior ou igual a zero';
        }

        // Validar código de barras único (se fornecido)
        if (!empty($codigo_barras)) {
            if ($this->produtoModel->codigoBarrasExiste($codigo_barras, $id)) {
                $erros[] = 'Código de barras já existe em outro produto';
            }
        }

        // Se houver erros, exibir e retornar false
        if (!empty($erros)) {
            LoginController::setFlashMessage('erro', implode(', ', $erros));
            return false;
        }

        return [
            'nome' => $nome,
            'codigo_barras' => $codigo_barras,
            'categoria' => $categoria,
            'marca' => $marca,
            'preco_custo' => $preco_custo,
            'preco_venda' => $preco_venda,
            'estoque_atual' => $estoque_atual,
            'estoque_minimo' => $estoque_minimo
        ];
    }

    /**
     * Retorna as categorias padrão do sistema
     * @return array
     */
    public static function getCategoriasDisponiveis() {
        return [
            'Alimentos',
            'Bebidas',
            'Higiene',
            'Limpeza',
            'Hortifruti',
            'Padaria',
            'Açougue',
            'Outros'
        ];
    }

    /**
     * Exporta relatório de produtos em CSV
     */
    public function exportarCSV() {
        $produtos = $this->produtoModel->listarTodos();
        
        // Definir headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=produtos_' . date('Y-m-d') . '.csv');
        
        // Abrir output stream
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos do CSV
        fputcsv($output, [
            'ID', 'Nome', 'Código de Barras', 'Categoria', 'Marca', 
            'Preço Custo', 'Preço Venda', 'Estoque Atual', 'Estoque Mínimo', 
            'Status Estoque', 'Data Cadastro'
        ], ';');
        
        // Dados dos produtos
        foreach ($produtos as $produto) {
            $status = HomeController::getTextoStatusEstoque($produto);
            
            fputcsv($output, [
                $produto['id'],
                $produto['nome'],
                $produto['codigo_barras'],
                $produto['categoria'],
                $produto['marca'],
                number_format($produto['preco_custo'], 2, ',', '.'),
                number_format($produto['preco_venda'], 2, ',', '.'),
                $produto['estoque_atual'],
                $produto['estoque_minimo'],
                $status,
                date('d/m/Y H:i', strtotime($produto['data_cadastro']))
            ], ';');
        }
        
        fclose($output);
        exit;
    }
}
?>