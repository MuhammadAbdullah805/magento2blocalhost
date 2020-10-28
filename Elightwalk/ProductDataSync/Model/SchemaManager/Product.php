<?php

namespace Elightwalk\ProductDataSync\Model\SchemaManager;

use Magento\Framework\Exception\FileSystemException;

class Product extends AbstractModel
{
    protected $_connectionProduct;

    protected $_connectionEav;

    protected $_connectionCategory;

    protected $_website;

    protected $_varchar = 0;

    protected $_varchar_media = 0;

    protected $_staticvb = 0;

    protected $storeIdAdmin = 0;

    protected $websiteIdAdmin = 0;

    protected $storeIds = [];

    protected $websites = [["code" => "base"]];



    public function __construct(
        \Elightwalk\ProductDataSync\Model\Connection\Product $connectionProduct,
        \Elightwalk\ProductDataSync\Model\Connection\Eav $connectionEav,
        \Elightwalk\ProductDataSync\Model\Connection\Category $connectionCategory,
        \Magento\Store\Model\Website $website
    ) {
        $this->_connectionProduct = $connectionProduct;
        $this->_connectionEav = $connectionEav;
        $this->_connectionCategory = $connectionCategory;
        $this->_website = $website;
    }

    public function schemaCreator($row, $argument)
    {
        try {
            $_isConfigProduct = false;
            if (isset($row['variants'])) {
                if (count($row['variants']) > 0) {
                    $_isConfigProduct = true;
                }
            }
            if (isset($row['variants'])) {
                $variants = $row['variants'];
            } else {
                $variants = [];
            }
            unset($row['variants']);
        
            $stockAttributes = [];
            if (isset($row['stock_option'])) {
                $stockAttributes = $row['stock_option'];
            }
            unset($row['stock_option']);

            $schemaCollector = [];

            if (!$this->_connectionProduct->isProduct($row['sku'])) {
                $schemaCollector['master_table']["attribute_set_id"] = $row['attribute_set'];

                if ($_isConfigProduct) {
                    $schemaCollector["master_table"]["type_id"] = "configurable";
                    $schemaCollector["master_table"]["has_options"] = 1;
                    $schemaCollector["master_table"]["required_options"] = 1;
                    $row['options_container'] = 'container1';
                } else {
                    if (isset($row['type'])) {
                        $schemaCollector["master_table"]["type_id"] = $row['type_id'];
                        $schemaCollector["master_table"]["has_options"] = 0;
                        $schemaCollector["master_table"]["required_options"] = 0;
                    } else {
                        $schemaCollector["master_table"]["type_id"] = "virtual";
                        $schemaCollector["master_table"]["has_options"] = 0;
                        $schemaCollector["master_table"]["required_options"] = 0;
                    }
                }
            } else {
                $schemaCollector["master_table"] = array();
            }


            $this->varchar = 0;
            $this->varchar_media = 0;
            $this->staticvb = 0;

            $_keyAttribute = array_keys($row);
            $_productTableColumnsArray = $this->_connectionProduct->getTableColumnsInArray($this->_connectionProduct->catalogProductEntity());




            $basicProductAttributeCollector = $this->basicProductAttributeCollector($_keyAttribute, $_productTableColumnsArray, $row);
            $schemaCollector = array_merge_recursive($schemaCollector, $basicProductAttributeCollector);

            $stockCollector = $this->stockCollector($stockAttributes);
            $schemaCollector = array_merge_recursive($schemaCollector, $stockCollector);

            $websitesCollector = $this->websitesCollector($this->websites);
            $schemaCollector = array_merge_recursive($schemaCollector, $websitesCollector);

            if(isset($argument['inventory'])):
                $mediaGalleryCollector = $this->mediaGalleryCollector($row);
                $schemaCollector = array_merge_recursive($schemaCollector, $mediaGalleryCollector);
            endif;


            if (isset($row['category_ids'])) {
                $categoriesCollector = $this->categoriesCollector($row['category_ids']);
                $schemaCollector = array_merge_recursive($schemaCollector, $categoriesCollector);
            }

            $inventorySourceItem = $this->getInventorySourceItem($row);
            $schemaCollector = array_merge_recursive($schemaCollector, $inventorySourceItem);
            
            //print_r($schemaCollector);
            //exit;
            $parentId = $this->_maysqldumpdate($schemaCollector);

            if ($_isConfigProduct) {

                //print_r($variants);
                foreach ($variants as $variant) {

                    $schemaCollector = array();
                    $this->varchar = 0;
                    $this->varchar_media = 0;
                    $this->staticvb = 0;
                    if (!$this->_connectionProduct->isProduct($variant['sku'])) {
                        $schemaCollector['master_table']["attribute_set_id"] = $variant['attribute_set'];
                        $schemaCollector["master_table"]["type_id"] = "simple";
                        $schemaCollector["master_table"]["has_options"] = 0;
                        $schemaCollector["master_table"]["required_options"] = 0;
                    } else {
                        $schemaCollector["master_table"]["type_id"] = "simple";
                    }
                    $name = $variant['name'];
                    $config_option = $variant['config_option'];
                    foreach ($config_option as $option) {
                        $option['attribute'] = strtolower($option['attribute']);
                        $configAttribute = $option['attribute'];
                        $configAttributeValue = $option['value'];
                        $configAttributeTitle = $option['title'];
                        $attributeInfo = array();
                        $attributeInfo = $this->_connectionEav->getAttributeInfo($configAttribute, $this->_connectionProduct->entityTypeId);

                        if (count($attributeInfo) == 0) {
                            $attributeArray = array();
                            $attributeArray['entity_type_id'] = $this->_connectionProduct->entityTypeId;
                            $attributeArray['attribute_code'] = $configAttribute;
                            $attributeArray['backend_type'] = 'int';
                            $attributeArray['frontend_input'] = 'select';
                            $attributeArray['frontend_label'] = $configAttributeTitle;
                            $attributeArray['is_required'] = 0;
                            $attributeArray['is_user_defined'] = 1;
                            $attributeArray['is_unique'] = 0;

                            $catalogAttributeArray = array();
                            $catalogAttributeArray['is_global'] = 1;
                            $catalogAttributeArray['apply_to'] = "simple,configurable,virtual,bundle,downloadable";


                            $attributeId = $this->_connectionEav->_dumpeavAttribute($attributeArray);
                            $this->_connectionEav->_dumpeavCatalogAttributeTable($catalogAttributeArray, $attributeId);

                            $checkattributeset = $this->_connectionEav->_checkeavAttributeset($variant['attribute_set'], $attributeId, $this->_connectionProduct->entityTypeId);

                            $attributeGroupMove = array();
                            if ($checkattributeset) {
                                $attributeGroupMove["entity_type_id"] = $this->_connectionProduct->entityTypeId;
                                $attributeGroupMove["attribute_set_id"] = $variant['attribute_set'];
                                $attributeGroupMove["attribute_group_id"] = $checkattributeset['attribute_group_id'];
                                $attributeGroupMove["attribute_id"] = $attributeId;
                                $attributeGroupMove["sort_order"] = $checkattributeset['sort_order'];
                            } else {
                                $attributeGroupMove["entity_type_id"] = $this->_connectionProduct->entityTypeId;
                                $attributeGroupMove["attribute_set_id"] = $variant['attribute_set'];
                                $attributeGroupMove["attribute_group_id"] = 7;
                                $attributeGroupMove["attribute_id"] = $attributeId;
                                $attributeGroupMove["sort_order"] = 7;
                            }

                            $this->_connectionEav->_dumpEavAttributeGroupTable($attributeGroupMove);
                        } else {
                            $attributeCode = $attributeInfo['attribute_code'];
                            $attributeId = $attributeInfo['attribute_id'];
                            $attributeArray['attribute_code'] = $attributeCode;
                            $attributeArray['attribute_id'] = $attributeId;
                            $attributeArray['backend_type'] = 'int';
                            $attributeArray['frontend_input'] = 'select';

                            $catalogAttributeArray = array();
                            $catalogAttributeArray['is_global'] = 1;
                            $catalogAttributeArray['apply_to'] = "simple,configurable,virtual,bundle,downloadable";
                            $attributeId = $this->_connectionEav->_dumpeavAttribute($attributeArray);
                            $this->_connectionEav->_dumpeavCatalogAttributeTable($catalogAttributeArray, $attributeId);

                            $checkattributeset = $this->_connectionEav->_checkeavAttributeset($variant['attribute_set'], $attributeId, $this->_connectionProduct->entityTypeId);

                            $attributeGroupMove = array();
                            if ($checkattributeset) {
                                $attributeGroupMove["entity_type_id"] = $this->_connectionProduct->entityTypeId;
                                $attributeGroupMove["attribute_set_id"] = $variant['attribute_set'];
                                $attributeGroupMove["attribute_group_id"] = $checkattributeset['attribute_group_id'];
                                $attributeGroupMove["attribute_id"] = $attributeId;
                                $attributeGroupMove["sort_order"] = $checkattributeset['sort_order'];
                            } else {
                                $attributeGroupMove["entity_type_id"] = $this->_connectionProduct->entityTypeId;
                                $attributeGroupMove["attribute_set_id"] = $variant['attribute_set'];
                                $attributeGroupMove["attribute_group_id"] = 7;
                                $attributeGroupMove["attribute_id"] = $attributeId;
                                $attributeGroupMove["sort_order"] = 7;
                            }

                            $this->_connectionEav->_dumpEavAttributeGroupTable($attributeGroupMove);
                        }

                        $swatchValue = "";
                        if(isset($variant[$configAttribute.'_swatch'])){
                            $valueArray = explode(',',$variant[$configAttribute.'_swatch']);
                            $swatchValue = $valueArray[0];
                            $hexapos = strpos($swatchValue, '#');
                            if ($hexapos === false) {
                                $swatchValue="#".$swatchValue;
                            }
                        }

                        $variantAttributeCollector = $this->variantAttributeCollector($option,$swatchValue);
                        $schemaCollector = array_merge_recursive($schemaCollector, $variantAttributeCollector);
                        $name .= "-" . $configAttributeValue;
                    }

                    $variant['name'] = $name;

                    $_KeyAttribute = array_keys($variant);
                    $basicProductAttributeCollector = $this->basicProductAttributeCollector($_KeyAttribute, $_productTableColumnsArray, $variant);
                    $schemaCollector = array_merge_recursive($schemaCollector, $basicProductAttributeCollector);
                    

                    // Upadte Stock Attribute
                    $stockAttributes = [];
                    if (isset($variant['stock_option'])) {
                        $stockAttributes = $variant['stock_option'];
                    }
                    unset($variant['stock_option']);

                    $stockCollector = $this->stockCollector($stockAttributes);
                    $schemaCollector = array_merge_recursive($schemaCollector, $stockCollector);
                    if (!$this->_connectionProduct->isProduct($variant['sku'])) {
                        $websitesCollector = $this->websitesCollector($this->websites);
                        $schemaCollector = array_merge_recursive($schemaCollector, $websitesCollector);
                    }

                    if(isset($argument['inventory'])):
                        $mediaGalleryCollector = $this->mediaGalleryCollector($variant);
                        $schemaCollector = array_merge_recursive($schemaCollector, $mediaGalleryCollector);
                    endif;

                    if (isset($variant['category_ids'])) {
                        $categoriesCollector = $this->categoriesCollector($variant['category_ids']);
                        $schemaCollector = array_merge_recursive($schemaCollector, $categoriesCollector);
                    }

                    if (isset($variant['group_price'])) {
                        $groupPriceCollector = $this->groupPriceCollector($variant['group_price']);
                        $schemaCollector = array_merge_recursive($schemaCollector, $groupPriceCollector);
                    }

                    $schemaCollector["product_super_link_table"]["product_id"] = null;
                    $schemaCollector["product_super_link_table"]["parent_id"] = $parentId;

                    $inventorySourceItem = $this->getInventorySourceItem($variant);
                    $schemaCollector = array_merge_recursive($schemaCollector, $inventorySourceItem);

                    

                    //print_r($schemaCollector);
                    //exit;
                    $this->_maysqldumpdate($schemaCollector);
                    //$this->writeFingerPrintId($variant['sku']);

                }
            }
            return ;
        } catch (\Exception $e) {
            throw new FileSystemException(new \Magento\Framework\Phrase($e->getMessage()), $e);
        }

        
        //print_r($schemaCollector);
        // exit;
    }

