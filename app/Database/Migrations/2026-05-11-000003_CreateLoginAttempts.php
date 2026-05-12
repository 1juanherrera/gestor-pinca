<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoginAttempts extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
                ip_address     VARCHAR(45)  NOT NULL,
                username_attempt VARCHAR(100) NULL,
                created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT pk_login_attempts PRIMARY KEY (id),
                KEY idx_ip    (ip_address),
                KEY idx_fecha (created_at)
            ) DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci
        ");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS login_attempts");
    }
}
