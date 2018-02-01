# GitHub ChangelogGenerator
> Creates a markdown changelog for your repository, based on your repository's releases, issues
> and pull-requests. Inspired by [github-changelog-generator][ruby-generator-link] for Ruby.

## Installation
### Composer
```cli
$ composer require ins0/github-changelog-generator
```

## Usage
**Note:** *You can see an example of the output generated, [here](CHANGELOG.md).*

### PHP
```php
<?php
require_once 'vendor/autoload.php';

$token = '...';   // The token is not required, but is still recommended.
$repository = new ins0\GitHub\Repository('ins0/github-changelog-generator', $token);
$changelog = new ins0\GitHub\ChangelogGenerator($repository);

// The ChangelogGenerator::generate() method does throw
// exceptions, so remember to wrap your code in try/catch blocks.
try {
    $handle = fopen('CHANGELOG.md', 'w');

    if (!$handle) {
        throw new RuntimeException('Cannot open file for writing');
    }

    // Write markdown output to file
    fwrite($handle, $changelog->generate());
    fclose($handle);
} catch (Exception $e) {
    // handle exceptions...
}
```

If your repository uses labels other than `feature`, `bug` or `enhancement` you can customize them, like so:
```php
require_once 'vendor/autoload.php';

$labelMappings = [
    GithubChangelogGenerator::LABEL_TYPE_ADDED => ['feature', 'anotherFeatureLabel'],
    GithubChangelogGenerator::LABEL_TYPE_CHANGED => ['enhancement', 'anotherEnhancementLabel'],
    GithubChangelogGenerator::LABEL_TYPE_FIXED => ['bug', 'anotherBugLabel']
];

$changelog = new ins0\GitHub\ChangelogGenerator($repository, $labelMappings);
```

If you would like to customize the section headers, you can override the built in ones or add additional
```php
require_once 'vendor/autoload.php';

$typeHeadings = [
    GithubChangelogGenerator::LABEL_TYPE_ADDED => '### New stuff!'
];

$changelog = new ins0\GitHub\ChangelogGenerator($repository, [], $labelHeaders);
```

### CLI
```cli
$ php vendor/bin/github-changelog-generator ins0/github-changelog-generator > CHANGELOG.md
```

## CLI
This command line tool supports output redirection/pipelining, unless the `--file` option is provided.

**Required:**
- `[repository]`**:** *The url to your GitHub repository, without the domain.* **E.g.** `ins0/github-changelog-generator`

**Boolean:**
- *This tool does not use any boolean flags.*

**Optional:**
- `--token` (`-t`)**:** *Your GithHub OAUTH token.*
- `--file` (`-f`)**:** *Write output to a file.*
- `--help`**:** *Access the help menu.*

**Exit Codes:**
- `0`**:** *success*
- `1`**:** *fail*

## Testing
This library uses the [PHPUnit](https://github.com/sebastianbergmann/phpunit) test suite.
```cli
$ composer test
```

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security
If you discover any security related issues, please email rieger@racecore.de instead of using the issue tracker.

## Credits
- [Marco Rieger](https://github.com/ins0)
- [Nathan Bishop](https://github.com/nbish11)
- [Tony Murray](https://github.com/murrant)

## License
The MIT License (MIT). Please see the [LICENSE](LICENSE.md) file for more information.

[ruby-generator-link]: https://github.com/skywinder/github-changelog-generator
