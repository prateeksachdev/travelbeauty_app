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

    public function checkSkuCodeExist($code) {
        $query = $this->db->query("SELECT * from amazon_sku_match  WHERE  amazon_sku = trim('" . $code . "') ");
        $rowArray = $query->row_array();

        if (!empty($rowArray)) {
            return true;
        } else
            return False;
    }

    public function matchSkuCode($code) {
        $query = $this->db->query("SELECT * from amazon_sku_match  WHERE  amazon_sku = trim('" . $code . "') ");
        $rowArray = $query->row_array();

        if (!empty($rowArray['shopify_sku'])) {

            return $rowArray;
        } else
            return $rowArray = False;
    }

    public function saveOrderDetails($orderDetails) {
        $currentTime = date("Y-m-d H:i:s");
        if ($orderDetails['AmazonOrderId']) {

            $checkquery = $this->db->query("SELECT Count(*) as count FROM `order_details` where order_id = '" . $orderDetails['AmazonOrderId'] . "'");
            $rowCount = $checkquery->row_array();

            if ($rowCount['count'] > 0) {
                $date = str_replace(array('T', 'Z'), ' ', $orderDetails['PurchaseDate']);
                $updatedate = str_replace(array('T', 'Z'), ' ', $orderDetails['LastUpdateDate']);
                $query = $this->db->query("update `order_details` set `purchase_date` ='" . $date . "',`update_date` ='" . $updatedate . "',`amazon_order_status` ='" . $orderDetails['OrderStatus'] . "',`time` ='" . $currentTime . "' where order_id = '" . $orderDetails['AmazonOrderId'] . "'");
            } else {
                $date = str_replace(array('T', 'Z'), ' ', $orderDetails['PurchaseDate']);
                $updatedate = str_replace(array('T', 'Z'), ' ', $orderDetails['LastUpdateDate']);
                $query = $this->db->query("Insert into order_details(purchase_date,update_date,order_id,amazon_order_status,time) values ('" . $date . "','" . $updatedate . "','" . $orderDetails['AmazonOrderId'] . "','" . $orderDetails['OrderStatus'] . "','" . $currentTime . "')");
            }
        }
    }

    public function getOrderDetails() {


        $query = $this->db->query("SELECT `update_date` FROM `order_details` order by `update_date` desc limit 1");

        $rowArray = $query->row_array();
        if (empty($rowArray['update_date'])) {
            return "2015-02-24 21:17:35";
        }
        return $rowArray['update_date'];
    }

    public function getCancelOrderTimings() {


        $query = $this->db->query("SELECT `start_time` FROM `cancel_order_time` order by `start_time` desc limit 1");

        $rowArray = $query->row_array();
        if (empty($rowArray['start_time'])) {
            return "2015-02-24 21:17:35";
        }
        return $rowArray['start_time'];
    }

    public function saveCancelOrderTimings() {
        $date = date("Y-m-d H:i:s");

        $query = $this->db->query("INSERT into `cancel_order_time`(start_time) values ('" . $date . "')");
    }

    public function updateCanceledOrders($amazonOrderId) {
        if ($amazonOrderId) {
            $query = $this->db->query("update `order_details` set `amazon_order_status` = 'Canceled' where order_id = '" . $amazonOrderId . "' ");
        }
    }

    public function dumpAmazonOrderDetails($orderJson, $orderDetails) {
        $date = date("Y-m-d H:i:s");
        $orderJson = str_replace("'", "\'", $orderJson);
        $query = $this->db->query("Insert into orders_backup(amazon_response,amazon_order_id,amazon_dump_time) values ('" . $orderJson . "','" . $orderDetails['AmazonOrderId'] . "','" . $date . "')");
        $id = mysql_insert_id();
        return $id;
    }

    public function dumpOrderIdData($lastInsertedId, $orderIdData) {

        $orderJson = str_replace("'", "\'", $orderIdData);
        $query = $this->db->query("update `orders_backup` set `order_id_dump` ='" . $orderJson . "' where id = '" . $lastInsertedId . "'");
    }

    public function dumpShopifyOrderDetails($orderJson, $id) {
        $orderJson = str_replace("'", "\'", $orderJson);
        $date = date("Y-m-d H:i:s");
        $query = $this->db->query("update `orders_backup` set `shopify_response` ='" . $orderJson . "', `shopify_dump_time` ='" . $date . "' where id = '" . $id . "'");
    }

    public function updateOrderDetails($amazonOrderId, $status, $shopifyId = "") {
        if ($amazonOrderId) {
            $query = $this->db->query("update `order_details` set `shopify_update_status` ='" . $status . "',`shopify_order_id` ='" . $shopifyId . "' where order_id = '" . $amazonOrderId . "' ");
        }
    }
    public function emailStatus($amazonOrderId) {
        if ($amazonOrderId) {
            $query = $this->db->query("update `order_details` set `email_status` = 1 where order_id = '" . $amazonOrderId . "' ");
        }
    }

    public function getOrderDetailsId($amazonId) {
        $query = $this->db->query("SELECT `id` FROM `order_details` where order_id = '" . $amazonId . "'");
        $rowArray = $query->row_array();
        return $rowArray['id'];
    }
    public function orderStatusByCronjob() {
          $query = $this->db->query("SELECT `order_id` FROM `order_details` where amazon_order_status = 'Unshipped'  and (shopify_order_id = 0 or  shopify_order_id is NULL) and (email_status = 0 or email_status is null)");
        $rowArray = $query->result_array();
        return $rowArray;
    }

    public function getAmazonJsonData($amazonId) {
        $query = $this->db->query("SELECT `amazon_response`,`order_id_dump` FROM `orders_backup` where amazon_order_id = '" . $amazonId . "' order by amazon_dump_time DESC limit 1");
        $rowArray = $query->row_array();
        return $rowArray;
    }

    public function updateSkuInOrderDetails($amazonOrderId, $sku) {
        $query = $this->db->query("update `order_details` set `amazon_sku` ='$sku' where order_id = '" . $amazonOrderId . "' ");
    }

    public function createShopifyProductTable($sku, $product_id, $variant_id) {
        echo $query = $this->db->query("Insert into shopify_table(sku,product_id,variant_id) values ('" . $sku . "','" . $product_id . "','" . $variant_id . "')");

        return $id;
    }

    public function getShippingMethod($amazonShipMethod) {
        $query = $this->db->query("SELECT `shopify_shipping` FROM `shipping_match` where amazon_shipping = '" . $amazonShipMethod . "'");
        $rowArray = $query->row_array();
        return $rowArray;
    }

    public function checkIfOrderExists($orderid) {
        $checkquery = $this->db->query("SELECT Count(*) as count FROM `order_details` where order_id = '" . $orderid . "'");
        $rowCount = $checkquery->row_array();
        if ($rowCount['count'] > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function dumpRepeatOrderDetails($orderJson, $orderDetails) {
        $date = date("Y-m-d H:i:s");
        $orderJson = str_replace("'", "\'", $orderJson);
        $query = $this->db->query("Insert into repeat_order(amazon_order_id,amazon_dump,time) values ('" . $orderDetails['AmazonOrderId'] . "','" . $orderJson . "','" . $date . "')");
        $id = mysql_insert_id();
        return $id;
    }

}

?>
