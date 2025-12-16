<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $conn;
    private $error;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->dbname,
                $this->user,
                $this->pass,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            die("Connection failed: " . $this->error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    public function insert($table, $data) {
        // Add timestamps if not present
        if (!isset($data['created_at']) && $this->hasColumn($table, 'created_at')) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at']) && $this->hasColumn($table, 'updated_at')) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        return $this->query($sql, array_values($data));
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        // Add updated_at timestamp
        if (!isset($data['updated_at']) && $this->hasColumn($table, 'updated_at')) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = ?";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        
        return $this->query($sql, $params);
    }
    
    private function hasColumn($table, $column) {
        static $cache = [];
        $key = "{$table}.{$column}";
        
        if (!isset($cache[$key])) {
            $sql = "SHOW COLUMNS FROM {$table} LIKE ?";
            $result = $this->fetchOne($sql, [$column]);
            $cache[$key] = !empty($result);
        }
        
        return $cache[$key];
    }
}
?>
