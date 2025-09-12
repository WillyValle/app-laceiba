<?php
class UserEmployee{
    private $pdo;

    private $id_user_employee;
    private $employee_id;
    private $username;
    private $password;
    private $force_password_change;
    private $status;

    public function __CONSTRUCT(){
        $this->pdo = BasedeDatos::Conectar();
    }

    // Métodos GET y SET (mantener todos los existentes)
    public function getIdUserEmployee(): ?int {
        return $this->id_user_employee;
    }

    public function setIdUserEmployee(int $id_user_employee): void {
        $this->id_user_employee = $id_user_employee;
    }

    public function getEmployeeId(): ?int {
        return $this->employee_id;
    }

    public function setEmployeeId(int $employee_id): void {
        $this->employee_id = $employee_id;
    }

    public function getUsername(): ?string {
        return $this->username;
    }

    public function setUsername(string $username): void {
        $this->username = $username;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getForcePasswordChange(): ?int {
        return $this->force_password_change;
    }

    public function setForcePasswordChange(bool $force_password_change): void {
        $this->force_password_change = $force_password_change;
    }

    public function getStatus(): ?int {
        return $this->status;
    }

    public function setStatus(bool $status): void {
        $this->status = $status;
    }

    public function Listar(){
        try{
            $consulta = $this->pdo->prepare("
            SELECT
                ue.id_user_employee,
                CONCAT(
                e.name_employee,
                ' ',
                e.lastname_employee
                ) AS employee_name,
                ue.username,
                ue.password_hash,
                ue.force_password_change,
                ue.status
            FROM USER_EMPLOYEE ue
            INNER JOIN EMPLOYEE e ON ue.employee_id_employee = e.id_employee
            ");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Obtener($id){
        try{
            $consulta = $this->pdo->prepare("
            SELECT
                ue.id_user_employee,
                ue.employee_id_employee,
                CONCAT(
                e.name_employee,
                ' ',
                e.lastname_employee
                ) AS employee_name,
                ue.username,
                ue.password_hash,
                ue.force_password_change,
                ue.status
            FROM USER_EMPLOYEE ue
            INNER JOIN EMPLOYEE e ON ue.employee_id_employee = e.id_employee
            WHERE ue.id_user_employee = ?
            ");
            $consulta->execute(array($id));
            $r = $consulta->fetch(PDO::FETCH_OBJ);
            if ($r) {
                $this->id_user_employee = $r->id_user_employee;
                $this->employee_id = $r->employee_id_employee;
                $this->username = $r->username;
                $this->password = $r->password_hash;
                $this->force_password_change = $r->force_password_change;
                $this->status = $r->status;
            }
            return $r;
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Actualizar(UserEmployee $useremployee){
        try{
            $consulta = "
            UPDATE USER_EMPLOYEE SET
                employee_id_employee = ?,
                username = ?,
                password_hash = ?,
                force_password_change = ?,
                status = ?
            WHERE id_user_employee = ?
            ";
            $this->pdo->prepare($consulta)->execute(array(
                $useremployee->getEmployeeId(),
                $useremployee->getUsername(),
                password_hash($useremployee->getPassword(), PASSWORD_BCRYPT),
                $useremployee->getForcePasswordChange() ? 1 : 0,
                $useremployee->getStatus() ? 1 : 0,
                $useremployee->getIdUserEmployee()
            ));
        }catch(Exception $e){
            die($e->getMessage());
        }

    }

    public function CambiarPassword($username, $newPassword) {
    try {
        $stmt = $this->pdo->prepare("CALL sp_change_employee_password(?, ?)");
        $stmt->execute([$username, $newPassword]);
    } catch (Exception $e) {
        throw new Exception("Error al cambiar la contraseña: " . $e->getMessage());
    }
}

public function ActualizarFlagCambioPassword($idUser, $forceChange) {
    try {
        $stmt = $this->pdo->prepare("
            UPDATE USER_EMPLOYEE 
            SET FORCE_PASSWORD_CHANGE = ? 
            WHERE ID_USER_EMPLOYEE = ?
        ");
        $stmt->execute([$forceChange, $idUser]);
    } catch (Exception $e) {
        throw new Exception("Error al actualizar el flag de cambio de contraseña: " . $e->getMessage());
    }
}

}