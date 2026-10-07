<?php
/**
 * DATABASE CONFIGURATION
 * Cấu hình kết nối database MySQL
 */

// =========================================================
// CẤU HÌNH DATABASE - THAY ĐỔI Ở ĐÂY KHI DEPLOY
// =========================================================

// Phát hiện môi trường InfinityFree
// (path chứa 'infinityfree.com' HOẶC domain chứa 'infinityfree')
$_dbHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$_dbDir = __DIR__;
$isInfinityFree = (
    strpos($_dbDir, 'infinityfree.com') !== false ||    // phát hiện qua đường dẫn server
    strpos($_dbHost, 'infinityfreeapp.com') !== false || // phát hiện qua domain app
    strpos($_dbHost, 'infinityfree.net') !== false
);

if ($isInfinityFree) {
    // =============================================
    // ⚠️ HOSTING INFINITYFREE - Cấu hình production
    // =============================================
    define('DB_HOST', 'sql309.infinityfree.com');
    define('DB_PORT', '3306');
    define('DB_NAME', 'qldt');
    define('DB_USER', 'if0_42542969');
    define('DB_PASS', 'thachkongsaoane');
    define('DB_CHARSET', 'utf8mb4');
} else {
    // =============================================
    // 💻 LOCALHOST - XAMPP / Local development
    // =============================================
    define('DB_HOST', '127.0.0.1'); // 127.0.0.1 ép TCP, tránh lỗi UNIX socket
    define('DB_PORT', '3306');
    define('DB_NAME', 'qldt');
    define('DB_USER', 'root');      // user mặc định XAMPP
    define('DB_PASS', '');           // mật khẩu mặc định XAMPP (rỗng)
    define('DB_CHARSET', 'utf8mb4');
}

class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        $this->connect();
    }

    /**
     * Establish a new PDO connection using config constants.
     * Called by constructor and when reconnecting after a dropped connection.
     */
    private function connect()
    {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->conn->exec("SET NAMES utf8mb4");
        } catch (PDOException $e) {
            // Trường hợp môi trường local dev chưa khởi tạo user qldt_user
            if (DB_USER !== 'root') {
                try {
                    $this->conn = new PDO($dsn, 'root', '', $options);
                    $this->conn->exec("SET NAMES utf8mb4");
                    return;
                } catch (PDOException $ex) {
                    // Giữ lỗi ban đầu nếu fallback cũng thất bại
                }
            }
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Không thể kết nối đến database: " . $e->getMessage());
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            throw new Exception("Lỗi database: " . $e->getMessage());
        }
    }

    /**
     * Force the singleton to drop the old connection and create a new one.
     */
    public function reconnect()
    {
        $this->conn = null;
        $this->connect();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        // If we have no connection, attempt to (re)connect
        if ($this->conn === null) {
            $this->connect();
        } else {
            // try a simple query to verify the connection is still alive
            try {
                $this->conn->query('SELECT 1');
            } catch (PDOException $e) {
                // MySQL server has gone away or lost connection
                if (stripos($e->getMessage(), '2006') !== false || stripos($e->getMessage(), 'gone away') !== false) {
                    error_log("Database connection lost, reconnecting...");
                    $this->connect();
                } else {
                    throw new Exception("Lỗi kết nối database: " . $e->getMessage());
                }
            }
        }
        return $this->conn;
    }

    // Prevent cloning
    private function __clone()
    {
    }

    // Prevent unserialize
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
