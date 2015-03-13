<?php

class Amazonorders extends CI_Controller {

//    private $seller_id = $this->config->item('api_key');
//    private $marketplace_id = "ATVPDKIKX0DER";
//    private $access_key = "AKIAIZVO6ZPOMHLXN7UA";
//    private $secret_key = "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";

    public function __construct() {
        parent:: __construct();
        $this->load->model("countrycode_model", 'obj');
    }

    public function index() {
        try {

            echo $date = $this->obj->getOrderDetails();
            $tostring = "$date";
            $date = strtotime($tostring) + 1;
            $mysql = date("Y-m-d H:i:s", $date);
            $param = array();
            $param['AWSAccessKeyId'] = $this->config->item('access_key');
            $param['Action'] = 'ListOrders';
            $param['SellerId'] = $this->config->item('seller_id');
            //$param['Signature'] = 'ZQLpf8vEXAMPLE0iC265pf18n0%3D';
            $param['SignatureMethod'] = 'HmacSHA256';
            $param['SignatureVersion'] = '2';
            $param['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
            $param['Version'] = '2011-01-01';
            $param['MarketplaceId.Id.1'] = $this->config->item('marketplace_id');
            $param['CreatedAfter'] = date("Y-m-d\TH:i:s.\\0\\0\\0\\Z", $date);
            $timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z"); //"2010-10-05T18%3A12%3A31.687Z";
            $secret = $this->config->item('secret_key');
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
           
            if ($orders) {
                if (isset($orders[0])) {
                    $ordersarray = $orders;
                } else {
                    $ordersarray[0] = $orders;
                }


                foreach ($ordersarray as $key => $order) {
                    if (count($ordersarray)) {
                        $lastInsertedId = $this->obj->dumpAmazonOrderDetails(json_encode($ordersarray[$key]), $ordersarray[$key]);
                        if ($order['OrderStatus'] == "Unshipped") {
                           $this->obj->saveOrderDetails($order);
                            $ordersarray[$key][$order['AmazonOrderId']] = $this->detailorder($order['AmazonOrderId'], $lastInsertedId);
                            if ($ordersarray[$key][$order['AmazonOrderId']]) {
                                $this->obj->dumpOrderIdData($lastInsertedId, json_encode($ordersarray[$key][$order['AmazonOrderId']]));
                            }
                          
                            $this->shopifyAddOrders($ordersarray[$key], $lastInsertedId);
                        }
                    }
                }
            }
            echo "<pre>";
            print_r($ordersarray);
        } catch (Exception $e) {
            $reason = $e->getMessage();
            $this->notifybyemail('Amazon Order', $reason);
        }
    }

    function createSignature($operation, $timestamp, $secret) {
        $the_string = $operation . $timestamp;
        return base64_encode(hash_hmac("sha256", $the_string, $secret, true));
    }

    public function detailorder($id, $lastInsertedId) {

        $param = array();
        $param['AWSAccessKeyId'] = $this->config->item('access_key');
        $param['AmazonOrderId'] = $id;
        $param['Action'] = 'ListOrderItems';

        $param['SellerId'] = $this->config->item('seller_id');
        //$param['Signature'] = 'ZQLpf8vEXAMPLE0iC265pf18n0%3D';
        $param['SignatureMethod'] = 'HmacSHA256';
        $param['SignatureVersion'] = '2';
        $param['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
        $param['Version'] = '2011-01-01';
        $param['MarketplaceId.Id.1'] = $this->config->item('marketplace_id');
        $param['CreatedAfter'] = '2015-01-14';
        $param['CreatedBefore'] = '2015-01-15';

        $timestamp = "2010-10-05T18%3A12%3A31.687Z";
        $secret = $this->config->item('secret_key');
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
        if ($xml_array) {
            $sku = $xml_array['ListOrderItemsResult']['OrderItems']['OrderItem']['SellerSKU'];
            $this->obj->updateSkuInOrderDetails($id, $sku);
        }

        return $xml_array;
    }

    public function shopifyAddOrders($orderDetails, $lastInsertedId, $extra = "", $page = 1) {

       
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
        $email = "ankush" . $this->randomAlphaString(4) . "@mobikasa.com";
        $country_code = $this->obj->getCountryCode($orderDetails['ShippingAddress']['CountryCode']);
//      print_r($orderCustom);die;
        for ($j = 0; $j < $itemcount; $j++) {
            $saveSkuForEmail[] = $orderCustom[$j]['SellerSKU'];
            $skuStatus = $this->obj->checkSkuCodeExist($orderCustom[$j]['SellerSKU']);
            if ($skuStatus == false) {
                break;
            }
        }
       

        if ($skuStatus) {
            $totalTax = 0;
            $totalItemsPrice = 0;
            $Totalprice = 0;
            $shippingPrice = 0;
            $shippingTax = 0;
            $data = "[";
            for ($i = 0; $i < $itemcount; $i++) {
                $shopify_prd_details = $this->obj->matchSkuCode($orderCustom[$i]['SellerSKU']);
                $price = ($orderCustom[$i]['ItemPrice']['Amount'] / $orderCustom[$i]['QuantityOrdered']);

                $totalTax = $totalTax + $orderCustom[$i]['ItemTax']['Amount'];
                $totalItemsPrice = $totalItemsPrice + $orderCustom[$i]['ItemPrice']['Amount'];
                $shippingPrice = $shippingPrice + $orderCustom[$i]['ShippingPrice']['Amount'];
                $shippingTax = $shippingTax + $orderCustom[$i]['ShippingTax']['Amount'];
                $data .= '{
                      "variant_id": "' . $shopify_prd_details['variant_id'] . '",
                      "title": "' . $orderCustom[$i]['Title'] . '",
                      "price": "' . $price . '",
                      "sku" : "' . $shopify_prd_details['shopify_sku'] . '",
                      "product_id" : "' . $shopify_prd_details['product_id'] . '",
                      "quantity":"' . $orderCustom[$i]['QuantityOrdered'] . '"
                    }';
                if ($itemcount - 1 > $i) {
                    $data .= ",";
                }
            }
            $data .= "]";

            $Totalprice = $totalItemsPrice + $totalTax + $shippingPrice + $shippingTax;
            $totalTax = $totalTax + $shippingTax;
            if ($totalTax > 0) {
                $taxPercent = ($totalTax) / $totalItemsPrice;
            } else {
                $taxPercent = 0;
            }
            
            if($shippingTax >0){
                 $shipTaxPercent = ($shippingTax) / $shippingPrice;
            }else{
                $shipTaxPercent = 0;
            }

            $name = explode(" ", trim($orderDetails['BuyerName']));
            $nameCount = count($name);


            if ($nameCount > 1) {
                $firstName = "";
                for ($k = 0; $k < $nameCount - 1; $k++) {
                    $firstName .=$name[$k] . " ";
                }
                end($name);
                $lastName = $name[key($name)];
            } else {
                $firstName = $name[0];
                $lastName = $name[0];
            }
          
            // for the shipping name
            $Shipname = explode(" ", trim($orderDetails['ShippingAddress']['Name']));
            $nameCountShip = count($Shipname);

            if ($nameCountShip > 1) {
                $firstNameShip = "";
                for ($k = 0; $k < $nameCountShip - 1; $k++) {
                    $firstNameShip .=$Shipname[$k] . " ";
                }
                end($Shipname);
                $lastNameShip = $Shipname[key($Shipname)];
            } else {
                $firstNameShip = $Shipname[0];
                $lastNameShip = $Shipname[0];
            }

            // for the shipping name ends here
            // shipping method and tax starts
             $shipMethod = $orderDetails['ShipmentServiceLevelCategory'];
             $shopifyShipMethod = $this->obj->getShippingMethod($shipMethod);
             $shippingDetails = '"code":"' . $shopifyShipMethod['shopify_shipping'] . '",
                     "price":"' . $shippingPrice . '",
                     "title":"' . $shopifyShipMethod['shopify_shipping'] . '",
                    "source": "Shopify", 
                   "tax_lines": [
                    {
                      "price" : "' . $shippingTax . '", 
                      "rate" : "' . $shipTaxPercent . '", 
                      "title" : "Shipping Tax"
                    }]';
            // shipping method and tax ends here
            
            //    echo "totaltax: ".$totalTax.",totatlitemprice : ".$totalItemsPrice.",taxrate:".$taxRate;die;
            $session = curl_init();
            curl_setopt($session, CURLOPT_URL, $url);
            curl_setopt($session, CURLOPT_POST, 1);
            curl_setopt($session, CURLOPT_HEADER, false);
            curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));

            $sbaddress = '"first_name":"' . $firstName . '",
                     "last_name":"' . $lastName . '",
                    "address1": "' . $orderDetails['ShippingAddress']['AddressLine1'] . '",';

            if (isset($orderDetails['ShippingAddress']['AddressLine2'])) {
                $sbaddress .= '"address2": "' . $orderDetails['ShippingAddress']['AddressLine2'] . '",';
                $shipAddress2 = '"address2": "' . $orderDetails['ShippingAddress']['AddressLine2'] . '",';
            }

            $commonaddress .= '"phone": "' . $orderDetails['ShippingAddress']['Phone'] . '",
                    "city": "' . $orderDetails['ShippingAddress']['City'] . '",
                    "province":  "' . $orderDetails['ShippingAddress']['StateOrRegion'] . '",
                    "country": "' . $country_code . '",
                    "zip": "' . $orderDetails['ShippingAddress']['PostalCode'] . '"';
            $sbaddress .= $commonaddress;
            $shippingAddress = '"first_name":"' . $firstNameShip . '",
                     "last_name":"' . $lastNameShip . '",
                    "address1": "' . $orderDetails['ShippingAddress']['AddressLine1'] . '",';
            $shippingAddress .= $shipAddress2;
            $shippingAddress .= $commonaddress;
            $buyerEmail = substr($orderDetails['BuyerEmail'], 0, strpos($orderDetails['BuyerEmail'], "@"));
            $buyerEmail = $buyerEmail . "@travelbeauty.com";
            curl_setopt($session, CURLOPT_POSTFIELDS, '{
                "order": {
                  "line_items": ' . $data . ',
                   "customer": {
                    "first_name": "Amazon",
                    "last_name":  "Order"
                    },
                  "billing_address": {' . $sbaddress . '},
                  "shipping_address": {' . $shippingAddress . '},
                   "shipping_lines": [{' . $shippingDetails . '}],
                  "source_name" : "Amazon",
                   "email": "' . $buyerEmail . '",
                   "transactions": [
                    {
                      "kind": "capture",
                      "status": "success",
                      "amount": "' . $Totalprice . '"
                    }],
                    "processing_method" : "direct",
                    "subtotal_price" : "' . $orderDetails['OrderTotal']['Amount'] . '",
                    "total_price" : "' . $Totalprice . '",
                    "total_tax" : "' . $totalTax . '",
                    "tax_lines": [
                    {
                      "price" : "' . $totalTax . '", 
                      "rate" : "' . $taxPercent . '", 
                      "title" : "Item Tax"
                    }],
                     "tags" : "Amazon-order" 
                
                }
              }');

            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
            $jsondata = curl_exec($session);

            //  print_r(curl_getinfo($session));die;
            if ($jsondata) {
                $this->obj->dumpShopifyOrderDetails($jsondata, $lastInsertedId);
            }
            $info = curl_getinfo($session);

            curl_close($session);



            $jsondata = str_replace("\n", "", $jsondata);
            $jsondata = str_replace("\r", "", $jsondata);
            $obj = json_decode($jsondata, true);
            $output['response'] = $jsondata;
         
            if (isset($obj['errors']) || isset($obj['error'])) {

                $this->obj->updateOrderDetails($orderDetails['AmazonOrderId'], "Not Updated");
                $reason = 'Hi,<br/>This order information could not be copied over to Shopify due to the following reason :<br/>
                            There was no shopify SKU information present for items in this order. Please update the SKU match table and reprocess it from the dashboard.';
                $subject = 'Amazon Order #' . $orderDetails['AmazonOrderId'] . ' could not be processed';
                $this->notifybyemail($subject, $reason);
                $result = false;
            } else {
                $shopifyOrderId = ltrim($obj['order']['name'], '#');
                $this->obj->updateOrderDetails($orderDetails['AmazonOrderId'], "Updated", $shopifyOrderId);
                $result = true;
            }
        } else {

            $this->obj->updateOrderDetails($orderDetails['AmazonOrderId'], "Not Updated");
            $reason = 'Hi,</br><br/>This order information could not be copied over to Shopify due to the following reason :<br/>
                            There was no shopify SKU information present for items in this order. Please update the SKU match table and reprocess it from the dashboard.';
            $reason .= "<br/>Amazon seller SKU is/are ";
            $skucount = count($saveSkuForEmail);
            echo $skucount;die;
            for ($r = 0; $r <= $skucount - 1; $r++) {
                $reason .= $saveSkuForEmail[$r];
                if ($r < ($skucount - 1))
                    $reason .= ",";
            }
            $subject = 'Amazon Order #' . $orderDetails['AmazonOrderId'] . ' could not be processed';
            $this->notifybyemail($subject, $reason);
            $result = false;
        }
      
