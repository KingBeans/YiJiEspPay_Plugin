<?php

class ModelPaymentEspay extends Model {

    # easy status
    const STATUS_NEW         = 1;
    const STATUS_AUTHORIZING = 2;
    const STATUS_AUTHORIZED  = 3;
    const STATUS_PAYED       = 4;
    const STATUS_REFUND      = 5;
    const STATUS_CANCEL      = 6;
    const STATUS_FAIL        = 7;

    const NEED_AUTHORIZE = 1;

    # install SQL declare
//     const INSTALL_SQL = <<<EOF
//             CREATE TABLE IF NOT EXISTS `:prefixyjfpayc_history` (
//                 `id`                INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
//                 `order_id`          INT NOT NULL,
//                 `status`            TINYINT NOT NULL DEFAULT 0,

//                 `order_fee`         DECIMAL(10,2) NOT NULL,
//                 `order_date`        INT NOT NULL,
//                 `currency`          VARCHAR(10),

//                 `payment_fee`       DECIMAL(10,2) NOT NULL,
//                 `payment_date`      INT NOT NULL DEFAULT 0,

//                 `authorize`         TINYINT NOT NULL DEFAULT 0,
//                 `authorize_date`    INT NOT NULL DEFAULT 0,
//                 `authorize_result`  TINYINT NOT NULL DEFAULT 0,

//                 `refund_fee`        DECIMAL(10,2) NOT NULL DEFAULT 0,
//                 `refund_date`       INT NOT NULL  DEFAULT 0,
//                 `note`              VARCHAR(256) NULL DEFAULT ''
//             )  ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
// EOF;

    const INSTALL_SQL
                    = <<<EOF
                CREATE TABLE IF NOT EXISTS yjfpayc_history (
                    order_id            INT NOT NULL PRIMARY KEY,
                    order_no            CHAR(20) NOT NULL,

                    status              TINYINT NOT NULL DEFAULT 1
                                        COMMENT "1:Processing,2:Authorize,3:Payed,4:Refund,5:Cancel",
                    pay_total           DECIMAL(14,2),
                    pay_date            DATETIME NULL,
                    pay_status          VARCHAR(10) NOT NULL DEFAULT '',
                    pay_message         VARCHAR(256) NOT NULL DEFAULT '',

                    refund_date         DATETIME NULL,
                    refund_total        DECIMAL(10,2) NOT NULL DEFAULT 0,
                    refund_reason       VARCHAR(256) NOT NULL DEFAULT '',

                    auth_date           DATETIME NULL,
                    auth_accept         CHAR(5) NOT NULL DEFAULT 0 COMMENT "0:none,1:access,2:deny",
                    auth_reason         VARCHAR(256) NOT NULL DEFAULT '',
                    auth_message        VARCHAR(256) NOT NULL DEFAULT '',

                    add_date            DATETIME NOT NULL,
                    add_data            TEXT NOT NULL
                );
    EOF;

    const UNINSTALL_SQL = <<<EOF
        DROP TABLE IF EXISTS `prefixyjfpayc_history`;"
EOF;

    const QUERY_SQL = <<<EOF
        SELECT * FROM :prefixyjfpayc_history WHERE `order_id` = :order_id
EOF;

    const AUTHORIZED_SQL = <<<EOF
        UPDATE :prefixyjfpayc_history
            SET `status` = :status,`authorize_date` = :authorize_date,`authorize_result` = :authorize_result
        WHERE `order_id` = :order_id AND `status` = 2
EOF;

    const PAYED_SQL = <<<EOF
         UPDATE :prefixyjfpayc_history
            SET `status` = :status,`payment_date` = :payment_date,`payment_fee` = `order_fee`,`refund_fee` = 0
        WHERE `order_id` = :order_id
EOF;

    const PAYED_FAIL_SQL = <<<EOF
        UPDATE :prefixyjfpayc_history
            SET `status` = :status,`payment_date` = :payment_date,`payment_fee` = 0,`refund_fee` = 0
        WHERE `order_id` = :order_id
EOF;

    const AUTHORIZING_SQL = <<<EOF
        UPDATE :prefixyjfpayc_history
            SET `status` = :status,`payment_date` = :payment_date,`authorize` = :authorize
        WHERE `order_id` = :order_id AND `status` = 1
EOF;

    const REFUND_SQL = <<<EOF
        UPDATE :prefixyjfpayc_history
            SET `status` = :status,`payment_fee` = `payment_fee` - :refund_fee,`refund_date` = :refund_date,`refund_fee` = `refund_fee` + :refund_fee
        WHERE `order_id` = :order_id AND `status` = 4
EOF;

    const CANCEL_SQL = <<<EOF
        UPDATE :prefixyjfpayc_history
            SET `status` = :status,`payment_fee` = 0,`refund_date` = :refund_date,`refund_fee` = `order_fee`
        WHERE `order_id` = :order_id AND `status` = 4
EOF;

    public function query($orderID) {
        $result = $this->db->query(str_replace(array(
            ':prefix', ':order_id'
        ), array(DB_PREFIX, $orderID), self::QUERY_SQL));

        return $result->rows ? $result->rows[0] : false;
    }

    public function payed($orderID, $date = false) {
        if ($date && is_string($date)) {
            $date = strtotime($date);
        } else if ($date == false) {
            $date = time();
        }

        $this->db->query(str_replace(array(
            ':prefix', ':order_id', ':status', ':payment_date'
        ), array(
            DB_PREFIX, $orderID, self::STATUS_PAYED, $date
        ), self::PAYED_SQL));
    }

    public function payedFail($orderID, $date = false) {
        if ($date && is_string($date)) {
            $date = strtotime($date);
        } else if ($date == false) {
            $date = time();
        }

        $this->db->query(str_replace(array(
            ':prefix', ':order_id', ':status', ':payment_date'
        ), array(
            DB_PREFIX, $orderID, self::STATUS_FAIL, $date
        ), self::PAYED_FAIL_SQL));
    }

    public function authorizing($orderID, $date = false) {
        if ($date && is_string($date)) {
            $date = strtotime($date);
        } else if ($date == false) {
            $date = time();
        }

        $this->db->query(str_replace(array(
            ':prefix', ':order_id', ':status', ':payment_date', ':authorize'
        ), array(
            DB_PREFIX, $orderID, self::STATUS_AUTHORIZING, $date, self::NEED_AUTHORIZE
        ), self::AUTHORIZING_SQL));
    }

    public function authorized($orderID, $result) {
        $this->db->query(str_replace(array(
            ':prefix', ':order_id', ':status', ':authorize_date', ':authorize_result'
        ), array(
            DB_PREFIX, $orderID, self::STATUS_AUTHORIZED, time(), $result
        ), self::AUTHORIZED_SQL));
    }

    public function refund($orderID, $fee) {
        $this->db->query(str_replace(array(
            ':prefix', ':status', ':order_id', ':refund_fee', ':refund_date'
        ), array(
            DB_PREFIX, self::STATUS_REFUND, $orderID, $fee, time()
        ), self::REFUND_SQL));
    }

    public function cancel($orderID) {
        $this->db->query(str_replace(array(
            ':prefix', ':status', ':order_id', ':refund_date'
        ), array(
            DB_PREFIX, self::STATUS_CANCEL, $orderID, time()
        ), self::CANCEL_SQL));
    }

    public function addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false, $override = false) {
        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
    }

    public function install() {
        $this->db->query(str_replace(':prefix', DB_PREFIX, self::INSTALL_SQL));
    }

    public function uninstall() {
        $this->db->query(str_replace(':prefix', DB_PREFIX, self::UNINSTALL_SQL));
    }
}