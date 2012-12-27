Feature: User authentication
    In order to associate test results with my account
    As an existing user
    I want to authenticate myself

    Scenario: Non-existant account
        Given I have supplied "incorrect-email@example.com" and "password"
        When I attempt to authenticate myself
        Then I should get a 401 response code
        And I should see the error message "Invalid email address or password. Please try again."

    Scenario: Incorrect password
        Given the following accounts exist:
            | email                 | password                                                      | name          |
            | existing@example.com  | $2a$10$Nfop43.5bbzmndx2b1cTgOK4OOIE3qnV9fbZRwifUQX91rMu.zLjW  | Existing User |
        And I have supplied "existing@example.com" and "pissword"
        When I attempt to authenticate myself
        Then I should get a 401 response code
        And I should see the error message "Invalid email address or password. Please try again."

    Scenario: Successful authentication
        Given the following accounts exist:
            | email                 | password                                                      | name          |
            | existing@example.com  | $2a$10$Nfop43.5bbzmndx2b1cTgOK4OOIE3qnV9fbZRwifUQX91rMu.zLjW  | Existing User |
        And I have supplied "existing@example.com" and "password"
        When I attempt to authenticate myself
        Then I should get a 200 response code
        And I should receive an authorisation token
        And I should receive a link to my profile url
