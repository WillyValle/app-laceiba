<?php

class TypeDoc {
    private $pdo;

    private $id_type_doc;
    private $name_type_doc;
    private $description;
    private $status;

    public function __CONSTRUCT() {
        $this->pdo = BasedeDatos::Conectar();
    }

    // Métodos GET y SET
    public function getIdTypeDoc(): ?int {
        return $this->id_type_doc;
    }

    public function setIdTypeDoc(int $id): void {
        $this->id_type_doc = $id;
    }

    public function getNameTypeDoc(): ?string {
        return $this->name_type_doc;
    }

    public function setNameTypeDoc(string $name): void {
        $this->name_type_doc = $name;
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
            $consulta = $this->pdo->prepare("SELECT * FROM TYPE_DOC");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function Insertar (TypeDoc $categoria){
        try {
            $consulta = $this->pdo->prepare("INSERT INTO TYPE_DOC (name_type_doc, description, status) VALUES (?, ?, ?)");
            $consulta->execute(array(
                $categoria->getNameTypeDoc(),
                $categoria->getDescription(),
                $categoria->getStatus()
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function Actualizar(TypeDoc $categoria) {
        try {
            $consulta = $this->pdo->prepare("UPDATE TYPE_DOC SET name_type_doc = ?, description = ?, status = ? WHERE id_type_doc = ?");
            $consulta->execute(array(
                $categoria->getNameTypeDoc(),
                $categoria->getDescription(),
                $categoria->getStatus(),
                $categoria->getIdTypeDoc()
            ));
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

}