<?php
/**
 * Controller de Estoque
 * Responsavel por gerenciar movimentacoes de estoque de livros
 *
 * FUNCIONALIDADES:
 * - Exibir interface de gestao de estoque
 * - Registrar movimentacoes (entrada/saida)
 * - Exibir historico de movimentacoes
 * - Listar livros ordenados alfabeticamente
 * - Gerar alertas de estoque baixo
 *
 * CONCEITOS IMPORTANTES:
 * - Transacoes no banco (registrar movimentacao + atualizar estoque)
 * - Flash messages para feedback
 * - Ordenacao de dados (Bubble Sort)
 * - Pattern POST-Redirect-GET
 * - Validacao de metodo HTTP
 */

class EstoqueController {
    // Armazena instancias dos Models necessarios
    private $livroModel;
    private $movimentacaoModel;

    /**
     * Construtor
     * Inicializa os models necessarios ao criar o controller
     * Dependency Injection: os models sao injetados no construtor
     */
    public function __construct() {
        $this->livroModel = new Livro();
        $this->movimentacaoModel = new Movimentacao();
    }

    /**
     * Metodo: index
     * Exibe a pagina principal de gestao de estoque
     *
     * RESPONSABILIDADES:
     * 1. Verifica se usuario esta autenticado
     * 2. Busca todos os livros
     * 3. Ordena livros alfabeticamente (algoritmo Bubble Sort)
     * 4. Busca historico de movimentacoes (ultimas 20)
     * 5. Recupera e limpa mensagens flash
     * 6. Carrega a view com todos os dados
     */
    public function index() {
        // PROTECAO DE ROTA
        // Garante que apenas usuarios logados acessem esta pagina
        verificarLogin();

        // BUSCA DE DADOS
        // Busca todos os livros cadastrados no banco
        $livros = $this->livroModel->buscarTodos();

        // ORDENACAO
        // Ordena livros alfabeticamente por titulo usando Bubble Sort
        // Este e um requisito explicito: demonstrar uso de algoritmo de ordenacao
        $livros = $this->livroModel->ordenarPorTitulo($livros);

        // HISTORICO DE MOVIMENTACOES
        // Busca as ultimas 20 movimentacoes (entrada/saida)
        // Parametro 20 = limite de registros (evita sobrecarga)
        $historico = $this->movimentacaoModel->historico(20);

        // FLASH MESSAGES
        // Flash messages sao mensagens temporarias armazenadas na sessao
        // Sao exibidas uma vez e depois removidas
        // Operador ternario: condicao ? valor_se_true : valor_se_false
        $mensagem = isset($_SESSION['flash']['mensagem']) ? $_SESSION['flash']['mensagem'] : null;
        $tipoMensagem = isset($_SESSION['flash']['tipo_mensagem']) ? $_SESSION['flash']['tipo_mensagem'] : 'info';
        $alertaEstoque = isset($_SESSION['flash']['alerta_estoque']) ? $_SESSION['flash']['alerta_estoque'] : null;

        // LIMPEZA DAS FLASH MESSAGES
        // Remove as mensagens da sessao apos carrega-las
        // Isso garante que nao aparecerao novamente ao recarregar a pagina
        unset($_SESSION['flash']['mensagem']);
        unset($_SESSION['flash']['tipo_mensagem']);
        unset($_SESSION['flash']['alerta_estoque']);

        // Recupera nome do usuario logado da sessao
        $usuario_nome = $_SESSION['usuario_nome'];

        // CARREGA A VIEW
        // Todas as variaveis criadas acima ficam disponiveis na view
        // A view acessa: $livros, $historico, $mensagem, $tipoMensagem, $alertaEstoque, $usuario_nome
        require_once '../views/estoque/index.php';
    }

    /**
     * Metodo: registrar
     * Processa o formulario de registro de movimentacao de estoque
     * Metodo HTTP: POST
     *
     * FLUXO:
     * 1. Verifica autenticacao
     * 2. Valida que e requisicao POST
     * 3. Coleta dados do formulario
     * 4. Chama model para registrar movimentacao
     * 5. Armazena mensagens de feedback na sessao
     * 6. Redireciona de volta para a pagina de estoque
     *
     * CONCEITOS:
     * - POST-Redirect-GET: apos processar POST, sempre redirecione
     * - Isso evita reenvio do formulario ao atualizar a pagina (F5)
     * - Transacao no banco: registra movimentacao E atualiza estoque
     */
    public function registrar() {
        // PROTECAO DE ROTA
        verificarLogin();

        // VALIDACAO DE METODO HTTP
        // Garante que esta acao so pode ser acessada via POST
        // Previne acesso direto pela URL (GET)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=estoque");
            exit;
        }

        // COLETA DE DADOS DO FORMULARIO
        // Operador null coalescing (??): retorna valor ou default se nao existir
        // Isso previne erros "undefined index" se campo nao foi enviado
        $dados = [
            'livro_id' => $_POST['livro_id'] ?? 0,
            'tipo' => $_POST['tipo'] ?? '',  // 'entrada' ou 'saida'
            'quantidade' => $_POST['quantidade'] ?? 0,
            'data_movimentacao' => $_POST['data_movimentacao'] ?? '',
            'observacao' => $_POST['observacao'] ?? ''
        ];

        // PROCESSAMENTO NO MODEL
        // O model fara:
        // 1. Validacoes dos dados
        // 2. Registrar a movimentacao na tabela movimentacoes
        // 3. Atualizar o estoque_atual na tabela livros
        // 4. Verificar se estoque ficou abaixo do minimo (alerta)
        // Tudo isso em uma TRANSACAO (ou tudo sucede, ou tudo falha)
        $resultado = $this->movimentacaoModel->registrar($dados, $_SESSION['usuario_id']);

        // ARMAZENA FEEDBACK NA SESSAO (FLASH MESSAGES)
        // Mensagem de sucesso ou erro
        $_SESSION['flash']['mensagem'] = $resultado['mensagem'];
        // Tipo da mensagem: 'success' ou 'error' (usado para cor na view)
        $_SESSION['flash']['tipo_mensagem'] = $resultado['sucesso'] ? 'success' : 'error';

        // ALERTA DE ESTOQUE BAIXO
        // Se o model detectou que estoque ficou abaixo do minimo, armazena alerta
        if (!empty($resultado['alerta'])) {
            $_SESSION['flash']['alerta_estoque'] = $resultado['alerta'];
        }

        // POST-REDIRECT-GET (PRG)
        // Redireciona de volta para a pagina de estoque
        // Isso previne reenvio do formulario ao pressionar F5
        header("Location: index.php?action=estoque");
        exit; // IMPORTANTE: sempre use exit apos header
    }
}
