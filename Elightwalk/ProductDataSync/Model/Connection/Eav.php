<?php

namespace Elightwalk\ProductDataSync\Model\Connection;

class Eav extends AbstractModel
{

    // EAV Table Defination 

    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\ResourceConnection $connection
    ) {
        $this->_objectmanager = $objectmanager;
        parent::__construct($connection);
    }

    public function eavAttribute()
    {
        $tableName = $this->getCoreResource()->getTableName('eav_attribute');
        return $tableName;
    }

    public function eavEntityType()
    {
        $tableName = $this->getCoreResource()->getTableName('eav_entity_type');
        return $tableName;
    }

    // Attribute Table Defination

    public function catalogEavAttribute()
    {
        $tableName = $this->getCoreResource()->getTableName('catalog_eav_attribute');
        return $tableName;
    }

    public function eavAttributeLabel()
    {
        $tableName = $this->getCoreResource()->getTableName('eav_attribute_label');
        return $tableName;
    }

    public function eavAttributeOption()
    {
        $tableName = $this->getCoreResource()->getTableName('eav_attribute_option');
        return $tableName;
    }

    public function eavAttributeOptionValue()
    {
        $tableName = $this->getCoreResource()->getTableName('eav_attribute_option_value');
        return $tableName;
    }

    public function eavAttributeOptionSwatch()
    {
        $tableName = $this->getCoreResource()->getTableName('eav_attribute_option_swatch');
        return $tableName;
    }

    // Attribute Set Table Defination

    public function eavAttributeSet()
    {
        $tableName = $this->getCoreResource()->getTableName('eav_attribute_set');
        return $tableName;
    }

    public function eavEntityAttribute()
    {
        $tableName = $this->getCoreResource()->getTableName('eav_entity_attribute');
        return $tableName;
    }

    public function eavAttributeGroup()
    {
        $tableName = $this->getCoreResource()->getTableName('eav_attribute_group');
        return $tableName;
    }

    public function getAttributeInfoById($attributeId)
    {
        $attributeInfo = $this->getReadConnection()->fetchAll("select * from " . $this->eavAttribute() . " where ( attribute_id='" . $attributeId . "')");
        return $attributeInfo;
    }


    public function getAttributeInfo($attributeCode, $entityTypeId)
    {
        $attributeInfo = $this->getReadConnection()->fetchRow("select * from " . $this->eavAttribute() . " where ( attribute_code='" . $attributeCode . "' AND entity_type_id='" . $entityTypeId . "' ) OR ( frontend_label='" . $attributeCode . "' AND entity_type_id='" . $entityTypeId . "' )");
        return $attributeInfo;
    }

    public function _dumpAttributeOptionTable($values)
    {
        $optionArray = array();
        foreach ($values as $value) {
            $attribute_id = $value['attribute_id'];
            $sort_order = $value['sort_order'];
            $options = $value['options'];
            

            $optionId = $this->getReadConnection()->fetchOne("select * from " . $this->eavAttributeOption() . "," . $this->eavAttributeOptionValue() . " where " . $this->eavAttributeOption() . ".option_id=" . $this->eavAttributeOptionValue() . ".option_id AND " . $this->eavAttributeOption() . ".attribute_id='" . $attribute_id . "' AND " . $this->eavAttributeOptionValue() . ".value='" . addslashes($options['value']) . "'");

            if (!$optionId) {

                $sql = "Insert into " . $this->eavAttributeOption() . " (`attribute_id`,`sort_order`) VALUES ('" . $attribute_id . "','" . $sort_order . "')";
                $this->getWriteConnection()->query($sql);
                $optionId = $this->getWriteConnection()->lastInsertId();

                $sql = "Insert into " . $this->eavAttributeOptionValue() . " (`option_id`,`store_id`,`value`) VALUES ('" . $optionId . "','" . $options['store_id'] . "','" . addslashes($options['value']) . "')";
                $this->getWriteConnection()->query($sql);

                if(isset($value['swatch'])){
                    $swatch = $value['swatch'];
                    $sql = "Insert into " . $this->eavAttributeOptionSwatch() . " (`option_id`,`store_id`,`type`,`value`) VALUES ('" . $optionId . "','" . $swatch['store_id'] . "','" . $swatch['type'] . "','" . addslashes($swatch['value']) . "')";
                    $this->getWriteConnection()->query($sql);
                }
 
            }else{
                //$sql = "Insert into " . $this->eavAttributeOptionSwatch() . " (`option_id`,`store_id`,`type`,`value`) VALUES ('" . $optionId . "','" . $options['store_id'] . "','" . $options['type'] . "','" . addslashes($options['value']) . "')";
                if(isset($value['swatch'])){
                    $swatch = $value['swatch'];
                    $sql="update " . $this->eavAttributeOptionSwatch() . " set value='".addslashes($swatch['value'])."' , type='".$swatch['type']."' where option_id='".$optionId."' AND store_id='".$swatch['store_id']."'";
                    $this->getWriteConnection()->query($sql);
                }
            }

            $optionArray[$attribute_id] = $optionId;
        }
        return $optionArray;
    }

    // Eav Decimal Table Entry
    public function _dumpDeciamlTable($value, $entityId, $tableName)
    {

        foreach ($value as $row) {
            $row['entity_id'] = $entityId;
            $attribute_code = $row['attribute_code'];
            unset($row['attribute_code']);
            $_tablecolumn = array_keys($row);
            $attribute_id = $row['attribute_id'];
            $store_id = $row['store_id'];
            $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $tableName . " where entity_id='" . $entityId . "' AND attribute_id='" . $attribute_id . "' AND store_id='" . $store_id . "'");

            if ($TableInfo) {
                $sql = "Update " . $tableName;
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . $row[$_tablekey] . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . $row[$_tablekey] . "' where entity_id='" . $entityId . "' AND attribute_id='" . $attribute_id . "' AND store_id='" . $store_id . "'";
                    } else {
                        $sql .= " , " . $_tablekey . "='" . $row[$_tablekey] . "'";
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

    // Eav Varchar Table Entry
    public function _dumpVarcharTable($value, $entityId, $tableName, $storeIdAdmin)
    {
        //exit;
        foreach ($value as $row) {
            $row['entity_id'] = $entityId;
            $attribute_code = $row['attribute_code'];
            unset($row['attribute_code']);

            $attributeInfo = $this->getAttributeInfoById($row['attribute_id']);

            $_rowValueArray = explode(",", $row['value']);
            $rowValueArray = [];
            foreach ($_rowValueArray as $_rowValueAr) {
                $rowValueArray[] = trim($_rowValueAr);
            }
            $newValueArray = [];

            if (count($attributeInfo) > 0) {

                $backend_type = $attributeInfo[0]['backend_type'];
                if ($backend_type != "varchar") {
                    $source_model = $attributeInfo[0]['source_model'];
                    $isUserDefined = $attributeInfo[0]['is_user_defined'];
                    if ($source_model != "" && $isUserDefined != 1) {

                        $modelArray = Mage::getModel($source_model)->getAllOptions();

                        foreach ($modelArray as $mKey => $mValue) {
                            if (in_array($mValue['value'], $rowValueArray)) {
                                $newValueArray[] = $mValue['value'];
                            }
                        }

                        $row['value'] = implode(",", $newValueArray);
                    } else {

                        $sql = "select * from " . $this->eavAttributeOption() . ' as eo , ' . $this->eavAttributeOptionValue() . " as eov where eo.option_id=eov.option_id and eo.attribute_id=" . $row['attribute_id'];

                        $allOption = $this->getReadConnection()->fetchAll($sql);
                        $isMatched = false;
                        foreach ($allOption as $mValue) {
                            if (in_array($mValue['value'], $rowValueArray)) {
                                $newValueArray[] = $mValue['option_id'];
                                $isMatched = true;
                            }
                        }
                        if ($isMatched == false) {
                            $attCode = $attributeInfo[0]['source_model'];
                            $attId = $attributeInfo[0]['attribute_id'];
                            $tmpCollector["attribute_option_table"][$attCode]["attribute_id"] = $attId;
                            $tmpCollector["attribute_option_table"][$attCode]["sort_order"] = 0;
                            $tmpCollector["attribute_option_table"][$attCode]["options"]["option_id"] = null;
                            $tmpCollector["attribute_option_table"][$attCode]["options"]["store_id"] = $storeIdAdmin;
                            $tmpCollector["attribute_option_table"][$attCode]["options"]["value"] = $row['value'];
                            $optionId = $this->_dumpAttributeOptionTable($tmpCollector["attribute_option_table"]);
                            $newValueArray[] = $optionId[$attId];
                        }

                        $row['value'] = implode(",", $newValueArray);
                    }
                }
            }


            $_tablecolumn = array_keys($row);
            $attribute_id = $row['attribute_id'];
            $store_id = $row['store_id'];
            $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $tableName . " where entity_id='" . $entityId . "' AND attribute_id='" . $attribute_id . "' AND store_id='" . $store_id . "'");

            if ($TableInfo) {
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
                //Zend_Debug::dump($value);
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

    // Eav Datetime Table Entry	
    public function _dumpDatetimeTable($value, $entityId, $tableName)
    {
        foreach ($value as $row) {
            $row['entity_id'] = $entityId;
            $attribute_code = $row['attribute_code'];
            unset($row['attribute_code']);
            $_tablecolumn = array_keys($row);
            $attribute_id = $row['attribute_id'];
            $store_id = $row['store_id'];
            $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $tableName . " where entity_id='" . $entityId . "' AND attribute_id='" . $attribute_id . "' AND store_id='" . $store_id . "'");

            if ($TableInfo) {
                $sql = "Update " . $tableName;
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . $row[$_tablekey] . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . $row[$_tablekey] . "' where entity_id='" . $entityId . "' AND attribute_id='" . $attribute_id . "' AND store_id='" . $store_id . "'";
                    } else {
                        $sql .= " , " . $_tablekey . "='" . $row[$_tablekey] . "'";
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

    // Eav INT Table Entry	
    public function _dumpIntTable($value, $entityId, $tableName, $optionIdArray = array(), $storeIdAdmin)
    {
        foreach ($value as $row) {
            $attributeInfo = $this->getAttributeInfoById($row['attribute_id']);
            $isMatched = false;
            if (count($attributeInfo) > 0) {
                $source_model = $attributeInfo[0]['source_model'];
                $isUserDefined = $attributeInfo[0]['is_user_defined'];
                $frontendInput = $attributeInfo[0]['frontend_input'];
                if ($source_model != "" && $isUserDefined != 1) {

                    $modelArray = $this->_objectmanager->get($source_model)->getAllOptions();

                    foreach ($modelArray as $mKey => $mValue) {
                        if (is_object($mValue['label'])) {
                            if ($row['value'] == $mValue['label']->getText()) {
                                $row['value'] = $mValue['value'];
                            }
                        } else {
                            if ($row['value'] == $mValue['label']) {
                                $row['value'] = $mValue['value'];
                            }
                        }
                    }
                    // exit;

                } else {

                    if ($frontendInput == "select") {

                        $sql = "select * from " . $this->eavAttributeOption() . ' as eo , ' . $this->eavAttributeOptionValue() . " as eov where eo.option_id=eov.option_id and eo.attribute_id=" . $row['attribute_id'];

                        $allOption = $this->getReadConnection()->fetchAll($sql);

                        foreach ($allOption as $mValue) {
                            //Zend_Debug::dump($mValue);
                            if ($row['value'] == $mValue['value']) {
                                $row['value'] = $mValue['option_id'];
                                $isMatched = true;
                            }
                        }
                        if ($isMatched == false) {
                            $attCode = $attributeInfo[0]['source_model'];
                            $attId = $attributeInfo[0]['attribute_id'];
                            $tmpCollector["attribute_option_table"][$attCode]["attribute_id"] = $attId;
                            $tmpCollector["attribute_option_table"][$attCode]["sort_order"] = 0;
                            $tmpCollector["attribute_option_table"][$attCode]["options"]["option_id"] = null;
                            $tmpCollector["attribute_option_table"][$attCode]["options"]["store_id"] = $storeIdAdmin;
                            $tmpCollector["attribute_option_table"][$attCode]["options"]["value"] = $row['value'];
                            $optionId = $this->_dumpAttributeOptionTable($tmpCollector["attribute_option_table"]);
                            $row['value'] = $optionId[$attId];
                        }
                    }
                }
            }

            $row['entity_id'] = $entityId;
            $attribute_code = $row['attribute_code'];
            unset($row['attribute_code']);
            $_tablecolumn = array_keys($row);
            $attribute_id = $row['attribute_id'];
            $store_id = $row['store_id'];


            $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $tableName . " where entity_id='" . $entityId . "' AND attribute_id='" . $attribute_id . "' AND store_id='" . $store_id . "'");


            if ($TableInfo) {
                $sql = "Update " . $tableName;
                $i = 1;
                foreach ($_tablecolumn as $_tablekey) {
                    if ($i == 1) {
                        $sql .= " SET " . $_tablekey . "='" . $row[$_tablekey] . "'";
                    } elseif (count($_tablecolumn) == $i) {
                        $sql .= " , " . $_tablekey . "='" . $row[$_tablekey] . "' where entity_id='" . $entityId . "' AND attribute_id='" . $attribute_id . "' AND store_id='" . $store_id . "'";
                    } else {
                        $sql .= " , " . $_tablekey . "='" . $row[$_tablekey] . "'";
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

    // Eav Text Table Entry	
    public function _dumpTextTable($value, $entityId, $tableName)
    {
        foreach ($value as $row) {
            $row['entity_id'] = $entityId;
            $attribute_code = $row['attribute_code'];
            unset($row['attribute_code']);
            $_tablecolumn = array_keys($row);
            $attribute_id = $row['attribute_id'];
            $store_id = $row['store_id'];
            $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $tableName . " where entity_id='" . $entityId . "' AND attribute_id='" . $attribute_id . "' AND store_id='" . $store_id . "'");

            if ($TableInfo) {
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

    public function _dumpeavAttribute($value)
    {

        $attribute_code = $value['attribute_code'];
        $_tablecolumn = array_keys($value);

        $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $this->eavAttribute() . " where attribute_code='" . $attribute_code . "'");
        $attribute_id = $TableInfo;

        if ($TableInfo) {

            $attribute_id = $TableInfo;
            $sql = "Update " . $this->eavAttribute();
            $i = 1;

            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " SET " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "'";
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "' where entity_type_id='4' AND attribute_code='" . $attribute_code . "'";
                } else {
                    $sql .= " , " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "'";
                }
                $i++;
            }

            $this->getWriteConnection()->query($sql);
        } else {

            $sql = "Insert into " . $this->eavAttribute();

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
            $attribute_id = $this->getWriteConnection()->lastInsertId();
        }
        return $attribute_id;
    }

    public function _dumpeavCatalogAttributeTable($value, $attribute_id)
    {
        $value['attribute_id'] = $attribute_id;
        if (count($value) == 1) {
            $value['is_used_for_promo_rules'] = 0;
        }

        $_tablecolumn = array_keys($value);

        $TableInfo = $this->getReadConnection()->fetchOne("select * from " . $this->catalogEavAttribute() . " where attribute_id='" . $attribute_id . "'");
        if ($TableInfo) {

            $sql = "Update " . $this->catalogEavAttribute();
            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " SET " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "'";
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "' where attribute_id='" . $attribute_id . "'";
                } else {
                    $sql .= " , " . $_tablekey . "='" . addslashes($value[$_tablekey]) . "'";
                }
                $i++;
            }
            $this->getWriteConnection()->query($sql);
        } else {

            $sql = "Insert into " . $this->catalogEavAttribute();

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

    public function _checkeavAttributeset($attributeSetId, $attributeId, $entityTypeId)
    {
        $TableChildInfo = $this->getReadConnection()->fetchRow("select * from " . $this->eavEntityAttribute() . " where attribute_set_id='" . $attributeSetId . "' AND entity_type_id='" . $entityTypeId . "' AND attribute_id='" . $attributeId . "'");
        if ($TableChildInfo) {
            return $TableChildInfo;
        }
        return false;
    }

    public function _dumpEavAttributeGroupTable($row, $attributeSetId = 0)
    {
        if ($attributeSetId !== 0) {
            $row['attribute_set_id'] = $attributeSetId;
        }
        $_tablecolumn = array_keys($row);

        $TableChildInfo = $this->getReadConnection()->fetchOne("select * from " . $this->eavEntityAttribute() . " where attribute_group_id='" . $row['attribute_group_id'] . "' AND attribute_set_id='" . $row['attribute_set_id'] . "' AND entity_type_id='" . $row['entity_type_id'] . "' AND attribute_id='" . $row['attribute_id'] . "'");

        if ($TableChildInfo) {

            $sql = "Update " . $this->eavEntityAttribute();
            $i = 1;
            foreach ($_tablecolumn as $_tablekey) {
                if ($i == 1) {
                    $sql .= " SET " . $_tablekey . "='" . addslashes($row[$_tablekey]) . "'";
                } elseif (count($_tablecolumn) == $i) {
                    $sql .= " , " . $_tablekey . "='" . addslashes($row[$_tablekey]) . "' where attribute_group_id='" . $row['attribute_group_id'] . "' AND attribute_set_id='" . $row['attribute_set_id'] . "' AND entity_type_id='" . $row['entity_type_id'] . "' AND attribute_id='" . $row['attribute_id'] . "'";
                } else {
                    $sql .= " , " . $_tablekey . "='" . addslashes($row[$_tablekey]) . "'";
                }
                $i++;
            }
            $this->getWriteConnection()->query($sql);
        } else {

            $sql = "Insert into " . $this->eavEntityAttribute();

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
