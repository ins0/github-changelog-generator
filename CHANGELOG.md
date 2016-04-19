# Changelog
> This project adheres to [Semantic Versioning](http://semver.org/).

## [v0.2.1](https://github.com/nbish11/github-changelog-generator/releases/tag/v0.2.1) - 2016-04-19
### Changed:
- `Repository::fetch` is now private.

### Removed
- Autoloading hack in `github-changelog-generator.php`.
- Unused variable in `ChangelogGenerator::getTypeFromLabels`.

### Fixed
- Missing semicolon in `github-changelog-generator.php`.

## [v0.2.0](https://github.com/nbish11/github-changelog-generator) - 2016-04-19
### Added
- [Composer](https://getcomposer.org/)/[Packagist](https://packagist.org/) support.
- [Psr-4](http://www.php-fig.org/psr/psr-4/) autoloading and namespacing.
- Separate class for handling interactions with the GitHub API (removes tight coupling in original class).
- Made the CLI tool more robust.
- [EditorConfig](http://editorconfig.org/) support.
- CONDUCT.md and CONTRIBUTING.md guides.
- [PHPUnit](https://phpunit.de/) test suite (no actual tests written yet, though).

### Changed:
- The repository now has a more robust folder structure.
- README.md reflects all new changes.
- Renamed LICENSE file to LICENSE.md for some visual tweaks. ;)
- Code Style follows [PSR-2](http://www.php-fig.org/psr/psr-2/).

### Deprecated
- The following scripts: `github-changelog-generator-cli.php` and `github-changelog-generator.php.bat`.
- The following class: `GithubChangelogGenerator` (inside the `github-changelog-generator.php` file).

### Fixed
- CHANGELOG.md now reflects the current repository.
