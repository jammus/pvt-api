Feature: Report generation
    In order to assess how well I am sleeping
    As a user
    I want to submit my PVT reports to reflect my progress

    Scenario: Improving results
        Given I have previously submitted the following results
            | date          | average response time     | errors    |
            | 04/01/2012    | 557.3ms                   | 2         |
            | 05/01/2012    | 512.2ms                   | 1         |
        When I submit the following report
            | date          | average response time     | errors    |
            | 06/01/2012    | 450.3ms                   | 0         |
        Then I should receive a postitive report
        And I should be told that I am "continuing to improve"
        And I should be told that I am "over 100ms better than my baseline"

        
