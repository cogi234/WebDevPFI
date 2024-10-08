<?php

//////////////////////////////////////////////////////////////////////////////////////////////
//
// Module de génération de code sql et de gestion de base de données 
//
// Auteur : Nicolas Chourot dans le cadre du cours 420-KB9
// Mise à jour : 24 novembre 2023
//
//////////////////////////////////////////////////////////////////////////////////////////////

date_default_timezone_set('US/Eastern');

//////////////////////////////////////////////////////////////////////////////////////////////
//
// classe MySQLTable
//
// Auteur : Nicolas Chourot dans le cadre du cours 420-KB9
// Date : mise a jour 24 novembre 2023
////////////////////////////////////////////////////////////////////////////////////////////////
//
//  Cette classe offre les services de génération de requêtes SQL en se basant sur les
//  membres de la classe dérivées.
//
//  Le type des membres est déterminé soit automatiquement soit par le commentaire
//  formaté au dessus de leur déclaration.
//
//  Typage automatique:
//
//  En se basant sur le type de la valeur affectée aux membrex lors de la construction du prototype du model:
//      - string sera considéré comme étant de type VARCHAR(65535)
//      - integer sera considéré comme étant de type INT
//      - double sera considéré comme étant de type DOUBLE
//
//  Si l'indentificateur d'un membre
//      - commence par _ il sera ignoré
//      - est égal à "Id" il sera considéré comme la clé primaire
//      - contient "Id" il sera considéré comme une clé étrangère
//      - contient "Date" il sera considéré comme un champ de type DateTime
//      - est égal à "Password" il sera traité comme une champ encrypté (Attention! ne pas spécifier sa taille)
//      - contient 'GUID' il sera considéré comme un champ contenant un code chiffré unique     
//  
//  Typage manuel :
//
//      Le commentaire qui précède le membre devra être du format : /** ... */
//      par exemple:
//          ...
//          /** VARCHAR(14) */
//          public $Cellphone;
//
//          /** VARCHAR(255) */
//          public $Email;
//
//          Si le commentaire contient NULL le champ pourra être null
//          
//////////////////////////////////////////////////////////////////////////////////////////////
abstract class MySQLTable
{
    public $_DB = null;
    public $recordPrototype = null;

