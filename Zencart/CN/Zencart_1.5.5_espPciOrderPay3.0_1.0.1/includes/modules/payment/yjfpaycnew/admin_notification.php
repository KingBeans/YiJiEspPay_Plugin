<?php
    /**
     * yjfpay_admin_notification.php admin display component
     */

    $outputStartBlock = '';
    $outputMain       = '';
    $outputAuth       = '';
    $outputCapt       = '';
    $outputVoid       = '';
    $outputRefund     = '';
    $outputEndBlock   = '';
    $output           = '';

    $outputStartBlock .= '<td><table class="noprint">' . "\n";
    $outputStartBlock .= '<tr style="background-color : #bbbbbb; border-style : dotted;">' . "\n";

    $outputEndBlock .= '</tr>' . "\n";
    $outputEndBlock .= '</table></td>' . "\n";

    if (method_exists($this, '_doRefund') && ($history->fields['status'] == 3 || $history->fields['status'] == 4)) {
        $pay_date    = strtotime($history->fields['pay_date']);
        $pay_date_24 = strtotime(date('Y-m-d', $pay_date)) + 84600;
        $cancel      = time() < $pay_date_24 ? true : false;

        $outputRefund .= '<td><table class="noprint">' . "\n";
        $outputRefund .= '<tr style="background-color : #dddddd; border-style : dotted;">' . "\n";
        $outputRefund .= '<td class="main">' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_TITLE . '<br />' . "\n";
        $outputRefund .= sprintf(MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_SUFFIX, $history->fields['pay_total'], $history->fields['pay_date']);
        $outputRefund .= '<br/>';
        $outputRefund .= zen_draw_form('aimrefund', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doRefund', 'post', '', true) . zen_hide_session_id();;

        if ($cancel == false) {
            $outputRefund .= MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND . '<br />';
            $outputRefund .= MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_AMOUNT_TEXT . ' ' . zen_draw_input_field('refundAmount', $history->fields['pay_total'], 'length="8"') . '<br />';
        }

        //comment field
        $outputRefund .= '<br />' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('refundReason', 'soft', '50', '3', MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_DEFAULT_MESSAGE);
        //message text
        $outputRefund .= '<br />' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_SUFFIX;
        $outputRefund .= '<br />';

        if ($cancel) {
            $outputRefund .= '<input type="submit" name="cancel" value="' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_CANCEL_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_BUTTON_TEXT . '" />';
        } else {
            $outputRefund .= '<input type="submit" name="refund" value="' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_REFUND_BUTTON_TEXT . '" />';
        }

        $outputRefund .= '</form>';
        $outputRefund .= '</td></tr></table></td>' . "\n";
    }

    if (method_exists($this, '_doCapt') && $history->fields['status'] == 2) {
        $outputCapt .= '<td valign="top"><table class="noprint">' . "\n";
        $outputCapt .= '<tr style="background-color : #dddddd; border-style : dotted;">' . "\n";
        $outputCapt .= '<td class="main">' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_CAPTURE_TITLE . '<br />' . "\n";
        $outputCapt .= zen_draw_form('aimcapture', FILENAME_ORDERS, zen_get_all_get_params(array('action')) . 'action=doCapture', 'post', '', true) . zen_hide_session_id();
        $outputCapt .= MODULE_PAYMENT_YJFPAYCNEW_ENTRY_CAPTURE . '<br />';
        $outputCapt .= '<p><strong style="color:#ee0000">' . $history->fields['auth_message'] . '</strong></p>';
        //comment field
        $outputCapt .= '<br />' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_CAPTURE_TEXT_COMMENTS . '<br />' . zen_draw_textarea_field('resolveReason', 'soft', '50', '2', MODULE_PAYMENT_YJFPAYCNEW_ENTRY_CAPTURE_DEFAULT_MESSAGE);
        //message text
        $outputCapt .= '<br />' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_CAPTURE_SUFFIX;
        $outputCapt .= '<br /><input type="submit" name="authorize" value="' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_CAPTURE_BUTTON_TEXT . '" title="' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_CAPTURE_BUTTON_TEXT . '" />';
        $outputCapt .= '<input type="submit" name="cancel" value="' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_CAPTURE_BUTTON_CANCEL_TEXT . '" title="' . MODULE_PAYMENT_YJFPAYCNEW_ENTRY_CAPTURE_BUTTON_CANCEL_TEXT . '" />';
        $outputCapt .= '</form>';
        $outputCapt .= '</td></tr></table></td>' . "\n";
    }

    if ($history->fields['status'] == 1 || $history->fields['status'] == 0) {
        $output .= '<td valign="top">' . "\n";
        $output .= MODULE_PAYMENT_YJFPAYCNEW_WAIT_PAY_OR_NOT_PAY;
        $output .= '</td>' . "\n";
    }

    if ($history->fields['status'] == 5) {
        $output .= '<td valign="top">' . "\n";
        $output .= MODULE_PAYMENT_YJFPAYCNEW_CANCEL_ORDER;
        $output .= '</td>' . "\n";
    }

// prepare output based on suitable content components
#if (defined('MODULE_PAYMENT_YJFPAY_STATUS') && MODULE_PAYMENT_YJFPAY_STATUS != '') {
    $output = '<!-- BOF: aim admin transaction processing tools -->';
    $output .= $outputStartBlock;
#if (MODULE_PAYMENT_YJFPAY_AUTHORIZATION_TYPE == 'Authorize' || (isset($_GET['authcapt']) && $_GET['authcapt'] == 'on')) {
    if (method_exists($this, '_doRefund')) $output .= $outputRefund;
    if (method_exists($this, '_doCapt')) $output .= $outputCapt;
    if (method_exists($this, '_doVoid')) $output .= $outputVoid;
#} else {
#    if (method_exists($this, '_doRefund')) $output .= $outputRefund;
#    if (method_exists($this, '_doVoid')) $output .= $outputVoid;
#}
    $output .= $outputEndBlock;
    $output .= '<!-- EOF: aim admin transaction processing tools -->';
    #}

