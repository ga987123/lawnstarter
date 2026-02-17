Feature: Statistics endpoint
  In order to see query statistics
  As a client
  I need the statistics endpoint to return data

  Scenario: GET /api/statistics returns statistics data
    When I send a GET request to "/api/statistics"
    Then the response status code should be 200
    And the response body should contain JSON key "data"
    And the response body should contain JSON key "data.total_queries"
