# CartThrob Wish List: Build Process

1. From the terminal, make sure you are in the project root directory
2. Update and commit the version number bump
    * Versioning follows [semver](https://semver.org/) (&lt;MAJOR&gt;.&lt;MINOR&gt;.&lt;PATCH&gt;)
        * MAJOR version when you make incompatible API changes
        * MINOR version when you add functionality in a backwards-compatible manner
        * PATCH version when you make backwards-compatible bug fixes. A PATCH number should be excluded if it equals '0'
    * Update `CARTTHROB_WISH_LIST_NAME` constant in `system/user/addons/cartthrob_wish_list/addon.setup.php`
    * `$ git commit -am "Bumping version"`
3. Merge `develop` branch into `main` branch
    * `$ git checkout develop && git pull origin develop && git push origin develop`
    * `$ git checkout main && git pull origin main`
    * `$ git merge develop`
4. Tag the version in git
    * `$ git tag v<version-number>`
    * For example – `$ git tag v4.3` or `$ git tag v4.3.1`
5. Push all changes to GitHub
    * `$ git push --tags origin main`
6. Build CartThrob Wish List ZIP
    * `$ npm run build-addon`
    * Built CartThrob Wish List ZIP files will be added to the `./build` folder
7. [Publish the release](release.md)