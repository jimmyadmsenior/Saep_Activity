<?php
require_once dirname(__FILE__) . '/../config/database.php';

/**
 * Model para gerenciamento de usuários
 * Responsável por operações CRUD na tabela usuarios
 */
class Usuario {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Autentica um usuário com base no usuário e senha
     * @param string $usuario
     * @param string $senha
     * @return array|false Dados do usuário ou false se não autenticado
     */
    public function autenticar($usuario, $senha) {
        try {
            $sql = "SELECT id, nome, usuario, senha FROM usuarios WHERE usuario = :usuario";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($senha, $user['senha'])) {
                // Remove a senha do array retornado por segurança
                unset($user['senha']);
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Erro na autenticação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca um usuário pelo ID
     * @param int $id
     * @return array|false
     */
    public function buscarPorId($id) {
        try {
            $sql = "SELECT id, nome, usuario, data_cadastro FROM usuarios WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista todos os usuários
     * @return array
     */
    public function listarTodos() {
        try {
            $sql = "SELECT id, nome, usuario, data_cadastro FROM usuarios ORDER BY nome";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao listar usuários: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cadastra um novo usuário
     * @param array $dados
     * @return bool
     */
    public function cadastrar($dados) {
        try {
            // Validação básica
            if (empty($dados['nome']) || empty($dados['usuario']) || empty($dados['senha'])) {
                return false;
            }

            // Verificar se o usuário já existe
            $sql = "SELECT id FROM usuarios WHERE usuario = :usuario";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario', $dados['usuario'], PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return false; // Usuário já existe
            }

            // Hash da senha
            $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);

            // Inserir novo usuário
            $sql = "INSERT INTO usuarios (nome, usuario, senha) VALUES (:nome, :usuario, :senha)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nome', $dados['nome'], PDO::PARAM_STR);
            $stmt->bindParam(':usuario', $dados['usuario'], PDO::PARAM_STR);
            $stmt->bindParam(':senha', $senhaHash, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao cadastrar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza dados de um usuário
     * @param int $id
     * @param array $dados
     * @return bool
     */
    public function atualizar($id, $dados) {
        try {
            $sql = "UPDATE usuarios SET nome = :nome, usuario = :usuario";
            $params = [
                ':id' => $id,
                ':nome' => $dados['nome'],
                ':usuario' => $dados['usuario']
            ];

            // Se uma nova senha foi fornecida, incluir no update
            if (!empty($dados['senha'])) {
                $sql .= ", senha = :senha";
                $params[':senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove um usuário
     * @param int $id
     * @return bool
     */
    public function deletar($id) {
        try {
            $sql = "DELETE FROM usuarios WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao deletar usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se um usuário existe
     * @param string $usuario
     * @return bool
     */
    public function usuarioExiste($usuario) {
        try {
            $sql = "SELECT id FROM usuarios WHERE usuario = :usuario";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar usuário: " . $e->getMessage());
            return false;
        }
    }
}
?>