<?php

class RoleinService{

    private $pdo;

    private $ID_ROLE_IN_SERVICE;
    private $NAME_ROLE_IN_SERVICE;
    private $DESCRIPTION;
    private $STATUS;

    public function __CONSTRUCT(){
        $this->pdo = BasedeDatos::Conectar();
    }

    //Metodos GET y SET

    public function getID_ROLE_IN_SERVICE() : ?int{
        return $this->ID_ROLE_IN_SERVICE;
    }

    public function setID_ROLE_IN_SERVICE(int $ID){
        $this->ID_ROLE_IN_SERVICE = $ID;
    }

    public function getNAME_ROLE_IN_SERVICE() : ?string {
        return $this->NAME_ROLE_IN_SERVICE;
    }

    public function setNAME_ROLE_IN_SERVICE(string $NAME){
        $this->NAME_ROLE_IN_SERVICE = $NAME;
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
            $consulta = $this->pdo->prepare("SELECT * FROM ROLE_IN_SERVICE");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Insertar (RoleinService $roleinService){
        try{
            $consulta = $this->pdo->prepare("INSERT INTO ROLE_IN_SERVICE (NAME_ROLE_IN_SERVICE, DESCRIPTION, STATUS) VALUES (?, ?, ?)");
            $consulta->execute(array(
                $roleinService->getNAME_ROLE_IN_SERVICE(),
                $roleinService->getDESCRIPTION(),
                $roleinService->getSTATUS()
            ));
           
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Actualizar (RoleinService $categoria){
        try{
            $consulta = $this->pdo->prepare("UPDATE ROLE_IN_SERVICE SET NAME_ROLE_IN_SERVICE = ?, DESCRIPTION = ?, STATUS = ? WHERE ID_ROLE_IN_SERVICE = ?");
            $consulta->execute(array(
                $categoria->getNAME_ROLE_IN_SERVICE(),
                $categoria->getDESCRIPTION(),
                $categoria->getSTATUS(),
                $categoria->getID_ROLE_IN_SERVICE()
            ));
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

}