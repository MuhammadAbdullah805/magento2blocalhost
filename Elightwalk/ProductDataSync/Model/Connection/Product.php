<?php

namespace Elightwalk\ProductDataSync\Model\Connection;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Catalog\Model\Product\Media\Config;

class Product extends AbstractModel
{

    public $entityTypeId = 4;

    protected $_connectionEav;

    protected $_directoryList;

    protected $_file;

    protected $_filesystem;

    protected $_fileStorageDb;

    protected $_mediaConfig;


    public function __construct(
        \Elightwalk\ProductDataSync\Model\Connection\Eav $connectionEav,
        DirectoryList $directoryList,
        File $file,
        Filesystem $filesystem,
        Database $fileStorageDb,
        Config $mediaConfig,
        \Magento\Framework\App\ResourceConnection $connection
    ){
        $this->_connectionEav = $connectionEav;
        $this->_directoryList = $directoryList;
        $this->_file = $file;
        $this->_filesystem = $filesystem;
        $this->_fileStorageDb = $fileStorageDb;
        $this->_mediaConfig = $mediaConfig;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        parent::__construct($connection);
    }


    // Product Tables Defination

    public function cataloginventoryStockItem()
    {
        $tableName = $this->getCoreResource()->getTableName('cataloginventory_stock_item');
        return $tableName;
    }

    public function cataloginventoryStockStatus()
    {
        $tableName = $this->getCoreResource()->getTableName('cataloginventory_stock_status');
        return $tableName;
    }

    public function inventorySourceItem()
    {
        $tableName = $this->getCoreResource()->getTableName('inventory_source_item');
        return $tableName;
    }

