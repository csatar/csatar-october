1. Download and install Docker Desktop from: https://www.docker.com/products/docker-desktop/
2. Enable WSL 2, follow the instructions here: https://learn.microsoft.com/en-us/windows/wsl/tutorials/wsl-containers
3. From https://github.com/csatar/csatar-october.git clone csatar-october repository to your computer under WSL2, to a new directory, referred later as "project directory".
   - Important: Open the version files for `Csatar` and `KnowledgeRepository` plugins and comment the whole content of both files. This is necessary because migrations will fail if not done as described in step 10.
4. Go to the new directory and run `csatar-october/setup/dev/init.sh` (If you receive "Permission denied" error, run: `chmod +x csatar-october/setup/dev/init.sh` and try again) this will:
   - create a new docker container, called "csatar-octobercms3" by default
   - install OctoberCMS
   - map the local `/csatar-october/csatar-theme`, `csatar-october/csatar-plugins`, `/csatar-october/dev-config` directories to the corresponding directories in the container
   - create and map `octobercms-database` directory to the container
   - create and map `octobercms-files` directory to the container
   These mappings allow you to edit the files and the database on your host OS and the changes will be reflected in the container. Also, you can reinstall the container without losing the database and the files.
5. If step 4 is completed successfully, the container is running you can access the OctoberCMS site at http://localhost:8080 if you used the default port, or at the port you specified during the setup.
6. Open http://localhost:8080/backend where you can create a local admin account for yourself.
7. If you were able to create the admin account, contact hupu for the OctoberCMS license key, then go to http://localhost:8085/backend/system/updates and enter the license key. Please refer to https://octobercms.com/docker-dev-image/convert-to-licensed for the latest licensing instructions.
   - If the update process fails with "General Error. Status code: 500", follow these steps:
      1. open Docker Desktop, click on the running container, click "Files", then go to `var/www/html` and delete the `composer.lock` file.
      2. In the `composer.json` file add `"flynsarmy/oc-sociallogin-plugin": "dev-master"`, and `"google/apiclient": "^2.13"` to the `"require": { ... }` section.
      3. go back to the project directory root and run `docker exec -u root csatar-octobercms3 composer update`
   - If you run in to "file_put_contents(./composer.lock): Failed to open stream: Permission denied" error message, run `sudo chmod -R 777 {{project directory name}}`
8. The http://localhost:8080/backend/system/updates page will show a warning message: "There are missing dependencies needed for the system to run correctly." Ignore this message for now and continue with the next step.
9. If the above steps are completed successfully, go to http://localhost:8080/backend/system/updates page, click "Install Packages" button, "Sync Project button" (upper right corner).
   - If you receive errors similar to: "A syntax error was detected in /var/www/html/plugins/indikator/backend/updates/version.yaml. The string "!!! Updated for October 420+." could not be parsed as it uses an unsupported built-in tag at line 60 (near "1.6.6: !!! Updated for October 420+.") at line 43 (near "/var/www/html/vendor/october/rain/src/Parse/Yaml.php")." just locate the file in the error message and remove the "!!!", the click "Try again."
10. If the above steps are completed successfully uncomment the content of the version files for `Csatar` and `KnowledgeRepository` plugins, go to http://localhost:8080/backend/rainlab/builder then:
    - Select the Csatar from the plugin filter, click "Versions" tab, scroll down and select the latest version then click "Apply version".
    - Select the KnowledgeRepository from the plugin filter, click "Versions" tab, scroll down and select the latest version then click "Apply version".
11. If the above steps are completed successfully, go to http://localhost:8080/backend/system/settings, click "Frontend Theme" and activate the "CSATÁR" theme.
12. On the http://localhost:8080/backend/backend page, from the "Data" menu, run the following seeders: "Seeder data", "Test data", "Location Data", "Knowledge Repository Parameters". 

### Xdebug setup

In order to set up Xdebug you need to:

1. Run - `setup\dev\init-additional.cmd` (Windows) or `setup/dev/init-xdebug.sh` (MacOS, Linux), after the script is finished, restart the container
2. Download 'Xdebug helper' extension for your browser (it surely exists for Chrome and Firefox)
   - Go to the Options of the extension and at the 'IDE key' setting select PhpStorm and click 'Save'
   - Open the app in your browser (http://localhost:8085/), click on the 'Xdebug helper' icon and select 'Debug' to enable debugging for this site
   
3. In PHP strom:
   - Go to `Run` -> `Edit Configurations...`
   - Click on the `+` icon and select `PHP Remote Debug`
   - Set the `Name` to `Docker` or anything you prefer
   - Check the 'Filter debug connection by IDE key' checkbox
   - The 'IDE key (session id)' should be 'PHPSTORM'
   - Click on the `...` button next to the `Server` to set up the server configuration
   - Set the 'Name' to 'Docker' (it's IMPORTANT that the server name has to be what is set in the 'docker-compose.yml' for 'PHP_IDE_CONFIG' variable)
   - Set the `Host` to `localhost`
   - Set the `Port` to `8085`
   - Set the `Debugger` to `Xdebug`
   - Check the 'Use path mappings' checkbox
   - Set the `Absolute path on the server` to `/var/www/html/plugins/csatar` for `csatar-plugins` folder
   - Set the `Absolute path on the server` to `/var/www/html/themes/csatar` for `csatar-themes` folder
   - Click OK.
