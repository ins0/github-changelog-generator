<?php

class GithubChangelogGenerator
{
    private $token;
    private $fileName = 'CHANGELOG.md';

    private $currentIssues;

    const LABEL_TYPE_BUG = 'type_bug';
    const LABEL_TYPE_FEATURE = 'type_feature';
    const LABEL_TYPE_PR = 'type_pr';


    private $issueLabelMapping = [
        self::LABEL_TYPE_BUG => [
            'bug'
        ],
        self::LABEL_TYPE_FEATURE => [
            'enhancement'
        ],
    ];

    public function __construct($token = null, $issueMapping = null)
    {
        if ($this->issueLabelMapping) {
            $this->issueLabelMapping = $issueMapping;
        }

        $this->token = $token;
    }

    public function createChangelog($user, $repository, $savePath = null)
    {
        $savePath = !$savePath ? dirname(__FILE__) . '/' . $this->fileName : null;
        $releases = $this->collectReleaseIssues($user, $repository);

        $file = fopen($savePath, 'w');
        fwrite($file, '# Change Log' . "\n\r");
        foreach($releases as $release)
        {
            fwrite($file, sprintf('## [%s](%s) (%s)' . "\r\n\r\n", $release->tag_name, $release->html_url, $release->published_at));
            $this->writeReleaseIssues($file, $release->issues);
        }
    }

    private function writeReleaseIssues($fileStream, $issues)
    {
        foreach ($issues as $type => $currentIssues)
        {
            switch ($type)
            {
                case $this::LABEL_TYPE_BUG: fwrite($fileStream, '** Fixed bugs: **' . "\r\n\r\n"); break;
                case $this::LABEL_TYPE_FEATURE: fwrite($fileStream, '** New features: **' . "\r\n\r\n"); break;
                case $this::LABEL_TYPE_PR: fwrite($fileStream, '** Merged pull requests: **' . "\r\n\r\n"); break;
            }

            foreach ($currentIssues as $issue) {
                fwrite($fileStream, sprintf('- %s [\#%s](%s)' . "\r\n", $issue->title, $issue->number, $issue->html_url));
            }

            fwrite($fileStream, "\r\n");
        }
    }

    private function collectReleaseIssues($user, $repository, $startDate = null)
    {
        $releases = $this->callGitHubApi(sprintf('repos/%s/%s/releases', $user, $repository));
        $data = [];

        do
        {
            $currentRelease = current($releases);

            if ($startDate && date_diff(new \DateTime($currentRelease->published_at), new \DateTime($startDate))->days <= 0) {
                continue;
            }

            $lastRelease = next($releases);
            $lastReleaseDate = $lastRelease ? $lastRelease->published_at : null;
            prev($releases);

            $currentRelease->issues = $this->collectIssues($currentRelease, $lastReleaseDate, $user, $repository);
            $data[] = $currentRelease;

        }while(next($releases));

        return $data;
    }

    private function collectIssues($currentRelease, $lastReleaseDate, $user, $repository)
    {
        if (!$this->currentIssues) {
            $this->currentIssues = $this->callGitHubApi(sprintf('repos/%s/%s/issues', $user, $repository), [
                'state' => 'closed'
            ]);
        }

        $issues = [];
        foreach ($this->currentIssues as $x => $issue)
        {
            if (new \DateTime($issue->closed_at) > new \DateTime($lastReleaseDate) || $lastReleaseDate == null)
            {
                unset($this->currentIssues[$x]);

                $events = $this->callGitHubApi(sprintf('repos/%s/%s/issues/%s/events', $user, $repository, $issue->number));
                $isMerged = false;

                foreach ($events as $event) {
                    if(($event->event == 'merged' || $event->event == 'referenced') && !empty($event->commit_id)) {
                        $isMerged = true;
                        break;
                    }
                }

                if (!isset($issue->pull_request)) {
                    $type = $this->getTypeFromLabels($issue->labels);
                } else {
                    $type = $this::LABEL_TYPE_PR;
                }

                if ($type && $isMerged) {
                    $issues[$type][] = $issue;
                }
            }
        }

        return $issues;
    }

    private function getTypeFromLabels($labels)
    {
        $type = null;
        foreach ($labels as $label)
        {
            if($foundLabel = $this->getTypeFromLabel($label->name)) {
                return $foundLabel;
            }
        }

        return null;
    }

    private function getTypeFromLabel($label, $haystack = null)
    {
        $haystack = !$haystack ? $this->issueLabelMapping : $haystack;
        foreach($haystack as $key => $value) {
            $current_key = $key;
            if($label === $value OR (is_array($value) && $this->getTypeFromLabel($label, $value) !== false)) {
                return $current_key;
            }
        }
        return false;
    }

    private function callGitHubApi($call, $params = [], $page = 1)
    {
        $params = array_merge(
            [
                'access_token' => $this->token,
                'page' => $page
            ],
            $params
        );

        $options  = [
            'http' => [
                'user_agent' => 'github-changelog-generator'
            ]
        ];

        $url = sprintf('https://api.github.com/%s?%s', $call, http_build_query($params));
        $context  = stream_context_create($options);
        $response = file_get_contents($url, null, $context);
        return $response ? json_decode($response) : null;
    }
}


$options = getopt("u:r:t:f:");

$token = isset($options['t']) ? $options['t'] : null;
$user = isset($options['u']) ? $options['u'] : null;
$repository = isset($options['r']) ? $options['r'] : null;
$saveFilePath = isset($options['f']) ? $options['f'] : null;

if (!$user || !$repository)
{
    die('option -u [username] -r [repository] are required');
}

$gen = new GithubChangelogGenerator($token);
$gen->createChangelog($user, $repository, $saveFilePath);