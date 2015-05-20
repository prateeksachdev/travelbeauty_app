    <div class="container top">
      
      <ul class="breadcrumb">
        <li>
          <a href="<?php echo site_url("admin"); ?>">
            <?php echo ucfirst($this->uri->segment(1));?>
          </a> 
          <span class="divider">/</span>
        </li>
        <li>
          <a href="<?php echo site_url("admin").'/'.$this->uri->segment(2); ?>">
            <?php echo ucfirst($this->uri->segment(2));?>
          </a> 
          <span class="divider">/</span>
        </li>
        <li class="active">
          <a href="#">Update</a>
        </li>
      </ul>
      
      <div class="page-header">
        <h2>
          Updating Sku Table
        </h2>
      </div>

 
      <?php
      //flash messages
     
      if($this->session->flashdata('flash_message')){
        if($this->session->flashdata('flash_message') == 'updated')
        {
          echo '<div class="alert alert-success">';
            echo '<a class="close" data-dismiss="alert">×</a>';
            echo '<strong>Well done!</strong> product updated  successfuly.';
          echo '</div>';       
        }else{
          echo '<div class="alert alert-error">';
            echo '<a class="close" data-dismiss="alert">×</a>';
            echo '<strong>Sku Details Not Found</strong>';
          echo '</div>';          
        }
      }
      ?>
      
      <?php
      //form data
      $attributes = array('class' => 'form-horizontal', 'id' => '');
      $options_manufacture = array('' => "Select");
      foreach ($manufactures as $row)
      {
        $options_manufacture[$row['id']] = $row['name'];
      }

      //form validation
      echo validation_errors();

      echo form_open('admin/products/update/'.$this->uri->segment(4).'', $attributes);
      ?>
        <fieldset>
          <div class="control-group">
            <label for="inputError" class="control-label">amazon_sku</label>
            <div class="controls">
              <input type="text" id="" name="amazon_sku" value="<?php echo $product[0]['amazon_sku']; ?>" >
              <!--<span class="help-inline">Woohoo!</span>-->
            </div>
          </div>
                  
          <div class="control-group">
            <label for="inputError" class="control-label">Shopify Sku</label>
            <div class="controls">
              <input type="text" id="" name="shopify_sku" value="<?php echo $product[0]['shopify_sku'];?>">
              <!--<span class="help-inline">Cost Price</span>-->
            </div>
          </div>
        
          
          <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save changes</button>
            <?php echo '<a href="' . site_url("admin") . '/products" class="btn">Cancel</a>'; ?> 
       
          </div>
        </fieldset>

      <?php echo form_close(); ?>

    </div>
     