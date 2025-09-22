<?php

class BasedeDatos{

    public static function Conectar(){
        $host = getenv('DB_HOST');
        $dbname = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');

        try{
            $conexion = new PDO
            ("mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass
            );
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // AGREGAR: Configurar zona horaria de Guatemala
            date_default_timezone_set('America/Guatemala');
            $conexion->exec("SET time_zone = '-06:00'");
            
            return $conexion;
        }catch(PDOException $e){
            echo "❌ Error de conexión: " . $e->getMessage();
            return null; // <- importante
            //return "Error de conexion".$e->getMessage();
        }
    }
}