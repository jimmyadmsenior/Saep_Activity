<?php
/**
 * Controller de Login
 * Responsavel por autenticacao de usuarios
 * Gerencia login, logout e sessoes
 */

class LoginController {
    // Armazena uma instancia do Model Usuario
    private $usuarioModel;

    /**
     * Construtor
     * Inicializa o model de Usuario ao criar o controller
     */
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Exibe a tela de login
     * Se o usuario ja estiver logado, redireciona para a home
     */
    public function mostrarLogin() {
        // Verifica se ja existe uma sessao ativa
        if (isset($_SESSION['usuario_id'])) {
            // Se ja estiver logado, redireciona para a pagina inicial
            header("Location: index.php");
            exit;
        }

        // Busca mensagens flash (mensagens temporarias de uma requisicao)
        // Essas mensagens sao exibidas uma vez e depois apagadas
        $erro = isset($_SESSION['flash']['erro']) ? $_SESSION['flash']['erro'] : null;
        $sucesso = isset($_SESSION['flash']['sucesso']) ? $_SESSION['flash']['sucesso'] : null;

        // Remove as mensagens da sessao apos busca-las
        // Assim elas nao aparecem novamente em outros recarregamentos
        unset($_SESSION['flash']['erro']);
        unset($_SESSION['flash']['sucesso']);

        // Carrega a view de login
        // As variaveis $erro e $sucesso estarao disponiveis na view
        require_once '../views/login.php';
    }

    /**
     * Processa o formulario de login
     * Valida credenciais e cria sessao se autenticacao for bem-sucedida
     * Metodo: POST
     */
    public function autenticar() {
        // Verifica se a requisicao e do tipo POST
        // Previne acesso direto pela URL (GET)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=login");
            exit;
        }

        // Recebe dados do formulario
        // Operador ?? retorna '' se a variavel nao existir
        $usuario = $_POST['usuario'] ?? '';
        $senha = $_POST['senha'] ?? '';

        // Chama o model para validar as credenciais
        // O model retorna um array com 'sucesso' e 'mensagem' ou 'usuario'
        $resultado = $this->usuarioModel->autenticar($usuario, $senha);

        if ($resultado['sucesso']) {
            // AUTENTICACAO BEM-SUCEDIDA
            // Armazena dados do usuario na sessao
            // Esses dados ficam disponiveis em todas as paginas
            $_SESSION['usuario_id'] = $resultado['usuario']['id'];
            $_SESSION['usuario_nome'] = $resultado['usuario']['nome'];
            $_SESSION['usuario_login'] = $resultado['usuario']['usuario'];

            // Redireciona para a pagina inicial
            header("Location: index.php");
            exit;
        } else {
            // AUTENTICACAO FALHOU
            // Armazena mensagem de erro em flash message
            $_SESSION['flash']['erro'] = $resultado['mensagem'];

            // Redireciona de volta para o login
            header("Location: index.php?action=login");
            exit;
        }
    }

    /**
     * Realiza logout do usuario
     * Destroi a sessao e redireciona para login
     */
    public function logout() {
        // Destroi todos os dados da sessao
        session_destroy();

        // Inicia uma nova sessao (para armazenar a mensagem de sucesso)
        session_start();

        // Define mensagem de sucesso
        $_SESSION['flash']['sucesso'] = 'Logout realizado com sucesso!';

        // Redireciona para a tela de login
        header("Location: index.php?action=login");
        exit;
    }
}
