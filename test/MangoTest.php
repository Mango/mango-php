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
        $this->client = new Mango\Client();
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
        $response = $this->client->request(
            "POST",
            "/tokens/",
            $this->PUBLIC_API_KEY,
            $testCard
        );
        return $response->uid;
    }

    protected function createTokenCCV($ccv) {
        $response = $this->client->request(
            "POST",
            "/ccvs/",
            $this->PUBLIC_API_KEY,
            array("ccv" => $ccv)
        );
        return $response->uid;
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
        $response = $this->mango->Customers->get_list();
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

    /**
     * @expectedException Mango\NotFound
     */
    public function testDeleteCustomer() {
        $customer = $this->mango->Customers->create($this->customer_data);
        $this->mango->Customers->delete($customer->uid);
        $response = $this->mango->Customers->get($customer->uid);
    }


    /* Cards */
    public function testCreateCard(){
        $customer = $this->mango->Customers->get_list();
        $customer = $customer[0]->uid;
        $token = $this->createToken();
        $card = $this->mango->Cards->create(array(
            "customer" => $customer,
            "token" => $token
        ));
        $this->assertTrue(strlen($card->uid) > 0);
    }

    public function testGetCard(){
        $customer = $this->mango->Customers->get_list();
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
        $cards = $this->mango->Cards->get_list();
        $this->assertTrue(is_array($cards));
    }

    public function testUpdateCard(){
        $customer = $this->mango->Customers->get_list();
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
        $customer = $this->mango->Customers->get_list();
        $customer = $customer[0]->uid;
        $token = $this->createToken();
        $card = $this->mango->Cards->create(array(
            "customer" => $customer,
            "token" => $token
        ));
        $response = $this->mango->Cards->delete($card->uid);
        $this->assertTrue($response);
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
        $charges = $this->mango->Charges->get_list();
        $this->assertTrue(is_array($charges));
    }


    /* Queue */
    public function testGetQueue() {
        $token = $this->createToken();
        $charge = $this->mango->Charges->create(array(
            "amount" => 1000,
            "email" => "test-php@example.org",
            "token" => $token,
            "enqueue" => true
        ));
        $queue = $this->mango->Queue->get($charge->queue);
        $this->assertEquals($queue->resource_uid, $charge->uid);
    }

    public function testListQueue() {
        $queue = $this->mango->Queue->get_list();
        $this->assertTrue(is_array($queue));
    }

    /**
     * @expectedException Mango\NotFound
     */
    public function testDeleteQueue() {
        $token = $this->createToken();
        $charge = $this->mango->Charges->create(array(
            "amount" => 1000,
            "email" => "test-php@example.org",
            "token" => $token,
            "enqueue" => true
        ));
        $queue = $this->mango->Queue->get_list();
        $this->mango->Queue->delete($queue[0]->uid);
        $deletedQueue = $this->mango->Queue->get($queue[0]->uid);
    }

    public function testDeleteAllQueue() {
        $queue = count($this->mango->Queue->get_list());
        $this->assertTrue($queue > 0);
        $cleanQueue = $this->mango->Queue->delete_all();
        $this->assertTrue($cleanQueue);
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
        $refunds = $this->mango->Refunds->get_list();
        $this->assertTrue(is_array($refunds));
    }


    /* Installments */
    public function testListInstallment() {
        $installments = $this->mango->Installments->get_list();
        $this->assertTrue(is_array($installments));
    }

    public function testListInstallmentWithOptions() {
        $installments = $this->mango->Installments->get_list(array(
            'cardtype' => 'visa'
        ));
        $this->assertTrue(is_array($installments));
        foreach ( $installments as $key => $val ) {
            $this->assertEquals($val->cardtype, 'visa');
        }
    }


    /* Promotions */
    public function testListPromotions() {
        $promotions = $this->mango->Promotions->get_list(array(
            "status" => "active"
        ));
        $this->assertTrue(is_array($promotions));
        foreach ( $promotions as $key => $val ) {
            $this->assertEquals($val->status, "active");
        }
    }

    public function testGetPromotion() {
        $promotion_list = $this->mango->Promotions->get_list(array(
            "status" => "active"
        ));
        $promotion_uid = $promotion_list[0]->uid;
        $promotion = $this->mango->Promotions->get($promotion_uid);
        $this->assertEquals($promotion->status, 'active');
        $this->assertEquals($promotion->uid, $promotion_uid);
    }

    /* Api Keys */
    /**
     * @expectedException Mango\InvalidApiKey
     */
    public function testNoApiKeys() {
        $mango = new Mango\Mango(array("api_key" => ''));
    }

    /**
     * @expectedException Mango\InvalidApiKey
     */
    public function testWrongKey() {
        $client = new Mango\Client();
        return $client->request("GET", "/queue/invalid_uid/", $this->PUBLIC_API_KEY);
    }


    /* Exceptions */
     /**
     * @expectedException Mango\InputValidationError
     */
    public function testInputValidationError() {
        $token = $this->createToken();
        $charge = $this->mango->Charges->create(array(
            "amount" => 'asd',
            "email" => "test-php@example.org",
            "token" => $token
        ));
    }

    /**
     * @expectedException Mango\AuthenticationError
     */
    public function testAuthenticationError() {
        $mango = new Mango\Mango(array("api_key" => 'asd'));
        $queue = $mango->Queue->get_list();
    }

    /**
     * @expectedException Mango\NotFound
     */
    public function testNotFound() {
        $response = $this->mango->Charges->get("fail_uid");
    }

    /**
     * @expectedException Mango\MethodNotAllowed
     */
    public function testMethodNotAllowed() {
        $this->client = new Mango\Client();
        $response = $this->client->request(
            "PATCH",
            "/tokens/",
            $this->PUBLIC_API_KEY,
            array()
        );
    }

}
?>
