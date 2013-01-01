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
        When I submit the following PVT result:
            | date                | errors    | rts                                 |
            | 1234567890          | 2         | 402.50,323.87,327.90,478.91,398.63  |
        Then I should get a 201 response code
        And I should be directed to the report at "/\/users\/(\d+)\/report\/1234567890/"

    Scenario: Resubmission
        Given the following accounts exist:
            | email             | password  | name      |
            | test@example.com  | ********  | Test User |
        And the following access tokens exist:
            | user              | access_token  |
            | test@example.com  | abcdefgh      |
        And I have supplied the access token "abcdefgh"
        And I have submitted the following PVT result:
            | date                | errors    | rts                                 |
            | 1234567890          | 2         | 402.50,323.87,327.90,478.91,398.63  |
        When I resubmit the following PVT result:
            | date                | errors    | rts                                 |
            | 1234567890          | 2         | 402.50,323.87,327.90,478.91,398.63  |
        Then I should get a 301 response code
        And I should be directed to the report at "/\/users\/(\d+)\/report\/1234567890/"

    Scenario: Viewing submitted report
        Given the following accounts exist:
            | email             | password  | name      |
            | test@example.com  | ********  | Test User |
        And the following access tokens exist:
            | user              | access_token  |
            | test@example.com  | abcdefgh      |
        And I have supplied the access token "abcdefgh"
        And I have submitted the following PVT result:
            | date                | errors    | rts                                 |
            | 1234567890          | 2         | 402.50,323.87,327.90,478.91,398.63  |
        When I view the PVT report
        Then I should see the report contains:
            | date              | average_response_time | errors    |
            | 1234567890        | 386.362               | 2         |
