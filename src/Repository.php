<?php

namespace ins0\GitHub;

use InvalidArgumentException;

/**
 * A simple class for working with GitHub's "Issues" API.
 *
 * @see https://developer.github.com/v3/issues/
 *
 * @version 0.2.1
 * @author Marco Rieger (ins0)
 * @author Nathan Bishop (nbish11) (Contributor and Refactorer)
 * @copyright (c) 2015 Marco Rieger
 * @license MIT
 */
class Repository
{
    /**
     * The root URL/domain to GitHub's API.
     *
     * @var string
     */
    const GITHUB_API_URL = 'https://api.github.com';

    /**
     * Stores the full URL to the GitHub v3 API "repos" resource.
     *
     * @var string
     */
    private $url;

    /**
     * The GitHub OAUTH token to use, if provided.
     *
     * @var string
     */
    private $token;

    /**
     * Constructs a new instance.
     *
     * @param string $repository The username and repository
     *                           provided in the following
     *                           format: ":username/:repository".
     * @param string $token      An optional OAUTH token for
     *                           authentication.
     */
    public function __construct($repository, $token = null)
    {
        if (strpos($repository, '/') === false) {
            throw new InvalidArgumentException('Invalid format. Required format is: ":username/:repository".');
        }

        $this->url = sprintf('%s/repos/%s', self::GITHUB_API_URL, $repository);
        $this->token = $token;
    }

    /**
     * Fetch all releases for the current repository.
     *
     * @param array   $params Allows for advanced sorting.
     * @param integer $page   Skip to a specific page.
     *
     * @return array Always returns an array, regardless of
     *               whether or not there are any releases for
     *               the current repository.
     */
    public function getReleases(array $params = [], $page = 1)
    {
        return $this->fetch(sprintf('%s/releases', $this->url), $params, $page);
    }

    /**
     * Fetches all issues for the current repository.
     *
     * @param array   $params Allows for advanced sorting.
     * @param integer $page   Skip to a specific page.
     *
     * @return array Always returns an array, regardless of
     *               whether or not there are any issues for
     *               the current repository.
     */
    public function getIssues(array $params = [], $page = 1)
    {
        return $this->fetch(sprintf('%s/issues', $this->url), $params, $page);
    }

    /**
     * Fetch all labels for the current repository.
     *
     * @return array Always returns an array, regardless of
     *               whether or not there are any labels
     *               for the current repository.
     */
    public function getLabels()
    {
        return $this->fetch(sprintf('%s/labels', $this->url));
    }

    /**
     * Fetch all available assignees, to which issues may be
     * assigned to.
     *
     * @return array Always returns an array, regardless of
     *               whether or not there are any assignees
     *               for the current repository.
     */
    public function getAssignees()
    {
        return $this->fetch(sprintf('%s/assignees', $this->url));
    }

    /**
     * Get all comments for a specific issue.
     *
     * @param integer $number The issue number.
     * @param array   $params Allows for advanced sorting.
     *
     * @return array Always returns an array, regardless of
     *               whether or not there are any comments for
     *               the selected issue.
     */
    public function getIssueComments($number, array $params = [])
    {
        return $this->fetch(sprintf('%s/issues/%s/events', $this->url, $number), $params);
    }

    /**
     * Get all events for a specific issue.
     *
     * @param integer $number The issue number.
     *
     * @return array Always returns an array, regardless of
     *               whether or not there are any events for
     *               the selected issue.
     */
    public function getIssueEvents($number)
    {
        return $this->fetch(sprintf('%s/issues/%s/events', $this->url, $number));
    }

    /**
     * Get all labels attached to a specific issue.
     *
     * @param integer $number The issue number.
     *
     * @return array Always returns an array, regardless of
     *               whether or not there are any labels for
     *               the selected issue.
     */
    public function getIssueLabels($number)
    {
        return $this->fetch(sprintf('%s/issues/%s/labels', $this->url, $number));
    }

    /**
     * Fetch all milestones for the current repository.
     *
     * @param array   $params Allows for advanced sorting.
     * @param integer $page   Skip to a specific page.
     *
     * @return [type] Always returns an array, regardless of
     *                whether or not there are any milestones
     *                for the current repository.
     */
    public function getMilestones(array $params = [], $page = 1)
    {
        return $this->fetch(sprintf('%s/milestones', $this->url), $params, $page);
    }

    /**
     * [fetch description]
     *
     * @param string  $call   [description]
     * @param array   $params [description]
     * @param integer $page   [description]
     *
     * @return object|array [description]
     */
    private function fetch($call, array $params = [], $page = 1)
    {
        $params = array_merge($params, [
            'access_token' => $this->token,
            'page' => $page
        ]);

        $options  = [
            'http' => [
                'user_agent' => 'github-changelog-generator'
            ]
        ];

        $url = sprintf('%s?%s', $call, http_build_query($params));
        $context  = stream_context_create($options);
        $response = file_get_contents($url, null, $context);
        $response = $response ? json_decode($response) : [];

        if (count(preg_grep('#Link: <(.+?)>; rel="next"#', $http_response_header)) === 1) {
            return array_merge($response, $this->fetch($call, $params, ++$page));
        }

        return $response;
    }
}