    public function __construct($_DB, $recordPrototype)
    {
        $this->_DB = $_DB;
        $this->recordPrototype = $recordPrototype;
        $this->create_if_Does_Not_Exist();
    }
    private function typeFromDocComment($docComment)
    {
        return str_replace('*', '', str_replace('/', '', $docComment));
    }
    private function getType($fieldName, $value)
    {
        $className = $this->className();
        $prop = new ReflectionProperty($className, $fieldName);
        $docComment = trim($this->typeFromDocComment($prop->getDocComment()));
        if ($docComment) {
            $docCommentParts = explode(' ', $docComment);
            if (strpos($fieldName, 'Id')) {
                if ($fieldName === 'Id') {
                    $type['type'] = 'primary_key';
                } else {
                    $type['type'] = 'foreign_key';
                }
            } else {
                $type['type'] = str_replace(' ', '', $docCommentParts[0]);
            }
            if ($fieldName !== 'Id') {
                $type['null'] = isset($docCommentParts[1]) ? str_replace(' ', '', $docCommentParts[1]) === 'NULL' : false;
            } else {
                $type['null'] = false;
            }
            return $type;
        }
        $phpType = gettype($value);
        if ($phpType === 'integer') {
            if ($fieldName === 'Id')
                $phpType = 'primary_key';
            else
                if (strpos($fieldName, 'Id'))
                    $phpType = 'foreign_key';
                else
                    $phpType = 'INT';
        }
        if (strpos($fieldName, 'Date')) {
            $phpType = 'date';
        }
        if ($fieldName === 'Password') {
            $phpType = 'password';
        }
        if (strpos($fieldName, 'GUID')) {
            $phpType = 'guid';
        }
        $type['type'] = $phpType;
        return $type;
    }
    private function Convert($target, $value)
    {
        $tType = gettype($target);
        if ($tType !== 'string') {
            $vType = gettype($value);
            if ($vType === 'string') {
                switch ($tType) {
                    case 'integer':
                        return intval($value);
                    case 'double':
                        return doubleval($value);
                }
            }
        }
        return $value;
    }
    private function className()
    {
        return get_class($this->recordPrototype);
    }
    private function excludedMember($memberName)
    {
        $firstCharacter = substr($memberName, 0, 1);
        return $firstCharacter === '_';
    }
    public function create_Table()
    {
        $tableName = $this->tableName();
        $sql = 'CREATE TABLE ' . $tableName . ' (';
        $primaryKey = null;
        $foreignKeys = [];
        foreach ($this->recordPrototype as $key => $value) {
            if (!$this->excludedMember($key)) {
                $type = $this->getType($key, $value);
                $primaryKeyFlag = false;
                switch ($type['type']) {
                    case 'primary_key':
                        $primaryKey = "PRIMARY KEY ($key)";
                        $sqlType = 'INT';
                        $primaryKeyFlag = true;
                        break;
                    case 'foreign_key':
                        $fkTable = str_replace('Id', '', $key) . 's ';
                        $foreignKeys[] = "FOREIGN KEY ($key) REFERENCES $fkTable(Id)";
                        $sqlType = 'INT';
                        break;
                    case 'string':
                        $sqlType = 'VARCHAR(256)';
                        break;
                    case 'password':
                        $hashSample = password_hash('sample', PASSWORD_DEFAULT);
                        $hashSampleLength = strlen($hashSample) + 1;
                        $sqlType = "VARCHAR($hashSampleLength)";
                        break;
                    case 'integer':
                        $sqlType = 'INT';
                        break;
                    case 'date':
                        $sqlType = 'DATETIME';
                        break;
                    case 'double':
                        $sqlType = 'DOUBLE';
                        break;
                    case 'guid':
                        $guidSample = com_create_guid();
                        $guidSampleLength = strlen($guidSample) + 1;
                        $sqlType = "VARCHAR($guidSampleLength)";
                        break;
                    default:
                        $sqlType = $type['type'];
                }
                $sql .= "$key $sqlType ";
                if ($primaryKeyFlag)
                    $sql .= "AUTO_INCREMENT ";
                if (!isset($type['null']))
                    $sql .= "NOT NULL";
                else
                    if (!$type['null'])
                        $sql .= "NOT NULL";
                $sql .= ', ';
            }
        }
        $sql .= $primaryKey . ', ';
        foreach ($foreignKeys as $fk) {
            $sql .= $fk . ', ';
        }
        $sql = rtrim($sql, ', ');
        $sql .= ');';
        return $sql;
    }
    public function tableName()
    {
        return $this->className() . "s";
    }
    private function prepareForSQL(&$value)
    {
        $phpType = gettype($value);
        if ($phpType === 'string')
            $value = "'" . str_replace("'", "''", $value) . "'";
    }
    function xss_clean($data)
    {
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }
        while ($old_data !== $data);

