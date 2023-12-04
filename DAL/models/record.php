<?php
include_once "php/formUtilities.php";
abstract class Record
{
    public $Id = 0;
    public $_compareKey = "";
    public $_UniqueKey = "";
    public function __construct($formData = null)
    {
        if ($formData != null)
            $this->bind($formData);
    }
    public function bind($formData)
    {
        foreach ($formData as $fieldName => $fieldValue) {
            $method = 'set' . ucfirst($fieldName);
            if (method_exists($this, $method))
                $this->$method(sanitizeString($fieldValue));
        }
    }
    public function setId($id)
    {
        $this->Id = $id;
    }
    public function setCompareKey($compareKey) {
        $this->_CompareKey = $compareKey;
    }
    public function setUniqueKey($uniqueKey) {
        $this->_UniqueKey = $uniqueKey;
    }
}
