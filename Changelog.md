# Csatar Changelog

## Unreleased

- CodeFactor issue fix: maintainability
- CS-583 - CodeFactor issue fix: reduced complexity in form builder methods
- CS-570 - Improved pagination in record list component, fixed row height in record list component
- CS-413 - Changed every "Scout systemdata" and "Knowledge repository parameter" model's base model to ModelExtended model
- CS-564 - Added weekly form plan to patrol, frontend from, list and PDF download
- CS-571 - Added association filter to the Knowledge repository models

## 1.13.0
### 2023-05-12

- CS-562 - Created ŐVáMTV frontend form, listing, PDF download, Google Calendar integration
- CS-520 - Changed the display of recordlist component filters to wrap to next line
- CS-532 - Added ajax search result popup to search input field
- CS-532 - Modified search results display, added hierarchy tree to result
- CS-532 - Created GallerySearchProvider, added gallery relation to model that has gallery
- CS-533 - Modified search results to display results starting with Scouts to Association and last results from Content page

## 1.12.0
### 2023-04-25

- CS-530 - Added xlsx import for Songs, fixed error handling on TrialSystems xlsx import, fixed missing approved_at date on Games xlsx import.
- CS-551 - Created Waiting For Approval accordions on Games, Methodologies and Songs page
- CS-551 - Modified Record List component so that multiple instances are usable without conflicts on the same page
- CS-551 - Added Delete button to Game, Methodology and Song page
- CS-551 - Fixed filterAgeGroupByAssociation methods to return query without modification when 'association_id' is not available
- CS-538 - Added functionality to update scout's troop id when scout is added to patrol, when patrol's troop is changed
- CS-563 - Added Trial System Trial Type to patrol
- CS-563 - Extended team Work Plan patrols field to include information about patrol's Trial System Trial Type
- CS-559 - Created WorkPlan model for team, backend form and list
- CS-559 - Created work plans tab on frontend team page, with list and new work plan button
- CS-559 - Created frontend work plan create/edit page
- CS-559 - Added work plan PDF download
- CS-500 - Added restore deleted scouts option on backend, added inactivation on scout soft deleted
- CS-554 - Extended TrialSystem model with effective knowledge attribute and create backend import page
- CS-555 - Added google calendar tab to Association, District and Team
- CS-475 - Added option to backend scout list to (soft)delete scout with personal data
- CS-516 - Created Song type model
- CS-517 - Created Folk song type model
- CS-518 - Created Region model
- CS-519 - Created Folk song rhythm model
- CS-508 - Created Song model
- CS-561 - Fixed auto fill for the adress street scout field on frontend

## 1.11.0
### 2023-04-13

- CS-520 - Modification and optimization of grid display and the design of the filtering
- CS-491 - Added scout xlsx import/export to teams page
- CS-331 - Added `manifest.json` file and updated readme with instructions on how to add PWA support
- CS-549 - Fixed exception caused by missing getEagerLoadSettings method on AccidentLogRecord model
- CS-514 - Fixed design for TrialSystem related models
- CS-514 - Created TrialSystem model, backend and frontend form and list
- CS-514 - Created TrialSystem related models, backend forms and lists
- CS-514 - Added TrialSystems relation to Game model
- CS-509 - Created TrialSystemCategory model, seeder, backend form and list
- CS-520 - Created RecordList component
- CS-318 - Added richtext editor to FromBuilder
- CS-318 - Changed Assocation leadership_presentation field to richt text
- CS-318 - Changed District leadership_presentation, description to rich text
- CS-318 - Changed Team leadership_presentation, history and description to richtext
- CS-318 - Updated formbuilder to support subforms, created ContentPageForm component using formbuilder subform capability
- CS-318 - Changed TinyMCE modules to ContentPageForm component
- CS-492 - Fixed street field in the locations table

## 1.10.0
### 2023-03-17

