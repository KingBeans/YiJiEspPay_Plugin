<?php

function card_value($name)
{
    global $card;
    return isset($card[$name]) ? $card[$name] : '';
}

function card_error($name)
{
    global $errors;
    return isset($errors[$name]) ? $errors[$name] : '';
}

$page = $_LANG['yjpaycn_page'];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title><?php echo $page['page_title']; ?></title>
    <link type="text/css" rel="stylesheet" href="yjpaycn/templates/css/style.css"/>
</head>
<body>
<div class="container">
	<br/>
    <div class="report">
        <div class="row">
            <div class="col-4">
                <h2><?php echo $page['order_total']; ?></h2>
            </div>
            <div class="col-6">
                <h2 class="price text-right">
				<?php echo sprintf('%s %.2f',$payment['yjpaycn_cfg_currency'],$order['order_amount']); ?>
				</h2>
            </div>
            <div class="col-end"></div>
        </div>
        <div class="row">
            <div class="col-2">
                <p>
                    <strong><?php echo $page['order_no']; ?></strong>
                </p>
            </div>
            <div class="col-8">
                <p class="text-right">
                    <span><?php echo $order['order_sn']; ?></span>
                </p>
            </div>
            <div class="col-end"></div>
        </div>
    </div>
    <?php if ($errMsg) { ?>
        <div class="errMsg">
            <?php echo $errMsg; ?>
        </div>
    <?php } ?>
    <div class="body">
        <form class="cform" method="POST" action="yjpaycn.php?order_sn=<?php echo $order['order_sn']; ?>&act=submit">
            <fieldset>
                <legend>
                    <?php echo $page['billing_title']; ?>
                </legend>

                <div class="form-group">
                    <div class="form-label col-2">
                        <label for="card_first_name"><?php echo $page['holder']; ?></label>
                    </div>
                    <div class="form-control col-3">
                        <input id="card_first_name" name="card[first_name]" type="text" placeholder="First name"
                               value="<?php echo card_value('first_name'); ?>" maxlength="32"/>
                    </div>
                    <div class="form-control col-3">
                        <input name="card[last_name]" type="text" placeholder="Last name"
                               value="<?php echo card_value('last_name'); ?>" maxlength="32"/>
                    </div>
                    <div class="form-control col-2">
                        <em class="error">
                            <?php echo card_error('holder'); ?>
                        </em>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <div class="form-group">
                    <div class="form-label col-2">
                        <label for="card_country"><?php echo $page['country']; ?></label>
                    </div>
                    <div class="form-control col-4">
                        <select id="card_country" name="card[country]">
                            <?php $country_code = card_value('country'); ?>
                            <?php foreach ($_LANG['yjpaycn_countries'] as $code => $country) {
                                if ($country_code == $code) { ?>
                                    <option value="<?php echo $code; ?>" selected><?php echo $country; ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $code; ?>"><?php echo $country; ?></option>
                                <?php }
                            } ?>
                        </select>
                    </div>
                    <div class="form-control col-3">
                        <em class="error">
                            <?php echo card_error('country'); ?>
                        </em>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="form-group">
                    <div class="form-label col-2">
                        <label for="card_state"><?php echo $page['state']; ?></label>
                    </div>
                    <div class="form-control col-4">
                        <input id="card_state" name="card[state]" type="text"
                               value="<?php echo card_value('state'); ?>" maxlength="32"/>
                    </div>
                    <div class="form-control col-3">
                        <em class="error">
                            <?php echo card_error('state'); ?>
                        </em>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="form-group">
                    <div class="form-label col-2">
                        <label for="card_city"><?php echo $page['city']; ?></label>
                    </div>
                    <div class="form-control col-4">
                        <input id="card_city" name="card[city]" type="text" maxlength="32"
                               value="<?php echo card_value('city'); ?>"/>
                    </div>
                    <div class="form-control col-3">
                        <em class="error">
                            <?php echo card_error('city'); ?>
                        </em>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="form-group">
                    <div class="form-label col-2">
                        <label for="card_address"><?php echo $page['address']; ?></label>
                    </div>
                    <div class="form-control col-4">
                        <input id="card_address" name="card[address]" type="text" maxlength="32"
                               value="<?php echo card_value('address'); ?>"/>
                    </div>
                    <div class="form-control col-3">
                        <em class="error">
                            <?php echo card_error('address'); ?>
                        </em>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="form-group" >
                    <div class="form-label col-2">
                        <label for="card_post_code"><?php echo $page['post_code']; ?></label>
                    </div>
                    <div class="form-control col-4">
                        <input id="card_post_code" name="card[post_code]" type="text" maxlength="6"
                               value="<?php echo card_value('post_code'); ?>"/>
                    </div>
                    <div class="form-control col-3">
                        <em class="error">
                            <?php echo card_error('post_code'); ?>
                        </em>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="form-group" >
                    <div class="form-label col-2">
                        <label for="card_email"><?php echo $page['email']; ?></label>
                    </div>
                    <div class="form-control col-4">
                        <input id="card_email" name="card[email]" type="text" maxlength="128"
                               value="<?php echo card_value('email'); ?>"/>
                    </div>
                    <div class="form-control col-3">
                        <em class="error">
                            <?php echo card_error('email'); ?>
                        </em>
                    </div>
                    <div class="clearfix"></div>
                </div>


                <div class="form-group">
                    <div class="form-label col-2">
                        <label for="card_phone_number"><?php echo $page['phone_number']; ?></label>
                    </div>
                    <div class="form-control col-4">
                        <input id="card_phone_number" name="card[phone_number]" type="text" maxlength="32"
                               value="<?php echo card_value('phone_number'); ?>"/>
                    </div>
                    <div class="form-control col-3">
                        <em class="error">
                            <?php echo card_error('phone_number'); ?>
                        </em>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </fieldset>
            <div class="form-submit">
                <input type="submit" class="btn" value="<?php echo $page['submit']; ?>"/>
            </div>
        </form>
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
</div>
    <div class="footer">
        Copyright 2016 <a href="https://www.yiji.com">www.yiji.com</a>
    </div>
</div>
<script type="text/javascript" src="/yjpaycn/templates/js/jquery.min.js"></script>
<script type="text/javascript">
    (function ($) {
        var card = {
            cardNoEvent: function () {
                $("#card_number").keyup(function (char) {
					var card = $(this).val();
					if (card.length == 0) {
						$("#card-info").html("XXXX XXXX XXXX XXXX");
					} else {
						if (card.length > 4) {
							card = card.replace(/(\d{4})/g,"$1 ")
						}
						
						$("#card-info").html(card);
					}
                });
				
				$("#card_number,#card_security_code").keypress(function(e) {
					var char_code = e.charCode ? e.charCode : e.keyCode;
					
					return (char_code >= 48 && char_code <= 57 );
				});
            },
            init: function () {
                this.cardNoEvent();
            }
        };

        card.init();
    })
    (window.jQuery);
</script>
</body>