<?php

namespace PharmaFEFO\config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection()
    {
        if(self::$pdo === null){

            $env = parse_ini_file("../.env");

            $host = $env['DB_HOST'];
            $dbname = $env['DB_NAME'];
            $user = $env['DB_USER'];
            $password = $env['DB_PASSWORD'];

            try{

                self::$pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname",
                    $user,
                    $password
                );

                self::$pdo->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION
                );

            }catch(PDOException $e){

                die("Connection failed : " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}