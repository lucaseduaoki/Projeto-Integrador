<?php

namespace app\database;

use PDO;

class DatabaseInitializer
{

    public function init(PDO $connection)
    {   
        error_log("[DatabaseInitializer] 🙃​🙃​🙃​🙃​tentnado inicializar o banco de dados");
        $connection->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $connection->exec("USE " . DB_NAME);

        $scriptPath = __DIR__ . '/scripts/script.sql';
        if (file_exists($scriptPath)) {
            error_log("O arquivo existe: $scriptPath");
            $sql = file_get_contents($scriptPath);
            
            try {
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