    protected function _maysqldumpdate($schemaCollector)
    {

        $productId = null;
        $optionId = null;

        foreach ($schemaCollector as $key => $value) {
            if ($key == "master_table") {
                $productId = $this->_connectionProduct->_dumpcatalogProductEntity($value);
            } elseif ($key == "attribute_option_table") {
                $optionId = $this->_connectionEav->_dumpAttributeOptionTable($value);
            } elseif ($key == "decimal_table") {
                $this->_connectionEav->_dumpDeciamlTable($value, $productId, $this->_connectionProduct->catalogProductEntityDecimal());
            } elseif ($key == "varchar_table") {
                $this->_connectionEav->_dumpVarcharTable($value, $productId, $this->_connectionProduct->catalogProductEntityVarchar(), $this->storeIdAdmin);
            } elseif ($key == "datetime_table") {
                $this->_connectionEav->_dumpDatetimeTable($value, $productId, $this->_connectionProduct->catalogProductEntityDatetime());
            } elseif ($key == "int_table") {
                $this->_connectionEav->_dumpIntTable($value, $productId, $this->_connectionProduct->catalogProductEntityInt(), $optionId, $this->storeIdAdmin);
            } elseif ($key == "text_table") {
                $this->_connectionEav->_dumpTextTable($value, $productId, $this->_connectionProduct->catalogProductEntityText());
            } elseif ($key == "category_product_table") {
                $this->_connectionProduct->_dumpCategoryProductTable($value, $productId);
            } elseif ($key == "stock_table") {
                $this->_connectionProduct->_dumpStockTable($value, $productId, $this->_connectionProduct->cataloginventoryStockItem());
            } elseif ($key == "varchar_media_table") {
                $this->_connectionProduct->_dumpVarcharMediaTable($value, $productId, $this->_connectionProduct->catalogProductEntityVarchar());
            } elseif ($key == "media_gallery") {
                $this->_connectionProduct->_dumpMediaGalleryTable($value, $productId, $this->_connectionProduct->catalogProductEntityMediaGallery(), $this->_connectionProduct->catalogProductEntityMediaGalleryValue(), $this->_connectionProduct->catalogProductEntityMediaGalleryValueToEntity());
            } elseif ($key == "product_super_attribute_table") {
                $parentProductId = $schemaCollector["product_super_link_table"]["parent_id"];
                $productSuperAttributeIdArray = $this->_connectionProduct->_dumpProductSuperAttributeTable($value, $parentProductId);
            } elseif ($key == "product_super_attribute_label_table") {
                $this->_connectionProduct->_dumpProductSuperAttributeLabelTable($value, $productSuperAttributeIdArray);
            } elseif ($key == "product_super_attribute_pricing_table") {
                $this->_connectionProduct->_dumpProductSuperAttributePricingTable($value, $productSuperAttributeIdArray, $optionId);
            } elseif ($key == "product_super_link_table") {
                $this->_connectionProduct->_dumpProductSuperLinkTable($value, $productId);
                $this->_connectionProduct->_dumpCatalogProductRelationTable($value, $productId);
            } elseif ($key == "product_website_table") {
                $this->_connectionProduct->_dumpCatalogProductWebsite($value, $productId);
            } elseif ($key == "tier_price_table") {
                $this->_connectionProduct->_dumpTierPrice($value, $productId);
            } elseif ($key == "catalog_product_entity_group_price_table") {
                $this->_connectionProduct->_dumpGroupPrice($value, $productId);
            } elseif ($key == "inventory_source_item_table") {
                $this->_connectionProduct->_dumpInventorySourceItem($value, $value['sku']);
            }
        }

        if ($productId != null) {
            $this->_connectionProduct->_dumpProducttimestamp($productId);
        }
        return $productId;
    }

