<?php

namespace app\database;

use Exception;
use PDO;

class ConnectionFactory
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        error_log("[ConnectionFactory] getConnection() called");

        if (self::$connection !== null) {
            return self::$connection;
        }

        try {
            // 1. Primeiro tenta conectar normalmente ao banco
            $dsn = "mysql:host=" . DB_HOST .
                   ";port=" . DB_PORT .
                   ";dbname=" . DB_NAME .
                   ";charset=utf8mb4";

            self::$connection = self::createConnection($dsn);

            error_log("[ConnectionFactory] Banco encontrado: " . DB_NAME);

        } catch (Exception $e) {

            error_log(
                "[ConnectionFactory] Banco não encontrado. Inicializando..."
            );

            try {
                // 2. Conecta ao MySQL SEM selecionar banco
                $dsnSemBanco = "mysql:host=" . DB_HOST .
                               ";port=" . DB_PORT .
                               ";charset=utf8mb4";

                $connection = self::createConnection($dsnSemBanco);

                // 3. Cria banco e executa script.sql
                $databaseInit = new DatabaseInitializer();
                $databaseInit->init($connection);

                // 4. Agora conecta novamente, já com o banco existente
                $dsn = "mysql:host=" . DB_HOST .
                       ";port=" . DB_PORT .
                       ";dbname=" . DB_NAME .
                       ";charset=utf8mb4";

                self::$connection = self::createConnection($dsn);

                error_log(
                    "[ConnectionFactory] Banco inicializado com sucesso!"
                );

            } catch (Exception $initException) {

                error_log(
                    "[ConnectionFactory] Erro ao inicializar banco: "
                    . $initException->getMessage()
                );

                throw new Exception(
                    "Não foi possível inicializar o banco: "
                    . $initException->getMessage()
                );
            }
        }

        return self::$connection;
    }

    private static function createConnection(string $dsn): PDO
    {
        $connection = new PDO(
            $dsn,
            DB_USER,
            DB_PASS
        );

        $connection->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $connection->setAttribute(
            PDO::ATTR_DEFAULT_FETCH_MODE,
            PDO::FETCH_ASSOC
        );

        return $connection;
    }
}