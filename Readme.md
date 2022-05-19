# CSATÁR

You're reading the root readme file of the RMCS's CSATÁR project.

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
    
