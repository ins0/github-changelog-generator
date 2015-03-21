<?php

require_once 'github-changelog-generator.php';

$options = getopt("u:r:t:f:");

$token = isset($options['t']) ? $options['t'] : null;
$user = isset($options['u']) ? $options['u'] : null;
$repository = isset($options['r']) ? $options['r'] : null;
$saveFilePath = isset($options['f']) ? $options['f'] : null;

if (!$user || !$repository)
{
    die('option -u [username] -r [repository] are required');
}

$generator = new GithubChangelogGenerator($token);
$generator->createChangelog($user, $repository, $saveFilePath);