    public function catalogProductEntity()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity');
        return $tableName;
    }

    public function catalogProductLink()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_link');
        return $tableName;
    }

    public function catalogProductLinkType()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_link_type');
        return $tableName;
    }

    public function catalogProductLinkAttribute()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_link_attribute');
        return $tableName;
    }

    public function catalogProductLinkAttributeDecimal()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_link_attribute_decimal');
        return $tableName;
    }

    public function catalogProductLinkAttributeInt()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_link_attribute_int');
        return $tableName;
    }

    public function catalogProductLinkAttributeVarchar()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_link_attribute_varchar');
        return $tableName;
    }

    public function catalogProductEntityDatetime()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_datetime');
        return $tableName;
    }

    public function catalogProductEntityDecimal()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_decimal');
        return $tableName;
    }

    public function catalogProductEntityInt()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_int');
        return $tableName;
    }

    public function catalogProductEntityText()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_text');
        return $tableName;
    }

    public function catalogProductEntityVarchar()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_varchar');
        return $tableName;
    }

    public function catalogProductEntityGallery()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_gallery');
        return $tableName;
    }

    public function catalogProductEntityMediaGallery()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_media_gallery');
        return $tableName;
    }

    public function catalogProductEntityMediaGalleryValue()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_media_gallery_value');
        return $tableName;
    }

    public function catalogProductEntityMediaGalleryValueToEntity()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_media_gallery_value_to_entity');
        return $tableName;
    }

    public function catalogCategoryProduct()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_category_product');
        return $tableName;
    }

    public function catalogProductWebsite()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_website');
        return $tableName;
    }



    public function catalogProductEntityTierPrice()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_tier_price');
        return $tableName;
    }


    public function catalogProductEntityGroupPrice()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_entity_group_price');
        return $tableName;
    }

    // Config Tables

    public function catalogProductSuperAttribute()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_super_attribute');
        return $tableName;
    }

    public function catalogProductSuperAttributeLabel()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_super_attribute_label');
        return $tableName;
    }

    public function catalogProductSuperAttributePricing()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_super_attribute_pricing');
        return $tableName;
    }

    public function catalogProductSuperLink()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_super_link');
        return $tableName;
    }

    public function catalogProductRelation()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_relation');
        return $tableName;
    }

    // Custom Option Table

    public function catalogProductOptionTypePrice()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_option_type_price');
        return $tableName;
    }

    public function catalogProductOptionTypeValue()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_option_type_value');
        return $tableName;
    }

    public function catalogProductOptionTypeTitle()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_option_type_title');
        return $tableName;
    }

    public function catalogProductOptionTitle()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_option_title');
        return $tableName;
    }

    public function catalogProductOptionPrice()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_option_price');
        return $tableName;
    }

    public function catalogProductOption()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_option');
        return $tableName;
    }

    // Bundle Product Tables

    public function catalogProductBundleOption()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_bundle_option');
        return $tableName;
    }

    public function catalogProductBundleOptionValue()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_bundle_option_value');
        return $tableName;
    }

    public function catalogProductBundleSelection()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_product_bundle_selection');
        return $tableName;
    }



    public function isProduct($sku)
    {

        $productInfo = $this->getReadConnection()->fetchOne("select * from " . $this->catalogProductEntity() . " where sku='" . addslashes(trim($sku)) . "'");

        return $productInfo;
    }


    public function _dumpProducttimestamp($productid)
    {
        $updatedate = date("y-m-d H:i:s");
        $updatetimestamp = "UPDATE " . $this->catalogProductEntity() . " SET updated_at='" . $updatedate . "' where entity_id=" . $productid;
        $this->getWriteConnection()->query($updatetimestamp);
    }


    public function _dumpcatalogProductEntity($value)
    {
        $sku = $value['sku'];

        $productInfo = $this->getReadConnection()->fetchOne("select * from " . $this->catalogProductEntity() . " where sku='" . addslashes($sku) . "'");

        $value['sku'] = addslashes($sku);
        $_tablecolumn = array_keys($value);


        if ($productInfo) {
            $value['updated_at'] = date("y-m-d H:i:s");
            $_tablecolumn[] = 'updated_at';
            $productId = $productInfo;
            $sql = "Update " . $this->catalogProductEntity();
            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " SET " . $_tablekey . "='" . $value[$_tablekey] . "'";
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "' where entity_id=" . $productId;
                } else {
                    $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "'";
                }
                $i++;
            }
            $this->getWriteConnection()->query($sql);
        } else {
            $_tablecolumn[] = 'created_at';
            $_tablecolumn[] = 'updated_at';
            $value['created_at'] = date("y-m-d H:i:s");
            $value['updated_at'] = date("y-m-d H:i:s");
            $sql = "Insert into " . $this->catalogProductEntity();

            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " ( " . $_tablekey;
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . " )";
                } else {
                    $sql .= " , " . $_tablekey;
                }
                $i++;
            }
            $i = 1;
            foreach ($value as $_tablevalue) {
                if ($i == 1) {
                    $sql .= " VALUES ( '" . $_tablevalue . "'";
                } elseif (count($value) == $i) {
                    $sql .= " , '" . $_tablevalue . "' )";
                } else {
                    $sql .= " , '" . $_tablevalue . "'";
                }
                $i++;
            }
            $this->getWriteConnection()->query($sql);
            $productId = $this->getWriteConnection()->lastInsertId();
        }

        return $productId;
    }

    public function _dumpCategoryProductTable($value, $productId)
    {

        foreach ($value as $row) {
            $row['product_id'] = $productId;
            $_tablecolumn = array_keys($row);
            $category_id = $row['category_id'];
            $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $this->catalogCategoryProduct() . " where product_id='" . $productId . "' AND category_id='" . $row['category_id'] . "'");

            if ($TableInfo) {
                $sql = "Update " . $this->catalogCategoryProduct();
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . $row[$_tablekey] . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . $row[$_tablekey] . "' where product_id='" . $productId . "' AND category_id='" . $category_id . "'";
                    } else {
                        $sql .= " , " . $_tablekey . "='" . $row[$_tablekey] . "'";
                    }
                    $i++;
                }
                $this->getWriteConnection()->query($sql);
            } else {
                $sql = "Insert into " . $this->catalogCategoryProduct();

                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " ( " . $_tablekey;
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . " )";
                    } else {
                        $sql .= " , " . $_tablekey;
                    }
                    $i++;
                }
                $i = 1;
                foreach ($row as $_tablevalue) {
                    if ($i == 1) {
                        $sql .= " VALUES ( '" . $_tablevalue . "'";
                    } elseif (count($row) == $i) {
                        $sql .= " , '" . $_tablevalue . "' )";
                    } else {
                        $sql .= " , '" . $_tablevalue . "'";
                    }
                    $i++;
                }
                $this->getWriteConnection()->query($sql);
            }
        }
    }

    // Stock Table Entry
    public function _dumpStockTable($value, $productId, $tableName)
    {

        $productInfo = $this->getReadConnection()->fetchAll("select * from " . $tableName . " where product_id='" . $productId . "'");
        $_tablecolumn = array_keys($value);

        if ($productInfo) {
            $productId = $productInfo[0]['product_id'];
            $value['product_id'] = $productId;

            $sql = "Update " . $tableName;
            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " SET " . $_tablekey . "='" . $value[$_tablekey] . "'";
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "' where product_id=" . $productId;
                } else {
                    $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "'";
                }
                $i++;
            }

            $this->getWriteConnection()->query($sql);
        } else {

            $value['product_id'] = $productId;
            $sql = "Insert into " . $tableName;

            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " ( " . $_tablekey;
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . " )";
                } else {
                    $sql .= " , " . $_tablekey;
                }
                $i++;
            }
            $i = 1;
            foreach ($value as $_tablevalue) {
                if ($i == 1) {
                    $sql .= " VALUES ( '" . $_tablevalue . "'";
                } elseif (count($value) == $i) {
                    $sql .= " , '" . $_tablevalue . "' )";
                } else {
                    $sql .= " , '" . $_tablevalue . "'";
                }
                $i++;
            }
            $this->getWriteConnection()->query($sql);
        }
    }

    // Eav Varchar Medui Table Entry
    public function _dumpVarcharMediaTable($value, $entityId, $tableName)
    {


        foreach ($value as $row) {
            $row['entity_id'] = $entityId;
            $attribute_code = $row['attribute_code'];
            unset($row['attribute_code']);
            if ($attribute_code == "image" || $attribute_code == "thumbnail" || $attribute_code == "small_image") {

                if (!isset($this->imageTmp[$row['value']])) {
                    $filename = $this->addImage($row['value']);


                    $this->imageTmp[$row['value']] = $filename;
                    $this->entityidTmp[$entityId][$filename] = $filename;
                    $row['value'] = $filename;
                } else {
                    if (!isset($this->entityidTmp[$entityId][$this->imageTmp[$row['value']]])) {
                        $filename = $this->addImage($row['value']);
                        $this->imageTmp[$row['value']] = $filename;
                        $this->entityidTmp[$entityId][$filename] = $filename;
                        $row['value'] = $filename;
                    } else {
                        $row['value'] = $this->imageTmp[$row['value']];
                    }
                }
            }
            $_tablecolumn = array_keys($row);
            $attribute_id = $row['attribute_id'];
            $store_id = $row['store_id'];

            $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $tableName . " where entity_id='" . $entityId . "' AND attribute_id='" . $attribute_id . "' AND store_id='" . $store_id . "'");


            if ($TableInfo) {
                //$valueId=$TableInfo;
                $sql = "Update " . $tableName;
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . addslashes($row[$_tablekey]) . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . addslashes($row[$_tablekey]) . "' where entity_id='" . $entityId . "' AND attribute_id='" . $attribute_id . "' AND store_id='" . $store_id . "'";
                    } else {
                        $sql .= " , " . $_tablekey . "='" . addslashes($row[$_tablekey]) . "'";
                    }
                    $i++;
                }

                $this->getWriteConnection()->query($sql);
            } else {
                $sql = "Insert into " . $tableName;

                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " ( " . $_tablekey;
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . " )";
                    } else {
                        $sql .= " , " . $_tablekey;
                    }
                    $i++;
                }
                $i = 1;
                foreach ($row as $_tablevalue) {
                    if ($i == 1) {
                        $sql .= " VALUES ( '" . addslashes($_tablevalue) . "'";
                    } elseif (count($row) == $i) {
                        $sql .= " , '" . addslashes($_tablevalue) . "' )";
                    } else {
                        $sql .= " , '" . addslashes($_tablevalue) . "'";
                    }
                    $i++;
                }

                $this->getWriteConnection()->query($sql);
            }
        }
    }

    // Eav Media Gallary Table Entry		
    public function _dumpMediaGalleryTable($value, $entityId, $tableName, $childTableName, $relationTableName)
    {

        foreach ($value as $row) {
            $child = $row['child'];
            $child['entity_id'] = $entityId;
            $relation = $row['relation'];
            unset($row['child']);
            unset($row['relation']);
            //$row['entity_id'] = $entityId;
            if (!isset($this->imageTmp[$row['value']])) {
                $filename = $this->addImage($row['value']);
                $this->imageTmp[$row['value']] = $filename;
                $row['value'] = $filename;
                $this->entityidTmp[$entityId][$filename] = $filename;
            } else {
                if (!isset($this->entityidTmp[$entityId][$this->imageTmp[$row['value']]])) {
                    $filename = $this->addImage($row['value']);
                    $this->imageTmp[$row['value']] = $filename;
                    $this->entityidTmp[$entityId][$filename] = $filename;
                    $row['value'] = $filename;
                } else {
                    $row['value'] = $this->imageTmp[$row['value']];
                }
            }

            $_tablecolumn = array_keys($row);

            $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $tableName . " where attribute_id='" . $row['attribute_id'] . "' AND value='" . $row['value'] . "'");

            if ($TableInfo) {
                $valueId = $TableInfo;
                $sql = "Update " . $tableName;
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . addslashes($row[$_tablekey]) . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . addslashes($row[$_tablekey]) . "' where entity_id='" . $entityId . "'";
                    } else {
                        $sql .= " , " . $_tablekey . "='" . addslashes($row[$_tablekey]) . "'";
                    }
                    $i++;
                }
                //$this->getWriteConnection()->query($sql);

                $this->_dumpCatalogProductEntityMediaGalleryValue($childTableName,$child,$valueId);
                /*$CTableInfo = $this->getReadConnection()->fetchOne("select * from " . $childTableName . " where value_id='" . $valueId . "'");

                if ($CTableInfo) {

                    $child['value_id'] = $valueId;
                    $_tablecolumn = array_keys($child);

                    $sql = "Update " . $childTableName;
                    $i = 1;
                    foreach ($_tablecolumn as $_tablekey) {
                        if ($i == 1) {
                            $sql .= " SET " . $_tablekey . "='" . addslashes($child[$_tablekey]) . "'";
                        } elseif (count($_tablecolumn) == $i) {
                            $sql .= " , " . $_tablekey . "='" . addslashes($child[$_tablekey]) . "' where value_id='" . $valueId . "'";
                        } else {
                            $sql .= " , " . $_tablekey . "='" . addslashes($child[$_tablekey]) . "'";
                        }
                        $i++;
                    }

                    //$this->getWriteConnection()->query($sql);
                } else {

                    $child['value_id'] = $valueId;
                    $_tablecolumn = array_keys($child);

                    $sql = "Insert into " . $childTableName;

                    $i = 1;
                    foreach ($_tablecolumn as $_tablekey) {
                        if ($i == 1) {
                            $sql .= " ( " . $_tablekey;
                        } elseif (count($_tablecolumn) == $i) {
                            $sql .= " , " . $_tablekey . " )";
                        } else {
                            $sql .= " , " . $_tablekey;
                        }
                        $i++;
                    }
                    $i = 1;
                    foreach ($child as $_tablevalue) {
                        if ($i == 1) {
                            $sql .= " VALUES ( '" . addslashes($_tablevalue) . "'";
                        } elseif (count($child) == $i) {
                            $sql .= " , '" . addslashes($_tablevalue) . "' )";
                        } else {
                            $sql .= " , '" . addslashes($_tablevalue) . "'";
                        }
                        $i++;
                    }
                    $this->getWriteConnection()->query($sql);
                }*/

                $relation['value_id'] = $valueId;
                $relation['entity_id'] = $entityId;
                
                $this->_dumpCatalogProductEntityMediaGalleryValueToEntity($relationTableName,$relation);


            } else {

                $sql = "Insert into " . $tableName;

                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " ( " . $_tablekey;
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . " )";
                    } else {
                        $sql .= " , " . $_tablekey;
                    }
                    $i++;
                }
                $i = 1;
                foreach ($row as $_tablevalue) {
                    if ($i == 1) {
                        $sql .= " VALUES ( '" . addslashes($_tablevalue) . "'";
                    } elseif (count($row) == $i) {
                        $sql .= " , '" . addslashes($_tablevalue) . "' )";
                    } else {
                        $sql .= " , '" . addslashes($_tablevalue) . "'";
                    }
                    $i++;
                }

                $this->getWriteConnection()->query($sql);
                $valueId = $this->getWriteConnection()->lastInsertId();

                $this->_dumpCatalogProductEntityMediaGalleryValue($childTableName,$child,$valueId);

                /*$child['value_id'] = $valueId;
                $_tablecolumn = array_keys($child);

                $sql = "Insert into " . $childTableName;

                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " ( " . $_tablekey;
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . " )";
                    } else {
                        $sql .= " , " . $_tablekey;
                    }
                    $i++;
                }
                $i = 1;
                foreach ($child as $_tablevalue) {
                    if ($i == 1) {
                        $sql .= " VALUES ( '" . addslashes($_tablevalue) . "'";
                    } elseif (count($child) == $i) {
                        $sql .= " , '" . addslashes($_tablevalue) . "' )";
                    } else {
                        $sql .= " , '" . addslashes($_tablevalue) . "'";
                    }
                    $i++;
                }
                $this->getWriteConnection()->query($sql);*/


                $relation['value_id'] = $valueId;
                $relation['entity_id'] = $entityId;
                
                $this->_dumpCatalogProductEntityMediaGalleryValueToEntity($relationTableName,$relation);

            }
        }
    }

    protected function _dumpCatalogProductEntityMediaGalleryValueToEntity($relationTableName,$relation){

        // Relation

        $_tablecolumn = array_keys($relation);
        $valueId = $relation['value_id'];
        $entityId = $relation['entity_id'];

        $RTableInfo = $this->getReadConnection()->fetchOne("select * from " . $relationTableName . " where value_id='" . $valueId . "' AND entity_id='" . $entityId . "'");
        if($RTableInfo){
            $valueId = $RTableInfo;
            $sql = "Update " . $relationTableName;
            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " SET " . $_tablekey . "='" . addslashes($relation[$_tablekey]) . "'";
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . "='" . addslashes($relation[$_tablekey]) . "' where value_id='" . $valueId . "'";
                } else {
                    $sql .= " , " . $_tablekey . "='" . addslashes($relation[$_tablekey]) . "'";
                }
                $i++;
            }    
        }else{
            $sql = "Insert into " . $relationTableName;

            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " ( " . $_tablekey;
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . " )";
                } else {
                    $sql .= " , " . $_tablekey;
                }
                $i++;
            }
            $i = 1;
            foreach ($relation as $_tablevalue) {
                if ($i == 1) {
                    $sql .= " VALUES ( '" . addslashes($_tablevalue) . "'";
                } elseif (count($relation) == $i) {
                    $sql .= " , '" . addslashes($_tablevalue) . "' )";
                } else {
                    $sql .= " , '" . addslashes($_tablevalue) . "'";
                }
                $i++;
            }

            $this->getWriteConnection()->query($sql);
        }
        
    }

    protected function _dumpCatalogProductEntityMediaGalleryValue($childTableName,$child,$valueId){

        $CTableInfo = $this->getReadConnection()->fetchOne("select * from " . $childTableName . " where value_id='" . $valueId . "'");

        if ($CTableInfo) {

            $child['value_id'] = $valueId;
            $_tablecolumn = array_keys($child);

            $sql = "Update " . $childTableName;
            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " SET " . $_tablekey . "='" . addslashes($child[$_tablekey]) . "'";
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . "='" . addslashes($child[$_tablekey]) . "' where value_id='" . $valueId . "'";
                } else {
                    $sql .= " , " . $_tablekey . "='" . addslashes($child[$_tablekey]) . "'";
                }
                $i++;
            }

            //$this->getWriteConnection()->query($sql);
        } else {

            $child['value_id'] = $valueId;
            $_tablecolumn = array_keys($child);

            $sql = "Insert into " . $childTableName;

            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " ( " . $_tablekey;
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . " )";
                } else {
                    $sql .= " , " . $_tablekey;
                }
                $i++;
            }
            $i = 1;
            foreach ($child as $_tablevalue) {
                if ($i == 1) {
                    $sql .= " VALUES ( '" . addslashes($_tablevalue) . "'";
                } elseif (count($child) == $i) {
                    $sql .= " , '" . addslashes($_tablevalue) . "' )";
                } else {
                    $sql .= " , '" . addslashes($_tablevalue) . "'";
                }
                $i++;
            }
            $this->getWriteConnection()->query($sql);
        }
    }

    public function _dumpProductSuperAttributeTable($values, $productId)
    {

        $productSuperAttributeIdArray = array();
        foreach ($values as $Attkey => $value) {
            $value['product_id'] = $productId;
            $attributeId = $value['attribute_id'];

            $productSuperAttributeId = $this->getReadConnection()->fetchOne("select product_super_attribute_id from " . $this->catalogProductSuperAttribute() . " where product_id='" . $productId . "' AND attribute_id='" . $attributeId . "'");
            $_tablecolumn = array_keys($value);

            if ($productSuperAttributeId) {

                $sql = "Update " . $this->catalogProductSuperAttribute();
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . $value[$_tablekey] . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "' where product_id=" . $productId . " AND attribute_id=" . $attributeId;
                    } else {
                        $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "'";
                    }
                    $i++;
                }

                $this->getWriteConnection()->query($sql);
            } else {

                $sql = "Insert into " . $this->catalogProductSuperAttribute();

                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " ( " . $_tablekey;
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . " )";
                    } else {
                        $sql .= " , " . $_tablekey;
                    }
                    $i++;
                }
                $i = 1;
                foreach ($value as $_tablevalue) {
                    if ($i == 1) {
                        $sql .= " VALUES ( '" . $_tablevalue . "'";
                    } elseif (count($value) == $i) {
                        $sql .= " , '" . $_tablevalue . "' )";
                    } else {
                        $sql .= " , '" . $_tablevalue . "'";
                    }
                    $i++;
                }
                $this->getWriteConnection()->query($sql);
                $productSuperAttributeId = $this->getWriteConnection()->lastInsertId();
            }
            $productSuperAttributeIdArray[$Attkey] = $productSuperAttributeId;
        }

        return $productSuperAttributeIdArray;
    }

    public function _dumpProductSuperAttributeLabelTable($values, $productSuperAttributeIdArray)
    {

        foreach ($values as $Attkey => $value) {
            $productSuperAttributeId = $productSuperAttributeIdArray[$Attkey];
            $value['product_super_attribute_id'] = $productSuperAttributeIdArray[$Attkey];
            $storeId = $value['store_id'];

            $valueId = $this->getReadConnection()->fetchOne("select value_id from " . $this->catalogProductSuperAttributeLabel() . " where product_super_attribute_id='" . $productSuperAttributeId . "' AND store_id='" . $storeId . "'");
            $_tablecolumn = array_keys($value);

            if ($valueId) {

                $sql = "Update " . $this->catalogProductSuperAttributeLabel();
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . $value[$_tablekey] . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "' where product_super_attribute_id=" . $productSuperAttributeId . " AND store_id=" . $storeId;
                    } else {
                        $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "'";
                    }
                    $i++;
                }


                $this->getWriteConnection()->query($sql);
            } else {

                $sql = "Insert into " . $this->catalogProductSuperAttributeLabel();

                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " ( " . $_tablekey;
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . " )";
                    } else {
                        $sql .= " , " . $_tablekey;
                    }
                    $i++;
                }
                $i = 1;
                foreach ($value as $_tablevalue) {
                    if ($i == 1) {
                        $sql .= " VALUES ( '" . $_tablevalue . "'";
                    } elseif (count($value) == $i) {
                        $sql .= " , '" . $_tablevalue . "' )";
                    } else {
                        $sql .= " , '" . $_tablevalue . "'";
                    }
                    $i++;
                }
                $this->getWriteConnection()->query($sql);
            }
        }
    }

    public function _dumpProductSuperAttributePricingTable($values, $productSuperAttributeIdArray, $optionIdArray)
    {

        foreach ($values as $Attkey => $value) {
            $productSuperAttributeId = $productSuperAttributeIdArray[$Attkey];
            $value['product_super_attribute_id'] = $productSuperAttributeIdArray[$Attkey];
            $websiteId = $value['website_id'];
            $getAttributeInfo = $this->_connectionEav->getAttributeInfo($Attkey,$this->entityTypeId);
            $attId = $getAttributeInfo['attribute_id'];
            $value['value_index'] = $optionIdArray[$attId];

            $valueId = $this->getReadConnection()->fetchOne("select value_id from " . $this->catalogProductSuperAttributePricing() . " where product_super_attribute_id='" . $productSuperAttributeId . "' AND store_id='" . $storeId . "'");
            $_tablecolumn = array_keys($value);

            if ($valueId) {

                $sql = "Update " . $this->catalogProductSuperAttributePricing();
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . $value[$_tablekey] . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "' where product_super_attribute_id=" . $productSuperAttributeId . " AND store_id=" . $storeId;
                    } else {
                        $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "'";
                    }
                    $i++;
                }

                $this->getWriteConnection()->query($sql);
            } else {

                $sql = "Insert into " . $this->catalogProductSuperAttributePricing();

                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " ( " . $_tablekey;
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . " )";
                    } else {
                        $sql .= " , " . $_tablekey;
                    }
                    $i++;
                }
                $i = 1;
                foreach ($value as $_tablevalue) {
                    if ($i == 1) {
                        $sql .= " VALUES ( '" . $_tablevalue . "'";
                    } elseif (count($value) == $i) {
                        $sql .= " , '" . $_tablevalue . "' )";
                    } else {
                        $sql .= " , '" . $_tablevalue . "'";
                    }
                    $i++;
                }
                $this->getWriteConnection()->query($sql);
            }
        }
    }

    public function _dumpProductSuperLinkTable($value, $productId)
    {

        $value['product_id'] = $productId;

        $valueId = $this->getReadConnection()->fetchOne("select * from " . $this->catalogProductSuperLink() . " where product_id='" . $productId . "' AND parent_id='" . $value['parent_id'] . "'");
        $_tablecolumn = array_keys($value);

        if ($valueId) {

            $sql = "Update " . $this->catalogProductSuperLink();
            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " SET " . $_tablekey . "='" . $value[$_tablekey] . "'";
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "' where product_id=" . $productId . " AND parent_id=" . $value['parent_id'];
                } else {
                    $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "'";
                }
                $i++;
            }


            $this->getWriteConnection()->query($sql);
        } else {

            $sql = "Insert into " . $this->catalogProductSuperLink();

            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " ( " . $_tablekey;
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . " )";
                } else {
                    $sql .= " , " . $_tablekey;
                }
                $i++;
            }
            $i = 1;
            foreach ($value as $_tablevalue) {
                if ($i == 1) {
                    $sql .= " VALUES ( '" . $_tablevalue . "'";
                } elseif (count($value) == $i) {
                    $sql .= " , '" . $_tablevalue . "' )";
                } else {
                    $sql .= " , '" . $_tablevalue . "'";
                }
                $i++;
            }
            $this->getWriteConnection()->query($sql);
        }
    }

    public function _dumpCatalogProductRelationTable($value, $productId)
    {

        $value['child_id'] = $productId;
        unset($value['product_id']);

        $valueId = $this->getReadConnection()->fetchOne("select * from " . $this->catalogProductRelation() . " where child_id='" . $productId . "' AND parent_id='" . $value['parent_id'] . "'");
        $_tablecolumn = array_keys($value);

        if ($valueId) {

            $sql = "Update " . $this->catalogProductRelation();
            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " SET " . $_tablekey . "='" . $value[$_tablekey] . "'";
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "' where child_id=" . $productId . " AND parent_id=" . $value['parent_id'];
                } else {
                    $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "'";
                }
                $i++;
            }
            $this->getWriteConnection()->query($sql);
        } else {

            $sql = "Insert into " . $this->catalogProductRelation();

            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " ( " . $_tablekey;
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . " )";
                } else {
                    $sql .= " , " . $_tablekey;
                }
                $i++;
            }
            $i = 1;
            foreach ($value as $_tablevalue) {
                if ($i == 1) {
                    $sql .= " VALUES ( '" . $_tablevalue . "'";
                } elseif (count($value) == $i) {
                    $sql .= " , '" . $_tablevalue . "' )";
                } else {
                    $sql .= " , '" . $_tablevalue . "'";
                }
                $i++;
            }
            $this->getWriteConnection()->query($sql);
        }
    }

    public function _dumpCatalogProductWebsite($values, $productId)
    {

        foreach ($values as $value) {
            $value['product_id'] = $productId;
            $websiteId = $value['website_id'];
            $productInfo = $this->getReadConnection()->fetchOne("select * from " . $this->catalogProductWebsite() . " where product_id='" . $productId . "' AND website_id='" . $websiteId . "'");
            $_tablecolumn = array_keys($value);
            if ($productInfo) {
                $sql = "Update " . $this->catalogProductWebsite();
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . $value[$_tablekey] . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "' where product_id=" . $productId . " AND website_id=" . $websiteId;
                    } else {
                        $sql .= " , " . $_tablekey . "='" . $value[$_tablekey] . "'";
                    }
                    $i++;
                }
                $this->getWriteConnection()->query($sql);
            } else {
                $sql = "Insert into " . $this->catalogProductWebsite();

                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " ( " . $_tablekey;
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . " )";
                    } else {
                        $sql .= " , " . $_tablekey;
                    }
                    $i++;
                }
                $i = 1;
                foreach ($value as $_tablevalue) {
                    if ($i == 1) {
                        $sql .= " VALUES ( '" . $_tablevalue . "'";
                    } elseif (count($value) == $i) {
                        $sql .= " , '" . $_tablevalue . "' )";
                    } else {
                        $sql .= " , '" . $_tablevalue . "'";
                    }
                    $i++;
                }
                $this->getWriteConnection()->query($sql);
            }
        }
    }

    public function _dumpTierPrice($value, $productId)
    {
        $value['entity_id'] = $productId;
        foreach ($value[0]['value'] as  $tierpricevalue) {
            $allGroups = $tierpricevalue['all_groups'];
            $customerGroupId = $tierpricevalue['customer_group_id'];
            $qty = $tierpricevalue['qty'];
            $websiteId = $tierpricevalue['website_id'];
            $tierpricevalue['entity_id'] = $productId;
            $_tablecolumn = array_keys($tierpricevalue);


            $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $this->catalogProductEntityTierPrice() . " where entity_id='" . $productId . "' AND all_groups='" . $allGroups . "' AND customer_group_id='" . $customerGroupId . "' AND qty='" . $qty . "' AND website_id='" . $websiteId . "'");

            $attribute_id = $TableInfo;
            if ($TableInfo) {

                $value_id = $TableInfo;
                $sql = "Update " . $this->catalogProductEntityTierPrice();
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "' where value_id='" . $value_id . "'";
                    } else {
                        $sql .= " , " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "'";
                    }
                    $i++;
                }

                $this->getWriteConnection()->query($sql);
            } else {

                $sql = "Insert into " . $this->catalogProductEntityTierPrice();

                $i = 1;

                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " ( " . $_tablekey;
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . " )";
                    } else {
                        $sql .= " , " . $_tablekey;
                    }
                    $i++;
                }
                $i = 1;
                foreach ($tierpricevalue as $_tablevalue) {
                    if ($i == 1) {
                        $sql .= " VALUES ( '" . addslashes($_tablevalue) . "'";
                    } elseif (count($tierpricevalue) == $i) {
                        $sql .= " , '" . addslashes($_tablevalue) . "' )";
                    } else {
                        $sql .= " , '" . addslashes($_tablevalue) . "'";
                    }
                    $i++;
                }
                $this->getWriteConnection()->query($sql);
            }
        }
    }

    public function _dumpGroupPrice($values, $productId)
    {

        foreach ($values as $value) {
            $value['entity_id'] = $productId;
            $allGroups = $value['all_groups'];
            $customerGroupId = $value['customer_group_id'];
            $price = $value['value'];
            $websiteId = $value['website_id'];
            $_tablecolumn = array_keys($value);

            $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $this->catalogProductEntityGroupPrice() . " where entity_id='" . $productId . "' AND all_groups='" . $allGroups . "' AND customer_group_id='" . $customerGroupId . "' AND website_id='" . $websiteId . "'");
            $attribute_id = $TableInfo;
            if ($TableInfo) {

                $value_id = $TableInfo;
                $sql = "Update " . $this->catalogProductEntityGroupPrice();
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "' where value_id='" . $value_id . "'";
                    } else {
                        $sql .= " , " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "'";
                    }
                    $i++;
                }

                $this->getWriteConnection()->query($sql);
            } else {

                $sql = "Insert into " . $this->catalogProductEntityGroupPrice();

                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " ( " . $_tablekey;
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . " )";
                    } else {
                        $sql .= " , " . $_tablekey;
                    }
                    $i++;
                }
                $i = 1;
                foreach ($value as $_tablevalue) {
                    if ($i == 1) {
                        $sql .= " VALUES ( '" . addslashes($_tablevalue) . "'";
                    } elseif (count($value) == $i) {
                        $sql .= " , '" . addslashes($_tablevalue) . "' )";
                    } else {
                        $sql .= " , '" . addslashes($_tablevalue) . "'";
                    }
                    $i++;
                }
                $this->getWriteConnection()->query($sql);
            }
        }
    }

    public function _dumpInventorySourceItem($value, $productSku)
    {
        $_tablecolumn = array_keys($value);

        $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $this->inventorySourceItem() . " where sku='" . $productSku . "'");
        if ($TableInfo) {

            $value_id = $TableInfo;
            $sql = "Update " . $this->inventorySourceItem();
            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " SET " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "'";
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "' where source_item_id='" . $value_id . "'";
                } else {
                    $sql .= " , " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "'";
                }
                $i++;
            }

            $this->getWriteConnection()->query($sql);
        } else {

            $sql = "Insert into " . $this->inventorySourceItem();

            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " ( " . $_tablekey;
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . " )";
                } else {
                    $sql .= " , " . $_tablekey;
                }
                $i++;
            }

            $i = 1;
            foreach ($value as $_tablevalue) {
                if ($i == 1) {
                    $sql .= " VALUES ( '" . addslashes($_tablevalue) . "'";
                } elseif (count($value) == $i) {
                    $sql .= " , '" . addslashes($_tablevalue) . "' )";
                } else {
                    $sql .= " , '" . addslashes($_tablevalue) . "'";
                }
                $i++;
            }

            file_put_contents(BP . '/var/log/events.log', print_r($sql,true)."\n", FILE_APPEND);
            
            $this->getWriteConnection()->query($sql);
        }
        return;
    }



    public function addImage($imageUrl, $move = false)
    {
        /** @var string $tmpDir */
        $tmpDir = $this->getMediaDirTmpDir();
        /** create folder if it is not exists */
        $this->_file->checkAndCreateFolder($tmpDir);
        /** @var string $newFileName */
        $newFileName = $tmpDir . baseName($imageUrl);
        /** read file from URL and copy it to the new destination */
        $result = $this->_file->read($imageUrl, $newFileName);
        if($result){

            $file = $newFileName;
            $file = $this->_mediaDirectory->getRelativePath($file);
            if (!$this->_mediaDirectory->isFile($file)) {
                throw new LocalizedException(__("The image doesn't exist."));
            }

            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $pathinfo = pathinfo($file);
            $imgExtensions = ['jpg', 'jpeg', 'gif', 'png'];
            if (!isset($pathinfo['extension']) || !in_array(strtolower($pathinfo['extension']), $imgExtensions)) {
                throw new LocalizedException(
                    __('The image type for the file is invalid. Enter the correct image type and try again.')
                );
            }

            $fileName = \Magento\MediaStorage\Model\File\Uploader::getCorrectFileName($pathinfo['basename']);
            $dispersionPath = \Magento\MediaStorage\Model\File\Uploader::getDispersionPath($fileName);
            $fileName = $dispersionPath . '/' . $fileName;

            $fileName = $this->getNotDuplicatedFilename($fileName, $dispersionPath);

            $destinationFile = $this->_mediaConfig->getMediaPath($fileName);
           

            try {
                /** @var $storageHelper \Magento\MediaStorage\Helper\File\Storage\Database */
                $storageHelper = $this->_fileStorageDb;
                if ($move) {
                    $this->_mediaDirectory->renameFile($file, $destinationFile);

                    //If this is used, filesystem should be configured properly
                    $storageHelper->saveFile($this->_mediaConfig->getMediaShortUrl($fileName));
                } else {
                    $this->_mediaDirectory->copyFile($file, $destinationFile);

                    $storageHelper->saveFile($this->_mediaConfig->getMediaShortUrl($fileName));
                }
            } catch (\Exception $e) {
                throw new LocalizedException(__('The "%1" file couldn\'t be moved.', $e->getMessage()));
            }

            $fileName = str_replace('\\', '/', $fileName);

            return $fileName;
        }else{
            return null;
        }
    }

    protected function getMediaDirTmpDir()
    {
        return $this->_directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
    }

    protected function getNotDuplicatedFilename($fileName, $dispersionPath)
    {
        $fileMediaName = $dispersionPath . '/'
            . \Magento\MediaStorage\Model\File\Uploader::getNewFileName($this->_mediaConfig->getMediaPath($fileName));
        $fileTmpMediaName = $dispersionPath . '/'
            . \Magento\MediaStorage\Model\File\Uploader::getNewFileName($this->_mediaConfig->getTmpMediaPath($fileName));

        if ($fileMediaName != $fileTmpMediaName) {
            if ($fileMediaName != $fileName) {
                return $this->getNotDuplicatedFilename(
                    $fileMediaName,
                    $dispersionPath
                );
            } elseif ($fileTmpMediaName != $fileName) {
                return $this->getNotDuplicatedFilename(
                    $fileTmpMediaName,
                    $dispersionPath
                );
            }
        }

        return $fileMediaName;
    }
}
