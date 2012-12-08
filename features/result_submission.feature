Feature: Result submission
    In order to assess how well I am sleeping
    As a user
    I want to submit my PVT results and get a personal report

    Scenario: An unauthorised user
        Given I have not supplied my credentials
        When I submit my PVT result
        Then I should get a 401 response code

    Scenario: An authorised user
        Given I have supplied a valid access token
        When I submit my PVT result
        Then I should get a 201 response code
        And I should receive the address of the relevant report

    Scenario: Resubmission
        Given I have supplied a valid access token
        And I have already submitted my PVT result
        Then I should get a 301 response code
        And I should receive the address of the relevant report
