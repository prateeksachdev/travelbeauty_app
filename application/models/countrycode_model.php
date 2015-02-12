<?php

class countrycode_model extends CI_Model
{
    function  __construct() {
        parent::__construct();

    }
    public  function  getCountryCode($code)
    { 
        //$query = $this->db->query("Select * from orders where fulfillment_id IS NOT NULL AND bmt_orderno IS NOT NULL");
        $query = $this->db->query("SELECT name from country  WHERE  country_code = trim('" . $code . "') ");
      
        $rowArray = $query->row_array();
        return $rowArray['name'];
    }
    
     public  function  matchSkuCode($code)
    { 
          $query = $this->db->query("SELECT shopify_sku,product_id from amazon_sku_match  WHERE  amazon_sku = trim('" . $code . "') ");
             $rowArray = $query->row_array();
            
             if(!empty($rowArray)){
             
             return $rowArray;
             }
             else   return $rowArray = array("shopify_sku"=>"1111111","product_id"=>"111111");
                 
    }
    public function saveOrderDetails($orderDetails){     
        if($orderDetails){
           $date = str_replace(array('T', 'Z'), ' ', $orderDetails['PurchaseDate']);
         
             $query = $this->db->query("Insert into order_details(purchase_date,order_id) values ('".$date."','".$orderDetails['AmazonOrderId']."')");
             
        }
    }
     public function getOrderDetails(){     
       
        
             $query = $this->db->query("SELECT `purchase_date` FROM `order_details` order by `purchase_date` desc limit 1");
               $rowArray = $query->row_array();
              return $rowArray['purchase_date'];
        
    }
}

?>
