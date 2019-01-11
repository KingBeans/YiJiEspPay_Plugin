<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <h1><?= $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?= $breadcrumb['href']; ?>"><?= $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if ($error_warning) { ?>
        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>
            <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?= $heading_title; ?></h3>
            </div>

            <div class="panel-body">
                <form action="<?= $action ?>" method="post" enctype="multipart/form-data" id="form" class="form-horizontal">

                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-status"><?= $entry_status ?></label>
                        <div class="col-sm-10">
                            <select name="yjpay_status" class="form-control">
                                <option value="1" <?= isset($yjpay_status) && $yjpay_status == 1 ? 'selected="selected"' : '' ; ?> >open</option>
                                <option value="0" <?= isset($yjpay_status) && $yjpay_status == 0 ? 'selected="selected"' : '' ; ?>>close</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-merchant"><?= $text_sort_order ?></label>
                        <div class="col-sm-10">
                            <input size="10" type="number" name="yjpay_sort_order"  value="<?= isset($yjpay_sort_order) ? $yjpay_sort_order :''; ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-merchant"><?= $entry_style ?></label>
                        <div class="col-sm-10">
                            <input type="radio" name="yjpay_style" value="jump" <?= isset($yjpay_style) && $yjpay_style == 'jump' ? 'checked' : 'checked'; ?> > Jump
                            <input type="radio" name="yjpay_style" value="embed" <?= isset($yjpay_style) && $yjpay_style == 'embed' ? 'checked' : ''; ?> > Embed
                        </div>
                    </div>

                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-status"><?= $entry_debug ?></label>
                        <div class="col-sm-10">
                            <select name="yjpay_debug" class="form-control">
                                <option value="1" <?= isset($yjpay_debug) && $yjpay_debug == 1 ? 'selected="selected"' : '' ; ?> >open</option>
                                <option value="0" <?= isset($yjpay_debug) && $yjpay_debug == 0 ? 'selected="selected"' : '' ; ?>>close</option>
                            </select>
                        </div>
                    </div>

                    <fieldset>
                        <legend>Partner Info</legend>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-merchant"><?= $text_partnerId ?></label>
                            <div class="col-sm-10">
                                <input size="10" type="text" name="yjpay_partnerId" value="<?= isset($yjpay_partnerId) ? $yjpay_partnerId :''; ?>" class="form-control" />
                                <span class="error"><?= isset($error_partnerId) ? $error_partnerId :  'partner Id' ; ?></span>
                            </div>
                        </div>

                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-merchant"><?= $text_secretKey ?></label>
                            <div class="col-sm-10">
                                <input size="10" type="text" name="yjpay_secretKey" value="<?= isset($yjpay_secretKey) ? $yjpay_secretKey :''; ?>" class="form-control" />
                                <span class="error"><?= isset($error_secretKey) ? $error_secretKey :  'secret key' ; ?></span>
                            </div>
                        </div>

                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-merchant"><?= $text_email ?></label>
                            <div class="col-sm-10">
                                <input type="text" name="yjpay_email" value="<?= isset($yjpay_email) ? $yjpay_email :''; ?>" class="form-control" />
                            </div>
                        </div>

                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-merchant"><?= $text_mname ?></label>
                            <div class="col-sm-10">
                                <input type="text" name="yjpay_mname" value="<?= isset($yjpay_mname) ? $yjpay_mname :''; ?>" class="form-control" />
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Order Status Setting</legend>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-status"><?= $entry_start_status ?></label>
                            <div class="col-sm-10">
                                <select name="yjpay_start_status" class="form-control">
                                    <?php foreach($orderStatusList as $v ){ ?>
                                    <option value="<?= $v['order_status_id'] ?>" ><?= $v['name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-status"><?= $entry_success_status ?></label>
                            <div class="col-sm-10">
                                <select name="yjpay_success_status" class="form-control">
                                    <?php foreach($orderStatusList as $v ){ ?>
                                    <option value="<?= $v['order_status_id'] ?>" <?= isset($yjpay_success_status) && $yjpay_success_status == $v['order_status_id'] ? ' selected="selected" ' : ''; ?> ><?= $v['name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-status"><?= $entry_authorizing_status ?></label>
                            <div class="col-sm-10">
                                <select name="yjpay_authorizing_status" class="form-control">
                                    <?php foreach($orderStatusList as $v ){ ?>
                                    <option value="<?= $v['order_status_id'] ?>" <?= isset($yjpay_authorizing_status) && $yjpay_authorizing_status == $v['order_status_id'] ? ' selected="selected" ' : ''; ?> ><?= $v['name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-status"><?= $entry_fail_status ?></label>
                            <div class="col-sm-10">
                                <select name="yjpay_fail_status" class="form-control">
                                    <?php foreach($orderStatusList as $v ){ ?>
                                    <option value="<?= $v['order_status_id'] ?>" <?= isset($yjpay_fail_status) && $yjpay_fail_status == $v['order_status_id'] ? ' selected="selected" ' : ''; ?> ><?= $v['name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Other Setting</legend>
                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-status"><?= $entry_currency ?></label>
                            <div class="col-sm-10">
                                <select name="yjpay_currency" class="form-control">
                                    <option value="USD" <?= isset($yjpay_currency) && $yjpay_currency == 'USD' ? ' selected="selected" ' :  ' selected="selected" ' ; ?> >USD</option>
                                    <option value="CNY" <?= isset($yjpay_currency) && $yjpay_currency == 'CNY' ? ' selected="selected" ' :  ' ' ; ?> >CNY</option>
                                    <option value="JPY" <?= isset($yjpay_currency) && $yjpay_currency == 'JPY' ? ' selected="selected" ' :  ' ' ; ?> >JPY</option>
                                    <option value="AUD" <?= isset($yjpay_currency) && $yjpay_currency == 'AUD' ? ' selected="selected" ' :  ' ' ; ?> >AUD</option>
                                    <option value="HKD" <?= isset($yjpay_currency) && $yjpay_currency == 'HKD' ? ' selected="selected" ' :  ' ' ; ?> >HKD</option>
                                    <option value="GBP" <?= isset($yjpay_currency) && $yjpay_currency == 'GBP' ? ' selected="selected" ' :  ' ' ; ?> >GBP</option>
                                    <option value="EUR" <?= isset($yjpay_currency) && $yjpay_currency == 'EUR' ? ' selected="selected" ' :  ' ' ; ?> >EUR</option>
                                    <option value="SGD" <?= isset($yjpay_currency) && $yjpay_currency == 'SGD' ? ' selected="selected" ' :  ' ' ; ?> >SGD</option>
                                    <option value="KRW" <?= isset($yjpay_currency) && $yjpay_currency == 'KRW' ? ' selected="selected" ' :  ' ' ; ?> >KRW</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-status"><?= $entry_language ?></label>
                            <div class="col-sm-10">
                                <select name="yjpay_language" class="form-control">
                                    <option value="en" <?= isset($yjpay_language) && $yjpay_language == 'en' ? ' selected="selected" ' :  ' ' ; ?> >English</option>
                                    <option value="de" <?= isset($yjpay_language) && $yjpay_language == 'de' ? ' selected="selected" ' :  ' ' ; ?> >German</option>
                                    <option value="es" <?= isset($yjpay_language) && $yjpay_language == 'es' ? ' selected="selected" ' :  ' ' ; ?> >Spain</option>
                                    <option value="fr" <?= isset($yjpay_language) && $yjpay_language == 'fr' ? ' selected="selected" ' :  ' ' ; ?> >French</option>
                                    <option value="ja" <?= isset($yjpay_language) && $yjpay_language == 'ja' ? ' selected="selected" ' :  ' ' ; ?> >Japanese</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group required">
                            <label class="col-sm-2 control-label" for="input-merchant"><?= $text_notifyUrl ?></label>
                            <div class="col-sm-10">
                                <input size="10" type="text" readonly  value="<?= isset($yjpay_notifyUrl) ? $yjpay_notifyUrl :''; ?>" class="form-control" />
                            </div>
                        </div>

                    </fieldset>


                    <div class="form-group">
                        <div class="pull-right">
                            <button type="submit" form="form" data-toggle="tooltip" title="save" class="btn btn-primary"><i class="fa fa-save"></i></button>
                            <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="cancel" class="btn btn-default"><i class="fa fa-reply"></i></a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>