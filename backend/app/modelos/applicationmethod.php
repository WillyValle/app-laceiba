<?php

class ApplicationMethod {
    private $pdo;
    
    private $id_application_method;
    private $name_application_method;
    private $description;
    private $status;

    public function __CONSTRUCT() {
        $this->pdo = BasedeDatos::Conectar();
    }

    // Métodos GET y SET
    public function getIdApplicationMethod(): ?int {
        return $this->id_application_method;
    }

    public function setIdApplicationMethod(int $id): void {
        $this->id_application_method = $id;
    }

    public function getNameApplicationMethod(): ?string {
        return $this->name_application_method;
    }

    public function setNameApplicationMethod(string $name): void {
        $this->name_application_method = $name;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function getStatus(): int {
        return (int) $this->status;
    }

    public function setStatus(bool $status): void {
        $this->status = $status ? 1 : 0;
    }

    public function Listar() {
        try {
            $consulta = $this->pdo->prepare("SELECT * FROM APPLICATION_METHOD");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function Insertar (ApplicationMethod $categoria){
        try {
            $consulta = $this->pdo->prepare("INSERT INTO APPLICATION_METHOD (name_application_method, description, status) VALUES (?, ?, ?)");
            $consulta->execute(array(
                $categoria->getNameApplicationMethod(),
                $categoria->getDescription(),
                $categoria->getStatus()
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function Actualizar(ApplicationMethod $categoria) {
        try {
            $consulta = $this->pdo->prepare("UPDATE APPLICATION_METHOD SET name_application_method = ?, description = ?, status = ? WHERE id_application_method = ?");
            $consulta->execute(array(
                $categoria->getNameApplicationMethod(),
                $categoria->getDescription(),
                $categoria->getStatus(),
                $categoria->getIdApplicationMethod()
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

}