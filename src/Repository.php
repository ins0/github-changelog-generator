<?php declare(strict_types=1);

namespace ins0\GitHub;

use InvalidArgumentException;
use Generator;
use RuntimeException;

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
     * The user agent string sent to GitHub.
     *
     * @var string
     */
    const USER_AGENT = 'github-changelog-generator';

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
    private $context;

    /**
     * Constructs a new instance.
     *
     * @param string $repository The username and repository
     *                           provided in the following
     *                           format: ":username/:repository".
     * @param string $token      An optional OAUTH token for
     *                           authentication.
     */
    public function __construct(string $repository, string $token = null)
    {
        if (strpos($repository, '/') === false) {
            throw new InvalidArgumentException('Invalid format. Required format is: ":username/:repository".');
        }

        $this->url = sprintf('%s/repos/%s', self::GITHUB_API_URL, $repository);
        $headers = [sprintf('User-Agent: %s', self::USER_AGENT)];

        if ($token) {
            $headers[] = sprintf('Authorization: token %s', $token);
        }

        $this->context = stream_context_create(['http' => ['header' => $headers]]);
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
    public function getReleases(array $params = []): Generator
    {
        return $this->fetch(sprintf('%s/releases?%s', $this->url, http_build_query($params)));
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
    public function getIssues(array $params = []): Generator
    {
        return $this->fetch(sprintf('%s/issues?%s', $this->url, http_build_query($params)));
    }

    /**
     * Fetch all labels for the current repository.
     *
     * @return array Always returns an array, regardless of
     *               whether or not there are any labels
     *               for the current repository.
     */
    public function getLabels(): Generator
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
    public function getAssignees(): Generator
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
    public function getIssueComments(int $number, array $params = []): Generator
    {
        return $this->fetch(sprintf('%s/issues/%d/events?%s', $this->url, $number, http_build_query($params)));
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
    public function getIssueEvents(int $number): Generator
    {
        return $this->fetch(sprintf('%s/issues/%d/events', $this->url, $number));
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
    public function getIssueLabels(int $number): Generator
    {
        return $this->fetch(sprintf('%s/issues/%d/labels', $this->url, $number));
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
    public function getMilestones(array $params = []): Generator
    {
        return $this->fetch(sprintf('%s/milestones?%s', $this->url, http_build_query($params)));
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
    private function fetch(string $url): Generator
    {
        $response = file_get_contents($url, false, $this->context);

        if (!$response) {
            throw new RuntimeException(sprintf('Cannot connect to: %s', $url));
        }

        yield from json_decode($response);

        // "It's important to form calls with Link header values instead of constructing your own URLs." - GitHub
        if ($nextPage = $this->getNextPageFromLinkHeader($http_response_header)) {
            yield from $this->fetch($nextPage);
        }
    }

    /**
     * [getNextPageFromLinkHeader description]
     *
     * @param string[] $responseHeaders The response headers of the last request sent.
     * @return string The URL of the next page or an empty string.
     */
    private function getNextPageFromLinkHeader(array $responseHeaders): string
    {
        foreach ($responseHeaders as $responseHeader) {
            if (preg_match('`<([^>]*)>; rel="next"`', $responseHeader, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }
}
