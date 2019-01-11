<?php echo $header; ?><?php echo $column_left; ?>
<div id="content" xmlns="http://www.w3.org/1999/html">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-espay-pay" data-toggle="tooltip"
                        title="<?php echo $button_save; ?>" class="btn btn-primary">
                    <i class="fa fa-save"></i>
                </button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                   class="btn btn-default"><i class="fa fa-reply"></i>
                </a>
            </div>

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
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data"
                      id="form-espay-pay" class="form-horizontal">
                    <fieldset>
                        <legend>
                            <?php echo $text_merchant_info; ?>
                        </legend>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-merchant-name"><?php echo $entry_merchant_name; ?></label>

                            <div class="col-sm-10">
                                <input type="text" name="espay_merchant_name"
                                       value="<?php echo $espay_merchant_name; ?>"
                                       placeholder="<?php echo $entry_merchant_name; ?>" id="espay-merchant-name"
                                       class="form-control"/>
                                <?php if ($error_merchant_name) { ?>
                                <div class="text-danger"><?php echo $error_merchant_name; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-merchant-email"><?php echo $entry_merchant_email; ?></label>

                            <div class="col-sm-10">
                                <input type="text" name="espay_merchant_email"
                                       value="<?php echo $espay_merchant_email; ?>"
                                       placeholder="<?php echo $entry_merchant_email; ?>" id="espay-merchant-email"
                                       class="form-control"/>
                                <?php if ($error_merchant_email) { ?>
                                <div class="text-danger"><?php echo $error_merchant_email; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-partner-id"><?php echo $entry_partner_id; ?></label>

                            <div class="col-sm-10">
                                <input type="text" name="espay_partner_id" value="<?php echo $espay_partner_id; ?>"
                                       placeholder="<?php echo $entry_partner_id; ?>" id="espay-partner-id"
                                       class="form-control"/>
                                <?php if ($error_partner_id) { ?>
                                <div class="text-danger"><?php echo $error_partner_id; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-certificate-cipher"><?php echo $entry_certificate_cipher; ?></label>

                            <div class="col-sm-10">
                                <input type="text" name="espay_certificate_cipher" value="<?php echo $espay_certificate_cipher; ?>"
                                       placeholder="<?php echo $entry_certificate_cipher; ?>" id="espay-certificate-cipher"
                                       class="form-control"/>
                                <?php if ($error_certificate_cipher) { ?>
                                <div class="text-danger"><?php echo $error_certificate_cipher; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-currency"><?php echo $entry_currency; ?></label>

                            <div class="col-sm-10">
                                <select name="espay_currency" id="espay-currency" class="form-control">
                                    <?php foreach ($entry_currency_range as $currency => $currencyName) { ?>
                                    <option value="<?php echo $currency; ?>"
                                    <?php if ($currency == $espay_currency) echo 'selected';?>
                                    ><?php echo $currencyName; ?></option>
                                    <?php } ?>
                                </select>
                                <?php if ($error_currency) { ?>
                                <div class="text-danger"><?php echo $error_currency; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo $text_order_status; ?></legend>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-status-submit"><?php echo $entry_status_submit; ?></label>

                            <div class="col-sm-10">
                                <select name="espay_status_submit" id="espay-status-submit" class="form-control">
                                    <?php foreach ($order_statuses as $status) { ?>
                                    <option value="<?php echo $status['order_status_id']; ?>"
                                    <?php if ($espay_status_submit == $status['order_status_id']) echo 'selected';?>>
                                    <?php echo $status['name']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                <?php if ($error_status_submit) { ?>
                                <div class="text-danger"><?php echo $error_status_submit; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-status-authorize"><?php echo $entry_status_authorize; ?></label>

                            <div class="col-sm-10">
                                <select name="espay_status_authorize" id="espay-status-authorize" class="form-control">
                                    <?php foreach ($order_statuses as $status) { ?>
                                    <option value="<?php echo $status['order_status_id']; ?>"
                                    <?php if ($espay_status_authorize == $status['order_status_id']) echo 'selected';?>>
                                    <?php echo $status['name']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                <?php if ($error_status_authorize) { ?>
                                <div class="text-danger"><?php echo $error_status_authorize; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-status-complete"><?php echo $entry_status_complete; ?></label>

                            <div class="col-sm-10">
                                <select name="espay_status_complete" id="espay-status-complete" class="form-control">
                                    <?php foreach ($order_statuses as $status) { ?>
                                    <option value="<?php echo $status['order_status_id']; ?>"
                                    <?php if ($espay_status_complete == $status['order_status_id']) echo 'selected';?>>
                                    <?php echo $status['name']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                <?php if ($error_status_complete) { ?>
                                <div class="text-danger"><?php echo $error_status_complete; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-status-complete"><?php echo $entry_status_fail; ?></label>

                            <div class="col-sm-10">
                                <select name="espay_status_fail" id="espay-status-fail" class="form-control">
                                    <?php foreach ($order_statuses as $status) { ?>
                                    <option value="<?php echo $status['order_status_id']; ?>"
                                    <?php if ($espay_status_fail == $status['order_status_id']) echo 'selected';?>>
                                    <?php echo $status['name']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                <?php if ($error_status_fail) { ?>
                                <div class="text-danger"><?php echo $error_status_fail; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo $text_service_url; ?></legend>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-notify"><?php echo $entry_notify; ?></label>

                            <div class="col-sm-10">
                                <input type="text" name="espay_notify"
                                       value="<?php echo $espay_notify; ?>" disabled
                                       placeholder="<?php echo $entry_notify; ?>" id="espay-notify"
                                       class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label"
                                   for="espay-debug"><?php echo $entry_debug; ?></label>

                            <div class="col-sm-10">
                                <select name="espay_debug" id="espay-debug" class="form-control">
                                    <?php foreach ($entry_debug_range as $debug => $debugUrl) { ?>
                                    <option value="<?php echo $debug; ?>"
                                    <?php if ($espay_debug == $debug) echo 'selected';?>>
                                    <?php echo $debugUrl; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                <?php if ($error_debug) { ?>
                                <div class="text-danger"><?php echo $error_debug; ?></div>
                                <?php } ?>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo $text_status; ?></legend>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="espay-sort-order"><?php echo $entry_sort_order; ?></label>

                            <div class="col-sm-10">
                                <input type="text" name="espay_sort_order" class="form-control"
                                       value="<?php echo $espay_sort_order; ?>"
                                       placeholder="<?php echo $entry_sort_order; ?>" id="espay-sort-order"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="espay-geo-zone">
                                <?php echo $entry_geo_zone; ?>
                            </label>

                            <div class="col-sm-10">
                                <select name="espay_geo_zone" id="espay-geo-zone" class="form-control">
                                    <option value="0"><?php echo $entry_geo_all; ?></option>
                                    <?php foreach ($geo_zones as $zone) { ?>
                                    <option value="<?php echo $zone['geo_zone_id']; ?>"
                                    <?php if ($espay_geo_zone == $zone['geo_zone_id']) echo 'selected';?>>
                                    <?php echo $zone['name']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="espay-status"><?php echo $entry_status; ?></label>

                            <div class="col-sm-10">
                                <select name="espay_status" id="espay-status" class="form-control">
                                    <?php foreach ($entry_status_range as $key => $val) { ?>
                                    <option value="<?php echo $key; ?>"
                                    <?php if ($espay_status == $key) echo 'selected';?>>
                                    <?php echo $val; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
