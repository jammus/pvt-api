Feature: Result submission
    In order to assess how well I am sleeping
    As a user
    I want to submit my PVT results and get a personal report

    Scenario: An unauthorised user
        Given I have not supplied my credentials
        When I submit my PVT result
        Then I should get a 401 response code
        And I should see the error message "Please supply a valid access token."

    Scenario: An de-authorised user
        Given I have supplied the access token "abcdefgh"
        When I submit my PVT result
        Then I should get a 401 response code
        And I should see the error message "Please supply a valid access token."

    Scenario: An authorised user
        Given the following accounts exist:
            | email             | password  | name      |
            | test@example.com  | ********  | Test User |
        And the following access tokens exist:
            | user              | access_token  |
            | test@example.com  | abcdefgh      |
        And I have supplied the access token "abcdefgh"
        When I submit my PVT result
        Then I should get a 201 response code
        And I should receive the address of the relevant report

    Scenario: Resubmission
        Given the following accounts exist:
            | email             | password  | name      |
            | test@example.com  | ********  | Test User |
        And the following access tokens exist:
            | user              | access_token  |
            | test@example.com  | abcdefgh      |
        And I have supplied the access token "abcdefgh"
        And I have already submitted my PVT result
        Then I should get a 301 response code
        And I should receive the address of the relevant report
