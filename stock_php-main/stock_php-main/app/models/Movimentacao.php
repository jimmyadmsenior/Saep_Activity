<?php
/**
 * Model Movimentacao
 * Responsavel por registrar entradas e saidas de livros no estoque
 * Utiliza TRANSACOES DE BANCO DE DADOS para garantir integridade dos dados
 */

class Movimentacao {
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
     * Registra uma movimentacao de estoque (entrada ou saida)
     * USA TRANSACAO para garantir que AMBAS operacoes sejam concluidas:
     *   1. Atualizar o estoque do livro
     *   2. Registrar a movimentacao no historico
     * Se alguma falhar, NENHUMA e executada (rollback)
     *
     * @param array $dados Dados da movimentacao (livro_id, tipo, quantidade, data, observacao)
     * @param int $usuarioId ID do usuario que esta registrando
     * @return array Resultado com 'sucesso', 'mensagem' e possivel 'alerta'
     */
    public function registrar($dados, $usuarioId) {
        // Valida os dados antes de processar
        $erros = $this->validar($dados);

        if (!empty($erros)) {
            return [
                'sucesso' => false,
                'mensagem' => implode('<br>', $erros)
            ];
        }

        try {
            // INICIA A TRANSACAO
            // A partir daqui, as operacoes no banco sao temporarias ate o commit()
            $this->pdo->beginTransaction();

            // Busca os dados do livro
            $livroModel = new Livro();
            $livro = $livroModel->buscarPorId($dados['livro_id']);

            // Verifica se o livro existe
            if (!$livro) {
                throw new Exception('Livro nao encontrado');
            }

            // Calcula o novo estoque baseado no tipo de movimentacao
            $novoEstoque = $livro['estoque_atual'];

            if ($dados['tipo'] === 'entrada') {
                // ENTRADA: aumenta o estoque
                $novoEstoque += $dados['quantidade'];
            } else {
                // SAIDA: diminui o estoque

                // Valida se ha estoque suficiente para saida
                if ($dados['quantidade'] > $livro['estoque_atual']) {
                    throw new Exception('Quantidade de saida maior que estoque disponivel');
                }

                $novoEstoque -= $dados['quantidade'];
            }

            // OPERACAO 1: Atualiza o estoque do livro
            $livroModel->atualizarEstoque($dados['livro_id'], $novoEstoque);

            // OPERACAO 2: Registra a movimentacao no historico
            $sql = "INSERT INTO movimentacoes (livro_id, usuario_id, tipo, quantidade, data_movimentacao, observacao)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $dados['livro_id'],
                $usuarioId,
                $dados['tipo'],
                $dados['quantidade'],
                $dados['data_movimentacao'],
                sanitizar($dados['observacao'])
            ]);

            // COMMIT: confirma todas as operacoes no banco
            // Se chegou ate aqui sem erros, as mudancas sao permanentes
            $this->pdo->commit();

            // Verifica se o estoque ficou abaixo do minimo apos a saida
            $alerta = '';
            if ($dados['tipo'] === 'saida' && $novoEstoque < $livro['estoque_minimo']) {
                // Calcula quanto falta para atingir o estoque minimo
                $deficit = $livro['estoque_minimo'] - $novoEstoque;

                // Monta mensagem de alerta
                $alerta = "ALERTA: O livro '{$livro['titulo']}' esta com estoque abaixo do minimo! " .
                         "Estoque atual: {$novoEstoque} | Estoque minimo: {$livro['estoque_minimo']} | " .
                         "Deficit: {$deficit} unidades";
            }

            return [
                'sucesso' => true,
                'mensagem' => 'Movimentacao registrada com sucesso!',
                'alerta' => $alerta
            ];

        } catch (Exception $e) {
            // ROLLBACK: se houver qualquer erro, desfaz TODAS as operacoes
            // O banco volta ao estado anterior, como se nada tivesse acontecido
            $this->pdo->rollBack();

            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao registrar movimentacao: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Busca o historico de movimentacoes
     * Retorna as ultimas movimentacoes com informacoes do livro e usuario
     * Usa JOIN para trazer dados relacionados
     *
     * @param int $limite Numero maximo de registros a retornar (padrao: 20)
     * @return array Array com historico de movimentacoes
     */
    public function historico($limite = 20) {
        // SQL com INNER JOIN para buscar dados relacionados:
        // - movimentacoes (m): tabela principal
        // - livros (l): para pegar titulo e ISBN
        // - usuarios (u): para pegar nome do responsavel
        $stmt = $this->pdo->query(
            "SELECT m.*, l.titulo, l.isbn, u.nome as responsavel
            FROM movimentacoes m
            INNER JOIN livros l ON m.livro_id = l.id
            INNER JOIN usuarios u ON m.usuario_id = u.id
            ORDER BY m.data_registro DESC
            LIMIT {$limite}"
        );

        return $stmt->fetchAll();
    }

    /**
     * Valida os dados de uma movimentacao
     * Verifica se todos os campos obrigatorios estao preenchidos corretamente
     *
     * @param array $dados Dados a serem validados
     * @return array Array com mensagens de erro (vazio se nao houver erros)
     */
    private function validar(array $dados) {
        $erros = [];

        // Valida se um livro foi selecionado
        if (empty($dados['livro_id'])) {
            $erros[] = 'Selecione um livro';
        }

        // Valida se o tipo e 'entrada' ou 'saida'
        if (empty($dados['tipo']) || !in_array($dados['tipo'], ['entrada', 'saida'])) {
            $erros[] = 'Tipo de movimentacao invalido';
        }

        // Valida a quantidade (deve ser numero positivo)
        if (!is_numeric($dados['quantidade']) || $dados['quantidade'] <= 0) {
            $erros[] = 'Quantidade deve ser maior que zero';
        }

        // Valida se a data foi informada
        if (empty($dados['data_movimentacao'])) {
            $erros[] = 'Data da movimentacao e obrigatoria';
        }

        return $erros;
    }
}
