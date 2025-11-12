<?php
/** Para Relembrar alguns pontos importantes:
 * $this é uma referência ao OBJETO ATUAL da classe.
 * $livro1 = new Livro();  // Cria primeiro objeto
 * $livro2 = new Livro();  // Cria segundo objeto
 * Dentro de $livro1, $this se refere a $livro1
 * Dentro de $livro2, $this se refere a $livro2
 * 
 * Usando PDO todo objeto tem sua propria conexao com o banco
 * $this->pdo significa "a conexão PDO DESTE objeto específico
 * 
 * ---
 * 
 * $stmt é abreviação de "statement" (declaração/comando SQL)
 * É um objeto PDOStatement que representa uma consulta SQL preparada.
 * * FLUXO DE TRABALHO:
 * 1. $stmt = $pdo->query(...)     // Executa a consulta SQL
 * 2. $stmt->fetchAll() 
 * 
 *  * MÉTODOS PRINCIPAIS DE $stmt:
 * - $stmt->fetch()      // Busca UMA linha do resultado
 * - $stmt->fetchAll()   // Busca TODAS as linhas
 * - $stmt->rowCount()   // Conta quantas linhas foram afetadas
 * - $stmt->execute()    // Executa prepared statement
 * 
 * EXEMPLO COM PREPARED STATEMENT:
 * $stmt = $pdo->prepare("SELECT * FROM livros WHERE id = :id");
 * $stmt->execute(['id' => 5]);  // Executa com o ID 5
 * $livro = $stmt->fetch();  
 * 
 * A seta -> é usada para acessar propriedades e métodos de um OBJETO
 * 
 * SINTAXE:
 * $objeto->propriedade   // Acessa uma propriedade
 * $objeto->metodo()      // Chama um método
 */

/**
 * Model Livro
 * Responsavel por todas as operacoes relacionadas a livros
 * Inclui CRUD completo, busca, validacao e algoritmo de ordenacao (Bubble Sort)
 */

class Livro {
    // Propriedade privada que armazena a conexao com o banco
    private $pdo;

    /**
     * Construtor da classe
     * Inicializa a conexao com o banco de dados
     */
    public function __construct() {
        $this->pdo = conectarBanco();
    }

    /**
     * Busca todos os livros no banco de dados
     * Retorna uma lista ordenada por titulo
     *
     * @return array Array de livros
     */
    public function buscarTodos() {
        $stmt = $this->pdo->query("SELECT * FROM livros ORDER BY titulo");
        return $stmt->fetchAll();
    }

    /**
     * Busca livros filtrando por um termo de pesquisa
     * Procura em: titulo, autor, ISBN e categoria
     *
     * @param string $termo Termo de busca (opcional)
     * @return array Array de livros encontrados
     */
    public function buscar($termo = "") {
        if (!empty($termo)) {
            // Se houver termo de busca, usa LIKE para busca parcial
        /**
        *  O símbolo % é um "coringa" (wildcard) usado com o operador LIKE no SQL.
        *  Ele representa "qualquer sequência de caracteres" (zero ou mais caracteres).

        *  Exemplo:
        *  - 'abc%' corresponde a qualquer string que comece com 'abc'
        *  - '%xyz' corresponde a qualquer string que termine com 'xyz'
        *  - '%mid%' corresponde a qualquer string que contenha 'mid' em   qualquer posição
        */

            $termoBusca = "%" . $termo . "%";
            // Prepara a consulta SQL com placeholders
            $stmt = $this->pdo->prepare(
                "SELECT * FROM livros
                WHERE titulo LIKE ?
                OR autor LIKE ?
                OR isbn LIKE ?
                OR categoria LIKE ?
                ORDER BY titulo"
            );
            // Usa o mesmo termo para buscar em todos os campos
            // Executa a consulta com os parametros
            $stmt->execute([$termoBusca, $termoBusca, $termoBusca, $termoBusca]);
        } else {
            // Se nao houver termo, retorna todos os livros
            $stmt = $this->pdo->query("SELECT * FROM livros ORDER BY titulo");
        }
        // Retorna os resultados como array associativo
        return $stmt->fetchAll();
    }

