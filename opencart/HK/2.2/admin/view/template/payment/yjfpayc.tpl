<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-cod" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>

    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $MODULE_PAYMENT_YJFPAYC_TEXT_TITLE; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-cod" class="form-horizontal">

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-debug"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_STATUS_TITLE; ?></label>
			  <div class="col-sm-10">
				<select name="yjfpayc_status" id="input-yjfpayc_status" class="form-control">
				  <?php if ($yjfpayc_status) { ?>
					  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
					  <option value="0"><?php echo $text_disabled; ?></option>
				  <?php } else { ?>
					  <option value="1"><?php echo $text_enabled; ?></option>
					  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
				  <?php } ?>
				</select>
				
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-total"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_NAME_TITLE; ?> </label>
			  <div class="col-sm-10">
				<input type="text" name="yjfpayc_merchant_name" value="<?php echo $yjfpayc_merchant_name; ?>" id="input-merchant-name" class="form-control" />
				</div>
			</div>
			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-total"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_MERCHANT_EMAIL_TITLE; ?> </label>
			  <div class="col-sm-10">
				<input type="text" name="yjfpayc_merchant_email" value="<?php echo $yjfpayc_merchant_email; ?>" id="input-merchant-email" class="form-control" />
				 </div>
			</div>
			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-total"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PARTNER_ID_TITLE; ?> </label>
			  <div class="col-sm-10">
				<input type="text" name="yjfpayc_partner_id" value="<?php echo $yjfpayc_partner_id; ?>" id="input-partner-id" class="form-control" />
				<span class="help-block"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PARTNER_ID_DESCRIPTION; ?></span> </div>
			</div>
			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-total"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SECRET_KEY_TITLE; ?> </label>
			  <div class="col-sm-10">
				<input type="text" name="yjfpayc_secret_key" value="<?php echo $yjfpayc_secret_key; ?>" id="input-secret-key" class="form-control" />
				<span class="help-block"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SECRET_KEY_DESCRIPTION; ?></span> </div>
			</div>
			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-total"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ACQUIRING_TYPE_TITLE; ?> </label>
			  <div class="col-sm-10">
				  <select name="yjfpayc_acquiring_type" id="input-yjfpayc-acquiring-type" class="form-control">
							<?php if ($yjfpayc_acquiring_type == 'CRDIT') { ?>
							<option value="CRDIT" selected="selected"><?php echo "CRDIT"; ?></option>
							<?php } else { ?>
							<option value="CRDIT"><?php echo "CRDIT"; ?></option>
							<?php } ?>
							<?php if ($yjfpayc_acquiring_type == 'YANDEX') { ?>
							<option value="YANDEX" selected="selected"><?php echo "YANDEX"; ?></option>
							<?php } else { ?>
							<option value="YANDEX"><?php echo "YANDEX"; ?></option>
							<?php } ?>
				  </select>
				<span class="help-block"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ACQUIRING_TYPE_DESCRIPTION; ?></span> </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_SUBMIT_STATUS_ID_TITLE; ?></label>
			  <div class="col-sm-10">
				<select name="yjfpayc_submit_status_id" class="form-control">
				  <?php foreach ($order_statuses as $order_status) { ?>
					  <?php if ($order_status['order_status_id'] == $yjfpayc_submit_status_id) { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
					  <?php } else { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
					  <?php } ?>
				  <?php } ?>
				</select>
			  </div>
			</div>
			<div class="form-group">
			  <label class="col-sm-2 control-label"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_PAYMENT_STATUS_ID_TITLE; ?></label>
			  <div class="col-sm-10">
				<select name="yjfpayc_payment_status_id" class="form-control">
				  <?php foreach ($order_statuses as $order_status) { ?>
					  <?php if ($order_status['order_status_id'] == $yjfpayc_payment_status_id) { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
					  <?php } else { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
					  <?php } ?>
				  <?php } ?>
				</select>
			  </div>
			</div>
			<div class="form-group">
			  <label class="col-sm-2 control-label"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_AUTHORIZE_STATUS_ID_TITLE; ?></label>
			  <div class="col-sm-10">
				<select name="yjfpayc_authorize_status_id" class="form-control">
				  <?php foreach ($order_statuses as $order_status) { ?>
					  <?php if ($order_status['order_status_id'] == $yjfpayc_authorize_status_id) { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
					  <?php } else { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
					  <?php } ?>
				  <?php } ?>
				</select>
			  </div>
			</div>
			<div class="form-group">
			  <label class="col-sm-2 control-label"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_FAIL_STATUS_ID_TITLE; ?></label>
			  <div class="col-sm-10">
				<select name="yjfpayc_fail_status_id" class="form-control">
				  <?php foreach ($order_statuses as $order_status) { ?>
					  <?php if ($order_status['order_status_id'] == $yjfpayc_fail_status_id) { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
					  <?php } else { ?>
						  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
					  <?php } ?>
				  <?php } ?>
				</select>
			  </div>
			</div>

			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-geo-zone"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ZONE_TITLE; ?></label>
			  <div class="col-sm-10">
				<select name="yjfpayc_geo_zone_id" id="input-geo-zone" class="form-control">
				  <option value="0"><?php echo $text_all_zones; ?></option>
				  <?php foreach ($geo_zones as $geo_zone) { ?>
					  <?php if ($geo_zone['geo_zone_id'] == $yjfpayc_geo_zone_id) { ?>
						  <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
					  <?php } else { ?>
						  <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
					  <?php } ?>
				  <?php } ?>
				</select>
				<span class="help-block"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ZONE_DESCRIPTION; ?></span> 
			  </div>
			</div>
			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-debug"><?php echo $MODULE_PAYMENT_YJFPAYC_GATEWAY_URL_TITLE; ?></label>
			  <div class="col-sm-10">
				<select name="yjfpayc_gateway_url_debug" id="input-payment_yjfpayc_gateway_url" class="form-control">
				  <?php if ($yjfpayc_gateway_url_debug) { ?>
					  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
					  <option value="0"><?php echo $text_disabled; ?></option>
				  <?php } else { ?>
					  <option value="1"><?php echo $text_enabled; ?></option>
					  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
				  <?php } ?>
				</select>
				<span class="help-block"><?php echo $MODULE_PAYMENT_YJFPAYC_GATEWAY_URL_DESCRIPTION; ?></span>
			  </div>
			</div>
			<div class="form-group">
			  <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $MODULE_PAYMENT_YJFPAYC_CONFIGURATION_ORDER_TITLE; ?></label>
			  <div class="col-sm-10">
				<input type="text" name="yjfpayc_sort_order" value="<?php echo $yjfpayc_sort_order; ?>"id="input-sort-order" class="form-control" />
			  </div>
			</div>

        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?> 

