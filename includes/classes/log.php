<?php

if (!class_exists('foosale')) {

    require_once '_base.php';

    class foolic_log extends foolic_base {

        const META_LOG_INFO = 'log_meta';

        function foolic_log($transaction_id) {
            $this->ID = 0;

            $log = get_page_by_title($transaction_id, 'OBJECT', FOOLIC_CPT_LOG);

            if ($log) {
                $this->load_log($log);
            }
        }

        function load_log($log) {
            $data = get_post_meta($log->ID, self::META_LOG_INFO, true);

            $this->ID = $log->ID;
            $this->slug = $log->post_name;
            $this->log_title = $log->post_title;
        }
    }

}