- CS-327 - Made inactive dynamic fields visible as disabled if they are saved on the model
- CS-490 - Moved inactive members list to structure tab, in accordion
- CS-490 - Moved member list csv download/upload to structure tab
- CS-411 - Fixed CSS to prevent images overflowing the screen
- CS-493 - Added active members count to Association, District, Team, Troop, Patrol models
- CS-493 - Modified form builder to display custom attributes
- CS-493 - Added missing translations
- CS-489 - Added citizenship attribute to scout model
- CS-489 - Modified personal identification number validation to skip cnp validator if scout citizenship is not RO
- CS-411 - Fixed the header on the team page, so neither the image, nor the header will overflow the screen
- CS-387 - Created Games front end menu, added Create New Game and Edit Game buttons, added functionality to approve game
- CS-387 - Created Partials component to make partial sharing possible between plugins
- CS-387 - Added TagList (multiselect) widget to form builder, it is based on OctoberCMS Taglist widget, but bugs related key based use are fixed
- CS-497 - Made Number of Patrols in Age Group field required on Team Reports
- CS-377 - Created Methodology model and backend form
- CS-374 - Created MethodologyType model and backend menu
- CS-550 - Added erasmus to the sponsors

## 1.9.1
### 2023-02-27

- CS-411 - Fixed the header on the team page, so neither the image, nor the header will overflow the screen
- CS-499 - Optimized pages with forms, added eager load settings to organization base models, optimized organization base models to reduce query numbers

## 1.9.0
### 2023-02-17

- CS-498 - Optimized query for inactive mandates, modified inactive mandates accordion according to the changes
- CS-384 - Created GameType model, controller, seeder, backend list and form
- CS-371 - Created Location model, controller, seeder, backend list and form
- CS-369 - Created Duration model, controller, seeder, backend list and form
- CS-368 - Created Headcount model, controller, seeder, backend list and form
- CS-375 - Created Tool model, controller, seeder backend list and form
- CS-386 - Created AccidentRiskLevel model, seeder controller, backend list and form
- CS-385 - Created GameDevelopmentGoal model, seeder controller, backend list and form
- CS-385 - Changed migrations, renamed "order" columns to "sort_order"
- CS-363 - Fixed success/error message issue on back list bulk delete
- CS-454 - Remove the Team column from the Mandates tables
- CS-455 - Sort the teams in the menu
- CS-370 - Create Knowledge Repository and Knowledge Repository Parameters menus on backend
- CS-451 - Membership card cannot be activated if the assigned scout is inactive
- CS-460 - Added frontend scout csv import-export
- CS-452 - Mandate Types must have different names
- CS-460 - Fixed getBirthDateFromCNP method
- CS-460 - Fixed typo in getOptionsWithLabels method name
- CS-462 - Added warning to team report if any scout doesn't have registration form uploaded
- CS-436 - Added option to hide mandate types on frontend
- CS-466 - Created migrations for "Knowledge repository" models
- CS-327 - Create dynamic fields
- CS-397 - Changed accordion to show the active tag lists on the model pages
- CS-459 - Added team number to the Team reports filter

## 1.8.3
### 2023-02-15

- CS-461 - Created membership card requests backend list

## 1.8.2
### 2023-02-15

- CS-478 Refactored structure component used for the accordion and menu
- CS-478 Optimized the query for structure component
- CS-478 Cached the query result used by the structure component

## 1.8.1
### 2023-02-08

- CS-460 - Added frontend scout csv import-export
- CS-397 - Changed accordion to show the active tag lists on the model pages

## 1.8.0
### 2023-02-06

- CS-458 - Added buttons partial, buttons partial can handle passed permission value
- CS-458 - Changed buttons on every component to one of the buttons partial, expect gallery
- CS-444 - Personalized TAB design and added to the design system
- CS-412 - The form on mobile view is now displayed on a single column, instead of two
- CS-412 - Fixed overflow problem for dropdown boxes on mobile view
- CS-148 - Updated Terms and conditions page
- CS-358 - Added design for the 2FA information display, added key icon to 2FA form-control fields
- CS-441 - Added "Inactive mandates" accordion to Association, District, Team, Troop, Patrol pages
- CS-443 - Changed accordion to tabs on organizations page
- CS-424 - Hide inactive and expired mandates on Association, District, Team, Troop, Patrol pages
- CS-424 - When listing mandates on frontend at organization data, the mandate owner name is clickable
- CS-336 - Extended Personal Identification Number validation
- CS-421 - Member card data import
- CS-417 - Order team on district page by team number
- CS-429 - Hide associations without teams from menu
- CS-432 - Added "is_approved" field for Scout
- CS-430 - Added option and menu to list all team reports in association regardless of team id
- CS-418 - Create member card model
- CS-356 - Added tags list to the patrol and troop forms