        if (!empty($extra)) {

            if ($result) {
                $this->session->set_flashdata('success', 'Order Created on Shopify Successfully');
            } else {
                $this->session->set_flashdata('error', 'Error While Creating Shopify Order, Please Update SKU table and retry');
            }
            redirect('admin/orders/' . $page);
        }
       
    }

    public function nametest() {
        $sdf = "sdfa";
        $abd = '"address2": "' . (isset($sdf)) ? $sdf : "" . '"';
        echo $abd;
        return;
        $name = explode(" ", "Michael L. Krass, Ph.D.");
        $nameCount = count($name);


        if ($nameCount > 1) {
            $firstName = "";
            for ($k = 0; $k < $nameCount - 1; $k++) {
                $firstName .=$name[$k] . " ";
            }
            end($name);
            $lastName = $name[key($name)];
        } else {
            $firstName = $name[0];
            $lastName = $firstName;
        }
        echo $firstName;
        echo "<br/>" . $lastName;
    }

    public function randomAlphaString($length = 7) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 1; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }

    public function notifybyemail($subject, $reason = "") {

        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.mandrillapp.com',
            'smtp_port' => 587,
            'smtp_user' => 'ankit@mobikasa.com',
            'smtp_pass' => '2fzmv1hLZA7Yj1MYR1mTHA',
            'mailtype' => 'html'
        );
        $this->load->library('email', $config);
        //$this->email->set_newline("\r\n");
        // Set to, from, message, etc.
        $this->email->from('info@travelbeauty.com', 'Travel Beauty');

        $this->email->to($this->config->item('notifyEmail'));

        $this->email->cc('ankit@mobikasa.com');
