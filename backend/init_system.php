<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicialización del Sistema - APP La Ceiba</title>
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <b>APP</b> La Ceiba
    </div>
    
    <div class="card">
        <div class="card-body">
            <h4 class="text-center">Inicialización del Sistema</h4>
            
            <?php
            if (isset($_GET['action']) && $_GET['action'] === 'init') {
                echo "<div class='alert alert-info'>Inicializando sistema...</div>";
                
                try {
                    require_once "app/modelos/database.php";
                    require_once "app/modelos/permission.php";
                    require_once "app/modelos/rolepermission.php";
                    
                    // 1. Crear tablas de permisos si no existen
                    $pdo = BasedeDatos::Conectar();
                    
                    // Verificar si las tablas existen
                    $check_permission = $pdo->query("SHOW TABLES LIKE 'PERMISSION'");
                    $check_role_permission = $pdo->query("SHOW TABLES LIKE 'ROLE_PERMISSION'");
                    
                    if ($check_permission->rowCount() == 0) {
                        echo "<p>✓ Creando tabla PERMISSION...</p>";
                        $pdo->exec("
                            CREATE TABLE PERMISSION (
                              ID_PERMISSION INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                              NAME_PERMISSION VARCHAR(50) NOT NULL UNIQUE,
                              DESCRIPTION VARCHAR(100) DEFAULT NULL,
                              STATUS TINYINT NOT NULL DEFAULT 1
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
                        ");
                    }
                    
                    if ($check_role_permission->rowCount() == 0) {
                        echo "<p>✓ Creando tabla ROLE_PERMISSION...</p>";
                        $pdo->exec("
                            CREATE TABLE ROLE_PERMISSION (
                              ROLE_ID INT UNSIGNED NOT NULL,
                              PERMISSION_ID INT UNSIGNED NOT NULL,
                              PRIMARY KEY (ROLE_ID, PERMISSION_ID),
                              FOREIGN KEY (ROLE_ID) REFERENCES ROLE_EMPLOYEE(ID_ROLE_EMPLOYEE),
                              FOREIGN KEY (PERMISSION_ID) REFERENCES PERMISSION(ID_PERMISSION)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
                        ");
                    }
                    
                    // 2. Inicializar permisos
                    echo "<p>✓ Inicializando permisos básicos...</p>";
                    $permissionModel = new Permission();
                    $permissionModel->InicializarPermisosBasicos();
                    
                    // 3. Configurar permisos por defecto
                    echo "<p>✓ Configurando permisos por defecto...</p>";
                    $rolePermissionModel = new RolePermission();
                    $rolePermissionModel->ConfigurarPermisosDefecto();
                    
                    echo "<div class='alert alert-success mt-3'>";
                    echo "<h5>✅ Sistema inicializado correctamente</h5>";
                    echo "<p>El sistema de permisos está listo para usar.</p>";
                    echo "</div>";
                    
                    echo "<a href='?c=auth' class='btn btn-primary btn-block mt-3'>Ir al Login</a>";
                    
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>";
                    echo "<h5>❌ Error durante la inicialización</h5>";
                    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
                    echo "</div>";
                }
            } else {
            ?>
                <p class="text-center">Este script configurará el sistema de permisos por primera vez.</p>
                
                <div class="alert alert-warning">
                    <h5>⚠️ Advertencia</h5>
                    <p>Ejecute este script solo una vez y asegúrese de que:</p>
                    <ul>
                        <li>La base de datos esté configurada correctamente</li>
                        <li>Las tablas principales ya existan</li>
                        <li>Tenga una copia de seguridad de la base de datos</li>
                    </ul>
                </div>
                
                <a href="?action=init" class="btn btn-primary btn-block">Inicializar Sistema</a>
                <a href="?c=auth" class="btn btn-secondary btn-block mt-2">Ir al Login</a>
            <?php
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>