## 1.7.3
### 2023-01-26

- CS-456 - Added "Refresh" and "Delete" buttons to team reports, to allow refresh/delete when team report is not submitted
- CS-449 - Modified permission matrix syncronization to create own permission type only for scout model and mandate
- CS-449 - fixed missing status bug on Patrol save

## 1.7.2
### 2023-01-18

- CS-313 - Added Cancel button to the edit content form on the ogragization unit forms

## 1.7.1
### 2023-01-18

- CS-448 - Fixed bug in team reports membership fee

## 1.7.0
### 2023-01-16

- CS-440 - Fixed missing QR code for 2FA
- CS-431 - separated inactive and active scouts on team page
- CS-415 - inactive districts and teams are hidden in structure accordions and menu
- CS-415 - inactive teams are hidden in district page
- CS-434 - Fixed SeederData, removed permissions seeding from main seeder, removed association data overwrite
- fixed Plugin.php to run boot method only after specific plugin version
- CS-423 - Changed the scrollbar design
- CS-423 - Fixed the navigation position, added scroll to the third sub-leve
- CS-271 - Make the pivot relations editable on the form
- CS-416 - Created method to automatically add newly added field s to permission matrix
- CS-399 - Added and implemented status field to team
- CS-351 - Extended backend mandates list with "mandate_model_name", "mandate_team", "scout_team" columns and added filters for every column
- CS-326 - Finalized team report pdf download
- CS-395 - Made database fields and attributes nullable
- CS-38 - Import Scouts from ECSET.
- CS-333 - Add additional chronic illnesses
- CS-349 - Remove empty boxes on forms
- CS-302 - Reorganized navigation to fit more items, modified hamburger menu so it is activated on tablet view
- CS-320 - Simplified scout's page. Removed section names from labels.
- CS-378 - Created csv import-export for permissions matrix.
- CS-393 - Don't show the QR code for those who already activated 2FA

## 1.6.0
### 2022-11-03

- CS-296 - Created accident log record list page and csv export option
- CS-297 - Implemented UserRigthsProvider user rigths provider class
- CS-297 - Created menu items for accident log
- CS-297 - Created seeder for accident log user groups
- CS-297 - Implemented rights for accident
- CS-360 - Refactored permissions system to limit mandate permissions to organization units based on organization tree
- CS-354 - Added disabled button design to the design system
- CS-354 - Added padlock symbols
- CS-302 - Updated navigation: added navigation overlay for submenus on desktop view, reorganized on tablet view, added off-canvas sidebar on mobile view, visual updates
- CS-316 - Added permissions to the gallery 

## 1.5.3
### 2022-10-14

- CS-36 - Implemented two-factor authentication 
- CS-338 - Reduced EcsetCode length to 6 characters + separator + suffix
- CS-302 - Modified navigation bar to support multi-level

## 1.5.2
### 2022-10-14

- CS-353 - Fixed permissions for relation fields without pivot data.

## 1.5.1
### 2022-10-12

- CS-340 - Hotfix - Changed preselected disabled fields to readonly
- CS-302 - Modified navigation bar to support multi-level

## 1.5.0
### 2022-09-23

- CS-315 - Implemented breadcrumb
- CS-299 - Modified the profile card image class so it can accept photos and images with every aspect ratio
- CS-299 - Added white background to the profile picture on profile page card, so it supports transparent logos/photos
- CS-299 - Corrected the appearance of the Registration form on "crate/edit user" page (file-name size, position of the delete button, image size)
- CS-299 - Modified the social network log-in card on the log-in page
- CS-247 - Modified breadcrumb design in the design system
- CS-244 - Created Personal Identification Number Validator
- CS-317 - Made scout's phone number optional if scout is above 18 year
- CS-317 - If underage, legal_representative_phone, mothers_phone or fathers_phone is required
- CS-317 - oAuth error message update for missing email in returned data
- CS-317 - If scout's data isn't accepted navigation is not allowed for logged in scout. He always gets redirected to own page
- CS-317 - Implemented search in organization names, including scout

