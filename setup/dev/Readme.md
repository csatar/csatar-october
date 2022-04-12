1. Download Docker Desktop from: https://www.docker.com/products/docker-desktop/
2. From https://github.com/csatar/csatar-october.git clone csatar-october repository to your computer.
3. Contact hupu for the OctoberCMS license key.
4. In the root folder of the downloaded repository, where you can see the `docker-compose.yml` file, run `docker-compose build --build-arg LICENSE_KEY=<enter license key here>` then `docker-compose up`, from command line, in case of Windows, or terminal in case of MacOS.
5. Open http://localhost:8085 in your browser.