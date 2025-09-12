<?php
require_once "app/modelos/useremployee.php";

class UserEmployeeControlador{
    private $modelo;

    public function __CONSTRUCT(){
        $this->modelo = new UserEmployee();
    }

    public function Inicio(){
        require_once "app/vistas/header.php";
        require_once "app/vistas/useremployee/listuseremployee.php";
        require_once "app/vistas/footer.php";
    }

    public function FormEditar(){
        $datos = $this->modelo->Obtener($_GET['id']);
        require_once "app/vistas/header.php";
        require_once "app/vistas/useremployee/formuseremployee.php";
        require_once "app/vistas/footer.php";
    }

    public function Guardar() {
    try {
        if (!isset($_POST['id_user_employee']) || empty($_POST['id_user_employee'])) {
            throw new Exception("Operación no permitida: el usuario debe existir.");
        }

        $idUser = (int)$_POST['id_user_employee'];
        $forceChange = isset($_POST['force_password_change']) ? (int)$_POST['force_password_change'] : 0;

        // Obtener el usuario actual para extraer el username
        $usuario = $this->modelo->Obtener($idUser);
        if (!$usuario || empty($usuario->username)) {
            throw new Exception("Usuario no encontrado.");
        }

        if (!empty($_POST['password'])) {
            // Si hay nueva contraseña, usar el procedimiento que también pone force_change en 0
            $this->modelo->CambiarPassword($usuario->username, $_POST['password']);
        } else {
            // Si no hay nueva contraseña, actualizar solo el flag
            $this->modelo->ActualizarFlagCambioPassword($idUser, $forceChange);
        }

        header("Location: index.php?c=useremployee");
    } catch (Exception $e) {
        die("Error al guardar: " . $e->getMessage());
    }
}
}