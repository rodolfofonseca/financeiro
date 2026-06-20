<?php
require_once 'configuracao.php';
/**
 * Função responsávle por abrir a conexão com o banco de dados
 * @return PDO
 */
function conectarPostgres(): PDO {
    $dsn = "pgsql:host=".HOST.";port=".PORTA.";dbname=".BANCO;

    return new PDO(
        $dsn,
        USUARIO,
        SENHA,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}
?>