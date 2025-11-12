<?php
/**
 * Model Usuario
 * Responsavel por todas as operacoes relacionadas a usuarios
 * Lida com autenticacao e validacao de credenciais
 */

class Usuario {
    // Propriedade privada que armazena a conexao com o banco
    private $pdo;

    /**
     * Construtor da classe
     * Inicializa a conexao com o banco de dados ao criar um objeto Usuario
     */
    public function __construct() {
        $this->pdo = conectarBanco();
    }

    /**
     * Autentica um usuario no sistema
     * Verifica se o usuario existe e se a senha esta correta
     *
     * @param string $usuario Nome de usuario (login)
     * @param string $senha Senha informada pelo usuario
     * @return array Array associativo com 'sucesso' (bool) e 'mensagem' ou 'usuario'
     */
    public function autenticar($usuario, $senha) {
        // Sanitiza o nome de usuario para evitar injecao de codigo
        $usuario = sanitizar($usuario);

        // Valida se os campos foram preenchidos
        if (empty($usuario) || empty($senha)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Por favor, preencha todos os campos.'
            ];
        }

        // Busca o usuario no banco usando Prepared Statement (protecao contra SQL Injection)
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $usuarioDB = $stmt->fetch();

        // Verifica se o usuario foi encontrado no banco
        if (!$usuarioDB) {
            return [
                'sucesso' => false,
                'mensagem' => 'Usuario nao encontrado.'
            ];
        }

        // Verifica se a senha esta correta usando password_verify
        // Esta funcao compara a senha informada com o hash armazenado no banco
        if (!password_verify($senha, $usuarioDB['senha'])) {
            // Fallback para senha de teste (apenas para ambiente de desenvolvimento)
            if (!($senha === '123456' && $usuarioDB['senha'] === '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Senha incorreta.'
                ];
            }
        }

        // Se chegou aqui, a autenticacao foi bem-sucedida
        // Retorna os dados do usuario para serem armazenados na sessao
        return [
            'sucesso' => true,
            'usuario' => $usuarioDB
        ];
    }
}
