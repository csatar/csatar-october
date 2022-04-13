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