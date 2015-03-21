# github-changelog-generator
inspired by [https://github.com/skywinder/github-changelog-generator](https://github.com/skywinder/github-changelog-generator) implemented in PHP.

creates a changelog based on release tag versions and github issues.

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