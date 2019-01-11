<div class="form-horizontal">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo $refund_heading; ?>
        </div>
        <div class="panel-body">
            <?php if ($error) { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>
                <?php echo $error; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php } ?>

            <div class="form-group required">
                <label class="col-sm-2 control-label"
                       for="enable-refund-money"><?php echo $refund_enable_money; ?></label>

                <div class="col-sm-10">
                    <div class="input-group">
                        <input type="text" name="enable_refund_money" id="enable-refund-money"
                               value="<?php echo sprintf(" %.2f",$enableRefundMoney); ?>"
                        class="form-control" readonly/>
                        <span class="input-group-addon"><?php echo $currencyCode; ?></span>
                    </div>
                </div>
            </div>
            <div class="form-group required">
                <label class="col-sm-2 control-label"
                       for="refund-money"><?php echo $refund_money; ?></label>

                <div class="col-sm-10">
                    <div class="input-group">
                        <input type="text" name="refund_money" id="refund-money"
                               value="<?php echo sprintf(" %.2f",$input_refund_money); ?>"
                        class="form-control" />
                        <span class="input-group-addon"><?php echo $currencyCode; ?></span>
                    </div>
                    <?php if ($error_refund_money) { ?>
                    <div class="text-danger"><?php echo $error_refund_money; ?></div>
                    <?php } ?>
                </div>
            </div>
            <div class="form-group required">
                <label class="col-sm-2 control-label" for="cancel-status">
                    <?php echo $order_status; ?>
                </label>

                <div class="col-sm-10">
                    <select name="order_status" id="order-status" class="form-control">
                        <?php foreach ($order_statuses as $status) { ?>
                        <?php if ($status['order_status_id'] == $input_order_status) { ?>
                        <option value="<?php echo $status['order_status_id']; ?>" selected>
                            <?php echo $status['name']; ?>
                        </option>
                        <?php } else { ?>
                        <option value="<?php echo $status['order_status_id']; ?>">
                            <?php echo $status['name']; ?>
                        </option>
                        <?php } ?>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="form-group ">
                <label class="col-sm-2 control-label"
                       for="refund-note"><?php echo $refund_note; ?></label>

                <div class="col-sm-10">
                <textarea name="refund_note" id="refund-note"
                          class="form-control"><?php echo $input_refund_note; ?></textarea>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <a href="javascript:void(0);" id="submitRefund" class="btn btn-primary">
                <?php echo $button_submit; ?>
            </a>&nbsp;
            <a href="javascript:void(0);" id="cancelRefund" class="btn btn-default">
                <?php echo $button_cancel; ?>
            </a>
        </div>
    </div>
</div>