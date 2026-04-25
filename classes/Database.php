<?php
/**
 * Database Helper Class
 * Provides abstraction layer for database operations
 */

class Database {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Execute query
     */
    public function query($sql) {
        $result = $this->conn->query($sql);
        
        if (!$result) {
            throw new Exception('Database Query Error: ' . $this->conn->error);
        }
        
        return $result;
    }
    
    /**
     * Prepared statement
     */
    public function prepare($sql) {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Prepared Statement Error: ' . $this->conn->error);
        }
        
        return $stmt;
    }
    
    /**
     * Get single row
     */
    public function get_row($sql) {
        $result = $this->query($sql);
        return $result->fetch_assoc();
    }
    
    /**
     * Get all rows
     */
    public function get_results($sql) {
        $result = $this->query($sql);
        $rows = [];
        
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    /**
     * Insert record
     */
    public function insert($table, $data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $column_list = implode(', ', array_map(function($col) {
            return '`' . $col . '`';
        }, $columns));
        
        $sql = "INSERT INTO `{$table}` ({$column_list}) VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->prepare($sql);
        
        // Determine types
        $types = '';
        foreach ($values as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        
        // Bind parameters
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            throw new Exception('Insert Error: ' . $stmt->error);
        }
        
        return $this->conn->insert_id;
    }
    
    /**
     * Update record
     */
    public function update($table, $data, $where) {
        $set_clauses = [];
        $all_values = [];
        
        foreach ($data as $column => $value) {
            $set_clauses[] = '`' . $column . '` = ?';
            $all_values[] = $value;
        }
        
        $where_clauses = [];
        foreach ($where as $column => $value) {
            $where_clauses[] = '`' . $column . '` = ?';
            $all_values[] = $value;
        }
        
        $sql = "UPDATE `{$table}` SET " . implode(', ', $set_clauses);
        
        if (!empty($where_clauses)) {
            $sql .= " WHERE " . implode(' AND ', $where_clauses);
        }
        
        $stmt = $this->prepare($sql);
        
        // Determine types
        $types = '';
        foreach ($all_values as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        
        $stmt->bind_param($types, ...$all_values);
        
        if (!$stmt->execute()) {
            throw new Exception('Update Error: ' . $stmt->error);
        }
        
        return $stmt->affected_rows;
    }
    
    /**
     * Delete record
     */
    public function delete($table, $where) {
        $where_clauses = [];
        $values = [];
        
        foreach ($where as $column => $value) {
            $where_clauses[] = '`' . $column . '` = ?';
            $values[] = $value;
        }
        
        $sql = "DELETE FROM `{$table}` WHERE " . implode(' AND ', $where_clauses);
        
        $stmt = $this->prepare($sql);
        
        // Determine types
        $types = '';
        foreach ($values as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } else {
                $types .= 's';
            }
        }
        
        $stmt->bind_param($types, ...$values);
        
        if (!$stmt->execute()) {
            throw new Exception('Delete Error: ' . $stmt->error);
        }
        
        return $stmt->affected_rows;
    }
    
    /**
     * Get count
     */
    public function count($table, $where = []) {
        $sql = "SELECT COUNT(*) as count FROM `{$table}`";
        
        if (!empty($where)) {
            $conditions = [];
            $values = [];
            
            foreach ($where as $column => $value) {
                $conditions[] = '`' . $column . '` = ?';
                $values[] = $value;
            }
            
            $sql .= " WHERE " . implode(' AND ', $conditions);
            
            $stmt = $this->prepare($sql);
            
            // Determine types
            $types = '';
            foreach ($values as $value) {
                if (is_int($value)) {
                    $types .= 'i';
                } else {
                    $types .= 's';
                }
            }
            
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->query($sql);
        }
        
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    /**
     * Get connection
     */
    public function get_connection() {
        return $this->conn;
    }
}

?>
