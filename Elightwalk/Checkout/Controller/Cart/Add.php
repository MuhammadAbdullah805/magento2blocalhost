<?php
 
namespace Elightwalk\Checkout\Controller\Cart;

use Magento\Checkout\Controller\Cart\Add as CartAdd;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote\Item;

class Add extends CartAdd
{

    protected $_resolverInterface;

    protected $_storeManager;

    public function __construct(
        ResolverInterface $resolverInterface,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );

        

        $this->_checkoutSession = $checkoutSession;
        $this->_resolverInterface = $resolverInterface;
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId, true);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    public function execute()
    {
        try {
            $multipleParams = $this->getRequest()->getParams();
            $cartDatas = $this->cart->getQuote()->getAllItems();
            $quote = $this->_checkoutSession->getQuote();

            $selectedConfigurableOption =$multipleParams['selected_configurable_option'];
            $postData=[];

            if(count($selectedConfigurableOption)) {
                foreach($selectedConfigurableOption as $_selectedConfigurableOption) {
                    foreach($_selectedConfigurableOption as $key => $value) {
                        $postData[$key]['selected_configurable_option'][]=$value;
                    }
                }
            }

            $superAttributes=$multipleParams['super_attribute'];
            if(count($postData) && count($superAttributes)) {
                foreach($superAttributes as $_superAttributes) {
                    $postData['new_super_attribute']=$_superAttributes;
                    continue;
                }
            }

            $qty =$multipleParams['qty'];
            $superAttAndQty=[];
            if(count($postData) && isset($postData['new_super_attribute']) && count($qty)) {
                foreach($qty as $keyqty => $valueqty) {
                    foreach($valueqty as $key => $value){
                        $superAttAndQty[]=[
                            'super_attribute'=>[
                                array_key_first($postData['new_super_attribute'])=>$keyqty,
                                array_key_last($postData['new_super_attribute'])=>$key
                            ],
                            'qty'=>$value
                        ];
                    }
                }
            }

            if(isset($postData['new_super_attribute'])):
                unset($postData['new_super_attribute']);
            endif;

            $params=[];
            if(count($postData) && count($superAttAndQty)) {
                $i=0;
                foreach($postData as $_postData) {
                    if(isset($_postData['selected_configurable_option'])) {
                        foreach($_postData['selected_configurable_option'] as $value){
                            // if($superAttAndQty[$i]['qty'] > 0) {
                                $params[]=[
                                    'uenc'=>$multipleParams['uenc'],
                                    'product'=>$multipleParams['product'],
                                    'form_key'=>$multipleParams['form_key'],
                                    'item'=>$multipleParams['item'],
                                    'selected_configurable_option'=>$value,
                                    'super_attribute'=>$superAttAndQty[$i]['super_attribute'],
                                    'qty'=>$superAttAndQty[$i]['qty']
                                ];
                            // }
                            $i++;
                        }
                    }
                }
            }


            if(count($params)) {

                $updateArray=[];
                $productAdd=false;

                foreach($params as $key => $param) {

                    // Remove Card Item
                    if($param['qty'] < 1){
                        if(count($cartDatas)){
                            foreach($cartDatas as $cartData) {
                                if($cartData->getProductId() == $param['selected_configurable_option']) {
                                    $itemId = (int)$cartData->getItemId();
                                    $this->cart->removeItem($itemId);
                                    //$this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
                                    //$this->cart->removeItem($itemId)->save();
                                    //$this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
                                    sleep(1);
                                }
                            }
                        }
                        continue;
                    }

                    if (isset($param['qty'])) {
                        $filter = new \Zend_Filter_LocalizedToNormalized(
                            ['locale' => $this->_resolverInterface->getLocale()]
                        );
                        $param['qty'] = $filter->filter($param['qty']);
                    }

                    $product = $this->_initProduct();
                    $related = $this->getRequest()->getParam('related_product');

                    
                    // Check product availability
                    if (!$product) {
                        return $this->goBack();
                    }

                    

                    // Update Card Datas
                    if(count($cartDatas)){
                        $updateQty=false;
                        foreach($cartDatas as $cartData) {
                            if($cartData->getProductId() == $param['selected_configurable_option']) {
                                $updateQty=true;

                                $itemId = (int)$cartData->getItemId();
                                $item = $quote->getItemById($itemId);

                                $itemParentId = (int)$cartData->getParentItemId();
                                //$itemParent = $quote->getItemById($itemParentId);

                                $qty = $param['qty'];
                                if ($item && $item->getQty() != $param['qty']) {
                                   
                                    $updateArray[]=[
                                        'item_id'=>$itemId,
                                        'item_parent_id'=>$itemParentId,
                                        'item_qty'=>$qty,
                                        'item_price'=>$item->getProduct()->getPrice()
                                    ];
                                    $this->updateQuoteItem($itemId,$itemParentId, $qty,$item->getProduct()->getPrice());
                                    //file_put_contents(BP . '/var/log/events.log', print_r($updateArray,true)."\n", FILE_APPEND);

                                    //$this->updateQuoteItem($itemParentId, $qty);
                                    //$this->updateItemQuantity($item, $qty);
                                    //$this->cart->save();
                                }
                                //$item = $quote->getItemById($itemId);

                                continue;
                            }
                        }

                        if($updateQty) {
                            continue;
                        }
                    }

                    
                    // Add Product
                    $productAdd=true;
                    $this->cart->addProduct($product, $param);
                    if (!empty($related)) {
                        $this->cart->addProductsByIds(explode(',', $related));
                    }

                    

                    //
                    // @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
                    //
                    $this->_eventManager->dispatch(
                        'checkout_cart_add_product_complete',
                        ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                    );
                    sleep(1);

                    
                }
            }

            //if($productAdd) {
                $this->cart->save();
                $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
           // }

            // 
            
            //$this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();

            /*if(count($updateArray)) {
                foreach($updateArray as $_updateArray) {
                    $this->updateQuoteItem($_updateArray['item_id'], $_updateArray['item_parent_id'], $_updateArray['item_qty']);
                }
            }*/

            //$cartDatas = $this->cart->getQuote()->getAllItems();
            //$quote = $this->_checkoutSession->getQuote();

            /*foreach($cartDatas as $cartData) {
                $itemId = (int)$cartData->getItemId();
                //file_put_contents(BP . '/var/log/events.log', print_r('After Update Qty '.$itemId.' - '.$cartData->getQty(),true)."\n", FILE_APPEND);
                //file_put_contents(BP . '/var/log/events.log', print_r('After Update sku '.$itemId.' - '.$cartData->getSku(),true)."\n", FILE_APPEND);
                //file_put_contents(BP . '/var/log/events.log', print_r('After Update id '.$itemId.' - '.$cartData->getProductId(),true)."\n", FILE_APPEND);
                //file_put_contents(BP . '/var/log/events.log', print_r('After Update id '.$itemId.' - '.$cartData->getProduct()->getTypeId(),true)."\n", FILE_APPEND);

            }*/

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    if ($this->shouldRedirectToCart()) {
                        $message = __(
                            'You added %1 to your shopping cart.',
                            $product->getName()
                        );
                        $this->messageManager->addSuccessMessage($message);
                    } else {
                        $this->messageManager->addComplexSuccessMessage(
                            'addCartSuccessMessage',
                            [
                                'product_name' => $product->getName(),
                                'cart_url' => $this->getCartUrl(),
                            ]
                        );
                    }
                }
                return $this->goBack(null, $product);
            }



        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $url = $this->_redirect->getRedirectUrl($this->getCartUrl());
            }

            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->goBack();
        }



        /*$selectedConfigurableOption =$multipleParams['selected_configurable_option'];

        $cartDatas = $this->cart->getQuote()->getAllItems();
        $quote = $this->_checkoutSession->getQuote();

        try {

            if(isset($multipleParams['qty']) && is_array($multipleParams['qty'])) {

                foreach($multipleParams['qty'] as $mainAttKey => $mainAtt){
                    $params=$multipleParams;
                    
                    unset($multipleParams['selected_configurable_option']);
                    unset($params['qty']);

                    if(is_array($mainAtt)) {

                        $j=0;
                        foreach($mainAtt as $key => $value) {

                            $i=0;
                            foreach($params['super_attribute'] as $superAttributekey => $super_attribute) {
                                if($i>0){
                                    $params['super_attribute'][$superAttributekey] = $key;
                                }
                                $i++;
                            }

                           

                            $params['selected_configurable_option'] = $selectedConfigurableOption[$j];
                            $params['qty'] = $value;
                            $j++;

                            
                            if($params['qty'] < 1){
                                if(count($cartDatas)){
                                    foreach($cartDatas as $cartData) {
                                        if($cartData->getProductId() == $params['selected_configurable_option']) {
                                            $itemId = (int)$cartData->getItemId();
                                            $this->cart->removeItem($itemId)->save();
                                        }
                                    }
                                    $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();
                                }
                                continue;
                            }


                            if (isset($params['qty'])) {
                                $filter = new \Zend_Filter_LocalizedToNormalized(
                                    ['locale' => $this->_resolverInterface->getLocale()]
                                );
                                $params['qty'] = $filter->filter($params['qty']);
                            }

                            // echo "<pre>"; print_r($params); exit;
                
                            $product = $this->_initProduct();
                            $related = $this->getRequest()->getParam('related_product');
                
                            //
                           // Check product availability
                            //
                            if (!$product) {
                                return $this->goBack();
                            }

                            if(count($cartDatas)){
                                $updateQty=false;
                                foreach($cartDatas as $cartData) {
                                    if($cartData->getProductId() == $params['selected_configurable_option']) {

                                        $itemId = (int)$cartData->getItemId();
                                        $item = $quote->getItemById($itemId);

                                        $itemParentId = (int)$cartData->getParentItemId();
                                        $itemParent = $quote->getItemById($itemParentId);

                                        $qty = $params['qty'];

                                        if ($item) {
                                            $updateQty=true;
                                            $this->updateQuoteItem($itemId,$itemParentId, $qty);
                                            //$this->updateQuoteItem($itemParentId, $qty);
                                            //$this->updateItemQuantity($item, $qty);
                                            //$this->cart->save();
                                        }
                                        $item = $quote->getItemById($itemId);

                                        continue;
                                    }
                                }

                                if($updateQty) {
                                    continue;
                                }
                            }


                            $this->cart->addProduct($product, $params);
                            
                            if (!empty($related)) {
                                $this->cart->addProductsByIds(explode(',', $related));
                            }

                            //
                            // @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
                            //
                            $this->_eventManager->dispatch(
                                'checkout_cart_add_product_complete',
                                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                            );
                        }

                        
                    }
                }

                $this->cart->save();
                $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();

                $cartDatas = $this->cart->getQuote()->getAllItems();
                $quote = $this->_checkoutSession->getQuote();

                foreach($cartDatas as $cartData) {
                    $itemId = (int)$cartData->getItemId();
                    file_put_contents(BP . '/var/log/events.log', print_r('After Update Qty '.$itemId.' - '.$cartData->getQty(),true)."\n", FILE_APPEND);
                    file_put_contents(BP . '/var/log/events.log', print_r('After Update sku '.$itemId.' - '.$cartData->getSku(),true)."\n", FILE_APPEND);
                    file_put_contents(BP . '/var/log/events.log', print_r('After Update id '.$itemId.' - '.$cartData->getProductId(),true)."\n", FILE_APPEND);
                    file_put_contents(BP . '/var/log/events.log', print_r('After Update id '.$itemId.' - '.$cartData->getProduct()->getTypeId(),true)."\n", FILE_APPEND);

                }

                if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                    if (!$this->cart->getQuote()->getHasError()) {
                        if ($this->shouldRedirectToCart()) {
                            $message = __(
                                'You added %1 to your shopping cart.',
                                $product->getName()
                            );
                            $this->messageManager->addSuccessMessage($message);
                        } else {
                            $this->messageManager->addComplexSuccessMessage(
                                'addCartSuccessMessage',
                                [
                                    'product_name' => $product->getName(),
                                    'cart_url' => $this->getCartUrl(),
                                ]
                            );
                        }
                    }
                    return $this->goBack(null, $product);
                }
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);

            if (!$url) {
                $url = $this->_redirect->getRedirectUrl($this->getCartUrl());
            }

            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->goBack();
        }
        */

    }

    public function updateQuoteItem($itemId,$itemParentId, $itemQty,$price)
    {
        $itemData = [
            $itemId => ['qty' => $itemQty],
            $itemParentId => ['qty' => $itemQty]
        ];
        foreach ($itemData as $itemId => $itemInfo) {
            $item = $this->cart->getQuote()->getItemById($itemId);
            $qty = isset($itemInfo['qty']) ? (double)$itemInfo['qty'] : false;
            if ($qty > 0) {
                $item->setQty($qty);
                $item->setPrice($price);
                $item->save();
            }

        }
        //item_price
        //$this->cart->updateItems($itemData)->save();
        sleep(1);
        return;
    }

    
    /*private function updateItemQuantity(Item $item, float $qty)
    {
        if ($qty > 0) {
            $item->setQty($qty);

            if ($item->getHasError()) {
                file_put_contents(BP . '/var/log/events.log', print_r($item->getMessage(),true)."\n", FILE_APPEND);
                throw new LocalizedException(__($item->getMessage()));
            }
        }
    }*/

    /**
     * Is redirect should be performed after the product was added to cart.
     *
     * @return bool
     */
    private function shouldRedirectToCart()
    {
        return $this->_scopeConfig->isSetFlag(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns cart url
     *
     * @return string
     */
    private function getCartUrl()
    {
        return $this->_url->getUrl('checkout/cart', ['_secure' => true]);
    }
}