Feature: SWAPI proxy endpoints
  In order to fetch Star Wars data via the proxy
  As a client
  I need the API to proxy people and films from SWAPI

  Scenario: GET /api/swapi/people/1 returns a person
    When I send a GET request to "/api/swapi/people/1"
    Then the response status code should be 200
    And the response body should contain JSON key "data"
    And the response body should contain JSON key "data.name"

  Scenario: GET /api/swapi/films/1 returns a film
    When I send a GET request to "/api/swapi/films/1"
    Then the response status code should be 200
    And the response body should contain JSON key "data"
    And the response body should contain JSON key "data.title"

  Scenario: GET /api/swapi/people/0 returns 404
    When I send a GET request to "/api/swapi/people/0"
    Then the response status code should be 404

  Scenario: GET /api/swapi/films/0 returns 404
    When I send a GET request to "/api/swapi/films/0"
    Then the response status code should be 404
