<?php

class countrycode_model extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    public function getCountryCode($code) {
        //$query = $this->db->query("Select * from orders where fulfillment_id IS NOT NULL AND bmt_orderno IS NOT NULL");
        $query = $this->db->query("SELECT name from country  WHERE  country_code = trim('" . $code . "') ");

        $rowArray = $query->row_array();
        return $rowArray['name'];
    }

    public function matchSkuCode($code) {
        $query = $this->db->query("SELECT shopify_sku,product_id from amazon_sku_match  WHERE  amazon_sku = trim('" . $code . "') ");
        $rowArray = $query->row_array();
     
        if (!empty($rowArray)) {

            return $rowArray;
        } else
            return $rowArray = false;
    }

    public function saveOrderDetails($orderDetails) {
        if ($orderDetails['AmazonOrderId']) {
            
            $checkquery = $this->db->query("SELECT Count(*) as count FROM `order_details` where order_id = '" . $orderDetails['AmazonOrderId'] . "'");
            $rowCount = $checkquery->row_array();
       
            if ($rowCount['count'] > 0) {
                $date = str_replace(array('T', 'Z'), ' ', $orderDetails['PurchaseDate']);
                $query = $this->db->query("update `order_details` set `purchase_date` ='" . $date . "',`amazon_order_status` ='" . $orderDetails['OrderStatus'] . "' where order_id = '" . $orderDetails['AmazonOrderId'] . "'");
            } else {
                $date = str_replace(array('T', 'Z'), ' ', $orderDetails['PurchaseDate']);

                $query = $this->db->query("Insert into order_details(purchase_date,order_id,amazon_order_status) values ('" . $date . "','" . $orderDetails['AmazonOrderId'] . "','" . $orderDetails['OrderStatus'] . "')");
            }
        }
    }

    public function getOrderDetails() {


        $query = $this->db->query("SELECT `purchase_date` FROM `order_details` order by `purchase_date` desc limit 1");
        
        $rowArray = $query->row_array();
        if(empty($rowArray['purchase_date'])){
            return "2010-02-10 02:57:44";
        }
        return $rowArray['purchase_date'];
    }
    public function dumpAmazonOrderDetails($orderJson,$orderDetails){
        $date = date("Y-m-d H:i:s");
        $orderJson =  str_replace("'", "\'", $orderJson);
         $query = $this->db->query("Insert into orders_backup(amazon_response,amazon_order_id,amazon_dump_time) values ('" . $orderJson . "','" . $orderDetails['AmazonOrderId'] . "','" . $date . "')");
         $id = mysql_insert_id();
         return $id;
    }
    public function dumpOrderIdData($lastInsertedId,$orderIdData){
       
        $orderJson =  str_replace("'", "\'", $orderIdData);
          $query = $this->db->query("update `orders_backup` set `order_id_dump` ='" . $orderJson . "' where id = '" . $lastInsertedId . "'");
        
    }

    public function dumpShopifyOrderDetails($orderJson,$id){
          $orderJson =  str_replace("'", "\'", $orderJson);
          $date = date("Y-m-d H:i:s");
         $query = $this->db->query("update `orders_backup` set `shopify_response` ='" . $orderJson . "', `shopify_dump_time` ='" . $date . "' where id = '" . $id . "'");
        
    }
    public function updateOrderDetails($amazonOrderId) {
        if($amazonOrderId){
              $query = $this->db->query("update `order_details` set `shopify_update_status` ='Updated' where order_id = '" . $amazonOrderId . "' ");
        }
    }

}

?>
