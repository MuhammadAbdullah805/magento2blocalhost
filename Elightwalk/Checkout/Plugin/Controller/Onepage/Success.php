<?php

namespace Elightwalk\Checkout\Plugin\Controller\Onepage;

class Success
{

    public function __construct(
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
    }
	public function afterExecute(\Magento\Checkout\Controller\Onepage\Success $subject, $result)
	{

		$types = array('config','layout','block_html','collections','reflection','db_ddl','eav','full_page');
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }

        return $result;

	}

}