//        $this->email->bcc('them@their-example.com'); 

        $this->email->subject($subject);
        $this->email->message($reason);

        $result = $this->email->send();

        return;
    }

    public function notifybyemail_new($subject, $reason = "") {

        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.mandrillapp.com',
            'smtp_port' => 587,
            'smtp_user' => 'ankushmadaan@mobikasa.com',
            'smtp_pass' => 'XJXm1e33BIZoj21utYNPgQ',
            'mailtype' => 'html'
        );
        $this->load->library('email', $config);
        //$this->email->set_newline("\r\n");
        // Set to, from, message, etc.
        $this->email->from('info@travelbeauty.com', 'Travel Beauty');
        $emaiTo = array('jaishankar@mobikasa.com', 'ankit@mobikasa.com');
        $this->email->to($emaiTo);

        //$this->email->cc('ankit@mobikasa.com');
//        $this->email->bcc('them@their-example.com'); 

        $this->email->subject($subject);
        $this->email->message($reason);

        $this->email->send();
        return;
    }

    public function createShopifyOrder() {
        $amazonId = $_GET['id'];
        $page = $_GET['page'];
        $id = $this->obj->getOrderDetailsId($amazonId);

        $orderDetails = $this->obj->getAmazonJsonData($amazonId);
        if ($orderDetails) {
            $order_details = json_decode(str_replace("\'", "'", $orderDetails['amazon_response']), true);

            $order_amazon_details = json_decode(str_replace("\'", "'", $orderDetails['order_id_dump']), true);
            $order_details[$order_details['AmazonOrderId']] = $order_amazon_details;
        }

        $this->shopifyAddOrders($order_details, $id, "redirect", $page);
    }

}
