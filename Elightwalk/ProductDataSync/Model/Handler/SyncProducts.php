<?php
namespace Elightwalk\ProductDataSync\Model\Handler;

use Magento\Framework\DataObject;

class SyncProducts extends DataObject
{

    protected $_csvReader;

    protected $_dataMiddlewareProduct;

    public function __construct(
        \Elightwalk\ProductDataSync\Model\External\CsvReader $csvReader,
        \Elightwalk\ProductDataSync\Model\DataMiddleware\Product $dataMiddlewareProduct
    ){

        $this->_csvReader = $csvReader;
        $this->_dataMiddlewareProduct = $dataMiddlewareProduct;
    }


    public function initialization($argument){
 
        if(!$this->_csvReader->isImporting()){
            $this->_csvReader->initChunking();
        }else{
            $currentChunk = $this->_csvReader->readCurrentChunk();
            $this->_dataMiddlewareProduct->initMapping($currentChunk, $argument);
            
            // Move file to imported folder
            $this->_csvReader->moveCurrentFile();

            // Reindex
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $indexer = $objectManager->create('Elightwalk\ProductDataSync\Model\Indexer');
            $indexer->reIndexing();
        }
        
    }

}