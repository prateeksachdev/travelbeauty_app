<?php

class Admin_products extends CI_Controller {

    /**
     * Responsable for auto load the model
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->load->model('products_model');
        $this->load->model('manufacturers_model');

        if (!$this->session->userdata('is_logged_in')) {
            redirect('admin/login');
        }
    }

    /**
     * Load the main view with all the current model model's data.
     * @return void
     */
    public function index() {

        //all the posts sent by the view
        $manufacture_id = $this->input->post('manufacture_id');
        $search_string = $this->input->post('search_string');
        $order = $this->input->post('order');
        $order_type = $this->input->post('order_type');

        //pagination settings
        $config['per_page'] = 25;
        $config['base_url'] = base_url() . 'admin/products';
        $config['use_page_numbers'] = TRUE;
        $config['num_links'] = 20;
        $config['full_tag_open'] = '<ul>';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="active"><a>';
        $config['cur_tag_close'] = '</a></li>';

        //limit end
        $page = $this->uri->segment(3);

        //math to get the initial record to be select in the database
        $limit_end = ($page * $config['per_page']) - $config['per_page'];
        if ($limit_end < 0) {
            $limit_end = 0;
        }

        //if order type was changed
        if ($order_type) {
            $filter_session_data['order_type'] = $order_type;
        } else {
            //we have something stored in the session? 
            if ($this->session->userdata('order_type')) {
                $order_type = $this->session->userdata('order_type');
            } else {
                //if we have nothing inside session, so it's the default "Asc"
                $order_type = 'Asc';
            }
        }
        //make the data type var avaible to our view
        $data['order_type_selected'] = $order_type;


        //we must avoid a page reload with the previous session data
        //if any filter post was sent, then it's the first time we load the content
        //in this case we clean the session filter data
        //if any filter post was sent but we are in some page, we must load the session data
        //filtered && || paginated
        if ($search_string !== false && $order !== false || $this->uri->segment(3) == true) {

            /*
              The comments here are the same for line 79 until 99

              if post is not null, we store it in session data array
              if is null, we use the session data already stored
              we save order into the the var to load the view with the param already selected
             */


            if ($search_string) {
                $filter_session_data['search_string_selected'] = $search_string;
            } else {
                $search_string = $this->session->userdata('search_string_selected');
            }
            $data['search_string_selected'] = $search_string;

            if ($order) {
                $filter_session_data['order'] = $order;
            } else {
                $order = $this->session->userdata('order');
            }
            $data['order'] = $order;

            //save session data into the session
            $this->session->set_userdata($filter_session_data);

            //fetch manufacturers data into arrays
            $data['manufactures'] = $this->manufacturers_model->get_manufacturers();

            $data['count_products'] = $this->products_model->count_products($manufacture_id, $search_string, $order);
            $config['total_rows'] = $data['count_products'];

            //fetch sql data into arrays
            if ($search_string) {
                if ($order) {
                    $data['products'] = $this->products_model->get_products($manufacture_id, $search_string, $order, $order_type, $config['per_page'], $limit_end);
                } else {
                    $data['products'] = $this->products_model->get_products($manufacture_id, $search_string, '', $order_type, $config['per_page'], $limit_end);
                }
            } else {
                if ($order) {
                    $data['products'] = $this->products_model->get_products($manufacture_id, '', $order, $order_type, $config['per_page'], $limit_end);
                } else {
                    $data['products'] = $this->products_model->get_products($manufacture_id, '', '', $order_type, $config['per_page'], $limit_end);
                }
            }
        } else {

            //clean filter data inside section
            $filter_session_data['manufacture_selected'] = null;
            $filter_session_data['search_string_selected'] = null;
            $filter_session_data['order'] = null;
            $filter_session_data['order_type'] = null;
            $this->session->set_userdata($filter_session_data);

            //pre selected options
            $data['search_string_selected'] = '';
            $data['manufacture_selected'] = 0;
            $data['order'] = 'id';

            //fetch sql data into arrays
            $data['manufactures'] = $this->manufacturers_model->get_manufacturers();
            $data['count_products'] = $this->products_model->count_products();
            $data['products'] = $this->products_model->get_products('', '', '', $order_type, $config['per_page'], $limit_end);
            $config['total_rows'] = $data['count_products'];
        }//!isset($manufacture_id) && !isset($search_string) && !isset($order)
        //initializate the panination helper 
        $this->pagination->initialize($config);

        //load the view
        $data['main_content'] = 'admin/products/list';
        $this->load->view('includes/template', $data);
    }

//index

