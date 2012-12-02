<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    private $BASE_URL = "http://api.pvt/report";

    private $ch;

    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    /**
    @AfterScenario
     */
    public function closeConnection()
    {
        if ($this->ch) {
            curl_close($this->ch);
            $this->ch = null;
        }
    }

    /**
     * @Given /^I have not supplied my credentials$/
     */
    public function iHaveNotSuppliedMyCredentials()
    {

    }

    /**
     * @When /^I submit my PVT result$/
     */
    public function iSubmitMyPvtResult()
    {
        $this->ch = curl_init($this->BASE_URL);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, '');
        curl_exec($this->ch);
    }

    /**
     * @Then /^I should get a (\d+) response code$/
     */
    public function iShouldGetAResponseCode($expectedResponseCode)
    {
        $responseCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        assertEquals($expectedResponseCode, $responseCode);
    }
}
