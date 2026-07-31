<?php
// config/db.php - Smart connection manager supporting MySQL and SQLite fallback (Vercel compatible)

$use_sqlite = false;

// Auto-switch to SQLite on Vercel environment or explicit fallback query
if (getenv('VERCEL') || isset($_GET['sqlite_test'])) {
    $use_sqlite = true;
}

if (!$use_sqlite) {
    // Attempt MySQL connection
    $conn = @mysqli_connect("sql200.infinityfree.com", "if0_41084179", "00hm647j", "if0_41084179_simple_blog");
    if (!$conn) {
        $use_sqlite = true;
    } else {
        mysqli_set_charset($conn, "utf8mb4");
    }
}

if ($use_sqlite) {
    // SQLite configuration & database initialization
    $db_path = getenv('VERCEL') ? '/tmp/database_v4.sqlite' : dirname(__DIR__) . '/database_v4.sqlite';
    $is_new = !file_exists($db_path);

    try {
        $conn = new PDO("sqlite:" . $db_path);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($is_new) {
            $schema_file = dirname(__DIR__) . '/database.sql';
            if (file_exists($schema_file)) {
                $sql = file_get_contents($schema_file);
                
                // Adapt MySQL DDL to SQLite syntax
                $sql = preg_replace('/CREATE DATABASE[^;]*;/i', '', $sql);
                $sql = preg_replace('/USE [^;]*;/i', '', $sql);
                $sql = preg_replace('/INT AUTO_INCREMENT PRIMARY KEY/i', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
                $sql = preg_replace('/\bINT\b/i', 'INTEGER', $sql);
                $sql = preg_replace('/TIMESTAMP DEFAULT CURRENT_TIMESTAMP/i', 'DATETIME DEFAULT CURRENT_TIMESTAMP', $sql);
                $sql = str_replace("\\'", "''", $sql);

                $conn->exec($sql);
            }
        }
    } catch (Exception $e) {
        die("Database Connection Error: " . $e->getMessage());
    }
}

// Unified my_db_* wrapper functions for MySQL / SQLite compatibility

function my_db_connect() {
    global $conn;
    return $conn;
}

function my_db_connect_error() {
    global $use_sqlite;
    return $use_sqlite ? null : mysqli_connect_error();
}

function my_db_set_charset($conn, $charset) {
    global $use_sqlite;
    return $use_sqlite ? true : mysqli_set_charset($conn, $charset);
}

function my_db_real_escape_string($conn, $string) {
    global $use_sqlite;
    if ($use_sqlite) {
        return str_replace("'", "''", $string);
    }
    return mysqli_real_escape_string($conn, $string);
}

class SQLiteResult {
    public $rows = [];
    private $index = 0;
    public $num_rows = 0;

    public function __construct($stmt) {
        if ($stmt) {
            $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->num_rows = count($this->rows);
        }
    }

    public function fetch_assoc() {
        if ($this->index < $this->num_rows) {
            return $this->rows[$this->index++];
        }
        return null;
    }

    public function data_seek($offset) {
        if ($offset >= 0 && $offset < $this->num_rows) {
            $this->index = $offset;
            return true;
        }
        return false;
    }
}

function my_db_query($conn, $query) {
    global $use_sqlite;
    if ($use_sqlite) {
        try {
            // SHOW COLUMNS emulation for SQLite
            if (preg_match('/SHOW COLUMNS FROM `?(\w+)`? LIKE \'?([\w%]+)\'?/i', $query, $matches)) {
                $table = $matches[1];
                $column = str_replace('%', '', $matches[2]);
                $stmt = $conn->query("PRAGMA table_info({$table})");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $found = false;
                foreach ($rows as $row) {
                    if ($row['name'] === $column) {
                        $found = true;
                        break;
                    }
                }
                $dummy = new SQLiteResult(null);
                if ($found) {
                    $dummy->num_rows = 1;
                    $dummy->rows = [['Field' => $column]];
                }
                return $dummy;
            }

            if (preg_match('/ALTER TABLE `?(\w+)`? ADD COLUMN `?(\w+)`?[^,)]*/i', $query)) {
                $conn->exec($query);
                return true;
            }

            $stmt = $conn->query($query);
            return new SQLiteResult($stmt);
        } catch (Exception $e) {
            $GLOBALS['last_sqlite_error'] = $e->getMessage();
            return false;
        }
    }
    return mysqli_query($conn, $query);
}

function my_db_num_rows($result) {
    global $use_sqlite;
    if ($use_sqlite) {
        return ($result instanceof SQLiteResult) ? $result->num_rows : 0;
    }
    return mysqli_num_rows($result);
}

function my_db_fetch_assoc($result) {
    global $use_sqlite;
    if ($use_sqlite) {
        return ($result instanceof SQLiteResult) ? $result->fetch_assoc() : null;
    }
    return mysqli_fetch_assoc($result);
}

function my_db_data_seek($result, $offset) {
    global $use_sqlite;
    if ($use_sqlite) {
        return ($result instanceof SQLiteResult) ? $result->data_seek($offset) : false;
    }
    return mysqli_data_seek($result, $offset);
}

class SQLiteStmt {
    private $pdo;
    private $sql;
    private $stmt;
    private $params = [];
    public $insert_id = 0;

    public function __construct($pdo, $sql) {
        $this->pdo = $pdo;
        $this->sql = $sql;
    }

    public function bind_param($types, &...$vars) {
        $this->params = $vars;
        return true;
    }

    public function execute() {
        try {
            $this->stmt = $this->pdo->prepare($this->sql);
            foreach ($this->params as $i => $val) {
                $type = PDO::PARAM_STR;
                if (is_int($val)) $type = PDO::PARAM_INT;
                elseif (is_bool($val)) $type = PDO::PARAM_BOOL;
                elseif (is_null($val)) $type = PDO::PARAM_NULL;
                
                $this->stmt->bindValue($i + 1, $val, $type);
            }
            $res = $this->stmt->execute();
            $this->insert_id = $this->pdo->lastInsertId();
            return $res;
        } catch (Exception $e) {
            $GLOBALS['last_sqlite_error'] = $e->getMessage();
            return false;
        }
    }

    public function get_result() {
        return new SQLiteResult($this->stmt);
    }

    public function close() {
        $this->stmt = null;
        return true;
    }
}

function my_db_prepare($conn, $query) {
    global $use_sqlite;
    if ($use_sqlite) {
        return new SQLiteStmt($conn, $query);
    }
    return mysqli_prepare($conn, $query);
}

function my_db_stmt_bind_param($stmt, $types, &...$vars) {
    global $use_sqlite;
    if ($use_sqlite) {
        return $stmt->bind_param($types, ...$vars);
    }
    return mysqli_stmt_bind_param($stmt, $types, ...$vars);
}

function my_db_stmt_execute($stmt) {
    global $use_sqlite;
    if ($use_sqlite) {
        return $stmt->execute();
    }
    return mysqli_stmt_execute($stmt);
}

function my_db_stmt_get_result($stmt) {
    global $use_sqlite;
    if ($use_sqlite) {
        return $stmt->get_result();
    }
    return mysqli_stmt_get_result($stmt);
}

function my_db_stmt_close($stmt) {
    global $use_sqlite;
    if ($use_sqlite) {
        return $stmt->close();
    }
    return mysqli_stmt_close($stmt);
}

function my_db_error($conn) {
    global $use_sqlite;
    if ($use_sqlite) {
        return $GLOBALS['last_sqlite_error'] ?? '';
    }
    return mysqli_error($conn);
}

function my_db_insert_id($conn) {
    global $use_sqlite;
    if ($use_sqlite) {
        return $conn->lastInsertId();
    }
    return mysqli_insert_id($conn);
}
