<?php

namespace App\Chat;


trait ChatTrait
{

    private function app_env() {
        return config("app.pac_app_env");
    }

    private function contract_app() {
        return config("app.pac_contract_app");
    }

    private function contract_server(string $value = null) {
        return config("app.pac_contract_server");
    }



}
