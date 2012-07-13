Feature: uncacherecent
  In order to publish resources on cron
  As a MODX user
  I need to be able to clear the cache of recently published resources and their parents

  Scenario: Clear the cache of a recently published resource
    Given I have a cached resource named "first1" with pub_date "5 minutes ago"
    When I clear the cache of resources published in "10" minutes
    Then resource "first1" is not cached
    And resource "first1" is published "5 minutes ago"
