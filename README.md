# github-changelog-generator
inspired by [https://github.com/skywinder/github-changelog-generator](https://github.com/skywinder/github-changelog-generator) implemented in PHP.

creates a markdown change log based on release tag versions and github issues.

## usage

### cli
``github-changelog-generator.php.bat -u [github_user] -r [repository_name] -t [github_api_token]``

the token option is optional - without the github api calls are limited and your change log in large projects is may not fully generated

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

view ``CHANGELOG.md`` for a full change log created from [https://github.com/zendframework/modules.zendframework.com](https://github.com/zendframework/modules.zendframework.com)

# Change Log

## [1.4.1](https://github.com/zendframework/modules.zendframework.com/releases/tag/1.4.1) (2015-03-09T11:24:57Z)

**New features:**

- Feature: added google analytics code [\#480](https://github.com/zendframework/modules.zendframework.com/pull/480)
- Enhancement: Assert ModuleController::viewAction() is not dispatched to [\#477](https://github.com/zendframework/modules.zendframework.com/pull/477)
- Enhancement: added hhvm as allow failure [\#475](https://github.com/zendframework/modules.zendframework.com/pull/475)
- Fix: Reset before pulling in changes [\#473](https://github.com/zendframework/modules.zendframework.com/pull/473)
- [WIP] Feature: Flash Messenger Error Messages [\#421](https://github.com/zendframework/modules.zendframework.com/pull/421)

**Fixed bugs:**

- Fix: Do not collect code coverage . . . for now [\#476](https://github.com/zendframework/modules.zendframework.com/pull/476)
- Fix: Do not json_decode API response to associative array [\#474](https://github.com/zendframework/modules.zendframework.com/pull/474)