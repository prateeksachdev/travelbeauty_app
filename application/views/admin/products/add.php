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
          <a href="#">New</a>
        </li>
      </ul>
      
      <div class="page-header">
        <h2>
          Add Sku
        </h2>
      </div>
 
      <?php
      //flash messages
      if(isset($flash_message)){
        if($flash_message == TRUE)
        {
          echo '<div class="alert alert-success">';
            echo '<a class="close" data-dismiss="alert">×</a>';
            echo '<strong>Well done!</strong> new product created with success.';
          echo '</div>';       
        }else{
         if(isset($error)){
          echo '<div class="alert alert-error">';
            echo '<a class="close" data-dismiss="alert">×</a>';
            
               echo $error;
            echo '</div>'; 
           }
                  
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
      
      echo form_open('admin/products/add', $attributes);
      ?>
        <fieldset>
          <div class="control-group">
            <label for="inputError" class="control-label">Amazon Sku</label>
            <div class="controls">
              <input type="text" id="" name="amazon_sku" value="<?php echo set_value('amazon_sku'); ?>" >
              <!--<span class="help-inline">Woohoo!</span>-->
            </div>
          </div>
             
          <div class="control-group">
            <label for="inputError" class="control-label">Shopify Sku</label>
            <div class="controls">
              <input type="text" id="" name="shopify_sku" value="<?php echo set_value('shopify_sku'); ?>">
              <!--<span class="help-inline">Cost Price</span>-->
            </div>
          </div>
             <div class="control-group">
            <label for="inputError" class="control-label">Product id</label>
            <div class="controls">
              <input type="text" id="" name="product_id" value="<?php echo set_value('product_id'); ?>" >
              <!--<span class="help-inline">Woohoo!</span>-->
            </div>
          </div>
             
          <div class="control-group">
            <label for="inputError" class="control-label">Variant id</label>
            <div class="controls">
              <input type="text" id="" name="variant_id" value="<?php echo set_value('variant_id'); ?>">
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
     