<?php

class Employee{
    private $pdo;

    private $id_employee;
    private $name_employee;
    private $lastname_employee;
    private $address_employee;
    private $type_doc;
    private $doc_num;
    private $path_img;
    private $role_employee;
    private $whatsapp;
    private $tel;
    private $mail;
    private $status;

    public function __CONSTRUCT(){
        $this->pdo = BasedeDatos::Conectar();
    }

    // Métodos GET y SET (mantener todos los existentes)
    public function getIdEmployee(): ?int {
        return $this->id_employee;
    }
    public function setIdEmployee(int $id): void {
        $this->id_employee = $id;
    }
    public function getNameEmployee(): ?string {
        return $this->name_employee;
    }
    public function setNameEmployee(string $name): void {
        $this->name_employee = $name;
    }
    public function getLastnameEmployee(): ?string {
        return $this->lastname_employee;
    }
    public function setLastnameEmployee(string $lastname): void {
        $this->lastname_employee = $lastname;
    }
    public function getAddressEmployee(): ?string {
        return $this->address_employee;
    }
    public function setAddressEmployee(string $address): void {
        $this->address_employee = $address;
    }
    public function getTypeDoc(): ?string {
        return $this->type_doc;
    }
    public function setTypeDoc(string $type): void {
        $this->type_doc = $type;
    }
    public function getDocNum(): ?string {
        return $this->doc_num;
    }
    public function setDocNum(string $doc): void {
        $this->doc_num = $doc;
    }
    public function getPathImg(): ?string {
        return $this->path_img;
    }
    public function setPathImg(?string $path): void {
        $this->path_img = $path;
    }
    public function getRoleEmployee(): ?string {
        return $this->role_employee;
    }
    public function setRoleEmployee(string $role): void {
        $this->role_employee = $role;
    }
    public function getWhatsapp(): ?string {
        return $this->whatsapp;
    }
    public function setWhatsapp(string $whatsapp): void {
        $this->whatsapp = $whatsapp;
    }
    public function getTel(): ?string {
        return $this->tel;
    }
    public function setTel(string $tel): void {
        $this->tel = $tel;
    }
    public function getMail(): ?string {
        return $this->mail;
    }
    public function setMail(string $mail): void {
        $this->mail = $mail;
    }
    public function getStatus(): ?int {
        return $this->status;
    }
    public function setStatus(bool $status): void {
        $this->status = $status ? 1 : 0;
    }

    public function Listar(){
        try{
            $consulta = $this->pdo->prepare("
            SELECT
                e.id_employee,
                e.name_employee,
                e.lastname_employee,
                e.address_employee,
                e.type_doc_id_type_doc,
                td.name_type_doc,
                e.doc_num,
                e.path_img_employee,
                e.role_employee_id_role_employee,
                re.name_role_employee,
                e.whatsapp,
                e.tel,
                e.mail,
                e.status
            FROM EMPLOYEE e
            INNER JOIN TYPE_DOC td ON e.type_doc_id_type_doc = td.id_type_doc
            INNER JOIN ROLE_EMPLOYEE re ON e.role_employee_id_role_employee = re.id_role_employee
            ORDER BY e.name_employee
            ");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function ListarTipoDoc(){
        try{
            $consulta = $this->pdo->prepare("SELECT * FROM TYPE_DOC WHERE STATUS = 1 ORDER BY name_type_doc");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function ListarRoles(){
        try{
            $consulta = $this->pdo->prepare("SELECT * FROM ROLE_EMPLOYEE WHERE STATUS = 1 ORDER BY name_role_employee");
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
                e.id_employee,
                e.name_employee,
                e.lastname_employee,
                e.address_employee,
                e.type_doc_id_type_doc,
                td.name_type_doc,
                e.doc_num,
                e.path_img_employee,
                e.role_employee_id_role_employee,
                re.name_role_employee,
                e.whatsapp,
                e.tel,
                e.mail,
                e.status
            FROM EMPLOYEE e
            INNER JOIN TYPE_DOC td ON e.type_doc_id_type_doc = td.id_type_doc
            INNER JOIN ROLE_EMPLOYEE re ON e.role_employee_id_role_employee = re.id_role_employee
            WHERE e.id_employee = ?
            ");
            $consulta->execute(array($id));
            return $consulta->fetch(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }

    }

    public function Insertar(Employee $employee){
        try{
            $consulta = "
            INSERT INTO EMPLOYEE 
                (name_employee, 
                lastname_employee, 
                address_employee, 
                type_doc_id_type_doc, 
                doc_num, 
                path_img_employee, 
                role_employee_id_role_employee, 
                whatsapp, 
                tel, 
                mail, 
                status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ";
            $this->pdo->prepare($consulta)
                 ->execute([
                    $employee->getNameEmployee(),
                    $employee->getLastnameEmployee(),
                    $employee->getAddressEmployee(),
                    $employee->getTypeDoc(),
                    $employee->getDocNum(),
                    $employee->getPathImg(),
                    $employee->getRoleEmployee(),
                    $employee->getWhatsapp(),
                    $employee->getTel(),
                    $employee->getMail(),
                    $employee->getStatus()
                 ]);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Actualizar(Employee $employee){
        try{
            $consulta = "
            UPDATE EMPLOYEE SET 
                name_employee = ?, 
                lastname_employee = ?, 
                address_employee = ?, 
                type_doc_id_type_doc = ?, 
                doc_num = ?, 
                path_img_employee = ?, 
                role_employee_id_role_employee = ?, 
                whatsapp = ?, 
                tel = ?, 
                mail = ?, 
                status = ? 
            WHERE id_employee = ?
            ";
            $this->pdo->prepare($consulta)
                 ->execute([
                    $employee->getNameEmployee(),
                    $employee->getLastnameEmployee(),
                    $employee->getAddressEmployee(),
                    $employee->getTypeDoc(),
                    $employee->getDocNum(),
                    $employee->getPathImg(),
                    $employee->getRoleEmployee(),
                    $employee->getWhatsapp(),
                    $employee->getTel(),
                    $employee->getMail(),
                    $employee->getStatus(),
                    $employee->getIdEmployee()
                 ]);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

}