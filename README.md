# github-changelog-generator
inspired by [https://github.com/skywinder/github-changelog-generator](https://github.com/skywinder/github-changelog-generator) implemented in PHP.

creates a markdown changelog based on release tag versions and github issues.

## usage

### cli
``github-changelog-generator.php.bat -u [github_user] -r [repository_name] -t [github_api_token]``

the token option is optional - without the github api calls are limited and your changelog in large projects is may not fully generated

### php
    $generator = new GithubChangelogGenerator('*** github_api_token ***');
    $generator->createChangelog($user, $repository);
    
if your repository use different labels for ``features`` or ``bugs`` you need to customize the issue mapping like

    $issueLabelMapping = [
        GithubChangelogGenerator::LABEL_TYPE_BUG => [
            'otherLabelForBugs',
            'moreLabels',
        ],
        GithubChangelogGenerator::LABEL_TYPE_FEATURE => [
            'otherLabelForFeatures',
            'moreLabels',
        ],
    ];
    $generator = new GithubChangelogGenerator('*** github_api_token ***', $issueLabelMapping);
    $generator->createChangelog($user, $repository, $saveFilePath);
    
## example output

# Change Log

## [v2.0.1](https://github.com/ins0/google-measurement-php-client/releases/tag/v2.0.1) (2015-03-04T13:01:39Z)

** Merged pull requests: **

- Change answer reader script in socket.php [\#21](https://github.com/ins0/google-measurement-php-client/pull/21)

## [v2.0.0](https://github.com/ins0/google-measurement-php-client/releases/tag/v2.0.0) (2015-01-03T03:16:25Z)

** New features: **

- Sessions versus events [\#18](https://github.com/ins0/google-measurement-php-client/issues/18)

## [v1.1.0](https://github.com/ins0/google-measurement-php-client/releases/tag/v1.1.0) (2014-12-20T15:36:37Z)

## [v1.0.0](https://github.com/ins0/google-measurement-php-client/releases/tag/v1.0.0) (2014-12-20T13:41:01Z)

** Merged pull requests: **

- Additional Tracking Options [\#11](https://github.com/ins0/google-measurement-php-client/pull/11)
- Commerce Transaction [\#5](https://github.com/ins0/google-measurement-php-client/pull/5)
- Fix for issue #1 [\#2](https://github.com/ins0/google-measurement-php-client/pull/2)

** New features: **

- Client ID [\#7](https://github.com/ins0/google-measurement-php-client/issues/7)

** Fixed bugs: **

- Directory for "Racecore" should be camel-cased - currently breaking linux installations. [\#1](https://github.com/ins0/google-measurement-php-client/issues/1)

