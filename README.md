# Auto Follow Tags

Automatically subscribes users to selected tags in Flarum with admin configuration.

## Features

- Admin panel to select which tags users should be auto-subscribed to
- Automatically subscribes new users upon registration
- Command to bulk-subscribe existing users
- Support for multiple tags

## Installation
```bash
composer require bilgi42/flarum-autofollow-tags
cd extensions/bilgi42/flarum-autofollow-tags
npm install
npm run build
cd ../../..
php flarum cache:clear
php flarum extension:enable bilgi42-flarum-autofollow-tags
```

## Configuration

1. Go to Admin Panel → Extensions → Auto Follow Tags
2. Select the tags you want new users to automatically subscribe to
3. Click "Save Changes"

## Subscribing Existing Users

After configuring your tags, run this command to subscribe all existing users:
```bash
php flarum tags:subscribe-existing
```

This will subscribe all users who aren't already subscribed to your selected tags.

## License

MIT
```

**LICENSE**
```
MIT License

Copyright (c) 2025 bilgi42

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

**.gitignore**
```
/js/dist
/vendor
node_modules
composer.lock
package-lock.json
```

## Directory Structure

Your final structure should look like:
```
bilgi42/flarum-autofollow-tags/
├── js/
│   └── src/
│       └── admin/
│           └── index.js
├── locale/
│   └── en.yml
├── src/
│   └── Console/
│       └── SubscribeExistingUsersCommand.php
├── .gitignore
├── composer.json
├── extend.php
├── LICENSE
├── package.json
├── README.md
└── webpack.config.js
