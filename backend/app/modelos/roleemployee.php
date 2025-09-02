<?php

class RoleEmployee{

    private $pdo;

    private $ID_ROLE_EMPLOYEE;
    private $NAME_ROLE_EMPLOYEE;
    private $DESCRIPTION;
    private $STATUS;

    public function __CONSTRUCT(){
        $this->pdo = BasedeDatos::Conectar();
    }

    //Metodos GET y SET

    public function getID_ROLE_EMPLOYEE() : ?int{
        return $this->ID_ROLE_EMPLOYEE;
    }

    public function setID_ROLE_EMPLOYEE(int $ID){
        $this->ID_ROLE_EMPLOYEE = $ID;
    }

    public function getNAME_ROLE_EMPLOYEE() : ?string {
        return $this->NAME_ROLE_EMPLOYEE;
    }

    public function setNAME_ROLE_EMPLOYEE(string $NAME){
        $this->NAME_ROLE_EMPLOYEE = $NAME;
    }

    public function getDESCRIPTION() : ?string {
        return $this->DESCRIPTION;
    }

    public function setDESCRIPTION(string $DESCRIPTION) {
        $this->DESCRIPTION = $DESCRIPTION;
    }

    public function getSTATUS() : int {
        return (int) $this->STATUS;
    }
    public function setSTATUS(bool $STATUS): void {
        $this->STATUS = $STATUS ? 1 : 0;
    }

    public function Listar (){
        try{
            $consulta = $this->pdo->prepare("SELECT * FROM ROLE_EMPLOYEE");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Insertar (RoleEmployee $roleEmployee){
        try{
            $consulta = $this->pdo->prepare("INSERT INTO ROLE_EMPLOYEE (NAME_ROLE_EMPLOYEE, DESCRIPTION, STATUS) VALUES (?, ?, ?)");
            $consulta->execute(array(
                $roleEmployee->getNAME_ROLE_EMPLOYEE(),
                $roleEmployee->getDESCRIPTION(),
                $roleEmployee->getSTATUS()
            ));
           
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Actualizar (RoleEmployee $categoria){
        try{
            $consulta = $this->pdo->prepare("UPDATE ROLE_EMPLOYEE SET NAME_ROLE_EMPLOYEE = ?, DESCRIPTION = ?, STATUS = ? WHERE ID_ROLE_EMPLOYEE = ?");
            $consulta->execute(array(
                $categoria->getNAME_ROLE_EMPLOYEE(),
                $categoria->getDESCRIPTION(),
                $categoria->getSTATUS(),
                $categoria->getID_ROLE_EMPLOYEE()
            ));
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

}