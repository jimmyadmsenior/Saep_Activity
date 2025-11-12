<?php
/**
 * Ponto de entrada principal do sistema (Front Controller)
 * Este arquivo recebe todas as requisicoes e direciona para o controller apropriado
 * Usa o padrao MVC (Model-View-Controller) simplificado
 */

// Configuracoes de exibicao de erros (util para desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia a sessao PHP para armazenar dados do usuario logado
session_start();

// Carrega o arquivo de configuracao do banco de dados
require_once '../config/database.php';

// Carrega todos os Models (classes que lidam com dados)
require_once '../models/Usuario.php';
require_once '../models/Livro.php';
require_once '../models/Movimentacao.php';

// Carrega todos os Controllers (classes que processam requisicoes)
require_once '../controllers/LoginController.php';
require_once '../controllers/HomeController.php';
require_once '../controllers/LivrosController.php';
require_once '../controllers/EstoqueController.php';

// Pega o parametro 'action' da URL (ex: index.php?action=livros)
// Se nao existir, usa 'home' como padrao
$action = $_GET['action'] ?? 'home';

// Sistema de roteamento simples usando switch
// Direciona para o controller e metodo correto baseado na action
switch ($action) {
    // Rota para exibir a tela de login
    case 'login':
        $controller = new LoginController();
        $controller->mostrarLogin();
        break;

    // Rota para processar o formulario de login (POST)
    case 'autenticar':
        $controller = new LoginController();
        $controller->autenticar();
        break;

    // Rota para fazer logout (destruir sessao)
    case 'logout':
        $controller = new LoginController();
        $controller->logout();
        break;

    // Rota para a pagina inicial (menu principal)
    case 'home':
        $controller = new HomeController();
        $controller->index();
        break;

    // Rota para listar todos os livros
    case 'livros':
        $controller = new LivrosController();
        $controller->listar();
        break;

    // Rota para cadastrar novo livro (POST)
    case 'livros-cadastrar':
        $controller = new LivrosController();
        $controller->cadastrar();
        break;

    // Rota para editar livro existente (POST)
    case 'livros-editar':
        $controller = new LivrosController();
        $controller->editar();
        break;

    // Rota para excluir livro (POST)
    case 'livros-excluir':
        $controller = new LivrosController();
        $controller->excluir();
        break;

    // Rota para a pagina de gestao de estoque
    case 'estoque':
        $controller = new EstoqueController();
        $controller->index();
        break;

    // Rota para registrar movimentacao de estoque (POST)
    case 'estoque-registrar':
        $controller = new EstoqueController();
        $controller->registrar();
        break;

    // Rota padrao: se a action nao existir, redireciona para home
    default:
        header("Location: index.php");
        exit;
}
