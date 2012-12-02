Feature: Result submission
    In order to assess how well I am sleeping
    As a user
    I want to submit my PVT results and get a personal report

    Scenario: An authorised user
        Given I have not supplied my credentials
        When I submit my PVT result
        Then I should get a 401 response code