## 1.4.0
### 2022-09-19

- CS-247 - Added breadcrumb design
- CS-299 - Changed user symbol on site header
- CS-299 - Changed text size for sponsors on main page
- CS-299 - Changed thickness of accordion bar on main page
- CS-299 - Changed header color for grid table
- CS-299 - Redesigned the contact page
- CS-299 - Corrected text appearance in calendar box
- CS-299 - Corrected checkbox design
- CS-186 - Create card design for the form builder view mode
- CS-309 - Add icon to all the buttons

## 1.3.0
### 2022-09-18

- CS-303 - Changed gallery design
- CS-229 - Replaced table to grid in form builder pivot relations
- CS-167 - Added warning message and verification option for Scout to verify personal data
- CS-68 - Added and extended Offline.SiteSearch Plugin
- CS-300 - Added permissions manage page, with bulk copy/delete option based on Association and Manadate
- CS-50 - Set edit and create buttons permission based. Set team report buttons and listing permission based
- CS-264 - Added Modify buttons to organization pages
- CS-270 - Added new Scout button to Team and Patrol page and made permission based
- CS-272 - Refactored Model structure for OraganizationBase model and added PermissionBasedAccess class
- CS-272 - Created permissions matrix admin panel
- CS-273 - Added permissions handling to form builder
- CS-257 - Added profile data tabs and profile data cards
- CS-279 - Changed color of Grid based table heading
- CS-278 - Changed the style of the explanatory text
- CS-277 - Modified the position of the flash messages, depending on the screen size
- CS-47 - Created basics of the rights system
- CS-47  - Created basics of the rights system
- CS-268 - Added new pictograms, and changed definition of existing ones
- CS-199 - Extended social login plugin to handle Scout and User model connections
- CS-193 - Applied basic permissions
- CS-177 - Create mandate types
- CS-155 - Update the organization models for the mandates
- CS-188 - Build in the polymorphic and hasMany relations in the form builder
- CS-222 - Added foreign keys
- CS-284 - Add empty option and fix validation for chronic illnesses on Scout creation
- CS-226 - Store the data from the Contact page in the backend settings
- CS-292 - Fix the server error shown on scout creation
- CS-248 - Swap the images from the front page
- CS-258 - Rename the accordions from the organization unit pages
- CS-117 - Created gallery module

## 1.2.0
### 2022-08-24

- CS-229 - Changed grid solution using cards
- CS-229 - Deleted earlier grid solution from design system
- CS-229 - Fixed cards bottom rounded corner
- CS-229 - Fixed Form field height (added min-height)
- CS-150 - Added redirect to previous page after form "save and close" and "close" and flash message on "save"
- CS-178 - Added training list
- CS-215 - fixed remove attachment button
- CS-215 - Fixed validation message positioning
- CS-215 - Fixed validation for file uploads, changes scout's 'logo' attribute to 'profile image'
- CS-215 - Fixed file remove on new record
- CS-215 - Fixed obsolete validation errors
- CS-215 - Fixed validation for relations
- CS-215 - Added validation for belongsToMany relations
- CS-223 - Fixed the 'empty value' error on team report creation
- CS-118 - Fixed the 'get property pivot of non-object' error and the team and year validation on team report creation
- CS-189 - Added name abbreviation to association model and updated seeder
- CS-172 - Warning and fix for organization name containing organization type
- CS-193 - Applied basic permissions
- CS-177 - Create mandate types
- CS-155 - Update the organization models for the mandates
- CS-188 - Build in the polimorphic and hasMany relations in the form builder
- CS-222 - Added foreign keys

## 1.1.1
### 2022-07-26

- CS-239 - Fixed the 'Visual Search' edge specific error

## 1.1.0
### 2022-07-08

- CS-124 - Added belongsToMany relations to form builder
- CS-223 - Changed the design of the tables for the Team Report
- CS-198 - Added illustrations into the main page plus further fixes
- CS-200 - Fixed text sizes, updated design system,login and reset password pages
- CS-183 - Fixed typo
- CS-156 - Fixed missing redirect after logout

## 1.0.1
### 2022-06-24

- Merged Staging to Production

## 1.0.0
### 2022-06-24

- First release to Production
