Feature: uncache
  As a MODX user
  I need to be able to clear the cache of an individual resource and its parents

  Scenario: Clear the cache of a second-level resource
    Given I have a cached resource named "first1"
    And I have a cached resource named "second1" under "first1"
    And I have a cached resource named "first2"
    And I have a cached resource named "second2" under "first2"
    When I clear the cache of "second1"
    Then resource "second1" is not cached
    And resource "first1" is not cached
    And resource "second2" is cached
    And resource "first2" is cached
