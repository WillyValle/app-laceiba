<?php

class ServiceStatus {
    private $pdo;
    
    private $id_service_status;
    private $name;
    private $description;
    private $status;

    public function __CONSTRUCT() {
        $this->pdo = BasedeDatos::Conectar();
    }

    // Métodos GET y SET
    public function getIdServiceStatus(): ?int {
        return $this->id_service_status;
    }

    public function setIdServiceStatus(int $id): void {
        $this->id_service_status = $id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
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
            $consulta = $this->pdo->prepare("SELECT * FROM SERVICE_STATUS");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function Insertar (ServiceStatus $status){
        try {
            $consulta = $this->pdo->prepare("INSERT INTO SERVICE_STATUS (name_service_status, description, status) VALUES (?, ?, ?)");
            $consulta->execute(array(
                $status->getName(),
                $status->getDescription(),
                $status->getStatus()
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function Actualizar (ServiceStatus $status){
        try {
            $consulta = $this->pdo->prepare("UPDATE SERVICE_STATUS SET name_service_status = ?, description = ?, status = ? WHERE id_service_status = ?");
            $consulta->execute(array(
                $status->getName(),
                $status->getDescription(),
                $status->getStatus(),
                $status->getIdServiceStatus()
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

}