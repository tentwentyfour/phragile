# Phragile release notes

## Version 3.0.0 (2016-05-24)
* Added support for Phabricator release 2016 Week 15 or newer
* Task and transaction data for snapshots is now stored in Phragile's own format which will make future upgrades easier
* Provided a migration script for snapshots
* Fixed a bug with priority filtering

## Version 2.0.0 (2016-03-29)
* Phragile requires Phabricator >=2016 Week 8
* Support Phabricator Projects v3
* Use maniphest.search instead of maniphest.query
* Provide migration script for snapshot conversion

## Version 1.1 (2016-02-15)

* Restrict deleting snapshots to admins only
* Possibility to delete a sprint
* Setting for making story points optional
* Page to create sprints that exist on Phabricator but not on Phragile
* Possibility to export snapshot data
* Project statistics page
* Direct links to burnup or burndown charts
* Support for large projects
* Possibility to change a sprint’s project
* Switch from Conduit certificate authentication to Conduit API token authentication

## Version 1.0 (2015-08-14)

* login via Phabricator OAuth
* connect to existing sprint projects on Phabricator
* create new sprint projects directly from Phragile
* display sprint backlog with priority, storypoints, assignee and status
* sort and filter tasks by priority, storypoints, assignee and status
* display closed points per day
* generate burndown chart
* display ideal sprint progress in burndown chart
* display burnup chart with scope line
* switch between task-status and workboard column as data source for charts
* display pie chart of current sprint status
* create manual snapshots of sprint data
* automated daily snapshots of sprint data
* export live sprint data as JSON
