<div class="form-horizontal">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo $cancel_heading; ?>
        </div>
        <div class="panel-body">
            <?php if ($error) { ?>
            <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>
                <?php echo $error; ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
            <?php } ?>
            <div class="form-group required">
                <label class="col-sm-2 control-label" for="cancel-status">
                    <?php echo $order_status; ?>
                </label>

                <div class="col-sm-10">
                    <select name="order_status" id="order-status" class="form-control">
                        <?php foreach ($order_statuses as $status) { ?>
                        <option value="<?php echo $status['order_status_id']; ?>">
                            <?php echo $status['name']; ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="form-group ">
                <label class="col-sm-2 control-label" for="cancel-note">
                    <?php echo $cancel_note; ?>
                </label>

                <div class="col-sm-10">
                    <textarea name="cancel_note" id="cancel-note" class="form-control"></textarea>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <a href="javascript:void(0);" id="submitCancel" class="btn btn-primary">
                <?php echo $button_submit; ?>
            </a>&nbsp;
            <a href="javascript:void(0);" id="cancelCancel" class="btn btn-default">
                <?php echo $button_cancel; ?>
            </a>
        </div>
    </div>
</div>