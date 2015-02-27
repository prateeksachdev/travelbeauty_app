<?php
class Products_model extends CI_Model {
 
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
    public function get_product_by_id($id)
    {
		$this->db->select('*');
		$this->db->from('amazon_sku_match');
		$this->db->where('id', $id);
		$query = $this->db->get();
		return $query->result_array(); 
    }

    /**
    * Fetch amazon_sku_match data from the database
    * possibility to mix search, filter and order
    * @param int $manufacuture_id 
    * @param string $search_string 
    * @param strong $order
    * @param string $order_type 
    * @param int $limit_start
    * @param int $limit_end
    * @return array
    */
    public function get_products($manufacture_id=null, $search_string=null, $order=null, $order_type='Asc', $limit_start, $limit_end)
    {
	    
		$this->db->select('amazon_sku_match.id');
		$this->db->select('amazon_sku_match.amazon_sku');
		$this->db->select('amazon_sku_match.shopify_sku');
		$this->db->select('amazon_sku_match.product_id');
		$this->db->select('amazon_sku_match.variant_id');
		//$this->db->select('manufacturers.name as manufacture_name');
		$this->db->from('amazon_sku_match');
		
		if($search_string){
			$this->db->like('amazon_sku', $search_string);
		}

		

		$this->db->group_by('amazon_sku_match.id');

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
    function count_products($manufacture_id=null, $search_string=null, $order=null)
    {
		$this->db->select('*');
		$this->db->from('amazon_sku_match');
		
		if($search_string){
			$this->db->like('amazon_sku', $search_string);
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
    function store_product($data)
    {
        $this->db->where('amazon_sku', $this->input->post('amazon_sku'));
		$query = $this->db->get('amazon_sku_match');

        if($query->num_rows > 0){
        	echo '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>';
			  echo "Amazon Sku Should be Unique";	
			echo '</strong></div>';
		}else{
            $insert = $this->db->insert('amazon_sku_match', $data);
		    return $insert;
		}
	}

    /**
    * Update product
    * @param array $data - associative array with data to store
    * @return boolean
    */
    function update_product($id, $data)
    {       
		$this->db->where('id', $id);
		$this->db->update('amazon_sku_match', $data);
		$report = array();
		$report['error'] = $this->db->_error_number();
		$report['message'] = $this->db->_error_message();
		if($report !== 0){
			return true;
		}else{
			return false;
		}
	}

    /**
    * Delete product
    * @param int $id - product id
    * @return boolean
    */
	function delete_product($id){
		$this->db->where('id', $id);
		$this->db->delete('amazon_sku_match'); 
	}
 
}
?>	
