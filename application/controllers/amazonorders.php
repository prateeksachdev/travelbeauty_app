<?php
error_reporting(0);
class Amazonorders extends CI_Controller {

    private $seller_id = "A2THW3MTISTUM9";
    private $marketplace_id = "ATVPDKIKX0DER";
    private $access_key = "AKIAIZVO6ZPOMHLXN7UA";
    private $secret_key = "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";

    public function __construct() {
        parent:: __construct();
        $this->load->model("countrycode_model", 'obj');
    }

    public function index() {
        $url = "https://mws.amazonservices.com/Orders/2011-01-01";
        $post = "AWSAccessKeyId='" . $access_key . "'";
        $post .= "&Action=ListOrders&MarketplaceId.Id.1='" . $marketplace_id . "'";
        $post .= "&SellerId='" . $seller_id . "'";
        $post .= "&LastUpdatedAfter=2010-10-04T18%3A12%3A21
        &Timestamp=2010-10-05T18%3A12%3A21.687Z
        &Version=2011-01-01";

        curl_setopt($session, CURLOPT_URL, $url);
        curl_setopt($session, CURLOPT_POST, 1);
        curl_setopt($session, CURLOPT_HEADER, false);
    }

    public function test() { 
        set_time_limit(1500);
        $date =$this->obj->getOrderDetails();
        $tostring = "$date";
        $date = strtotime($tostring)+1;
        $mysql = date("Y-m-d H:i:s", $date);
        $param = array();
        $param['AWSAccessKeyId'] = $this->access_key;
        $param['Action'] = 'ListOrders';
        $param['SellerId'] = $this->seller_id;
        //$param['Signature'] = 'ZQLpf8vEXAMPLE0iC265pf18n0%3D';
        $param['SignatureMethod'] = 'HmacSHA256';
        $param['SignatureVersion'] = '2';
        $param['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
        $param['Version'] = '2011-01-01';
        $param['MarketplaceId.Id.1'] = $this->marketplace_id;
        $param['CreatedAfter'] = date("Y-m-d\TH:i:s.\\0\\0\\0\\Z", $date);
        $timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z");//"2010-10-05T18%3A12%3A31.687Z";
        $secret = $this->secret_key;
        $operation = "AWSECommerceService";
        $url = array();
        Ksort($param);
        foreach ($param as $key => $val) {
      $key = str_replace("%7E", "~", rawurlencode($key));
            $val = str_replace("%7E", "~", rawurlencode($val));
            $url[] = "{$key}={$val}";
        }
        sort($url);
        $arr = implode('&', $url);
        $sign = 'GET' . "\n";
        $sign .= 'mws.amazonservices.com' . "\n";
        $sign .= '/Orders/2015-01-28' . "\n";
        $sign .= $arr;
        $signature = hash_hmac("sha256", $sign, $secret, true);
        $signature = urlencode(base64_encode($signature));
        //  $signature = $this->createSignature($param['Signature'],$timestamp,$secret);

        $link = "https://mws.amazonservices.com/Orders/2015-01-28?";
        $link .= $arr . "&Signature=" . $signature;

        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($ch);

        $info = curl_getinfo($ch);
        curl_close($ch);
        $xml = simplexml_load_string($response);
        $xml_array = unserialize(serialize(json_decode(json_encode((array) $xml), 1)));
        $orders = $xml_array['ListOrdersResult']['Orders']['Order'];
        if(isset($orders[0])){
            $ordersarray = $orders;
        }else{
             $ordersarray[0] = $orders;
        }
        foreach ($ordersarray as $key => $order) { 
            $lastInsertedId =  $this->obj->dumpAmazonOrderDetails(json_encode($ordersarray[$key]),$ordersarray[$key]);
            if($order['OrderStatus'] == "Unshipped"){  
                        $this->obj->saveOrderDetails($order);
                        $ordersarray[$key][$order['AmazonOrderId']] = $this->detailorder($order['AmazonOrderId']); 
                        if($ordersarray[$key][$order['AmazonOrderId']]){ 
                        $this->obj->dumpOrderIdData($lastInsertedId,json_encode($ordersarray[$key][$order['AmazonOrderId']]));    
                        }
			$this->shopifyAddOrders($ordersarray[$key],$lastInsertedId);
			}
        }
		echo "<pre>";
        print_r($ordersarray);
    }
  
    function createSignature($operation, $timestamp, $secret) {
        $the_string = $operation . $timestamp;
        return base64_encode(hash_hmac("sha256", $the_string, $secret, true));
    }

    public function detailorder($id) {

        $param = array();
        $param['AWSAccessKeyId'] = $this->access_key;
        $param['AmazonOrderId'] = $id;
        $param['Action'] = 'ListOrderItems';

        $param['SellerId'] = $this->seller_id;
        //$param['Signature'] = 'ZQLpf8vEXAMPLE0iC265pf18n0%3D';
        $param['SignatureMethod'] = 'HmacSHA256';
        $param['SignatureVersion'] = '2';
        $param['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
        $param['Version'] = '2011-01-01';
        $param['MarketplaceId.Id.1'] = $this->marketplace_id;
        $param['CreatedAfter'] = '2015-01-14';
        $param['CreatedBefore'] = '2015-01-15';

        $timestamp = "2010-10-05T18%3A12%3A31.687Z";
        $secret = $this->secret_key;
        $operation = "AWSECommerceService";
        $url = array();
        Ksort($param);
        foreach ($param as $key => $val) {

            $key = str_replace("%7E", "~", rawurlencode($key));
            $val = str_replace("%7E", "~", rawurlencode($val));
            $url[] = "{$key}={$val}";
        }

        sort($url);

        $arr = implode('&', $url);

        $sign = 'GET' . "\n";
        $sign .= 'mws.amazonservices.com' . "\n";
        $sign .= '/Orders/2015-01-28' . "\n";
        $sign .= $arr;
        $signature = hash_hmac("sha256", $sign, $secret, true);
        $signature = urlencode(base64_encode($signature));
        //  $signature = $this->createSignature($param['Signature'],$timestamp,$secret);

        $link = "https://mws.amazonservices.com/Orders/2015-01-28?";
        // $link .= $arr;
        $link .= $arr . "&Signature=" . $signature;

        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        $xml = simplexml_load_string($response);
        $xml_array = unserialize(serialize(json_decode(json_encode((array) $xml), 1)));
        return $xml_array;
    }

    public function shopifyAddOrders($orderDetails,$lastInsertedId) {
        if (!isset($orderDetails[$orderDetails['AmazonOrderId']]['ListOrderItemsResult']['OrderItems']['OrderItem']['OrderItemId'])) {
            $orderCustom = $orderDetails[$orderDetails['AmazonOrderId']]['ListOrderItemsResult']['OrderItems']['OrderItem'];
            $itemcount = count($orderCustom);
          
        } else {
            $orderCustom['0'] = $orderDetails[$orderDetails['AmazonOrderId']]['ListOrderItemsResult']['OrderItems']['OrderItem'];
            $itemcount = 1;
        }
       
        $API_KEY = $this->config->item('shopify_api_key');
        $SECRET = $this->config->item('shopify_secret_key');
        $STORE_URL = $this->config->item('shopify_store_url');
        $url = 'https://' . $API_KEY . ':' . $SECRET . '@' . $STORE_URL . '/admin/orders.json';
        
        for ($i = 0; $i < $itemcount; $i++) {
         
            $country_code = $this->obj->getCountryCode($orderDetails['ShippingAddress']['CountryCode']);
            $shopify_prd_details = $this->obj->matchSkuCode($orderCustom[$i]['SellerSKU']);
//            if(empty($shopify_sku_code)){
//                $shopify_sku_code = "11111";
//            }
           
            if($shopify_prd_details){
		 
            $price = $orderCustom[$i]['ItemTax']['Amount'] +
                    $orderCustom[$i]['ItemPrice']['Amount'];
            $email = "ankush".$this->randomAlphaString(4)."@mobikasa.com";

            $session = curl_init();
            curl_setopt($session, CURLOPT_URL, $url);
            curl_setopt($session, CURLOPT_POST, 1);
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
            //  curl_setopt($session, CURLOPT_POSTFIELDS,'{"fulfillment":{"tracking_number":null,"notify_customer":true,"line_items":['."rg4g4".']}}');
//        if($lineitemjson == "")
//            curl_setopt($session, CURLOPT_POSTFIELDS,'{"order":{"tracking_number":null,"notify_customer":true}}');
//        else
            curl_setopt($session, CURLOPT_POSTFIELDS, '{
                "order": {
                  "line_items": [
                    {
                      "variant_id": "' . mt_rand(5, 15) . '",
                      "title": "' .$orderCustom[$i]['Title'] . '",
                      "price": "' . $price . '",
                      "sku" : "' .$shopify_prd_details['shopify_sku'] . '",
                      "product_id" : "' .$shopify_prd_details['product_id'] . '",
                      "quantity":"' . $orderCustom[$i]['QuantityOrdered'] . '"
                    }
                  ],
                   "customer": {
                    "first_name": "' . $orderDetails['BuyerName'] . '",
                    "last_name": "test",
                    "email": "' . $email . '"
                  },
                  "billing_address": {
                    "first_name":"' . $orderDetails['BuyerName'] . '",
                     "last_name":"amazon",   
                    "address1": "' . $orderDetails['ShippingAddress']['AddressLine1'] . '",
                    "phone": "' . $orderDetails['ShippingAddress']['Phone'] . '",
                    "city": "' . $orderDetails['ShippingAddress']['City'] . '",
                    "province":  "' . $orderDetails['ShippingAddress']['StateOrRegion'] . '",
                    "country": "' . $country_code . '",
                    "zip": "' . $orderDetails['ShippingAddress']['PostalCode'] . '"
                  },
                  "shipping_address": {
                    "first_name":"' . $orderDetails['BuyerName'] . '",
                     "last_name":"amazon",       
                    "address1": "' . $orderDetails['ShippingAddress']['AddressLine1'] . '",
                    "phone": "' . $orderDetails['ShippingAddress']['Phone'] . '",
                    "city": "' . $orderDetails['ShippingAddress']['City'] . '",
                    "province":  "' . $orderDetails['ShippingAddress']['StateOrRegion'] . '",
                    "country": "' . $country_code . '",
                    "zip": "' . $orderDetails['ShippingAddress']['PostalCode'] . '"
                  },
                   "email": "' . $orderDetails['BuyerEmail'] . '",
                   "transactions": [
                    {
                      "kind": "authorization",
                      "status": "success",
                      "amount": 50.0
                    }
                  ]

                }
              }');

            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
            $jsondata = curl_exec($session);
            if($jsondata){
            $this->obj->dumpShopifyOrderDetails($jsondata,$lastInsertedId);
            }
            $info = curl_getinfo($session);
          
            curl_close($session);



            $jsondata = str_replace("\n", "", $jsondata);
            $jsondata = str_replace("\r", "", $jsondata);
            $obj = json_decode($jsondata, true);
            $output['response'] = $jsondata;
            
            echo $lastInsertedId;
            }
        }
    }
 public function randomAlphaString($length = 7) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 1; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }
}
