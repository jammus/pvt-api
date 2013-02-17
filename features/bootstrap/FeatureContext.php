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
    private $BASE_URL = "http://api.pvt";

    private $postData;

    private $ch;

    private $db;

    public function __construct(array $parameters)
    {
        $this->postData = array();
        $this->configureDatabases($parameters);
    }

    private function configureDatabases(array $parameters)
    {
        $config = new \Doctrine\DBAL\Configuration();
        $this->db = \Doctrine\DBAL\DriverManager::getConnection(array(
            'dbname' => $parameters['database']['dbname'],
            'user' => $parameters['database']['username'],
            'password' => $parameters['database']['password'],
            'host' => $parameters['database']['host'],
            'driver' => $parameters['database']['driver'],
        ));
        $datasource = new \Phabric\Datasource\Doctrine(
            $this->db, 
            $parameters['Phabric']['entities']
        );
        $this->phabric = new Phabric\Phabric($datasource);
        $this->phabric->createEntitiesFromConfig($parameters['Phabric']['entities']);
        $this->phabric->addDataTransformation(
            'USERLOOKUP', function ($email, $phabric) {
                $users = $phabric->getEntity('users');
                return $users->getNamedItemId($email);
            }
        );
    }

    /**
     * @AfterScenario
     */
    public function closeConnection()
    {
        if ($this->ch) {
            curl_close($this->ch);
            $this->ch = null;
        }
    }

    /**
     * @AfterScenario 
     */
    public function clearDB($event)
    {
        $this->phabric->reset();
        $this->db->executeQuery('DELETE FROM users');
        $this->db->close();
    }

    /**
     * @Given /^I have not supplied my credentials$/
     */
    public function iHaveNotSuppliedMyCredentials()
    {
        // Do nothing
    }

    /**
     * @When /^I submit my PVT result$/
     */
    public function iSubmitMyPvtResult()
    {
        $this->submitForm('/report');
    }

    private function loadUrl($url)
    {
        $this->ch = curl_init($this->BASE_URL . $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $this->response = curl_exec($this->ch);
    }

    private function submitForm($url)
    {
        $this->ch = curl_init($this->BASE_URL . $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postData);
        $this->response = curl_exec($this->ch);
    }

    /**
     * @Then /^I should get a (\d+) response code$/
     */
    public function iShouldGetAResponseCode($expectedResponseCode)
    {
        $responseCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        assertEquals($expectedResponseCode, $responseCode);
    }

    /**
     * @When /^I attempt to create a new account$/
     */
    public function iAttemptToCreateANewAccount()
    {
        $this->submitForm('/users');
    }

    /**
     * @Given /^I should receive an authorisation token$/
     */
    public function iShouldReceiveAnAuthorisationToken()
    {
        $response = $this->jsonResponse();
        assertTrue(isset($response['access_token']), 'No auth token set in response');
        assertNotEmpty($response['access_token'], 'Auth token is empty');
    }
    
    /**
     * @Given /^I should receive a user object containing an id, "([^"]*)" and "([^"]*)"$/
     */
    public function iShouldReceiveAUserObjectContainingAnd($name, $email)
    {
        $response = $this->jsonResponse();
        $user = $response['user'];
        assertNotNull($user, 'No user in response');
        assertInternalType('integer', $user['id']);
        assertEquals($name, $user['name']);
        assertEquals($email, $user['email']);
    }

    /**
     * @Given /^I should receive a link to my profile url$/
     */
    public function iShouldReceiveALinkToMyProfileUrl()
    {
        $response = $this->jsonResponse();
        assertTrue(isset($response['user']['profile_url']), 'No profile set in response');
        assertNotEmpty($response['user']['profile_url'], 'Profile is empty');
    }

    /**
     * @Given /^I have supplied "([^"]*)", "([^"]*)" and "([^"]*)"$/
     */
    public function iHaveSupplied($email, $password, $name)
    {
        $this->postData = array(
            'email' => $email,
            'password' => $password,
            'name' => $name,
        );
    }

    /**
     * @Given /^I should see the error message "([^"]*)"$/
     */
    public function iShouldSeeTheErrorMessage($message)
    {
        $response = $this->jsonResponse();
        assertEquals($message, $response['error']['message']);
    }

    /**
     * @Given /^the following accounts exist:$/
     */
    public function theFollowingAccountsExist(TableNode $table)
    {
        $this->phabric->insertFromTable('users', $table);
    }

    private function jsonResponse()
    {
        return json_decode($this->response, true);
    }

    /**
     * @Given /^I have supplied the access token "([^"]*)"$/
     */
    public function iHaveSuppliedTheAccessToken($accessToken)
    {
        $this->postData['access_token'] = $accessToken;
    }

    /**
     * @Given /^the following access tokens exist:$/
     */
    public function theFollowAccessTokensExist(TableNode $table)
    {
        $this->phabric->insertFromTable('access_tokens', $table);
    }

    /**
     * @Given /^I have already submitted my PVT result$/
     */
    public function iHaveAlreadySubmittedMyPvtResult()
    {
        throw new PendingException();
    }

    /**
     * @Given /^I (have submitted|submit|resubmit) the following PVT result:$/
     */
    public function iHaveSubmittedTheFollowingPvtResult($tense, TableNode $table)
    {
        $reportData = $table->getHash();
        $this->postData['timestamp'] = $reportData[0]['timestamp'];
        $this->postData['response_times'] = $reportData[0]['rts'];
        $this->submitForm('/report');
    }

    /**
     * @Given /^I should be directed to the report at "([^"]*)"$/
     */
    public function iShouldBeDirectedToTheReportAt($reportPattern)
    {
        assertRegExp($reportPattern, $this->response, '"' . $this->response . '" does not match: "' . $reportPattern . '"');
    }

    /**
     * @When /^I view the PVT report$/
     */
    public function iViewThePvtReport()
    {
        $this->loadUrl($this->response);
    }

    /**
     * @Then /^I should see the report contains:$/
     */
    public function iShouldSeeTheReportContains(TableNode $table)
    {
        $expectedData = $table->getHash();
        $actualData = $this->jsonResponse();
        assertEquals($expectedData[0]['timestamp'], $actualData['timestamp']);
        assertEquals($expectedData[0]['lapses'], $actualData['lapses']);
        assertEquals($expectedData[0]['average_response_time'], $actualData['average_response_time']);
    }

    /**
     * @Given /^I have supplied "([^"]*)" and "([^"]*)"$/
     */
    public function iHaveSuppliedAnd($email, $password)
    {
        $this->postData = array(
            'email' => $email,
            'password' => $password,
        );
    }

    /**
     * @When /^I attempt to authenticate myself$/
     */
    public function iAttemptToAuthenticateMyself()
    {
        $this->submitForm('/login');
    }
}