        // we are done...
        return $data;
    }
    private function sanitizeString($str)
    {
        $str = stripslashes($str);
        $str = strip_tags($str);
        $str = $this->xss_clean($str);
        return $str;
    }
    public function bind($values)
    {
        $prototype = $this->recordPrototype;
        foreach ($prototype as $fieldName => $fieldValue) {
            if (!$this->excludedMember($fieldName)) {
                $method = 'set' . ucfirst($fieldName);
                if (method_exists($prototype, $method))
                    $prototype->$method($this->convert($fieldValue, $this->sanitizeString($values->$fieldName)));
            }
        }
    }
    private function create_if_Does_Not_Exist()
    {
        $tableName = $this->tableName();
        $sql = "DESCRIBE $tableName";
        $exist = $this->_DB->querySqlCmd($sql);
        if (!$exist) {
            $this->_DB->nonQuerySqlCmd($this->create_Table(), false);
        }
    }
    public function get($id = '')
    {
        if ($id !== '') {
            $data = $this->selectById($id);
            if (isset($data[0]))
                return $data[0];
            else
                return null;
        } else {
            $data = $this->selectAll();
            return $data;
        }
    }
    public function exist($id)
    {
        return ($this->get($id) !== null);
    }
    public function toObjectArray($data)
    {
        $objectsArray = [];
        $className = $this->className();
        foreach ($data as $record) {
            $objectsArray[] = new $className($record);
        }
        return $objectsArray;
    }
    public function selectAll($orderBy = null)
    {
        if ($orderBy == null) {
            if ($this->recordPrototype->_CompareKey !== "")
                $orderBy = $this->recordPrototype->_CompareKey;
        }
        $tableName = $this->tableName();
        $sql = "SELECT * FROM $tableName";
        if (isset($orderBy))
            $sql .= " $orderBy";
        $data = $this->_DB->querySqlCmd($sql);
        return $this->toObjectArray($data);
    }
    public function selectById($id)
    {
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $tableName = $this->tableName();
        $sql = "SELECT * FROM $tableName WHERE Id = $id";
        $data = $this->_DB->querySqlCmd($sql);
        return $this->toObjectArray($data);
    }
    public function selectWhere($criteria = null)
    {
        $tableName = $this->tableName();
        $sql = "SELECT * FROM $tableName WHERE $criteria";
        $data = $this->_DB->querySqlCmd($sql);
        return $this->toObjectArray($data);
    }
    public function insert($data)
    {
        if (isset($data)) {
            $this->bind($data);
            $tableName = $this->tableName();
            $sql = 'INSERT INTO ' . $tableName . ' (';
            foreach ($this->recordPrototype as $key => $value) {
                if (!$this->excludedMember($key)) {
                    if ($key !== 'Id') {
                        $sql .= $key . ', ';
                    }
                }
            }
            $sql = rtrim($sql, ', ') . ') values ( ';
            foreach ($this->recordPrototype as $key => $value) {
                if (!$this->excludedMember($key)) {
                    if ($key !== 'Id') {
                        if ($key === 'Password') {
                            $value = password_hash($value, PASSWORD_DEFAULT);
                        }
                        $this->prepareForSQL($value);
                        $sql .= $value . ', ';
                    }
                }
            }
            $sql = rtrim($sql, ', ') . ')';
            return $this->_DB->nonQuerySqlCmd($sql);
        }
        return 0;
    }
    public function update($data)
    {
        if (isset($data)) {
            $this->bind($data);
            $tableName = $this->tableName();
            $sql = 'UPDATE ' . $tableName . ' set ';
            foreach ($this->recordPrototype as $key => $value) {
                if (!$this->excludedMember($key)) {
                    if (($key !== 'Id') && ($key !== 'Password')) {
                        $this->prepareForSQL($value);
                        $sql .= $key . ' = ' . $value . ', ';
                    }
                }
            }
            $sql = rtrim($sql, ', ');
            $sql .= ' WHERE Id = ' . $data->Id;
            $this->_DB->nonQuerySqlCmd($sql);
        }
    }
    public function update_Including_Password($data)
    {
        if (isset($data)) {
            $this->bind($data);
            $tableName = $this->tableName();
            $sql = 'UPDATE ' . $tableName . ' set ';
            foreach ($this as $key => $value) {
                if (!$this->excludedMember($key)) {
                    if ($key !== 'Id') {
                        if ($key === 'Password') {
                            $value = password_hash($value, PASSWORD_DEFAULT);
                        }
                        $this->prepareForSQL($value);
                        $sql .= $key . ' = ' . $value . ', ';
                    }
                }
            }
            $sql = rtrim($sql, ', ');
            $sql .= ' WHERE Id = ' . $data->Id;
            $this->_DB->nonQuerySqlCmd($sql);
        }
    }
    public function conflict($value, $excludeId)
    {
        if ($this->recordPrototype->_UniqueKey !== "") {
            $criteria = $this->recordPrototype->_UniqueKey . " = '$value'";
            $record = $this->selectWhere($criteria);
            if ($record)
                return ((int) $record[0]->Id != $excludeId);
            else
                return false;
        }
        return false;
    }
    public function delete($id)
    {
        if (isset($id)) {
            $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
            $tableName = $this->tableName();
            $sql = "DELETE FROM $tableName WHERE Id = $id";
            $this->_DB->nonQuerySqlCmd($sql);
        }
    }
    public function deleteWhere($criteria)
    {
        $tableName = $this->tableName();
        $sql = "DELETE FROM $tableName WHERE $criteria";
        $this->_DB->nonQuerySqlCmd($sql);
    }
    public function deleteAll()
    {
        $tableName = $this->tableName();
        $sql = "DELETE FROM $tableName";
        $this->_DB->nonQuerySqlCmd($sql);
    }
    public static function JoinHelper(
        $tableX,
        $JoinTable,
        $tableY,
        $selection,
        $criteria
    ) {
        $fkX = mb_substr($tableX, 0, -1) . 'Id';
        $fkY = mb_substr($tableY, 0, -1) . 'Id';
        $sql = "SELECT $selection 
                FROM $tableX, $JoinTable, $tableY 
                WHERE $JoinTable.$fkX = $tableX.Id 
                AND $JoinTable.$fkY = $tableY.Id 
                AND $criteria";
        return $sql;
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////
//
// classe MySQLDataBase
//
// Auteur : Nicolas Chourot dans le cadre du cours 420-KB9
// Date : 24 novembre 2024
////////////////////////////////////////////////////////////////////////////////////////////////
//
//  Cette classe permet d'établir une connection à une base de données MySql.
//  Elle permet aussi d'éxécuter des requêtes SQL avec ou sans transaction.
//
////////////////////////////////////////////////////////////////////////////////////////////////
final class MySQLDataBase
{
    private static $_instance = null;
    private $host;
    private $username;
    private $password;
    private $dbName;
    private $autoCommit;
    private $conn;

    // Ici les réglages pourraient êtres stockés dans un fichier de constantes globales de l'application
    public function __construct($dbName, $host, $username, $password) {
        $this->dbName = $dbName;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->autoCommit = true;
        $this->conn = null;
    }
    
    public static function getInstance($dbName, $host, $username, $password) {
        if(is_null(self::$_instance)) {
           self::$_instance = new MySQLDataBase($dbName, $host, $username, $password);
        }
        return self::$_instance;
    }

    ////////////////////////// Database management /////////////////////////////////////
    //
    public function create()
    {
        $success = true;
        try {
            $this->hostConnect();
            if ($this->conn)
                $this->conn->exec("CREATE DATABASE $this->dbName");
        } catch (PDOException $e) {
            $success = false;
        }
        return $success;
    }
    public function Delete()
    {
        $success = true;
        try {
            $this->hostConnect();
            if ($this->conn)
                $this->conn->exec("DROP DATABASE $this->dbName");
        } catch (PDOException $e) {
            $success = false;
        }
        return $success;
    }
    private function hostConnect()
    {
        if ($this->conn === null) {
            $this->conn = new PDO(
                "mysql:host=$this->host",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }
    //
    ////////////////////////////////////////////////////////////////////////////////////
    private function DBConnect()
    {
        if ($this->conn === null) {
            try {
                if (!$this->exist()) {
                    $this->create();
                    $this->conn = null;
                }
                $this->conn = new PDO(
                    "mysql:host=$this->host;dbname=$this->dbName;charset=utf8",
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    if ( $this->autoCommit)
                $this->beginTransaction();
            } catch (PDOException $e) {
                var_dump($e);
            }
        }
    }

    public function beginTransaction()
    {
        if ($this->conn !== null && $this->autoCommit)
            $this->conn->beginTransaction();
    }
    public function endTransaction()
    {
        if ($this->conn !== null && $this->autoCommit)
            $this->commit();
    }
    private function commit()
    {
        if ($this->conn !== null && $this->autoCommit) {
            $this->conn->commit();
            $this->disconnect();
        }
    }
    private function rollBack()
    {
        if ($this->conn !== null) {
            $this->autoCommit = true;
            $this->conn->rollBack();
            $this->disconnect();
        }
    }
    private function disconnect()
    {
        if ($this->conn !== null && $this->autoCommit)
            $this->conn = null;
    }
    public function exist()
    {
        if ($this->conn !== null)
            return true;
        else {
            try {
                $conn = new PDO("mysql:host=$this->host; dbname=$this->dbName;", $this->username, $this->password);
                if ($conn !== null) {
                    $conn = null;
                    return true;
                }
            } catch (PDOException $e) {
                return false;
            }
        }
        return false;
    }
    public function lastInsertedId()
    {
        if ($this->conn !== null) {
            return $this->conn->lastInsertId();
        }
        return 0;
    }
    public function nonQuerySqlCmd($sql, $autoCommit = true)
    {
        $recordsAffected = 0;
        try {
            $this->DBConnect();
            if ($this->conn)
                $this->conn->exec($sql);
            $recordsAffected = $this->lastInsertedId();
            $this->autoCommit = $autoCommit;
            $this->commit();
        } catch (PDOException $e) {
            $this->rollBack();
            $recordsAffected = 0;
        }
        return $recordsAffected;
    }
    public function querySqlCmd($sql)
    {
        try {
            $this->DBConnect();
            if ($this->conn)
                $rows = $this->conn->query($sql)->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
        $this->disconnect();
        return $rows;
    }
}
?>