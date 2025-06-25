<?php
    namespace Amadev\CrudOperations;
    use Dotenv\Dotenv;
    try{
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        $dotenv = Dotenv::createImmutable(dirname(__DIR__)); // Adjusted path
        $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_NAME', 'TENANT_ID', 'DB_USERNAME', 'DB_PASSWORD']);
    }
    catch(\Exception $e)
    {
        echo $e->getMessage();
    }
    class crud_op
    {
        private $pdo;
        private $table_name;
        private $user_id;

        public function __construct($table_name) {
            $dsn = 'mysql:host='.$_ENV['DB_HOST'].';dbname='.$_ENV['DB_NAME'].';charset=utf8mb4';
            try {
                $this->pdo = new \PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
                $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                // Handle connection error
                die("Connection failed: " . $e->getMessage());
            }

            $this->table_name = $table_name;
            $this->user_id = $_SESSION['user_id'] ?? null;
        }

        public function selectDistinct($column, $conditions = [], $values = []): array {
            $sql = "SELECT DISTINCT {$column} FROM {$this->table_name}";
        
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
        
            try {
                $stmt = $this->pdo->prepare($sql);
                foreach ($values as $key => $val) {
                    $stmt->bindValue($key, $val);
                }
                $stmt->execute();
            } catch (\PDOException $e) {
                die("Error: " . $e->getMessage());
            }
        
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        public function select($conditions = [], $values = []): array {
            $sql = "SELECT * FROM {$this->table_name}";
        
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
        
            try {
                $stmt = $this->pdo->prepare($sql);
                foreach ($values as $key => $val) {
                    $stmt->bindValue($key, $val);
                }
                $stmt->execute();
            } catch (\PDOException $e) {
                die("Error: " . $e->getMessage());
            }
        
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        public function insert($data): bool|string {
            $columns = implode(',', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO {$this->table_name} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            $lastInsertedId = $this->pdo->lastInsertId();
            return $lastInsertedId;
        }

        public function update($id, $data): bool {
            $set = "";
            foreach ($data as $column => $value) {
                $set .= "{$column} = :{$column},";
            }
            $set = rtrim($set, ",");
            $sql = "UPDATE {$this->table_name} SET {$set} WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $data['id'] = $id;
            $stmt->execute($data);
            return true;
        }

        public function delete($id): bool {
            $sql = "DELETE FROM {$this->table_name} WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            return true;
        }

        public function setTable($table) {
            $this->table_name = $table;
        }

        public function closeConnection() {
            $this->pdo = null;
        }
    }