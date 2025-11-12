<?php
/**
 * Controller da Home (Pagina Inicial)
 * Responsavel por exibir o menu principal do sistema
 * Exibe opcoes para acessar outras funcionalidades
 */

class HomeController {
    /**
     * Metodo index - exibe a pagina inicial
     * Verifica se o usuario esta logado e carrega a view home
     */
    public function index() {
        // Verifica se o usuario esta autenticado
        // Se nao estiver, a funcao verificarLogin() redireciona para o login
        verificarLogin();

        // Busca o nome do usuario da sessao
        // Este nome sera exibido no cabecalho da pagina
        $usuario_nome = $_SESSION['usuario_nome'];

        // Carrega a view da pagina inicial (menu principal)
        // A variavel $usuario_nome estara disponivel na view
        require_once '../views/home.php';
    }
}
