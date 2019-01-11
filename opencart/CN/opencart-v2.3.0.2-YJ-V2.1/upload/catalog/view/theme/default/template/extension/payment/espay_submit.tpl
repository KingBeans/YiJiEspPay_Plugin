<?php echo $header; ?>
<div class="container">
    <div class="row">
        <div id="content">
            <div class="col-sm-10 col-sm-offset-1 ">
                <form action="<?php echo $action; ?>" class="form-horizontal" method="POST">
                    <div class="page-header">
                        <h1><?php echo $text_title; ?></h1>

                        <p class="help-block">
                            <?php echo $text_summary; ?>
                        </p>
                    </div>
                    <?php if ($text_error) { ?>
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">×</span></button>
                        <?php echo $text_error; ?>
                    </div>
                    <?php } ?>
                    <div class="form-group required">
                        <label class="control-label col-sm-2" for="card-no">
                            <?php echo $entry_card_no; ?>
                        </label>

                        <div class="col-sm-5">
                            <input type="text" name="card_no" class="form-control" id="card-no" maxlength="16"
                                   value="<?php echo $card_no; ?>"
                                   placeholder="<?php echo $entry_card_no; ?>"/>
                            <?php if ($error_card_no) { ?>
                            <div class="text-danger"><?php echo $error_card_no; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-5 col-sm-offset-2">
                            <div class="alert alert-info">
                                <p style="font-size:32px;margin:0;padding:10px;color:#444;">
                                    <?php if ($card_no) { ?>
                                    <?php echo $card_no_format; ?>
                                    <?php } else { ?>
                                    xxxx xxxx xxxx xxxx
                                    <?php } ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group required">
                        <label class="control-label col-sm-2" for="expire-date">
                            <?php echo $entry_expire_date; ?>
                        </label>

                        <div class="col-sm-3">
                            <select name="expire_month" class="form-control" id="expire-date">
                                <?php foreach ($entry_expire_month as $month => $monthName) { ?>
                                <option value="<?php echo $month; ?>"
                                <?php if ($month == $expire_month) echo 'selected'; ?>>
                                <?php echo $monthName; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select name="expire_year" class="form-control">
                                <?php foreach ($entry_expire_year as $year => $yearName) { ?>
                                <option value="<?php echo $year; ?>"
                                <?php if ($year == $expire_year) echo 'selected'; ?>>
                                <?php echo $yearName; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-5 col-sm-offset-2">
                            <?php if ($error_expire_date) { ?>
                            <div class="text-danger"><?php echo $error_expire_date; ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-group required">
                        <label class="control-label col-sm-2" for="security-code">
                            <?php echo $entry_cvv; ?>
                        </label>

                        <div class="col-sm-5">
                            <input type="text" id="security-code" name="security_code" maxlength="3"
                                   class="form-control" value="<?php echo $security_code; ?>" style="width:120px;"
                                   placeholder="<?php echo $entry_cvv; ?>"/>
                            <?php if ($error_security_code) { ?>
                            <div class="text-danger">
                                <?php echo $error_security_code; ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <br/>

                    <div class="form-group">
                        <div class="col-sm-3">
                            <input type="submit" class="btn btn-primary btn-lg btn-block"
                                   value="<?php echo $entry_pay_now; ?>"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div style="display:none;">
    <p style="background:url(http://h.online-metrix.net/fp/clear.png?org_id=<?php echo $sec_org; ?>&amp;session_id=<?php echo $sec_session ?>&amp;m=1)"></p>
    <img src="http://h.online-metrix.net/fp/clear.png?org_id=<?php echo $sec_org; ?>&amp;session_id=<?php echo $sec_session ?>&amp;m=2"
         alt="">
    <object type="application/x-shockwave-flash"
            data="http://h.online-metrix.net/fp/fp.swf?org_id=<?php echo $sec_org; ?>&amp;session_id=<?php echo $sec_session ?>"
            width="1" height="1" id="thm_fp">
        <param name="movie"
               value="http://h.online-metrix.net/fp/fp.swf?org_id=<?php echo $sec_org; ?>&amp;session_id=<?php echo $sec_session ?>"/>
    </object>
    <script src="http://h.online-metrix.net/fp/check.js?org_id=<?php echo $sec_org; ?>&amp;session_id=<?php echo $sec_session ?>"
            type="text/javascript"></script>
</div><script type="text/javascript">
    (function ($) {
        var card = {
            cardNoEvent: function () {
                $("input[name=card_no]").keyup(function (char) {
                    var card = $(this).val();
                    if (card.length == 0) {
                        $("#card-info").html("XXXX XXXX XXXX XXXX");
                    } else {
                        if (card.length > 4) {
                            card = card.replace(/(\d{4})/g, "$1 ")
                        }

                        $("#card-info").html(card);
                    }
                });

                $("input[name=card_no],input[name=security_code]").keypress(function (e) {
                    var char_code = e.charCode ? e.charCode : e.keyCode;

                    return (char_code >= 48 && char_code <= 57 );
                });
            },
            payLoad: function () {
                $("#btnESPay").click(function () {
                    $(this).button('loading');
                });
            },
            init: function () {
                this.cardNoEvent(), this.payLoad();
            }
        };


        card.init();
    })
    (window.jQuery);
</script>
<?php echo $footer; ?>