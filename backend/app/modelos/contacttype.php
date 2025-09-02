<?php

class ContactType {
    private $pdo;

    private $id_contact_type;
    private $name_contact_type;
    private $description;
    private $status;

    public function __CONSTRUCT() {
        $this->pdo = BasedeDatos::Conectar();
    }

    // Métodos GET y SET
    public function getIdContactType(): ?int {
        return $this->id_contact_type;
    }

    public function setIdContactType(int $id): void {
        $this->id_contact_type = $id;
    }

    public function getNameContactType(): ?string {
        return $this->name_contact_type;
    }

    public function setNameContactType(string $name): void {
        $this->name_contact_type = $name;
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
            $consulta = $this->pdo->prepare("SELECT * FROM CONTACT_TYPE");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function Insertar (ContactType $categoria){
        try {
            $consulta = $this->pdo->prepare("INSERT INTO CONTACT_TYPE (name_contact_type, description, status) VALUES (?, ?, ?)");
            $consulta->execute(array(
                $categoria->getNameContactType(),
                $categoria->getDescription(),
                $categoria->getStatus()
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function Actualizar(ContactType $categoria) {
        try {
            $consulta = $this->pdo->prepare("UPDATE CONTACT_TYPE SET name_contact_type = ?, description = ?, status = ? WHERE id_contact_type = ?");
            $consulta->execute(array(
                $categoria->getNameContactType(),
                $categoria->getDescription(),
                $categoria->getStatus(),
                $categoria->getIdContactType()
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

}