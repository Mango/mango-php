<?php

namespace Mango;

require_once "vendor/mashape/unirest-php/lib/Unirest.php";
require_once "errors.php";

class Client {

    const BASE_URL = "https://api.getmango.com/v1";

    public function request($method, $url, $api_key, $data = NULL, $headers= array("Content-Type" => "application/json", "Accept" => "application/json") ) {
        try {
            if($method == "GET") {
                $response = \Unirest::get(Client::BASE_URL . $url, $headers, $data, $api_key, "");
            } else if($method == "POST") {
                $response = \Unirest::post(Client::BASE_URL . $url, $headers, json_encode($data), $api_key, "");
            } else if($method == "PATCH") {
                $response = \Unirest::patch(Client::BASE_URL . $url, $headers, json_encode($data), $api_key, "");
            } else if($method == "DELETE") {
                $response = \Unirest::delete(Client::BASE_URL . $url, $headers, NULL, $api_key, "");
            }
        } catch (\Exception $e) {
            throw new UnableToConnect();
        }

        if ($response->code >= 200 && $response->code <= 206) {
            if ($method == "DELETE") {
                return $response->code == 204 || $response->code == 200;
            }
            return $response->body;
        }

        if ($response->code == 400) {
            $code = 0;
            $message = "";
            try {
                $error = (array)$response->body->errors[0];
                $code = key($error);
                $message = current($error);
            } catch (\Exception $e) {
                throw new UnhandledError($response->body_raw, $response->code);
            }
            throw new InputValidationError($message, $code);
        }
        if ($response->code == 401) {
            throw new AuthenticationError();
        }
        if ($response->code == 404) {
            throw new NotFound();
        }
        if ($response->code == 403) {
            throw new InvalidApiKey();
        }
        if ($response->code == 405) {
            throw new MethodNotAllowed();
        }

        throw new UnhandledError($response->body_raw, $response->code);
    }
}
?>