    public function add() {

        //if save button was clicked, get the data sent via post
        if ($this->input->server('REQUEST_METHOD') === 'POST') {

            //form validation
            $this->form_validation->set_rules('amazon_sku', 'amazon_sku', 'required');
            $this->form_validation->set_rules('shopify_sku', 'shopify_sku', 'required');


            $this->form_validation->set_error_delimiters('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>', '</strong></div>');

            //if the form has passed through the validation
            if ($this->form_validation->run() == FALSE) {
                $data['flash_message'] = FALSE;
            } else {
                $this->load->model('products_model');

                $details = $this->products_model->getSkuDetails($this->input->post('shopify_sku'));

                if (!$details) {
                    $this->getSkus();
                    $details = $this->products_model->getSkuDetails($this->input->post('shopify_sku'));
                    if (!$details) {
                        $data['flash_message'] = FALSE;
                        $data['error'] = "Sku Details Not Found";
                    }else{
                         $data_to_store = array(
                            'amazon_sku' => $this->input->post('amazon_sku'),
                            'shopify_sku' => $this->input->post('shopify_sku'),
                            'product_id' => $details['product_id'],
                            'variant_id' => $details['variant_id']
                        );
                        $result = $this->products_model->store_product($data_to_store);
                        if ($result) {
                            $data['flash_message'] = TRUE;
                        } else {
                            $data['flash_message'] = FALSE;
                            $data['error'] = "Amazon Sku Must be Unique";
                        }
                    }
                }else {

                        $data_to_store = array(
                            'amazon_sku' => $this->input->post('amazon_sku'),
                            'shopify_sku' => $this->input->post('shopify_sku'),
                            'product_id' => $details['product_id'],
                            'variant_id' => $details['variant_id']
                        );
                        $result = $this->products_model->store_product($data_to_store);
                        if ($result) {
                            $data['flash_message'] = TRUE;
                        } else {
                            $data['flash_message'] = FALSE;
                            $data['error'] = "Amazon Sku Must be Unique";
                        }
                    }
                
            }
        }
        //fetch manufactures data to populate the select field
        // $data['manufactures'] = $this->manufacturers_model->get_manufacturers();
        //load the view
        $data['main_content'] = 'admin/products/add';
        $this->load->view('includes/template', $data);
    }

    /**
     * Update item by his id
     * @return void
     */
    public function update() {
        //product id 
        error_reporting(-1);
        $id = $this->uri->segment(4);

        //if save button was clicked, get the data sent via post
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            //form validation
            $this->form_validation->set_rules('amazon_sku', 'amazon_sku', 'required');
            $this->form_validation->set_rules('shopify_sku', 'shopify_sku', 'required');

            $this->form_validation->set_error_delimiters('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>', '</strong></div>');
            //if the form has passed through the validation
            if ($this->form_validation->run()) {
                $details = $this->products_model->getSkuDetails($this->input->post('shopify_sku'));
                
               if (!$details) {
                    $this->getSkus();
                    $details = $this->products_model->getSkuDetails($this->input->post('shopify_sku'));
                   
                    if (!$details) { 
                        
                       $this->session->set_flashdata('flash_message', 'not_updated');
                       redirect('admin/products/update/' . $id . '');
                    }else{
                         $data_to_store = array(
                            'amazon_sku' => $this->input->post('amazon_sku'),
                            'shopify_sku' => $this->input->post('shopify_sku'),
                            'product_id' => $details['product_id'],
                            'variant_id' => $details['variant_id']
                        );
                        $result = $this->products_model->store_product($data_to_store);
                        if ($result) {
                            $data['flash_message'] = TRUE;
                        } else {
                            $data['flash_message'] = FALSE;
                            $data['error'] = "Amazon Sku Must be Unique";
                        }
                    }
                }else {

                        $data_to_store = array(
                            'amazon_sku' => $this->input->post('amazon_sku'),
                            'shopify_sku' => $this->input->post('shopify_sku'),
                            'product_id' => $details['product_id'],
                            'variant_id' => $details['variant_id']
                        );
                   
                    //if the insert has returned true then we show the flash message
                    if ($this->products_model->update_product($id, $data_to_store) == TRUE) {
                        $this->session->set_flashdata('flash_message', 'updated');
                    } else {
                        $this->session->set_flashdata('flash_message', 'not_updated');
                    }
                    redirect('admin/products/update/' . $id . '');
                }//validation run
            }
        }

