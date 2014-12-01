<?php

require "mango.php";

class MangoTest extends PHPUnit_Framework_TestCase {

    const API_KEY = "YOUR_SECRET_TEST_KEY";
    const PUBLIC_API_KEY = "YOUR_PUBLIC_TEST_KEY";

    protected function setUp() {
        $this->mango = new Mango\Mango(array("api_key" => MangoTest::API_KEY));
        $this->customer_data =  array(
            "email" => "test-php@example.org",
            "name" => "Test Customer"
        );
    }

    protected function createToken() {
        $testCard = array(
            "number" => "4507990000000010",
            "exp_month" => "12",
            "exp_year" => "2020",
            "holdername" => "John Doe",
            "type" => "visa",
            "ccv" => "123"
        );
        $response = \Unirest::post(Mango\Mango::BASE_URL . "/tokens/", array("Content-Type" => "application/json", "Accept" => "application/json"), json_encode($testCard), MangoTest::PUBLIC_API_KEY, "");
        return $response->body->uid;
    }

    public function testCreateCustomer() {
        $customer = $this->mango->Customers->create($this->customer_data);
        $this->assertEquals($customer->email, $this->customer_data["email"]);
    }

    public function testGetCustomer() {
        $customer = $this->mango->Customers->create($this->customer_data);
        $response = $this->mango->Customers->get($customer->uid);
        $this->assertEquals($response->email, $this->customer_data["email"]);
    }

    public function testListCustomer() {
        $response = $this->mango->Customers->get_list(NULL);
        $this->assertGreaterThanOrEqual(1, count($response));
    }

    public function testUpdateCustomer() {
        $customer = $this->mango->Customers->create($this->customer_data);
        $this->customer_data["name"] = "Changed Customer Name";
        $this->mango->Customers->update($customer->uid, $this->customer_data);
        $response = $this->mango->Customers->get($customer->uid);
        $this->assertEquals($response->name, $this->customer_data["name"]);
        $this->assertEquals($response->email, $this->customer_data["email"]);
    }

    public function testDeleteCustomer() {
        $customer = $this->mango->Customers->create($this->customer_data);
        $this->mango->Customers->delete($customer->uid);
        $response = $this->mango->Customers->get($customer->uid);
        $this->assertEquals($response->status, 404);
    }


    public function testListQueue() {
        $queue = $this->mango->Queues->get_list(NULL);
        $this->assertTrue(is_array($queue));
    }


    public function testCreateCard(){
        $customer = $this->mango->Customers->get_list(NULL);
        $customer = $customer[0]->uid;
        $token = $this->createToken();
        $card = $this->mango->Cards->create(array(
            "customer" => $customer,
            "token" => $token
        ));
        $this->assertTrue(strlen($card->uid) > 0);
    }

    public function testCreateCharge(){
        $token = $this->createToken();
        $charge = $this->mango->Charges->create(array(
            "amount" => 1000,
            "email" => "test-php@example.org",
            "token" => $token
        ));
        $this->assertTrue(strlen($charge->uid) > 0);
    }

    public function testCreateRefund(){
        $token = $this->createToken();
        $charge = $this->mango->Charges->create(array(
            "amount" => 1000,
            "email" => "test-php@example.org",
            "token" => $token
        ));
        $refund = $this->mango->Refunds->create(array(
            "charge" => $charge->uid
        ));
        $this->assertTrue(strlen($refund->uid) > 0);
    }

    public function testListInstallment() {
        $installment = $this->mango->Installments->get_list(array(
            "cardtype" => "amex"
        ));
        $this->assertTrue(is_array($installment));
        foreach ( $installment as $key => $val ) {
            $this->assertEquals($val->cardtype, "amex");
        }
    }

}
?>
