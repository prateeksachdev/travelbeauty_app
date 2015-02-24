    <div class="container top">

      <ul class="breadcrumb">
        <li>
          <a href="<?php echo site_url("admin"); ?>">
            <?php echo ucfirst($this->uri->segment(1));?>
          </a> 
          <span class="divider">/</span>
        </li>
        <li class="active">
          <?php echo ucfirst($this->uri->segment(2));?>
        </li>
      </ul>

      <div class="page-header users-header">
        <h2>
          <?php echo ucfirst($this->uri->segment(2));?> 
        </h2>
      </div>
      
      <div class="row">
        <div class="span12 columns">
          <div class="well">
           
            <?php
           
            $attributes = array('class' => 'form-inline reset-margin', 'id' => 'myform');
           
            //$options_manufacture = array(0 => "all");
            //foreach ($manufactures as $row)
            //{
            //  $options_manufacture[$row['id']] = $row['name'];
           // }
            //save the columns names in a array that we will use as filter         
            $options_products = array();    
            foreach ($products as $array) {
              foreach ($array as $key => $value) {
                $options_products[$key] = $key;
              }
              break;
            }

            echo form_open('admin/orders', $attributes);
     
              echo form_label('Search Amazon Order:', 'search_string');
              echo form_input('search_string', $search_string_selected, 'style="width: 170px;
height: 26px;"');

             
              echo form_label('Order by:', 'order');
              echo form_dropdown('order', $options_products, $order, 'class="span2"');

              $data_submit = array('name' => 'mysubmit', 'class' => 'btn btn-primary', 'value' => 'Go');

              $options_order_type = array('Asc' => 'Asc', 'Desc' => 'Desc');
              echo form_dropdown('order_type', $options_order_type, $order_type_selected, 'class="span1"');

              echo form_submit($data_submit);

            echo form_close();
            ?>

          </div>

          <table class="table table-striped table-bordered table-condensed">
            <thead>
              <tr>
                <th class="header">#</th>
                <th class="yellow header headerSortDown">Amazon order Id</th>
                <th class="red header">Amazon Status</th>
                <th class="red header">Amazon Sku</th>
                <th class="red header">Shopify Id</th>
                <th class="red header">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach($products as $row)
              {
                echo '<tr>';
                echo '<td>'.$row['id'].'</td>';
                echo '<td>'.$row['order_id'].'</td>';
                echo '<td>'.$row['amazon_order_status'].'</td>';
                echo '<td>'.$row['amazon_sku'].'</td>';
                echo '<td>'.$row['shopify_order_id'].'</td>';
                $line = '<td class="crud-actions">';
                  if (empty($row['shopify_order_id']) && $row['amazon_order_status'] == "Unshipped")
                {
               $line .='<a href="http://localhost/travelbeauty_app/amazonorders/createShopifyOrder?id='.$row['order_id'].'" class="btn btn-info">REPROCESS</a>'; 
               
                }
                echo $line;
                 echo '</td></tr>';
              }
              ?>      
            </tbody>
          </table>

          <?php echo '<div class="pagination">'.$this->pagination->create_links().'</div>'; ?>

      </div>
    </div>