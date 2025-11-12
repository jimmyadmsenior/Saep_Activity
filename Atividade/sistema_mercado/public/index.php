<?php
// Definir cabeçalho UTF-8 para todas as respostas
header('Content-Type: text/html; charset=UTF-8');

/**
 * Front Controller - Ponto único de entrada do sistema
 * Sistema de Gestão de Estoque para Mercado
 * 
 * Este arquivo é responsável por:
 * - Receber todas as requisições
 * - Rotear para o controller apropriado
 * - Gerenciar sessões
 * - Tratar erros globais
 */

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurar exibição de erros para desenvolvimento
// Em produção, definir como false
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir arquivos necessários
require_once dirname(__FILE__) . '/../app/config/database.php';
require_once dirname(__FILE__) . '/../app/controllers/LoginController.php';
require_once dirname(__FILE__) . '/../app/controllers/HomeController.php';
require_once dirname(__FILE__) . '/../app/controllers/ProdutosController.php';
require_once dirname(__FILE__) . '/../app/controllers/EstoqueController.php';

/**
 * Classe principal do Front Controller
 */
class FrontController {
    private $action;
    private $allowedActions;

    public function __construct() {
        // Definir ações permitidas no sistema
        $this->allowedActions = [
            // Autenticação
            'login' => ['controller' => 'LoginController', 'method' => 'mostrarLogin', 'auth' => false],
            'autenticar' => ['controller' => 'LoginController', 'method' => 'autenticar', 'auth' => false],
            'logout' => ['controller' => 'LoginController', 'method' => 'logout', 'auth' => false],
            
            // Home/Dashboard
            'home' => ['controller' => 'HomeController', 'method' => 'index', 'auth' => true],
            'home-ajax' => ['controller' => 'HomeController', 'method' => 'ajax', 'auth' => true],
            
            // Produtos
            'produtos' => ['controller' => 'ProdutosController', 'method' => 'listar', 'auth' => true],
            'produtos-cadastrar' => ['controller' => 'ProdutosController', 'method' => 'cadastrar', 'auth' => true],
            'produtos-editar' => ['controller' => 'ProdutosController', 'method' => 'editar', 'auth' => true],
            'produtos-excluir' => ['controller' => 'ProdutosController', 'method' => 'excluir', 'auth' => true],
            'produtos-buscar' => ['controller' => 'ProdutosController', 'method' => 'buscarPorId', 'auth' => true],
            'produtos-exportar-csv' => ['controller' => 'ProdutosController', 'method' => 'exportarCSV', 'auth' => true],
            
            // Estoque
            'estoque' => ['controller' => 'EstoqueController', 'method' => 'index', 'auth' => true],
            'estoque-registrar' => ['controller' => 'EstoqueController', 'method' => 'registrar', 'auth' => true],
            'estoque-buscar-produto' => ['controller' => 'EstoqueController', 'method' => 'buscarProduto', 'auth' => true],
            'estoque-listar-movimentacoes' => ['controller' => 'EstoqueController', 'method' => 'listarMovimentacoes', 'auth' => true],
            'estoque-reverter' => ['controller' => 'EstoqueController', 'method' => 'reverter', 'auth' => true],
            'estoque-relatorio-csv' => ['controller' => 'EstoqueController', 'method' => 'relatorioCSV', 'auth' => true],
            'estoque-estatisticas' => ['controller' => 'EstoqueController', 'method' => 'estatisticas', 'auth' => true],
            'estoque-atualizar-lote' => ['controller' => 'EstoqueController', 'method' => 'atualizarEstoqueMinimoLote', 'auth' => true]
        ];

        // Obter ação da URL
        $this->action = $_GET['action'] ?? 'default';
    }

