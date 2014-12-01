<?php

namespace Mango;

require_once "vendor/unirest-php/lib/Unirest.php";


class Mango {

    public $api_key;
    const BASE_URL = "https://api.getmango.com/v1";

    // Constructor
    public function __construct($options) {
        $this->api_key = $options["api_key"];
        $this->Charges = new Charges($this);
        $this->Refunds = new Refunds($this);
        $this->Customers = new Customers($this);
        $this->Cards = new Cards($this);
        $this->Queues = new Queues($this);
        $this->Installments = new Installments($this);
    }

}


class Resource {

    public function __construct($mango) {
        $this->mango = $mango;
    }

    protected function request($method, $url, $data = null, $headers= array("Content-Type" => "application/json", "Accept" => "application/json") ) {
        if($method == "GET") {
            $response = \Unirest::get(Mango::BASE_URL . $url, $headers, $data, $this->mango->api_key, "");
        } else if($method == "POST") {
            $response = \Unirest::post(Mango::BASE_URL . $url, $headers, json_encode($data), $this->mango->api_key, "");
        } else if($method == "PATCH") {
            $response = \Unirest::patch(Mango::BASE_URL . $url, $headers, json_encode($data), $this->mango->api_key, "");
        } else if($method == "DELETE") {
            $response = \Unirest::delete(Mango::BASE_URL . $url, $headers, NULL, $this->mango->api_key, "");
        }

        return $response->body;
    }

}


class Charges extends Resource {

    public function get_list($options = null) {
        return $this->request("GET", "/charges/", $options);
    }

    public function get($uid) {
        return $this->request("GET", "/charges/" . $uid . "/");
    }

    public function create($options) {
        return $this->request("POST", "/charges/", $options);
    }

}


class Refunds extends Resource {

    public function get_list($options) {
        return $this->request("GET", "/refunds/", $options);
    }

    public function get($uid) {
        return $this->request("GET", "/refunds/" . $uid . "/");
    }

    public function create($options) {
        return $this->request("POST", "/refunds/", $options);
    }

}


class Customers extends Resource {

    public function get_list($options) {
        return $this->request("GET", "/customers/", $options);
    }

    public function get($uid) {
        return $this->request("GET", "/customers/" . $uid . "/");
    }

    public function create($options) {
        return $this->request("POST", "/customers/", $options);
    }

    public function update($uid, $options) {
        return $this->request("PATCH", "/customers/" . $uid . "/", $options);
    }

    public function delete($uid) {
        return $this->request("DELETE", "/customers/" . $uid . "/");
    }

}


class Cards extends Resource {

    public function get_list($options) {
        return $this->request("GET", "/cards/", $options);
    }

    public function get($uid) {
        return $this->request("GET", "/cards/" . $uid . "/");
    }

    public function create($options) {
        return $this->request("POST", "/cards/", $options);
    }

    public function update($uid) {
        return $this->request("PATCH", "/cards/" . $uid , "/");
    }

    public function delete($uid) {
        return $this->request("DELETE", "/cards/" . $uid . "/");
    }

}


class Queues extends Resource {

    public function get_list($options) {
        return $this->request("GET", "/queue/", $options);
    }

    public function get($uid) {
        return $this->request("GET", "/queue/" . $uid . "/");
    }

    public function delete($uid) {
        return $this->request("DELETE", "/queue/" . $uid . "/");
    }

    public function delete_all() {
        return $this->request("DELETE", "/queue/");
    }

}


class Installments extends Resource {

    public function get_list($options) {
        return $this->request("GET", "/installments/", $options);
    }

}

?>
