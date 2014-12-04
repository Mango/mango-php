<?php

require "mango.php";

class MangoTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {
        $this->API_KEY = getenv("MANGO_SECRET_TEST_KEY");
        $this->PUBLIC_API_KEY = getenv("MANGO_PUBLIC_TEST_KEY");

        $this->mango = new Mango\Mango(array("api_key" => $this->API_KEY));
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
        $response = \Unirest::post(Mango\Mango::BASE_URL . "/tokens/", array("Content-Type" => "application/json", "Accept" => "application/json"), json_encode($testCard), $this->PUBLIC_API_KEY, "");
        return $response->body->uid;
    }

    protected function createTokenCCV($ccv) {
        $response = \Unirest::post(Mango\Mango::BASE_URL . "/ccvs/", array("Content-Type" => "application/json", "Accept" => "application/json"), json_encode(array("ccv" => $ccv)), $this->PUBLIC_API_KEY, "");
        return $response->body->uid;
    }


    /* Customers */
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


    /* Cards */
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

    public function testGetCard(){
        $customer = $this->mango->Customers->get_list(NULL);
        $customer = $customer[0]->uid;
        $token = $this->createToken();
        $card = $this->mango->Cards->create(array(
            "customer" => $customer,
            "token" => $token
        ));
        $response = $this->mango->Cards->get($card->uid);
        $this->assertEquals($response->uid, $card->uid);
    }

    public function testListCards() {
        $cards = $this->mango->Cards->get_list(NULL);
        $this->assertTrue(is_array($cards));
    }

    public function testUpdateCard(){
        $customer = $this->mango->Customers->get_list(NULL);
        $customer = $customer[0]->uid;
        $token = $this->createToken();
        $card = $this->mango->Cards->create(array(
            "customer" => $customer,
            "token" => $token
        ));
        $tokenCCV = $this->createTokenCCV(321);
        $response = $this->mango->Cards->update($card->uid, array(
            "ccv" => $tokenCCV
        ));
        $this->assertEquals($response->uid, $card->uid);
    }

    public function testDeleteCard(){
        $customer = $this->mango->Customers->get_list(NULL);
        $customer = $customer[0]->uid;
        $token = $this->createToken();
        $card = $this->mango->Cards->create(array(
            "customer" => $customer,
            "token" => $token
        ));
        $response = $this->mango->Cards->delete($card->uid);
        $this->assertEquals($response->uid, $card->uid);
    }


    /* Charges */
    public function testCreateCharge(){
        $token = $this->createToken();
        $charge = $this->mango->Charges->create(array(
            "amount" => 1000,
            "email" => "test-php@example.org",
            "token" => $token
        ));
        $this->assertTrue(strlen($charge->uid) > 0);
    }

    public function testGetCharge(){
        $token = $this->createToken();
        $charge = $this->mango->Charges->create(array(
            "amount" => 1000,
            "email" => "test-php@example.org",
            "token" => $token
        ));
        $response = $this->mango->Charges->get($charge->uid);
        $this->assertEquals($response->uid, $charge->uid);
    }

    public function testListCharges() {
        $charges = $this->mango->Charges->get_list(NULL);
        $this->assertTrue(is_array($charges));
    }


    /* Queues */
    public function testGetQueue() {
        $token = $this->createToken();
        $charge = $this->mango->Charges->create(array(
            "amount" => 1000,
            "email" => "test-php@example.org",
            "token" => $token,
            "enqueue" => true
        ));
        $queue = $this->mango->Queues->get($charge->queue);
        $this->assertEquals($queue->resource_uid, $charge->uid);
    }

    public function testListQueue() {
        $queue = $this->mango->Queues->get_list(NULL);
        $this->assertTrue(is_array($queue));
    }

    public function testDeleteQueue() {
        $token = $this->createToken();
        $charge = $this->mango->Charges->create(array(
            "amount" => 1000,
            "email" => "test-php@example.org",
            "token" => $token,
            "enqueue" => true
        ));
        $queue = $this->mango->Queues->get_list(NULL);
        $this->mango->Queues->delete($queue[0]->uid);
        $deletedQueue = $this->mango->Queues->get($queue[0]->uid);
        $this->assertEquals($deletedQueue->status, 404);
    }

    public function testDeleteAllQueue() {
        $queue = count($this->mango->Queues->get_list(NULL));
        $this->assertTrue($queue > 0);
        $cleanQueue = $this->mango->Queues->delete_all(NULL);
        $this->assertEquals(count($cleanQueue), 0);
    }


    /* Refunds */
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

    public function testGetRefund(){
        $token = $this->createToken();
        $charge = $this->mango->Charges->create(array(
            "amount" => 1000,
            "email" => "test-php@example.org",
            "token" => $token
        ));
        $refund = $this->mango->Refunds->create(array(
            "charge" => $charge->uid
        ));
        $response = $this->mango->Refunds->get($refund->uid);
        $this->assertEquals($response->uid, $refund->uid);
    }

    public function testListRefunds() {
        $refunds = $this->mango->Refunds->get_list(NULL);
        $this->assertTrue(is_array($refunds));
    }


    /* Installments */
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
