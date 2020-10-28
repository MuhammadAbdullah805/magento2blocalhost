<?php
namespace Elightwalk\ProductDataSync\Model;

class Indexer 
{

    protected $_indexerFactory;

    protected $_indexerCollectionFactory;

    protected $indexerIds = array(
        'catalog_category_product',
        'catalog_product_category',
        'catalogrule_rule',
        'catalog_product_attribute',
        'cataloginventory_stock',
        'catalogrule_product',
        'catalog_product_price',
        'catalogsearch_fulltext',
    );

    public function __construct(
        \Magento\Indexer\Model\IndexerFactory $indexerFactory
    ){

        $this->_indexerFactory = $indexerFactory;
    }


    public function reIndexing(){

        foreach ($this->indexerIds as $indexerId) {
            
            $indexer = $this->_indexerFactory->create();
            $indexer->load($indexerId);
            $res=$indexer->reindexAll();
        }
        
    }

}