<?php
/**
 * BASE MODEL
 * Model cơ sở cho tất cả các model khác kế thừa
 */

class BaseModel {
    protected $db;
    protected $table;
    private static $columnCache = [];

    protected function validateTable($table) {
        $tables = ['nguoi_dung','vai_tro','sinh_vien','giang_vien','lanh_dao','de_tai','dang_ky_de_tai',
            'thong_bao','thong_bao_do_an_content','thong_bao_do_an_notice','thong_bao_do_an_timeline',
            'cai_dat','lich_su_duyet_de_tai'];
        if (!in_array($table, $tables, true)) { throw new InvalidArgumentException('Unknown model table.'); }
        return $table;
    }

    protected function validateColumns($table, $columns) {
        $this->validateTable($table);
        if (!isset(self::$columnCache[$table])) {
            self::$columnCache[$table] = $this->db->query('SHOW COLUMNS FROM `' . $table . '`')->fetchAll(PDO::FETCH_COLUMN);
        }
        foreach ($columns as $column) {
            if (!is_string($column) || !in_array($column, self::$columnCache[$table], true)) {
                throw new InvalidArgumentException('Unknown model column.');
            }
        }
    }

    protected function validateOrder($orderBy) {
        foreach (explode(',', $orderBy) as $part) {
            if (!preg_match('/^\s*([a-zA-Z_][a-zA-Z0-9_]*)(?:\s+(ASC|DESC))?\s*$/i', $part, $match)) {
                throw new InvalidArgumentException('Invalid sort expression.');
            }
            $this->validateColumns($this->table, [$match[1]]);
        }
        return $orderBy;
    }
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Find by ID with custom table
     */
    public function findById($id, $customTable = null) {
        $table = $customTable ?: $this->table;
        $this->validateTable($table);
        $sql = "SELECT * FROM {$table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Find all
     */
    public function findAll($orderBy = 'id DESC') {
        $this->validateOrder($orderBy);
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Find by condition
     */
    public function findBy($conditions, $orderBy = 'id DESC', $limit = null) {
        $this->validateColumns($this->table, array_keys($conditions));
        $this->validateOrder($orderBy);
        if ($limit !== null && (!is_numeric($limit) || (int)$limit < 1)) { throw new InvalidArgumentException('Invalid limit.'); }
        $limit = $limit === null ? null : (int)$limit;
        $where = [];
        $params = [];
        
        foreach ($conditions as $key => $value) {
            $where[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) . " ORDER BY {$orderBy}";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Find one by condition
     */
    public function findOneBy($conditions) {
        $result = $this->findBy($conditions, 'id DESC', 1);
        return $result ? $result[0] : null;
    }
    
    /**
     * Insert
     */
    public function insert($data) {
        $this->validateColumns($this->table, array_keys($data));
        $fields = array_keys($data);
        $values = array_values($data);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (:" . implode(', :', $fields) . ")";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Update with custom table
     */
    public function update($id, $data, $customTable = null) {
        $table = $customTable ?: $this->table;
        $this->validateColumns($table, array_keys($data));
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "{$field} = :{$field}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $fields) . " WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    /**
     * Delete
     */
    public function delete($id) {
        $this->validateTable($this->table);
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Count
     */
    public function count($conditions = []) {
        $this->validateColumns($this->table, array_keys($conditions));
        if (empty($conditions)) {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->query($sql);
        } else {
            $where = [];
            $params = [];
            
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = :{$key}";
                $params[$key] = $value;
            }
            
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE " . implode(' AND ', $where);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }
        
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Execute custom query
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute custom query (single result)
     */
    public function queryOne($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
