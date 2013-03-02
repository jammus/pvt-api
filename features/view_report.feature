Feature: View report
    In order to provide a user with pvt data
    As a an api consumer
    I want to access the user's reports

    Scenario: An unauthorised user
        Given the following account exists:
            | email             | password  | name      |
            | test@example.com  | ********  | Test User |
        And the following access token exists:
            | user              | access_token  |
            | test@example.com  | abcdefgh      |
        And the following report exists:
            | user              | timestamp     | errors    | response_times |
            | test@example.com  | 1234567890    | 1         | 305.6,315.34   |
        When I view the report "1234567890" for user "test@example.com"
        Then I should get a 401 response code
        And I should get a 'WWW-Authenticate' header with value 'Bearer realm="pvt"'
    
    Scenario: An authorised user
        Given the following account exists:
            | email             | password  | name      |
            | test@example.com  | ********  | Test User |
        And the following access token exists:
            | user              | access_token  |
            | test@example.com  | abcdefgh      |
        And the following report exists:
            | user              | timestamp     | errors    | response_times |
            | test@example.com  | 1234567890    | 1         | 305.6,315.34   |
        When I have supplied the access token "abcdefgh"
        And I view the report "1234567890" for user "test@example.com"
        Then I should get a 200 response code
        And I should see the report contains:
            | timestamp         | average_response_time | errors    | lapses    |
            | 1234567890        | 310.47                | 1         | 0         |

    Scenario: Incorrect user
        Given the following account exists:
            | email             | password  | name      |
            | test@example.com  | ********  | Test User |
            | other@example.com | ********  | Other User |
        And the following access token exists:
            | user              | access_token  |
            | test@example.com  | abcdefgh      |
            | other@example.com | ijklmnop      |
        And the following report exists:
            | user              | timestamp     | errors    | response_times |
            | test@example.com  | 1234567890    | 1         | 305.6,315.34   |
        When I have supplied the access token "ijklmnop"
        And I view the report "1234567890" for user "test@example.com"
        Then I should get a 401 response code
        And I should get a 'WWW-Authenticate' header with value 'Bearer realm="pvt"'
