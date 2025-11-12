<?php
/**
 * Configuração de conexão com o banco de dados
 * Sistema de Gestão de Estoque para Mercado
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Configurações do banco de dados
    private $host = 'localhost';
    private $database = 'mercado_db';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';

    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new PDOException('Erro na conexão com o banco de dados: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Previne clonagem da instância
    private function __clone() {}

    // Previne deserialização da instância
    public function __wakeup() {}
}
?>