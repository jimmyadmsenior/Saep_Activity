<?php
/**
 * Arquivo de configuracao do banco de dados e funcoes auxiliares
 * Este arquivo contem a funcao de conexao com o banco e funcoes utilitarias
 * PDO é uma extensão do PHP que fornece uma interface unificada para acessar
 * diferentes tipos de bancos de dados (MySQL, PostgreSQL, SQLite, etc).
 */

/**
 * Funcao para conectar ao banco de dados MySQL
 * Cria e retorna uma conexao PDO configurada
 *
 * @return PDO Objeto de conexao com o banco de dados
 */
function conectarBanco() {
    try {
        // Cria nova conexao PDO com MySQL
        // host=localhost: servidor local
        // dbname=biblioteca_db: nome do banco de dados
        // charset=utf8mb4: suporte completo a caracteres especiais e emojis
        $pdo = new PDO(
            "mysql:host=localhost;dbname=biblioteca_db;charset=utf8mb4",
            "root",      // usuario do banco
            ""           // senha do banco (vazia no ambiente local)
        );

        // Configura PDO para lancar excecoes em caso de erro
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Define que os resultados das consultas serao arrays associativos
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Desabilita emulacao de prepared statements para maior seguranca
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    } catch (PDOException $e) {
        // Em caso de erro, exibe mensagem e encerra execucao
        die("Erro ao conectar ao banco de dados: " . $e->getMessage());
    }
}

/**
 * Funcao para sanitizar dados de entrada
 * Remove espacos extras, barras invertidas e converte caracteres especiais
 * Protecao basica contra XSS (Cross-Site Scripting)
 *
 * @param string $dados Dados a serem sanitizados
 * @return string Dados sanitizados
 */
function sanitizar($dados) {
    $dados = trim($dados);              // Remove espacos no inicio e fim
    $dados = stripslashes($dados);      // Remove barras invertidas
    $dados = htmlspecialchars($dados);  // Converte caracteres especiais para HTML
    return $dados;
}

/**
 * Funcao para verificar se o usuario esta logado
 * Se nao estiver, redireciona para a pagina de login
 * Usada em todas as paginas protegidas do sistema
 */
function verificarLogin() {
    // Verifica se existe a variavel de sessao 'usuario_id'
    if (!isset($_SESSION['usuario_id'])) {
        // Se nao existir, redireciona para o login
        header("Location: index.php?action=login");
        exit;
    }
}
