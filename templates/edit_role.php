<?php
/**
 * The template for edit role page.
 *
 */
 ?>
 <style>
 .formfield{float:left; width:auto; margin-right:15px;}
  .formfield label{display:block;}
 </style>
    <div class="wrap">
				<h2><?php _e("Edit Role",$this->textdomain); ?></h2>
				<form method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
					<div id="titlewrap">
					<label for="title" id="title-prompt-text" class=""><?php _e("Role Name",$this->textdomain); ?></label>
					<input type="text" <?php echo $disabled; ?> autocomplete="off" id="title" value="<?php echo $all_roles[$role_key]['name']; ?>" size="30" name="role_name">
					<br /><br />
					<label for="priceon" id="priceon-prompt-text" class=""><?php _e("Enable Price Levels",$this->textdomain); ?></label>&nbsp;
					<input type="checkbox" id="priceon"  name="priceon" value="1" <?php echo $priceon; ?>/>
					<input type="hidden" name="role_key" value="<?php echo $role_key; ?>">
					</div><br />
					<div class="formfield price_type"  style="display:none">
					<label for="price_type" id="price_type-prompt-text"><?php _e("Price Type",$this->textdomain); ?></label>
					<select name="price_type" id="price_type">
						<option value="u" <?php echo $un_price; ?>>Unique Price</option>
						<option value="c" <?php echo $cal_price; ?>>Calculated Price</option>
					</select>
					</div>
					<span id="price_options" style="display:none;">
						<div class="formfield price_type2">
						<label for="price_type2" id="price_type2-prompt-text"><?php _e("Calculation Source",$this->textdomain); ?></label>
						<select id="price_type2" name="price_type2">
							<option value="1" <?php if(isset($all_roles[$role_key]['price_type2']) && $all_roles[$role_key]['price_type2']==1){echo 'selected="selected"';} ?>>Regular Price</option>
							<option value="2" <?php if(isset($all_roles[$role_key]['price_type2']) && $all_roles[$role_key]['price_type2']==2){echo 'selected="selected"';} ?>>Cost</option>
							<option value="3" <?php if(isset($all_roles[$role_key]['price_type2']) && $all_roles[$role_key]['price_type2']==3){echo 'selected="selected"';} ?>>MSRP</option>
							<?php foreach($all_roles as $key=>$role){ 
								if (isset($role['priceon']) && $role['priceon']==1){
									$select_r='';
									if(isset($all_roles[$role_key]['price_type2']) && $all_roles[$role_key]['price_type2']=='pricerole_'.$key) $select_r='selected="selected"';
									echo '<option value="'.'pricerole_'.$key.'" '.$select_r.'>Role: '.$role['name'].'</option>';
								}
							} ?>
						</select>
						</div>
						<div class="formfield price_sign">
						<label for="price_sign" id="price_sign-prompt-text"><?php _e("Type",$this->textdomain); ?></label>
						<select name="price_sign">
							<option value="+" <?php if(isset($all_roles[$role_key]['price_sign']) && $all_roles[$role_key]['price_sign']=='+'){echo 'selected="selected"';} ?>>+</option>
							<option value="-" <?php if(isset($all_roles[$role_key]['price_sign']) && $all_roles[$role_key]['price_sign']=='-'){echo 'selected="selected"';} ?>>-</option>
						</select>
						</div>
						<div class="formfield price_sign">
						<label for="price_percent" id="price_percent-prompt-text"><?php _e("Amount",$this->textdomain); ?></label>
						<input type="text" name="price_percent" size="5" <?php if(isset($all_roles[$role_key]['price_percent'])){echo 'value="'.$all_roles[$role_key]['price_percent'].'"';} ?>/>%
						</div>
						<div style="clear: both; padding-top:10px;">
					<label for="priceover" id="priceover-prompt-text" class=""><?php _e("Enable Price Override on Product Page",$this->textdomain); ?></label>&nbsp;
					<input type="checkbox" id="priceover"  name="priceover" value="1" <?php echo $priceover; ?>/>
					</div>
					</span>
					<br /><br />
					
					<div style="clear: both; padding-top:15px;">
					<input type="hidden" name="action" value="editrole" />
					<input id="publish" class="button button-primary button-large" type="submit" accesskey="p" value="<?php _e("Save",$this->textdomain); ?>" name="publish">
					</div>
				</form>
        
    </div>
<script>
jQuery(document).ready(function(){
	if(jQuery('#priceon').is(":checked")){
			jQuery('.price_type').show();
			if(jQuery('#price_type').val()=='c'){
				jQuery('#price_options').show();
			}
	}
});
jQuery('#priceon').change(function(){
  if(jQuery(this).is(':checked')){
    jQuery('.price_type').show();
	if(jQuery('#price_type').val()=='c'){
			jQuery('#price_options').show();
	}
  } else {
   jQuery('.price_type').hide();
   jQuery('#price_options').hide();
  }
});
	jQuery('#price_type').change(function(){
		if(jQuery(this).val()=='c'){
			jQuery('#price_options').show();
		}else{
			jQuery('#price_options').hide();
		}
	});
	
</script>