    protected function categoriesCollector($categories)
    {
        $schemaCollector = [];
        $categories = explode(";",$categories);
        $categories = $this->_connectionCategory->getCategoriesArray($categories);
            foreach ($categories as $categoryId) {
                if ($categoryId) {
                    $schemaCollector['category_product_table'][$this->staticvb]["position"] = 1;
                    $schemaCollector['category_product_table'][$this->staticvb]["product_id"] = null;
                    $schemaCollector['category_product_table'][$this->staticvb]["category_id"] = $categoryId;
                    $this->staticvb++;
                }
            }
        return $schemaCollector;
    }

    protected function mediaGalleryCollector($row)
    {
        $schemaCollector = [];

        $thumbnail = 0;
        $base = 0;
        $small = 0;

        if (isset($row['image'])) {
            $baseImage = $row['image'];
            $attributeCode = "image";
            $attributeInfo = $this->_connectionEav->getAttributeInfo($attributeCode, $this->_connectionProduct->entityTypeId);

            $backendType = $attributeInfo['backend_type'];
            $attributeId = $attributeInfo['attribute_id'];
            $value = $baseImage;

            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["attribute_id"] = $attributeId;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["attribute_code"] = $attributeCode;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["store_id"] = $this->storeIdAdmin;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["entity_id"] = null;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["value"] = $value;
            $this->varchar_media++;

            $base = $baseImage;

            $attributeCode = "media_gallery";

            $attributeInfo = $this->_connectionEav->getAttributeInfo($attributeCode, $this->_connectionProduct->entityTypeId);
            $value = $baseImage;
            $attributeId = $attributeInfo['attribute_id'];

            $schemaCollector['media_gallery'][$this->varchar_media]["attribute_id"] = $attributeId;
            $schemaCollector['media_gallery'][$this->varchar_media]["media_type"] = 'image';
            $schemaCollector['media_gallery'][$this->varchar_media]["value"] = $value;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['entity_id'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['value_id'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['store_id'] = $this->storeIdAdmin;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['label'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['position'] = $this->varchar_media;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['disabled'] = 0;
            $schemaCollector['media_gallery'][$this->varchar_media]["relation"]['value_id'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["relation"]['entity_id'] = null;
            $this->varchar_media++;
        }

        if (isset($row['thumbnail'])) {
            $thumbnailImage = $row['thumbnail'];

            $attributeCode = "thumbnail";

            $attributeInfo = $this->_connectionEav->getAttributeInfo($attributeCode, $this->_connectionProduct->entityTypeId);

            $backendType = $attributeInfo['backend_type'];
            $attributeId = $attributeInfo['attribute_id'];
            $value = $thumbnailImage;

            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["attribute_id"] = $attributeId;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["attribute_code"] = $attributeCode;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["store_id"] = $this->storeIdAdmin;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["entity_id"] = null;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["value"] = $value;
            $this->varchar_media++;
            $thumbnail = $thumbnailImage;


            $attributeCode = "media_gallery";

            $attributeInfo = $this->_connectionEav->getAttributeInfo($attributeCode, $this->_connectionProduct->entityTypeId);
            $value = $thumbnail;
            $attributeId = $attributeInfo['attribute_id'];

            $schemaCollector['media_gallery'][$this->varchar_media]["attribute_id"] = $attributeId;
            $schemaCollector['media_gallery'][$this->varchar_media]["media_type"] = 'image';
            $schemaCollector['media_gallery'][$this->varchar_media]["value"] = $value;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['entity_id'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['value_id'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['store_id'] = $this->storeIdAdmin;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['label'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['position'] = $this->varchar_media;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['disabled'] = 0;
            $schemaCollector['media_gallery'][$this->varchar_media]["relation"]['value_id'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["relation"]['entity_id'] = null;

            $this->varchar_media++;
        }

        if (isset($row['small_image'])) {
            $smallImage = $row['small_image'];
            $attributeCode = "small_image";

            $attributeInfo = $this->_connectionEav->getAttributeInfo($attributeCode, $this->_connectionProduct->entityTypeId);

            $backendType = $attributeInfo['backend_type'];
            $attributeId = $attributeInfo['attribute_id'];
            $value = $smallImage;

            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["attribute_id"] = $attributeId;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["attribute_code"] = $attributeCode;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["store_id"] = $this->storeIdAdmin;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["entity_id"] = null;
            $schemaCollector[$backendType . '_media_table'][$this->varchar_media]["value"] = $value;
            $this->varchar_media++;
            $small = $smallImage;

            $attributeCode = "media_gallery";

            $attributeInfo = $this->_connectionEav->getAttributeInfo($attributeCode, $this->_connectionProduct->entityTypeId);
            $value = $small;
            $attributeId = $attributeInfo['attribute_id'];

            $schemaCollector['media_gallery'][$this->varchar_media]["attribute_id"] = $attributeId;
            $schemaCollector['media_gallery'][$this->varchar_media]["media_type"] = 'image';
            $schemaCollector['media_gallery'][$this->varchar_media]["value"] = $value;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['entity_id'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['value_id'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['store_id'] = $this->storeIdAdmin;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['label'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['position'] = $this->varchar_media;
            $schemaCollector['media_gallery'][$this->varchar_media]["child"]['disabled'] = 0;
            $schemaCollector['media_gallery'][$this->varchar_media]["relation"]['value_id'] = null;
            $schemaCollector['media_gallery'][$this->varchar_media]["relation"]['entity_id'] = null;




            $this->varchar_media++;
        }


        if (isset($row['media_gallery'])) {
            $media_gallery = $row['media_gallery'];
            foreach ($media_gallery as $image) {
                if ($base != $image || $thumbnail != $image || $small != $image) {
                    $attributeCode = "media_gallery";

                    $attributeInfo = $this->_connectionEav->getAttributeInfo($attributeCode, $this->_connectionProduct->entityTypeId);
                    $value = $image;
                    $attributeId = $attributeInfo['attribute_id'];

                    $schemaCollector['media_gallery'][$this->varchar_media]["attribute_id"] = $attributeId;
                    $schemaCollector['media_gallery'][$this->varchar_media]["media_type"] = 'image';
                    $schemaCollector['media_gallery'][$this->varchar_media]["value"] = $value;
                    $schemaCollector['media_gallery'][$this->varchar_media]["child"]['entity_id'] = null;
                    $schemaCollector['media_gallery'][$this->varchar_media]["child"]['value_id'] = null;
                    $schemaCollector['media_gallery'][$this->varchar_media]["child"]['store_id'] = $this->storeIdAdmin;
                    $schemaCollector['media_gallery'][$this->varchar_media]["child"]['label'] = null;
                    $schemaCollector['media_gallery'][$this->varchar_media]["child"]['position'] = $this->varchar_media;
                    $schemaCollector['media_gallery'][$this->varchar_media]["child"]['disabled'] = 0;
                    $schemaCollector['media_gallery'][$this->varchar_media]["relation"]['value_id'] = null;
                    $schemaCollector['media_gallery'][$this->varchar_media]["relation"]['entity_id'] = null;

                    $this->varchar_media++;
                }
            }
        }

        return $schemaCollector;
    }

    protected function websitesCollector($websites)
    {
        $schemaCollector = [];
        $websiteInc = 0;
        foreach ($websites as $website) {
            $websiteCode = $website['code'];
            $loadedWebsite = $this->_website->load($websiteCode);
            $schemaCollector['product_website_table'][$websiteInc]['website_id'] = $loadedWebsite->getWebsiteId();
            $schemaCollector['product_website_table'][$websiteInc]['product_id'] = null;
            $websiteInc++;
        }
        return $schemaCollector;
    }


    protected function stockCollector($_stock)
    {
        $schemaCollector = [];
        $_catalogInventoryStockItem = $this->_connectionProduct->getTableColumnsInArray($this->_connectionProduct->cataloginventoryStockItem());

        if (count($_stock) > 0) {
            $schemaCollector['stock_table']["product_id"] = null;
            $schemaCollector['stock_table']["stock_id"] = 1;
        }
        foreach ($_stock as $_key => $value) {
            if (!is_int($_key)) {
                if (in_array($_key, $_catalogInventoryStockItem)) {
                    $schemaCollector['stock_table'][$_key] = $_stock[$_key];
                }
            }
        }

        if(isset($schemaCollector['stock_table']['qty'])){
            if($schemaCollector['stock_table']['qty']>0){
                $schemaCollector['stock_table']['is_in_stock'] = 1;
            }
        }

        return $schemaCollector;
    }

    protected function getInventorySourceItem($row)
    {
        $inventoryData=[];

        $key = 'inventory_source_item_table';

        if(isset($row['sku'])) {

            $inventoryData[$key]['sku'] = $row['sku'];

            $inventoryData[$key]['source_code'] = 'default';
            if(isset($row['source_code'])) {
                $inventoryData[$key]['source_code'] = $row['source_code'];
            }

            $inventoryData[$key]['quantity'] = 0;
            if(isset($row['qty'])) {
                $inventoryData[$key]['quantity'] = $row['qty'];
            }

            $inventoryData[$key]['status'] = 0;
            if(isset($row['status'])) {
                $inventoryData[$key]['status'] = $row['status'];
            }
        }

        return $inventoryData;
    }

    protected function basicProductAttributeCollector($_keyAttribute, $_productTableColumnsArray, $_product)
    {
        $schemaCollector = [];

        foreach ($_keyAttribute as $_key) {

            $attributeInfoData = $this->_connectionEav->getAttributeInfo($_key, $this->_connectionProduct->entityTypeId);

            if ($attributeInfoData) {

                $attributeCode = $attributeInfoData['attribute_code'];
                $backendType = $attributeInfoData['backend_type'];
                $attributeId = $attributeInfoData['attribute_id'];

                if (in_array($attributeCode, $_productTableColumnsArray)) {
                    if (is_array($_product[$attributeCode])) {
                        foreach ($_product[$attributeCode] as $_Att_value) {
                            $schemaCollector['master_table'][$attributeCode] = $_Att_value;
                        }
                    } else {
                        $schemaCollector['master_table'][$attributeCode] = $_product[$attributeCode];
                    }
                } else {

                    if ($backendType == "varchar" || $backendType == "int" || $backendType == "text" || $backendType == "decimal" || $backendType == "datetime") {

                        if ($attributeCode != "media_gallery" && $attributeCode != "image" && $attributeCode != "small_image" && $attributeCode != "thumbnail") {
                            if (is_array($_product[$attributeCode])) {
                                foreach ($_product[$attributeCode] as $_Att_value) {
                                    if ($_Att_value != "") {
                                        if (count($this->storeIds)) {
                                            foreach ($this->storeIds as $sid) {
                                                $schemaCollector[$backendType . '_table'][$this->varchar]["attribute_id"] = $attributeId;
                                                $schemaCollector[$backendType . '_table'][$this->varchar]["attribute_code"] = $_key;
                                                $schemaCollector[$backendType . '_table'][$this->varchar]["store_id"] = $sid;
                                                $schemaCollector[$backendType . '_table'][$this->varchar]["entity_id"] = null;
                                                $schemaCollector[$backendType . '_table'][$this->varchar]["value"] = $_Att_value;
                                                $this->varchar++;
                                            }
                                        } else {
                                            $schemaCollector[$backendType . '_table'][$this->varchar]["attribute_id"] = $attributeId;
                                            $schemaCollector[$backendType . '_table'][$this->varchar]["attribute_code"] = $_key;
                                            $schemaCollector[$backendType . '_table'][$this->varchar]["store_id"] = $this->storeIdAdmin;
                                            $schemaCollector[$backendType . '_table'][$this->varchar]["entity_id"] = null;
                                            $schemaCollector[$backendType . '_table'][$this->varchar]["value"] = $_Att_value;
                                            $this->varchar++;
                                        }
                                    }
                                }
                            } elseif ($attributeCode == 'tier_price') {
                                if ($_product[$attributeCode] != '') :
                                    $csvvalue = explode(';', $_product[$attributeCode]);
                                    $tier_price = array();
                                    foreach ($csvvalue as  $explodevalue) {
                                        $temp_tierpricedata = explode(':', $explodevalue);
                                        $temp_tierprice_array = array('all_groups' => $temp_tierpricedata[0], 'customer_group_id' => $temp_tierpricedata[1], 'qty' => $temp_tierpricedata[2], 'value' => $temp_tierpricedata[3], 'website_id' => $temp_tierpricedata[4]);
                                        array_push($tier_price, $temp_tierprice_array);
                                    }
                                    $_product[$attributeCode] = $tier_price;
                                endif;
                                if ($_product[$attributeCode] != "") {
                                    if (count($this->storeIds)) {
                                        foreach ($this->storeIds as $sid) {

                                            $schemaCollector['tier_price_table'][$this->varchar]["attribute_id"] = $attributeId;
                                            $schemaCollector['tier_price_table'][$this->varchar]["attribute_code"] = $_key;
                                            $schemaCollector['tier_price_table'][$this->varchar]["store_id"] = $sid;
                                            $schemaCollector['tier_price_table'][$this->varchar]["entity_id"] = null;
                                            $schemaCollector['tier_price_table'][$this->varchar]["value"] = $_product[$attributeCode];
                                            $this->varchar++;
                                        }
                                    } else {
                                        $schemaCollector['tier_price_table'][$this->varchar]["attribute_id"] = $attributeId;
                                        $schemaCollector['tier_price_table'][$this->varchar]["attribute_code"] = $_key;
                                        $schemaCollector['tier_price_table'][$this->varchar]["store_id"] = $this->storeIdAdmin;
                                        $schemaCollector['tier_price_table'][$this->varchar]["entity_id"] = null;
                                        $schemaCollector['tier_price_table'][$this->varchar]["value"] = $_product[$attributeCode];
                                        $this->varchar++;
                                    }
                                }
                            } else {
                                // if ($_product[$attributeCode] != "") {
                                if (isset($_product[$attributeCode])) {
                                    if (count($this->storeIds)) {
                                        foreach ($this->storeIds as $sid) {
                                            $schemaCollector[$backendType . '_table'][$this->varchar]["attribute_id"] = $attributeId;
                                            $schemaCollector[$backendType . '_table'][$this->varchar]["attribute_code"] = $_key;
                                            $schemaCollector[$backendType . '_table'][$this->varchar]["store_id"] = $sid;
                                            $schemaCollector[$backendType . '_table'][$this->varchar]["entity_id"] = null;
                                            $schemaCollector[$backendType . '_table'][$this->varchar]["value"] = $_product[$attributeCode];
                                            $this->varchar++;
                                        }
                                    } else {
                                        $schemaCollector[$backendType . '_table'][$this->varchar]["attribute_id"] = $attributeId;
                                        $schemaCollector[$backendType . '_table'][$this->varchar]["attribute_code"] = $_key;
                                        $schemaCollector[$backendType . '_table'][$this->varchar]["store_id"] = $this->storeIdAdmin;
                                        $schemaCollector[$backendType . '_table'][$this->varchar]["entity_id"] = null;
                                        $schemaCollector[$backendType . '_table'][$this->varchar]["value"] = $_product[$attributeCode];
                                        $this->varchar++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $schemaCollector;
    }

    protected function variantAttributeCollector($attribute,$swatchValue)
    {

        $schemaCollector = array();
        $configAttribute = $attribute['attribute'];
        $configAttributeValue = $attribute['value'];
        $configAttributeTitle = $attribute['title'];
        $attributeInfo = array();
        $attributeInfo = $this->_connectionEav->getAttributeInfo($configAttribute, $this->_connectionProduct->entityTypeId);
        if (count($attributeInfo) > 0) {

            //$attributeCode = $attributeInfo['attribute_code'];
            //$backendType = $attributeInfo['backend_type'];
            //$attributeId = $attributeInfo['attribute_id'];
            if ($attributeInfo['frontend_input'] == "select") {

                $schemaCollector["attribute_option_table"][$configAttribute]["attribute_id"] = $attributeInfo['attribute_id'];
                $schemaCollector["attribute_option_table"][$configAttribute]["sort_order"] = 0;

                $schemaCollector["attribute_option_table"][$configAttribute]["options"]["option_id"] = null;
                $schemaCollector["attribute_option_table"][$configAttribute]["options"]["store_id"] = $this->storeIdAdmin;
                $schemaCollector["attribute_option_table"][$configAttribute]["options"]["value"] = $configAttributeValue;
                if($swatchValue){
                    $schemaCollector["attribute_option_table"][$configAttribute]["swatch"]["option_id"] = null;
                    $schemaCollector["attribute_option_table"][$configAttribute]["swatch"]["store_id"] = $this->storeIdAdmin;
                    $schemaCollector["attribute_option_table"][$configAttribute]["swatch"]["type"] = 1;
                    $schemaCollector["attribute_option_table"][$configAttribute]["swatch"]["value"] = $swatchValue;
                }
                //$schemaCollector[$backendType . '_table'][$this->varchar]["attribute_id"] = $attributeId;
                //$schemaCollector[$backendType . '_table'][$this->varchar]["attribute_code"] = $attributeCode;
                //$schemaCollector[$backendType . '_table'][$this->varchar]["store_id"] = $this->storeIdAdmin;
                //$schemaCollector[$backendType . '_table'][$this->varchar]["entity_id"] = null;
                //$schemaCollector[$backendType . '_table'][$this->varchar]["value"] = $configAttributeValue;
                $this->varchar++;
            }
            $schemaCollector["product_super_attribute_table"][$configAttribute]["product_id"] = null;
            $schemaCollector["product_super_attribute_table"][$configAttribute]["attribute_id"] = $attributeInfo['attribute_id'];
            $schemaCollector["product_super_attribute_table"][$configAttribute]["position"] = 1;

            $schemaCollector["product_super_attribute_label_table"][$configAttribute]["product_super_attribute_id"] = null;
            $schemaCollector["product_super_attribute_label_table"][$configAttribute]["store_id"] = $this->storeIdAdmin;
            $schemaCollector["product_super_attribute_label_table"][$configAttribute]["use_default"] = 1;
            $schemaCollector["product_super_attribute_label_table"][$configAttribute]["value"] = $configAttributeTitle;
            if (isset($attribute['price'])) {
                if ($attribute['price'] != "") {
                    $schemaCollector["product_super_attribute_pricing_table"][$configAttribute]["product_super_attribute_id"] = null;
                    $schemaCollector["product_super_attribute_pricing_table"][$configAttribute]["value_index"] = null;
                    $schemaCollector["product_super_attribute_pricing_table"][$configAttribute]["is_percent"] = 0;
                    $schemaCollector["product_super_attribute_pricing_table"][$configAttribute]["pricing_value"] = $attribute['price'];
                    $schemaCollector["product_super_attribute_pricing_table"][$configAttribute]["website_id"] = $this->websiteIdAdmin;
                }
            }
        }
        return $schemaCollector;
    }
}
