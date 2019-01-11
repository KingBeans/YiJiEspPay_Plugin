<table class="table table-bordered">
    <thead>
    <tr>
        <td colspan="2"><?php echo $textHeading; ?>
            <span class="pull-right">
               # <?php echo $order_sn; ?>
            </span>
        </td>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="width:200px;"><?php echo $textOrderFee ?></td>
        <td><?php echo $espay['currency'],'&nbsp;',$espay['order_fee'] ?></td>
    </tr>
    <tr>
        <td><?php echo $textStatus; ?></td>
        <td><?php echo $statuses[$espay['status']] ?></td>
    </tr>
    <tr>
        <td><?php echo $textPaymentFee; ?></td>
        <td>
            <span class="badge"><?php echo $espay['currency'],'&nbsp;',$espay['payment_fee'] ?></span>
            <small><?php echo $paymentDate ?></small>
        </td>
    </tr>
    <tr>
        <td><?php echo $textAuthorize ?></td>
        <td>
            <?php echo $authorizes[$espay['authorize']] ?>
            <?php echo $authorizeResults[$espay['authorize_result']];?>
            <small>
                <?php echo $authorizeDate ?>
            </small>
        </td>
    </tr>
    <tr>
        <td><?php echo $textRefundFee; ?></td>
        <td>
            <?php echo $espay['currency'],'&nbsp;',$espay['refund_fee']; ?>
            <small><?php echo $refundDate; ?></small>
        </td>
    </tr>
    </tbody>
</table>
<div id="refundContainer"></div>
<div id="espay-buttons" class="buttons">
    <?php if ($espay['status'] == ModelPaymentEspay::STATUS_PAYED) { ?>
    <?php if ($espay['payment_date'] > strtotime(date('Y-m-d',time())) + 84600) { ?>
    <a id="btnRefund" href="javascript:void(0);" class="btn btn-warning">
        <?php echo $button_refund; ?>
    </a>
    <?php } else { ?>
    {*<a id="btnCancel" href="javascript:void(0);" class="btn btn-warning">*}
        {*<?php echo $button_cancel; ?>*}
    {*</a>*}
    <?php } ?>
    <?php } else if ($espay['status'] == 2) { ?>
    <p class="well text-danger">
        <?php echo $espay['note']; ?>
    </p>
    <a id="btnAuthorizePass" href="javascript:void(0);" class="btn btn-warning">
        <?php echo $button_authorize_pass; ?>
    </a>
    &nbsp;
    <a id="btnAuthorizeDeny" href="javascript:void(0);" class="btn btn-warning">
        <?php echo $button_authorize_deny; ?>
    </a>
    <?php } ?>
</div>
<script type="text/javascript">
    (function ($) {

        var refund = {
            refundUrl: "index.php?route=payment/espay/refund&token=<?php echo $token; ?>&order_id=<?php echo $order_id; ?>",
            events: function () {
                var that = this;
                $("#btnRefund").click(function () {

                    $("#espay-buttons").hide();

                    if ($("#cancelRefund").length == 0) {
                        $("#refundContainer").load(that.refundUrl);
                    } else {
                        $("#refundContainer").show();
                    }
                });

                $(document).on('click', '#cancelRefund', function () {
                    $("#refundContainer").hide(function () {
                        $("#espay-buttons").show();
                    });
                });

                $(document).on('click', '#submitRefund', function () {
                    if (!confirm("<?php echo $refund_confirm; ?>")) {
                        return;
                    }

                    var enableMoney = $("input[name=enable_refund_money]").val();
                    var orderStatus = $("select[name=order_status]").val();
                    var money = $("input[name=refund_money]").val();
                    var note = $("textarea[name=refund_note]").val();

                    $.ajax({
                        url: that.refundUrl,
                        type: 'post',
                        data: {refund_money:money,refund_note:note,enable_refund_money:enableMoney,order_status:orderStatus},
                        dataType: 'html',
                        beforeSend: function () {
                            $('#submitRefund').button('loading');
                            $("#cancelRefund").hide();
                        },
                        complete: function () {
                            $('#submitRefund').button('reset');
                            $("#cancelRefund").show();
                        },
                        success: function (html) {
                            $("#refundContainer").html(html);
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        }
                    });
                });
            },
            init: function () {
                this.events();
            }
        };

        var cancel = {
            cancelUrl: "index.php?route=payment/espay/cancel&token=<?php echo $token; ?>&order_id=<?php echo $order_id; ?>",
            events: function () {
                var that = this;

                $("#btnCancel").click(function () {

                    $("#espay-buttons").hide();

                    if ($("#cancelCancel").length == 0) {
                        $("#refundContainer").load(that.cancelUrl);
                    } else {
                        $("#refundContainer").show();
                    }
                });

                $(document).on('click', '#cancelCancel', function () {
                    $("#refundContainer").hide(function () {
                        $("#espay-buttons").show();
                    });
                });

                $(document).on('click', '#submitCancel', function () {
                    if (!confirm("<?php echo $cancel_confirm; ?>")) {
                        return;
                    }

                    var status = $("select[name=order_status]").val();
                    var note = $("textarea[name=cancel_note]").val();

                    $.ajax({
                        url: that.cancelUrl,
                        type: 'post',
                        data: {order_status:status,cancel_note:note},
                        dataType: 'html',
                        beforeSend: function () {
                            $('#submitCancel').button('loading');
                            $("#cancelCancel").hide();
                        },
                        complete: function () {
                            $('#submitCancel').button('reset');
                            $("#cancelCancel").show();
                        },
                        success: function (html) {
                            $("#refundContainer").html(html);
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        }
                    });
                });
            },
            init: function () {
                this.events();
            }
        };

        var authorize = {
            authorizeUrl: "index.php?route=payment/espay/authorize&token=<?php echo $token; ?>&order_id=<?php echo $order_id; ?>",
            events: function () {
                var that = this;

                $("#btnAuthorizePass").click(function () {
                    $.ajax({
                        url: that.authorizeUrl,
                        type: 'post',
                        data: {pass:1},
                        dataType: 'html',
                        beforeSend: function () {
                            $('#btnAuthorizePass').button('loading');
                            $("#btnAuthorizeDeny").hide();
                        },
                        complete: function () {
                            $('#btnAuthorizePass').button('reset');
                            $("#btnAuthorizeDeny").show();
                        },
                        success: function (html) {
                            $("#refundContainer").html(html);
                            $("#espay-buttons").hide();
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        }
                    });
                });

                $("#btnAuthorizeDeny").click(function () {
                    $.ajax({
                        url: that.authorizeUrl,
                        type: 'post',
                        data: {deny:1},
                        dataType: 'html',
                        beforeSend: function () {
                            $('#btnAuthorizeDeny').button('loading');
                            $("#btnAuthorizePass").hide();
                        },
                        complete: function () {
                            $('#btnAuthorizeDeny').button('reset');
                            $("#btnAuthorizePass").show();
                        },
                        success: function (html) {
                            $("#refundContainer").html(html);
                            $("#espay-buttons").hide();
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        }
                    });
                });
            },
            init: function () {
                this.events();
            }
        };

        refund.init(), cancel.init(), authorize.init();
    })
    (window.jQuery);
</script>