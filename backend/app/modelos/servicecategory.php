<?php

class ServiceCategory{

    private $pdo;

    private $ID_SERVICE_CATEGORY;
    private $NAME_SERVICE_CATEGORY;
    private $DESCRIPTION;
    private $STATUS;

    public function __CONSTRUCT(){
        $this->pdo = BasedeDatos::Conectar();
    }

    //Metodos GET y SET

    public function getID_SERVICE_CATEGORY() : ?int{
        return $this->ID_SERVICE_CATEGORY;
    }

    public function setID_SERVICE_CATEGORY(int $ID){
        $this->ID_SERVICE_CATEGORY = $ID;
    }

    public function getNAME_SERVICE_CATEGORY() : ?string {
        return $this->NAME_SERVICE_CATEGORY;
    }

    public function setNAME_SERVICE_CATEGORY(string $NAME){
        $this->NAME_SERVICE_CATEGORY = $NAME;
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
            $consulta = $this->pdo->prepare("SELECT * FROM SERVICE_CATEGORY");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Insertar (ServiceCategory $categoria){
        try{
            $consulta = $this->pdo->prepare("INSERT INTO SERVICE_CATEGORY (NAME_SERVICE_CATEGORY, DESCRIPTION, STATUS) VALUES (?, ?, ?)");
            $consulta->execute(array(
                $categoria->getNAME_SERVICE_CATEGORY(),
                $categoria->getDESCRIPTION(),
                $categoria->getSTATUS()
            ));
           
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Actualizar (ServiceCategory $categoria){
        try{
            $consulta = $this->pdo->prepare("UPDATE SERVICE_CATEGORY SET NAME_SERVICE_CATEGORY = ?, DESCRIPTION = ?, STATUS = ? WHERE ID_SERVICE_CATEGORY = ?");
            $consulta->execute(array(
                $categoria->getNAME_SERVICE_CATEGORY(),
                $categoria->getDESCRIPTION(),
                $categoria->getSTATUS(),
                $categoria->getID_SERVICE_CATEGORY()
            ));
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

}