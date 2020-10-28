<?php
namespace Elightwalk\ProductDataSync\Model\DataMiddleware;

use Magento\Framework\DataObject;

abstract class AbstractModel extends DataObject
{

    protected function defaultAttribute(){
		
		$obj=array();
		
		$obj['status']=1;
		$obj['visibility']=4;
		$obj['weight']=0;
		$obj['price']=0;
		$obj['tax_class_id']=2;
		$obj['attribute_set']=4;
		return $obj;	
    }
    
    protected function parseCsvWithHeader($mapping,$data){

        $header = current($data);
        $parseData = [];
        foreach($data as $number => $value){
            if($number > 0){
                $_value = [];
                foreach($header as $key=>$column){
                    if(in_array($column,$mapping)){
                        $mappedkey = array_search ($column, $mapping);
                        $_value[$mappedkey] = $value[$key];
                    }
                }
                if(!isset($_value['url_key'])){
                    $_value['url_key']=$this->removeSpecialChapr(strtolower($_value['sku']));
                }
                
                if(!isset($_value['small_image'])){
                    $mappedkey = array_search ($mapping['small_image'], $header);
                    $_value['small_image']=$value[$mappedkey];
                }

                $parseData[] = $_value;
            }
        }

        return $parseData;
    }


    protected function removeSpecialChapr($value){
		$value = str_replace( array( '\'', '"', ',' , ';', '<', '>', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '|', '{', '}','/','_' ), '-', $value);
        return $value;
	}
}
