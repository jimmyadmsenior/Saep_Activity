<?php
// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../models/Usuario.php';

/**
 * Controller responsável pelo sistema de autenticação
 */
class LoginController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Exibe a página de login
     */
    public function mostrarLogin() {
        // Se já estiver logado, redireciona para home
        if (isset($_SESSION['usuario_logado'])) {
            header('Location: index.php?action=home');
            exit;
        }

        // Incluir a view de login
        include dirname(__FILE__) . '/../views/login.php';
    }

    /**
     * Processa a autenticação do usuário
     */
    public function autenticar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=login');
            exit;
        }

        $usuario = trim($_POST['usuario'] ?? '');
        $senha = $_POST['senha'] ?? '';

        // Validação básica
        if (empty($usuario) || empty($senha)) {
            $_SESSION['erro'] = 'Usuário e senha são obrigatórios';
            header('Location: index.php?action=login');
            exit;
        }

        // Protção contra XSS
        $usuario = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');

        // Tentar autenticar
        $usuarioLogado = $this->usuarioModel->autenticar($usuario, $senha);

        if ($usuarioLogado) {
            // Login bem-sucedido
            $_SESSION['usuario_logado'] = $usuarioLogado;
            $_SESSION['sucesso'] = 'Login realizado com sucesso!';
            
            // Regenerar ID da sessão por segurança
            session_regenerate_id(true);
            
            header('Location: index.php?action=home');
            exit;
        } else {
            // Login falhou
            $_SESSION['erro'] = 'Usuário ou senha incorretos';
            header('Location: index.php?action=login');
            exit;
        }
    }

    /**
     * Efetua o logout do usuário
     */
    public function logout() {
        // Verificar se existe sessão ativa
        if (isset($_SESSION['usuario_logado'])) {
            // Limpar todas as variáveis da sessão
            $_SESSION = array();

            // Destruir o cookie de sessão se existir
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // Destruir a sessão
            session_destroy();
        }

        // Iniciar nova sessão para a mensagem se necessário
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['sucesso'] = 'Logout realizado com sucesso!';
        
        header('Location: index.php?action=login');
        exit;
    }

    /**
     * Verifica se o usuário está autenticado
     * Middleware para proteger rotas
     */
    public static function verificarAutenticacao() {
        if (!isset($_SESSION['usuario_logado'])) {
            $_SESSION['erro'] = 'Acesso negado. Faça login para continuar.';
            header('Location: index.php?action=login');
            exit;
        }
    }

    /**
     * Retorna os dados do usuário logado
     * @return array|null
     */
    public static function getUsuarioLogado() {
        return $_SESSION['usuario_logado'] ?? null;
    }

    /**
     * Verifica se há mensagens flash na sessão e as retorna
     * @return array
     */
    public static function getFlashMessages() {
        $messages = [];
        
        if (isset($_SESSION['sucesso'])) {
            $messages['sucesso'] = $_SESSION['sucesso'];
            unset($_SESSION['sucesso']);
        }
        
        if (isset($_SESSION['erro'])) {
            $messages['erro'] = $_SESSION['erro'];
            unset($_SESSION['erro']);
        }
        
        if (isset($_SESSION['aviso'])) {
            $messages['aviso'] = $_SESSION['aviso'];
            unset($_SESSION['aviso']);
        }
        
        return $messages;
    }

    /**
     * Define uma mensagem flash
     * @param string $tipo
     * @param string $mensagem
     */
    public static function setFlashMessage($tipo, $mensagem) {
        $_SESSION[$tipo] = $mensagem;
    }
}
?>