    /**
     * Busca um livro especifico pelo ID
     *
     * @param int $id ID do livro
     * @return array|false Dados do livro ou false se nao encontrado
     */
    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM livros WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Cadastra um novo livro no banco de dados
     * Valida os dados antes de inserir
     *
     * @param array $dados Array associativo com os dados do livro
     * @return array Resultado da operacao com 'sucesso' e 'mensagem'
     */
    public function cadastrar($dados) {
        // Valida os dados antes de inserir
        $erros = $this->validar($dados);

        if (!empty($erros)) {
            return [
                'sucesso' => false,
                'mensagem' => implode('<br>', $erros)
            ];
        }

        try {
            // SQL para inserir novo livro
            $sql = "INSERT INTO livros (titulo, autor, isbn, editora, ano_publicacao, categoria, estoque_atual, estoque_minimo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                sanitizar($dados['titulo']),
                sanitizar($dados['autor']),
                sanitizar($dados['isbn']),
                sanitizar($dados['editora']),
                $dados['ano_publicacao'] ?: null,  // Se vazio, insere NULL
                sanitizar($dados['categoria']),
                $dados['estoque_atual'],
                $dados['estoque_minimo']
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Livro cadastrado com sucesso!'
            ];
        } catch (PDOException $e) {
            // Verifica se o erro e por ISBN duplicado (codigo 23000 = violacao de constraint UNIQUE)
            if ($e->getCode() == 23000) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'ISBN ja cadastrado no sistema.'
                ];
            }
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao cadastrar livro: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Atualiza os dados de um livro existente
     *
     * @param int $id ID do livro a ser atualizado
     * @param array $dados Novos dados do livro
     * @return array Resultado da operacao
     */
    public function atualizar($id, $dados) {
        // Valida os dados antes de atualizar
        $erros = $this->validar($dados);

        if (!empty($erros)) {
            return [
                'sucesso' => false,
                'mensagem' => implode('<br>', $erros)
            ];
        }

        try {
            // SQL para atualizar o livro
            $sql = "UPDATE livros
                    SET titulo = ?, autor = ?, isbn = ?, editora = ?, ano_publicacao = ?,
                        categoria = ?, estoque_atual = ?, estoque_minimo = ?
                    WHERE id = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                sanitizar($dados['titulo']),
                sanitizar($dados['autor']),
                sanitizar($dados['isbn']),
                sanitizar($dados['editora']),
                $dados['ano_publicacao'] ?: null,
                sanitizar($dados['categoria']),
                $dados['estoque_atual'],
                $dados['estoque_minimo'],
                $id  // ID do livro a ser atualizado
            ]);

            return [
                'sucesso' => true,
                'mensagem' => 'Livro atualizado com sucesso!'
            ];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'ISBN ja cadastrado no sistema.'
                ];
            }
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao atualizar livro: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Exclui um livro do banco de dados
     *
     * @param int $id ID do livro a ser excluido
     * @return array Resultado da operacao
     */
    public function excluir($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM livros WHERE id = ?");
            $stmt->execute([$id]);

            return [
                'sucesso' => true,
                'mensagem' => 'Livro excluido com sucesso!'
            ];
        } catch (PDOException $e) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao excluir livro: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ALGORITMO BUBBLE SORT
     * Ordena um array de livros alfabeticamente por titulo
     * Este e o algoritmo de ordenacao mais simples, usado para fins didaticos
     *
     * Funcionamento do Bubble Sort:
     * - Compara pares de elementos adjacentes
     * - Se estiverem fora de ordem, troca de posicao
     * - Repete esse processo ate que nao haja mais trocas necessarias
     *
     * Complexidade: O(n²) - nao e o mais eficiente, mas e facil de entender
     *
     * @param array $livros Array de livros a ser ordenado
     * @return array Array ordenado alfabeticamente por titulo
     */
    public function ordenarPorTitulo(array $livros) {
        $n = count($livros);  // Total de elementos no array

        // Loop externo: percorre todo o array (n-1 vezes)
        for ($i = 0; $i < $n - 1; $i++) {

            // Loop interno: compara elementos adjacentes
            // A cada iteracao do loop externo, o maior elemento "borbulha" para o final
            // Por isso, diminuimos o range com ($n - $i - 1)
            for ($j = 0; $j < $n - $i - 1; $j++) {

                // Compara os titulos de forma case-insensitive (ignora maiusculas/minusculas)
                /**
                 * A função strcasecmp() compara duas strings sem diferenciar maiúsculas de minúsculas. Ela retorna: 0 se as strings forem iguais, um valor negativo se a primeira string for lexicograficamente menor que a segunda, e um valor positivo se for maior.
                 */

                if (strcasecmp($livros[$j]["titulo"], $livros[$j + 1]["titulo"]) > 0) {

                    // Se o elemento atual e maior que o proximo, TROCA de posicao
                    $temp = $livros[$j];              // Armazena temporariamente
                    $livros[$j] = $livros[$j + 1];    // Move o menor para frente
                    $livros[$j + 1] = $temp;          // Coloca o maior atras
                }
            }
        }

        // Retorna o array ordenado
        return $livros;
    }

    /**
     * Atualiza apenas o estoque de um livro
     * Usado pela classe Movimentacao ao registrar entradas/saidas
     *
     * @param int $id ID do livro
     * @param int $novoEstoque Nova quantidade em estoque
     * @return bool True se atualizou com sucesso
     */
    public function atualizarEstoque($id, $novoEstoque) {
        $stmt = $this->pdo->prepare("UPDATE livros SET estoque_atual = ? WHERE id = ?");
        return $stmt->execute([$novoEstoque, $id]);
    }

    /**
     * Valida os dados de um livro antes de inserir/atualizar
     * Verifica campos obrigatorios e formatos corretos
     *
     * @param array $dados Dados a serem validados
     * @return array Array com mensagens de erro (vazio se nao houver erros)
     */
    private function validar(array $dados) {
        $erros = [];

        // Valida campo titulo
        if (empty($dados['titulo'])) {
            $erros[] = 'Titulo e obrigatorio';
        }

        // Valida campo autor
        if (empty($dados['autor'])) {
            $erros[] = 'Autor e obrigatorio';
        }

        // Valida campo ISBN
        if (empty($dados['isbn'])) {
            $erros[] = 'ISBN e obrigatorio';
        }

        // Valida campo categoria
        if (empty($dados['categoria'])) {
            $erros[] = 'Categoria e obrigatoria';
        }

        // Valida estoque atual (deve ser numerico e nao negativo)
        if (!is_numeric($dados['estoque_atual']) || $dados['estoque_atual'] < 0) {
            $erros[] = 'Estoque atual deve ser um numero valido';
        }

        // Valida estoque minimo (deve ser numerico e nao negativo)
        if (!is_numeric($dados['estoque_minimo']) || $dados['estoque_minimo'] < 0) {
            $erros[] = 'Estoque minimo deve ser um numero valido';
        }

        // Valida ano de publicacao (se informado)
        if (!empty($dados['ano_publicacao'])) {
            // Deve ser um numero entre 1000 e o ano atual
            if (
                !is_numeric($dados['ano_publicacao']) ||
                $dados['ano_publicacao'] < 1000 ||
                $dados['ano_publicacao'] > date('Y')
            ) {
                $erros[] = 'Ano de publicacao invalido';
            }
        }

        return $erros;
    }
}
