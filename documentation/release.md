# CartThrob Wish List: Release Process

## Publish a release on GitHub
1. [Draft a New Release](https://github.com/CartThrob/WishList/releases/new) on GitHub
2. Type the tag name from the tagging step above into the "Tag version" field
3. Repeat the tag name in the "Release title" field
4. Upload the built ZIP file by dropping it into the Dropzone uploader.
5. Publish the release

## Update the CartThrob website
1. SFTP to the CartThrob server (see 1Password)
2. Upload the CartThrob ZIP to `/var/www/cartthrob/production/software/releases/cartthrob_wish_list`
3. Sync the ["Product Downloads" directory](---)
4. Go to the [CartThrob Wish List entry](---)
5. In the "Software Downloads" field, clear the existing entry and select your uploaded ZIP file
6. In the "Version" field, change the version to match your release version
7. Save the entry
8. Update docs on website

## Update the ExpressionEngine website
1. Login to the [EE addon portal](https://expressionengine.com/forums/member/profile) (see 1Password)
2. Visit the [Edit the addon](---) page
3. In the "Latest Version" field, change the version to match your release
4. In the "Zip File" field, remove the existing file and upload your ZIP file
5. In the "Latest Update" field, update the date to match the release date
6. Save the entry

## Announcement
1. [Twitter](https://twitter.com/cartthrob)
2. [Mailchimp](https://us7.admin.mailchimp.com/campaigns/#f_list:all;t:campaigns-list)
