<?php

class LivrosController {
    private $livroModel;

    public function __construct() {
        $this->livroModel = new Livro();
    }

    public function listar() {
        verificarLogin();

        $termoBusca = $_GET['busca'] ?? '';
        $livros = $this->livroModel->buscar($termoBusca);

        $mensagem = isset($_SESSION['flash']['mensagem']) ? $_SESSION['flash']['mensagem'] : null;
        $tipoMensagem = isset($_SESSION['flash']['tipo_mensagem']) ? $_SESSION['flash']['tipo_mensagem'] : 'info';
        unset($_SESSION['flash']['mensagem']);
        unset($_SESSION['flash']['tipo_mensagem']);

        $usuario_nome = $_SESSION['usuario_nome'];

        require_once '../views/livros/listar.php';
    }

    public function cadastrar() {
        verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=livros");
            exit;
        }

        $dados = [
            'titulo' => $_POST['titulo'] ?? '',
            'autor' => $_POST['autor'] ?? '',
            'isbn' => $_POST['isbn'] ?? '',
            'editora' => $_POST['editora'] ?? '',
            'ano_publicacao' => $_POST['ano_publicacao'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'estoque_atual' => $_POST['estoque_atual'] ?? 0,
            'estoque_minimo' => $_POST['estoque_minimo'] ?? 0
        ];

        $resultado = $this->livroModel->cadastrar($dados);

        $_SESSION['flash']['mensagem'] = $resultado['mensagem'];
        $_SESSION['flash']['tipo_mensagem'] = $resultado['sucesso'] ? 'success' : 'error';

        header("Location: index.php?action=livros");
        exit;
    }

    public function editar() {
        verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=livros");
            exit;
        }

        $id = $_POST['id'] ?? 0;
        $dados = [
            'titulo' => $_POST['titulo'] ?? '',
            'autor' => $_POST['autor'] ?? '',
            'isbn' => $_POST['isbn'] ?? '',
            'editora' => $_POST['editora'] ?? '',
            'ano_publicacao' => $_POST['ano_publicacao'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'estoque_atual' => $_POST['estoque_atual'] ?? 0,
            'estoque_minimo' => $_POST['estoque_minimo'] ?? 0
        ];

        $resultado = $this->livroModel->atualizar($id, $dados);

        $_SESSION['flash']['mensagem'] = $resultado['mensagem'];
        $_SESSION['flash']['tipo_mensagem'] = $resultado['sucesso'] ? 'success' : 'error';

        header("Location: index.php?action=livros");
        exit;
    }

    public function excluir() {
        verificarLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=livros");
            exit;
        }

        $id = $_POST['id'] ?? 0;
        $resultado = $this->livroModel->excluir($id);

        $_SESSION['flash']['mensagem'] = $resultado['mensagem'];
        $_SESSION['flash']['tipo_mensagem'] = $resultado['sucesso'] ? 'success' : 'error';

        header("Location: index.php?action=livros");
        exit;
    }
}
