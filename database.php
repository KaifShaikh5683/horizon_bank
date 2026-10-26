
<?php

class Database
{
    private string $host = "localhost";
    private string $userName = "root";
    private string $password = "kaif5683968";
    private string $database = "horizon_bank";

    public function connect(): mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $connection = new mysqli(
                $this->host,
                $this->userName,
                $this->password,
                $this->database
            );

            $connection->set_charset("utf8mb4");

            echo "Database connected successfully!\n";

            return $connection;

        } catch (mysqli_sql_exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}
