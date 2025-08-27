<?php

class BasedeDatos{
    /*const servidor = "mysql";
    const usuariobd = "USR_APP_LACEIBA_DB";
    const clave = "L7IOeIRN3LPkt";
    const nombrebd = "APP_La_Ceiba_DB";*/

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
            return $conexion;
        }catch(PDOException $e){
            echo "❌ Error de conexión: " . $e->getMessage();
            return null; // <- importante
            //return "Error de conexion".$e->getMessage();
        }
    }
}