    /**
     * Executa o roteamento principal
     */
    public function run() {
        try {
            // Se não há ação específica, determinar ação padrão
            if ($this->action === 'default') {
                $this->action = $this->getDefaultAction();
            }

            // Verificar se a ação existe
            if (!array_key_exists($this->action, $this->allowedActions)) {
                $this->handleNotFound();
                return;
            }

            $actionConfig = $this->allowedActions[$this->action];

            // Verificar autenticação se necessária
            if ($actionConfig['auth'] && !$this->isAuthenticated()) {
                $this->redirectToLogin();
                return;
            }

            // Executar a ação
            $this->executeAction($actionConfig);

        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Determina a ação padrão baseada no estado da sessão
     */
    private function getDefaultAction() {
        return $this->isAuthenticated() ? 'home' : 'login';
    }

    /**
     * Verifica se o usuário está autenticado
     */
    private function isAuthenticated() {
        return isset($_SESSION['usuario_logado']) && !empty($_SESSION['usuario_logado']);
    }

    /**
     * Redireciona para a página de login
     */
    private function redirectToLogin() {
        LoginController::setFlashMessage('erro', 'Acesso negado. Faça login para continuar.');
        header('Location: index.php?action=login');
        exit;
    }

    /**
     * Executa a ação do controller
     */
    private function executeAction($actionConfig) {
        $controllerName = $actionConfig['controller'];
        $methodName = $actionConfig['method'];

        // Verificar se a classe do controller existe
        if (!class_exists($controllerName)) {
            throw new Exception("Controller {$controllerName} não encontrado.");
        }

        // Instanciar o controller
        $controller = new $controllerName();

        // Verificar se o método existe
        if (!method_exists($controller, $methodName)) {
            throw new Exception("Método {$methodName} não encontrado no controller {$controllerName}.");
        }

        // Executar o método
        $controller->$methodName();
    }

    /**
     * Trata erro 404 - Página não encontrada
     */
    private function handleNotFound() {
        http_response_code(404);
        
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['erro' => 'Ação não encontrada']);
            exit;
        }

        // Redirecionar para home se autenticado, senão para login
        if ($this->isAuthenticated()) {
            LoginController::setFlashMessage('erro', 'Página não encontrada.');
            header('Location: index.php?action=home');
        } else {
            LoginController::setFlashMessage('erro', 'Página não encontrada.');
            header('Location: index.php?action=login');
        }
        exit;
    }

    /**
     * Trata erros gerais do sistema
     */
    private function handleError($exception) {
        // Log do erro
        error_log("Erro no Front Controller: " . $exception->getMessage());
        error_log("Stack trace: " . $exception->getTraceAsString());

        // Em produção, não mostrar detalhes do erro
        $isDevelopment = true; // Definir como false em produção

        if ($this->isAjaxRequest()) {
            http_response_code(500);
            header('Content-Type: application/json');
            
            if ($isDevelopment) {
                echo json_encode([
                    'erro' => 'Erro interno do servidor',
                    'detalhes' => $exception->getMessage(),
                    'arquivo' => $exception->getFile(),
                    'linha' => $exception->getLine()
                ]);
            } else {
                echo json_encode(['erro' => 'Erro interno do servidor']);
            }
            exit;
        }

        // Para requisições normais, redirecionar com mensagem de erro
        if ($isDevelopment) {
            $mensagem = "Erro: " . $exception->getMessage() . " em " . $exception->getFile() . ":" . $exception->getLine();
        } else {
            $mensagem = "Ocorreu um erro interno. Tente novamente.";
        }

        LoginController::setFlashMessage('erro', $mensagem);

        if ($this->isAuthenticated()) {
            header('Location: index.php?action=home');
        } else {
            header('Location: index.php?action=login');
        }
        exit;
    }

    /**
     * Verifica se é uma requisição AJAX
     */
    private function isAjaxRequest() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (isset($_SERVER['CONTENT_TYPE']) && 
                strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
    }

    /**
     * Sanitiza e valida parâmetros de entrada
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida token CSRF (implementação básica)
     */
    public static function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Gera token CSRF
     */
    public static function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// Função para capturar erros fatais
function handleFatalError() {
    $error = error_get_last();
    if ($error && ($error['type'] & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR))) {
        error_log("Erro fatal: " . $error['message'] . " em " . $error['file'] . ":" . $error['line']);
        
        // Tentar redirecionar graciosamente
        if (!headers_sent()) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            LoginController::setFlashMessage('erro', 'Ocorreu um erro interno. Tente novamente.');
            
            if (isset($_SESSION['usuario_logado'])) {
                header('Location: index.php?action=home');
            } else {
                header('Location: index.php?action=login');
            }
            exit;
        }
    }
}

// Registrar handler para erros fatais
register_shutdown_function('handleFatalError');

// Instanciar e executar o Front Controller
$frontController = new FrontController();
$frontController->run();
?>