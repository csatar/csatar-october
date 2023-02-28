1. Download Docker Desktop from: https://www.docker.com/products/docker-desktop/
2. From https://github.com/csatar/csatar-october.git clone csatar-october repository to your computer.
3. Contact hupu for the OctoberCMS license key.
4. Open command line, in case of Windows, or terminal, in case of MacOS in the root folder of the downloaded repository, where you can see the `docker-compose.yml` file and run the following commands:
    - `docker-compose build --build-arg LICENSE_KEY=<enter license key here>` 
    - `docker-compose up -d` (During the first run of this command, Docker Desktop will ask for file sharing permissions, click: Share it.)
    - `setup\dev\init.cmd` (Windows) or `setup/dev/init.sh` (MacOS, Linux)
        - After this last command wait until you see 'DONE' in command line/terminal
5. Open http://localhost:8085 in your browser.
6. Open http://localhost:8085/admin where you can create a local admin account for yourself.

### Xdebug setup

In order to set up Xdebug you need to:

1. Run - `setup\dev\init-xdebug.cmd` (Windows) or `setup/dev/init-xdebug.sh` (MacOS, Linux)
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