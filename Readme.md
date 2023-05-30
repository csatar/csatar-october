# CSATÁR

You're reading the root readme file of the RMCSSZ's CSATÁR project.

## Git Setup

Make sure that your git is configured (in `.git/config`) with `autocrlf = input`

## Development Environment Setup

See the [Docker](setup/dev/Readme.md) based environment setup.

## How to deploy to the http://develop.csatar.adminner.com/

### Setting up the Deploy plugin to the deployment on the local server

1. Navigate to the Settings->Deploy menu in the backend
2. Create a new server for the develop environment (this step should be done once)
    1. Add 'Develop Server' to the Server Name
    2. Add 'http://develop.csatar.adminner.com/' to the url
    3. Add the full content of the [Deployment-key](setup/dev/develop-deployment-key.txt) file to the Deployment Key
    4. Create the server
    
### Deploy to the develop server

1. Pull the develop branch from the github
2. Merge the latest changes from your branch into the develop branch
3. Push the develop branch
4. Navigate to the deploy menu and select the Develop Server
5. If the Beacon status is Unreachable press the Check Beacon button, if everything is right the status will be change to Active
6. Press the Deploy to Server button, in the popup window check the plugins/theme that are modified with your changes. Anything else remains unchecked.
7. Press the Deploy button

### PDF template updates

PDF templates for the "Renatio Dynamic PDF Plugin" are loaded to database on the first use of the plugin. 
Once they are accessible from backend on `/admin/renatio/dynamicpdf/templates/update/` and `admin/renatio/dynamicpdf/layouts/update` pages,
any changes to documents `csatar-plugins/csatar/views/pdf/teamreporttemplate.htm` and `csatar-plugins/csatar/views/pdf/layouts/teamreportlayout.htm`
are not loaded automatically and will have no effect to the downloaded PDF-s. To apply the changes, click the red "Default" button,
located in the bottom right corner of the above-mentioned backend pages.

### Adding new attributes to PermissionBasedAccess model's child classes

When a new attribute or relation is added to a "PermissionBasedAccess" model child class and the new item should be visible on the frontend, a new "MandatePermission" should be added to the permissions' matrix. In order to automatically add the newly created item to every mandate type, log in to backend, go to `admin/csatar/csatar/seederdata/synchronizepermissionsmatrix` page and click the "Synchronize" button. This synchronization will scan all child classes of "PermissionBasedAccess" model and add a "Mandate permission" entry to the permissions matrix for every existing mandate type, based on child models "fillable", "belongsTo", "belongsToMany", "hasMany", "attachOne", "hasOne", "morphTo", "morphOne", "morphMany", "morphToMany", "morphedByMany", "attachMany", "hasManyThrough", "hasOneThrough" arrays. The new "Mandate permission" entry will have all permissions set to "null". Existing entries will not be affected.

### Adding PWA support

Since we don't have the root folder of the app under git, the `manifest.json` file is in the `csatar-theme` folder. After deploying the app to the server, the `manifest.json` file should be copied to the root folder of the app and the `.htaccess` file should be updated with the following line: `RewriteRule ^manifest.json - [L]`. This will make the `manifest.json` file accessible.