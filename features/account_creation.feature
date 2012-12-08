Feature: Account creation
    In order to create a personalised sleep profile
    As a new user
    I want to create a new account

    Scenario Outline: Successful registration
        Given I have supplied "<email>", "<password>" and "<name>"
        When I attempt to create a new account
        Then I should get a 200 response code
        And I should receive an authorisation token
        And I should receive a link to my profile url
        Examples:
            | email             | password  | name      |
            | user@example.com  | 123456    | Test User | 
            | son@example.com   | asdsabn   | 손가인    | 
            | user@example.com  | 123456    | Test User | 
            | user@example.com  | 123456    | Test User | 

    Scenario: Existing account
        Given the following accounts exist:
            | email             | password  | name          |
            | user@example.com  | ********  | Existing User |
        And I have supplied "user@example.com", "123456" and "Test User"
        When I attempt to create a new account
        Then I should get a 409 response code

    Scenario Outline: Missing and invalid details
        Given I have supplied "<email>", "<password>" and "<name>"
        When I attempt to create a new account
        Then I should get a 400 response code
        And I should be told that I must supply a "valid email, password and name"
        Examples:
            | email             | password  | name      |
            | notanemailaddress | 12345     | Test User |
            | user@example.com  |           |           |
            | user@example.com  | 12345     |           |
            | user@example.com  |           | Test User |
            |                   | 12345     | Test User |
            |                   | 12345     |           |
            |                   |           | Test User |
            |                   |           |           |
