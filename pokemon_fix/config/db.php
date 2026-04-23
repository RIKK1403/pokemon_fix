<?php
date_default_timezone_set('Asia/Jakarta');

class Database {
    private $host = '127.0.0.1';
    private $dbname = 'pokemon_fix';
    private $username = 'root';
    private $password = '';
    private $pdo;

    public function getConnection() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
                $this->pdo = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['error' => 'Database connection failed']));
            }
        }
        return $this->pdo;
    }
}
?>