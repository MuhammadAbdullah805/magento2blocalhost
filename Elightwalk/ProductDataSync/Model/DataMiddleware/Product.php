<?php
namespace Elightwalk\ProductDataSync\Model\DataMiddleware;

class Product extends AbstractModel {

    const CONFIG_IDENTI_COLOUM = 'style_number';
    const SIMPLE_IDENTI_COLOUM = 'sku';

    protected $_schemaManagerProduct;

    public function __construct(
        \Elightwalk\ProductDataSync\Model\SchemaManager\Product $schemaManagerProduct
    ){
        $this->_schemaManagerProduct = $schemaManagerProduct;
    }

	protected function defaultVirtualProductAttribute(){
		
		$obj=[];
		
		$obj['status']=1;
		$obj['visibility']=1;
		$obj['weight']=0;
		$obj['price']=0;
		$obj['tax_class_id']=0;
        $obj['attribute_set']=4;
        $obj['type_id']='virtual';
		return $obj;	
    }
    
    protected function defaultStockAttribute(){
		
		$obj=[];
		
		$obj['qty']=1;
		$obj['min_qty']=0;
		$obj['use_config_min_qty']=1;
		$obj['is_qty_decimal']=0;
		$obj['backorders']=0;
		$obj['use_config_backorders']=1;
		$obj['min_sale_qty']=1; 
		$obj['use_config_min_sale_qty']=1;
		$obj['max_sale_qty']=0;
		$obj['use_config_max_sale_qty']=1;
		$obj['is_in_stock']=1;
		$obj['use_config_notify_stock_qty']=1;
		$obj['manage_stock']=0;
		$obj['use_config_manage_stock']=1;
		$obj['stock_status_changed_auto']=0;
		$obj['use_config_qty_increments']=1;
		$obj['qty_increments']=0;
		$obj['use_config_enable_qty_inc']=1;
		$obj['enable_qty_increments']=0;
		$obj['is_decimal_divided']=0;
		
		return $obj;		
    }
    
    protected function mappingFields(){

        $obj=[];

        $obj['manufacturer']='Mill';
        $obj['sku']='SKU';
        //$obj['url_key']='SKU';
        $obj['style_number']='Style Number';
        $obj['name']='Style Name';
        $obj['description']='Style Description';
        $obj['image']='Style Image File';
        $obj['small_image']='Style Image File';
        $obj['color']='Color';
        $obj['thumbnail']='Color Image File';
        //$obj['Hex Value']='0';
        $obj['size']='Size';
        //$obj['Size Code']='0';
        $obj['gtin_number']='GTIN Number';
        $obj['qty']='Case Quantity';
        $obj['weight']='Item Weight';
        $obj['carton_width']='Carton Width';
        $obj['carton_length']='Carton Length';
        $obj['carton_height']='Carton Height';
        $obj['cost']='Best Price';
        //$obj['SKU']='0';
        $obj['price']='Markup Price';
        $obj['category_ids'] = 'category_paths';
        $obj['color_swatch'] = 'Hex Value';
        $obj['size_swatch'] = 'Hex Value';

        $obj['item_number']='Item Number';
        $obj['color_category']='Color Category';
        $obj['size_code']='Size Code';

        return $obj;	

    }
    

    protected function superAttributes(){

        $obj=[];
        $obj['color'] = 'color';
        $obj['size'] = 'size';
        return $obj;
    }

    public function initMapping($data, $argument){
        $csvData = $this->parseCsvWithHeader($this->mappingFields(),$data);
        $varintObject = $this->parseVariants($csvData);
        
        foreach($varintObject as $obj){

            //file_put_contents(BP . '/var/log/obj.log', print_r($obj,true)."\n", FILE_APPEND);
            //exit;
            $schema =  $this->_schemaManagerProduct->schemaCreator($obj, $argument);
        }
    }

    protected function parseDefaultMapping($row){

        foreach($this->defaultAttribute() as $key=>$value){
            if(!array_key_exists($key,$row)){	
                $row[$key]=$value;
            }
        }

        return $row;

    }

    protected function parseChildDefaultMapping($row){

        foreach($this->defaultVirtualProductAttribute() as $key=>$value){
            if(!array_key_exists($key,$row)){	
                $row[$key]=$value;
            }
        }

        if(isset($row['image']) && isset($row['thumbnail'])) {
            $row['image'] = $row['thumbnail'];
        }

        if(isset($row['small_image']) && isset($row['thumbnail'])) {
            $row['small_image'] = $row['thumbnail'];
        }

        return $row;

    }

    protected function parseDefaultStockMapping($row){

        $stockAttributes=$this->defaultStockAttribute();
        $_stockAttributes=[];
        foreach($stockAttributes as $key=>$value){
            if(array_key_exists($key,$row)){	
                $_stockAttributes[$key]=$row[$key];
            }
        }
        $stockAttributes=$_stockAttributes;
        
        if(isset($stockAttributes['qty'])){
            if($stockAttributes['qty']==0){
                $stockAttributes['is_in_stock']=0;	
            }
        }

        return $stockAttributes;
    }

    protected function parseVariants($data){

        $varintObject = [];
        foreach($data as $row){

            $identify = $row[self::CONFIG_IDENTI_COLOUM];
            if(!isset($varintObject[$identify])){
                $varintObject[$identify]=$this->parseDefaultMapping($row);
                $varintObject[$identify]['stock_option']=$this->parseDefaultStockMapping($row);

                $varintIdentify = self::SIMPLE_IDENTI_COLOUM;

                $varintObject[$identify]['variants'][$row[$varintIdentify]] = $this->parseChildDefaultMapping($row);

            }else{

                $varintIdentify = self::SIMPLE_IDENTI_COLOUM;

                $varintObject[$identify]['variants'][$row[$varintIdentify]] = $this->parseChildDefaultMapping($row);

            }

            $option = [];
            foreach($this->superAttributes() as $attribute){
                $option[] = ['attribute'=>$attribute,'value'=>$row[$attribute],'title'=>$attribute];
            }

            $varintObject[$identify]['variants'][$row[$varintIdentify]]['config_option']=$option;
            $varintObject[$identify]['variants'][$row[$varintIdentify]]['stock_option']=$this->parseDefaultStockMapping($row);

            $varintObject[$identify]['sku'] = $row[self::CONFIG_IDENTI_COLOUM];


        }

        return $varintObject;

    }
    

     
}