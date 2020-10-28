<?php
namespace Elightwalk\ProductDataSync\Model\Connection;

class Category extends AbstractModel {

    public $entityTypeId = 3;

    protected $_connectionEav;

    // Tables Definations

    public function __construct(
        \Elightwalk\ProductDataSync\Model\Connection\Eav $connectionEav,
        \Magento\Framework\App\ResourceConnection $connection
    ){
        $this->_connectionEav = $connectionEav;
        parent::__construct($connection);
    }

    public function catalogCategoryEntity(){
        $tableName = $this->getCoreResource()->getTableName('catalog_category_entity');
        return $tableName;
    }

    public function catalogCategoryEntityDatetime(){
        $tableName = $this->getCoreResource()->getTableName('catalog_category_entity_datetime');
        return $tableName;
    }

    public function catalogCategoryEntityDecimal(){
        $tableName = $this->getCoreResource()->getTableName('catalog_category_entity_decimal');
        return $tableName;
    }

    public function catalogCategoryEntityInt(){
        $tableName = $this->getCoreResource()->getTableName('catalog_category_entity_int');
        return $tableName;
    }

    public function catalogCategoryEntityText(){
        $tableName = $this->getCoreResource()->getTableName('catalog_category_entity_text');
        return $tableName;
    }

    public function catalogCategoryEntityVarchar(){
        $tableName = $this->getCoreResource()->getTableName('catalog_category_entity_varchar');
        return $tableName;
    }

    public function getCategoriesArray($categories){
        $returnArray=[];
        foreach($categories as $category){
            $urlPath=$this->_connectionEav->getAttributeInfo("url_path", $this->entityTypeId);
            $query="select entity_id from `".$this->catalogCategoryEntityVarchar()."` where  value='".strtolower($category)."' and attribute_id='".$urlPath['attribute_id']."'";
            $urlPathAttValue=$this->getReadConnection()->fetchOne($query);
            $returnArray[]=$urlPathAttValue;

        }

        return $returnArray;

    }

}
