<?php
class orders_model extends CI_Model {
 
    /**
    * Responsable for auto load the database
    * @return void
    */
    public function __construct()
    {
        $this->load->database();
    }

    /**
    * Get product by his is
    * @param int $product_id 
    * @return array
    */
    public function get_orders($search_string=null, $order=null, $order_type='Asc', $limit_start, $limit_end)
    {
        
        $this->db->select('order_details.id');
        $this->db->select('order_details.purchase_date');
        $this->db->select('order_details.order_id');
        $this->db->select('order_details.amazon_order_status');
        $this->db->select('order_details.shopify_update_status');
        $this->db->select('order_details.amazon_sku');  
          $this->db->select('order_details.shopify_order_id');
        //$this->db->select('products.manufacture_id');
        //$this->db->select('manufacturers.name as manufacture_name');
        $this->db->from('order_details');
       
        if($search_string){
            $this->db->like('order_id', $search_string);
        }

        //$this->db->join('manufacturers', 'order_details.manufacture_id = manufacturers.id', 'left');

        $this->db->group_by('order_details.id');

        if($order){
            $this->db->order_by($order, $order_type);
        }else{
            $this->db->order_by('id', $order_type);
        }


        $this->db->limit($limit_start, $limit_end);
        //$this->db->limit('4', '4');


        $query = $this->db->get();
        return $query->result_array();  
    }

    /**
    * Count the number of rows
    * @param int $manufacture_id
    * @param int $search_string
    * @param int $order
    * @return int
    */
    function count_orders($search_string=null, $order=null)
    {
        $this->db->select('*');
        $this->db->from('order_details');
        if($search_string){
            $this->db->like('order_id', $search_string);
        }
        if($order){
            $this->db->order_by($order, 'Asc');
        }else{
            $this->db->order_by('id', 'Asc');
        }
        $query = $this->db->get();
        return $query->num_rows();        
    }

    /**
    * Store the new item into the database
    * @param array $data - associative array with data to store
    * @return boolean 
    */
    
	
}
?>	
