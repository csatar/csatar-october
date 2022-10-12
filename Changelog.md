# Csatar Changelog

## Unreleased

- CS-36 - Implemented two-factor authentication 
- CS-338 - Reduced EcsetCode length to 6 characters + separator + suffix
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
