<?php
/**
 * The template for edit role page.
 *
 */
 ?>
    <div class="wrap">
				<h2><?php echo __("Edit Role",$this->textdomain); ?></h2>
				<form method="post" action="<?php echo admin_url( 'admin.php' ); ?>">
					<div id="titlewrap">
					<label for="title" id="title-prompt-text" class=""><?php echo __("Role Name",$this->textdomain); ?></label>
					<input type="text" <?php echo $disabled; ?> autocomplete="off" id="title" value="<?php echo $all_roles[$role_key]['name']; ?>" size="30" name="role_name">
					<br /><br />
					<label for="priceon" id="priceon-prompt-text" class=""><?php echo __("Enable Price Levels",$this->textdomain); ?></label>&nbsp;
					<input type="checkbox" id="priceon"  name="priceon" value="1" <?php echo $priceon; ?>/>
					<input type="hidden" name="role_key" value="<?php echo $role_key; ?>">
					</div><br />
					<select name="price_type" id="price_type">
						<option value="u" <?php echo $un_price; ?>>Unique Price</option>
						<option value="c" <?php echo $cal_price; ?>>Calculated Price</option>
					</select>
					<br /></br />
					<div id="price_options" style="display:none">
						<select id="price_type2" name="price_type2">
							<option value="1" <?php if(isset($all_roles[$role_key]['price_type2']) && $all_roles[$role_key]['price_type2']==1){echo 'selected="selected"';} ?>>Regular Price</option>
							<option value="2" <?php if(isset($all_roles[$role_key]['price_type2']) && $all_roles[$role_key]['price_type2']==2){echo 'selected="selected"';} ?>>Cost</option>
							<option value="3" <?php if(isset($all_roles[$role_key]['price_type2']) && $all_roles[$role_key]['price_type2']==3){echo 'selected="selected"';} ?>>MSRP</option>
						</select>
						&nbsp;<select id="price_roles" name="price_roles">
							<?php foreach($all_roles as $key=>$role){ 
								if (isset($role['priceon']) && $role['priceon']==1){
									$select_r='';
									if(isset($all_roles[$role_key]['price_roles']) && $all_roles[$role_key]['price_roles']==$key) $select_r='selected="selected"';
									echo '<option value="'.$key.'" '.$select_r.'>'.$role['name'].'</option>';
								}
							} ?>
						</select>
						<br /></br />
						<select name="price_sign">
							<option value="+" <?php if(isset($all_roles[$role_key]['price_sign']) && $all_roles[$role_key]['price_sign']=='+'){echo 'selected="selected"';} ?>>+</option>
							<option value="-" <?php if(isset($all_roles[$role_key]['price_sign']) && $all_roles[$role_key]['price_sign']=='-'){echo 'selected="selected"';} ?>>-</option>
						</select>
						&nbsp;<input type="text" name="price_percent" size="5" <?php if(isset($all_roles[$role_key]['price_percent'])){echo 'value="'.$all_roles[$role_key]['price_percent'].'"';} ?>/>%
					</div>
					<br /></br />
					<input type="hidden" name="action" value="editrole" />
					<input id="publish" class="button button-primary button-large" type="submit" accesskey="p" value="<?php echo __("Save",$this->textdomain); ?>" name="publish">
				</form>
        
    </div>
<script>
jQuery(document).ready(function(){
if(jQuery('#price_type').val()=='c'){
			jQuery('#price_options').show();
	}
if(jQuery('#price_type2').val()=='1'){
			jQuery('#price_roles').show();
}else{
			jQuery('#price_roles').hide();
}	
});
	jQuery('#price_type').change(function(){
		if(jQuery(this).val()=='c'){
			jQuery('#price_options').show();
		}else{
			jQuery('#price_options').hide();
		}
	});
	jQuery('#price_type2').change(function(){
		if(jQuery(this).val()=='1'){
			jQuery('#price_roles').show();
		}else{
			jQuery('#price_roles').hide();
		}
	});
</script>