        //if we are updating, and the data did not pass trough the validation
        //the code below wel reload the current data
        //product data 
        $data['product'] = $this->products_model->get_product_by_id($id);
        //fetch manufactures data to populate the select field
        $data['manufactures'] = $this->manufacturers_model->get_manufacturers();
        //load the view
        $data['main_content'] = 'admin/products/edit';
    //  print_r($data);die;
        $this->load->view('includes/template', $data);
    }

//update

    /**
     * Delete product by his id
     * @return void
     */
    public function delete() {
        //product id 
        $id = $this->uri->segment(4);
        $this->products_model->delete_product($id);
        redirect('admin/products');
    }

    public function getSkus() {
        set_time_limit(0);
        $this->db->query("Delete from shopify_sku_details");
        $countObj = $this->getcurldata("/admin/products/count.json");
        $count = $countObj['count'];
        $limit = 50;
        //$pages = round($count / $limit); comment by pankaj
        $pages = ceil($count / $limit);
        //echo $pages;
        for ($i = 1; $i <= $pages; $i++) {
            //echo '/admin/products.json?published_status=published&fields=id,variants,vendor&limit='.$limit.'&page='.$i."<br>";
            $productsObj = $this->getcurldata('/admin/products.json?fields=id,variants,vendor&limit=' . $limit . '&page=' . $i);
            for ($j = 0; $j < count($productsObj['products']); $j++) {
                $product = $productsObj['products'][$j];
                $productId = $product['id'];
                // $metafieldObj = $this->getcurldata('/admin/products/'.$productId.'/metafields.json');
                // if(!$this->checkifseohidden($metafieldObj['metafields']))
                if (true) {
                    $vendor = $product['vendor'];
                    $variants = $product['variants'];
                    for ($k = 0; $k < count($variants); $k++) {
                        $variantId = $variants[$k]['id'];
                        $variantSku = $variants[$k]['sku'];
                        $variantPrice = $variants[$k]['price'];
                        $variantComparePrice = $variants[$k]['compare_at_price'];
                        $data = array(
                            'shopify_sku' => $variantSku,
                            'variant_id' => $variantId,
                            'product_id' => $productId,
                            'price' => $variantPrice,
                            'compare_at_price' => $variantComparePrice,
                            'vendor' => $vendor
                        );
                        $this->db->insert('shopify_sku_details', $data);
                    }
                }
            }
        }
    }

    function getcurldata($urlparameters) {
        $API_KEY = $this->config->item('shopify_api_key');
        $SECRET = $this->config->item('shopify_secret_key');
        $STORE_URL = $this->config->item('shopify_store_url');
        $url = 'https://' . $API_KEY . ':' . $SECRET . '@' . $STORE_URL . $urlparameters;
        $session = curl_init();
        curl_setopt($session, CURLOPT_URL, $url);
        //curl_setopt($session, CURLOPT_POST, 1); 
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $jsondata = curl_exec($session);
        curl_close($session);
        $jsondata = str_replace("\n", "", $jsondata);
        $jsondata = str_replace("\r", "", $jsondata);
        $obj = json_decode($jsondata, true);
        return $obj;
    }

//edit
}
