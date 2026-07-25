<?php

namespace app\database;

use PDO;

class DatabaseInitializer
{

    public function init(PDO $connection)
    {
        $connection->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $connection->exec("USE " . DB_NAME);

        $scriptPath = __DIR__ . '/scripts/script.sql';

        if (file_exists($scriptPath)) {
            $sql = file_get_contents($scriptPath);
            
            try {
                // Split by semicolon and execute each statement separately
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $connection->exec($statement);
                    }
                }
            } catch (\Exception $e) {
                error_log("Erro ao inicializar banco de dados: " . $e->getMessage());
                throw $e;
            }
        } else {
            throw new \Exception("Script SQL não encontrado em: $scriptPath");
        }
    }
}
