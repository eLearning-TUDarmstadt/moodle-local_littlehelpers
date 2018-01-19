<?php
namespace local_littlehelpers\task;

global $CFG;
require_once $CFG->dirroot . '/local/littlehelpers/batchmailing/lib.php';

class send_batchmails extends \core\task\scheduled_task {

    public function get_name() {
        // Shown in admin screens
        return "Versendet Mails stapelweise";
    }

    public function execute() {
        \BatchMailer::sendNextBatch();
    }

}