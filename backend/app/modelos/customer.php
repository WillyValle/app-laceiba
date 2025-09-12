<?php
class Customer{
    private $pdo;

    private $id_customer;
    private $name_customer;
    private $address_customer;
    private $type_doc;
    private $doc_num;
    private $whatsapp;
    private $tel;
    private $mail;
    private $status;

    public function __CONSTRUCT(){
        $this->pdo = BasedeDatos::Conectar();
    }

    // Métodos GET y SET (mantener todos los existentes)
    public function getIdCustomer(): ?int {
        return $this->id_customer;
    }
    public function setIdCustomer(int $id): void {
        $this->id_customer = $id;
    }
    public function getNameCustomer(): ?string {
        return $this->name_customer;
    }
    public function setNameCustomer(string $name): void {
        $this->name_customer = $name;
    }
    public function getAddressCustomer(): ?string {
        return $this->address_customer;
    }
    public function setAddressCustomer(string $address): void {
        $this->address_customer = $address;
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
    public function getStatus(): int {
        return (int) $this->status;
    }
    public function setStatus(bool $status): void {
        $this->status = $status ? 1 : 0;
    }

    // MÉTODO CORREGIDO: Ahora trae TODOS los registros con JOIN para obtener el nombre del tipo de documento
    public function Listar(){
        try{
            $consulta = $this->pdo->prepare("
                SELECT 
                    c.id_customer, 
                    c.name_customer, 
                    c.address_customer, 
                    c.type_doc_id_type_doc,
                    td.name_type_doc,
                    c.doc_num, 
                    c.whatsapp, 
                    c.tel, 
                    c.mail, 
                    c.status 
                FROM CUSTOMER c
                LEFT JOIN TYPE_DOC td ON c.TYPE_DOC_ID_TYPE_DOC = td.ID_TYPE_DOC
                ORDER BY c.name_customer
            ");
            $consulta->execute();
            $resultados = $consulta->fetchAll(PDO::FETCH_OBJ);
            return $resultados ? $resultados : [];
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function ListarTipoDoc(){
        try{
            $consulta = $this->pdo->prepare("SELECT ID_TYPE_DOC, NAME_TYPE_DOC FROM TYPE_DOC WHERE STATUS = 1 ORDER BY NAME_TYPE_DOC");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    // Método para obtener un cliente específico
    public function Obtener($id){
        try{
            $consulta = $this->pdo->prepare("
                SELECT 
                    c.id_customer, 
                    c.name_customer, 
                    c.address_customer, 
                    c.type_doc_id_type_doc,
                    td.name_type_doc,
                    c.doc_num, 
                    c.whatsapp, 
                    c.tel, 
                    c.mail, 
                    c.status 
                FROM CUSTOMER c
                LEFT JOIN TYPE_DOC td ON c.TYPE_DOC_ID_TYPE_DOC = td.ID_TYPE_DOC
                WHERE c.id_customer = ?
            ");
            $consulta->execute(array($id));
            return $consulta->fetch(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Insertar(Customer $cliente){
        try{
            $consulta = $this->pdo->prepare("INSERT INTO CUSTOMER (name_customer, address_customer, type_doc_id_type_doc, doc_num, whatsapp, tel, mail, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $consulta->execute(array(
                $cliente->getNameCustomer(),
                $cliente->getAddressCustomer(),
                $cliente->getTypeDoc(),
                $cliente->getDocNum(),
                $cliente->getWhatsapp(),
                $cliente->getTel(),
                $cliente->getMail(),
                $cliente->getStatus()
            ));
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    public function Actualizar(Customer $cliente){
        try{
            $consulta = $this->pdo->prepare("UPDATE CUSTOMER SET name_customer = ?, address_customer = ?, type_doc_id_type_doc = ?, doc_num = ?, whatsapp = ?, tel = ?, mail = ?, status = ? WHERE id_customer = ?");
            $consulta->execute(array(
                $cliente->getNameCustomer(),
                $cliente->getAddressCustomer(),
                $cliente->getTypeDoc(),
                $cliente->getDocNum(),
                $cliente->getWhatsapp(),
                $cliente->getTel(),
                $cliente->getMail(),
                $cliente->getStatus(),
                $cliente->getIdCustomer()
            ));
        }catch(Exception $e){
            die($e->getMessage());
        }
    }

    // Método para obtener todos los tipos de documento activos
    public function ObtenerTiposDocumento(){
        try{
            $consulta = $this->pdo->prepare("SELECT ID_TYPE_DOC, NAME_TYPE_DOC FROM TYPE_DOC WHERE STATUS = 1 ORDER BY NAME_TYPE_DOC");
            $consulta->execute();
            return $consulta->fetchAll(PDO::FETCH_OBJ);
        }catch(Exception $e){
            die($e->getMessage());
        }
    }
}