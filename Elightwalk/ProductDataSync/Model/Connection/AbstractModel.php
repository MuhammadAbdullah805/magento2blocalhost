<?php
namespace Elightwalk\ProductDataSync\Model\Connection;

use Magento\Framework\DataObject;

abstract class AbstractModel extends DataObject
{

    protected $_resources;

    protected $writeConnection;

    protected $readConnection;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resources
    ){
        $this->_resources = $resources;
    }

    public function getCoreResource(){
        
        return $this->_resources;
    }

    public function getWriteConnection()
    {
        if (!$this->writeConnection) {
            $this->writeConnection = $this->getCoreResource()->getConnection('core_write');
        }
        return $this->writeConnection;
    }

    public function getReadConnection()
    {
        if (!$this->readConnection) {
           
            $this->readConnection = $this->getCoreResource()->getConnection('core_read');
        }
        return $this->readConnection;
    }

    public function getTableColumnsInArray($tableName){
        $_tableColumnsArray=array();	
        $_tableColumns=$this->getReadConnection()->describeTable($tableName);
        foreach($_tableColumns as $key=>$value){
            $_tableColumnsArray[]=$key;
        }
        return $_tableColumnsArray;
    }



}
