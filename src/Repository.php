<?php declare(strict_types=1);

namespace ins0\GitHub;

use InvalidArgumentException;
use Generator;
use RuntimeException;

/**
 * A simple class for fetching data from a single GitHub repository.
 *
 * @link https://developer.github.com/v3/
 * @author Marco Rieger (ins0)
 * @author Nathan Bishop (nbish11) (Contributor and Refactorer)
 * @copyright (c) 2015 Marco Rieger
 * @license MIT
 */
class Repository
{
    /**
     * The domain to GitHub's API.
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
     * The domain and path to the user's repository.
     *
     * @var string
     */
    private $url;

    /**
     * Additional information sent along with the request.
     *
     * @var resource
     */
    private $context;

    /**
     * Constructs a new instance.
     *
     * @param string $repository The username and repository in the following format: ":username/:repository".
     * @param string|null $token The OAUTH token used to validate against GithHub's API.
     * @throws InvalidArgumentException If the path to the repository is not in the correct format.
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
     * @param mixed[] $params Supported parameters are: "page".
     * @throws RuntimeException If a connection could not be established to the URL.
     * @return Generator An iterator that gradually resolves with more data as it becomes available.
     */
    public function getReleases(array $params = []): Generator
    {
        return $this->fetch(sprintf('%s/releases?%s', $this->url, http_build_query($params)));
    }

    /**
     * Fetches all issues for the current repository.
     *
     * @param mixed[] $params Supported parameters are: "page", "milestone", "state", "assignee",
     *                        "creator", "mentioned", "labels", "sort", "direction" and "since".
     * @throws RuntimeException If a connection could not be established to the URL.
     * @return Generator An iterator that gradually resolves with more data as it becomes available.
     */
    public function getIssues(array $params = []): Generator
    {
        return $this->fetch(sprintf('%s/issues?%s', $this->url, http_build_query($params)));
    }

    /**
     * Fetch all labels for the current repository.
     *
     * @throws RuntimeException If a connection could not be established to the URL.
     * @return Generator An iterator that gradually resolves with more data as it becomes available.
     */
    public function getLabels(): Generator
    {
        return $this->fetch(sprintf('%s/labels', $this->url));
    }

    /**
     * Fetch all available assignees, to which issues may be assigned to.
     *
     * @throws RuntimeException If a connection could not be established to the URL.
     * @return Generator An iterator that gradually resolves with more data as it becomes available.
     */
    public function getAssignees(): Generator
    {
        return $this->fetch(sprintf('%s/assignees', $this->url));
    }

    /**
     * Get all comments for a specific issue.
     *
     * @param integer $number The issue number.
     * @param mixed[] $params Supported parameters are: "page", sort", "direction" and "since".
     * @throws RuntimeException If a connection could not be established to the URL.
     * @return Generator An iterator that gradually resolves with more data as it becomes available.
     */
    public function getIssueComments(int $number, array $params = []): Generator
    {
        return $this->fetch(sprintf('%s/issues/%d/events?%s', $this->url, $number, http_build_query($params)));
    }

    /**
     * Get all events for a specific issue.
     *
     * @param integer $number The issue number.
     * @throws RuntimeException If a connection could not be established to the URL.
     * @return Generator An iterator that gradually resolves with more data as it becomes available.
     */
    public function getIssueEvents(int $number): Generator
    {
        return $this->fetch(sprintf('%s/issues/%d/events', $this->url, $number));
    }

    /**
     * Get all labels attached to a specific issue.
     *
     * @param integer $number The issue number.
     * @throws RuntimeException If a connection could not be established to the URL.
     * @return Generator An iterator that gradually resolves with more data as it becomes available.
     */
    public function getIssueLabels(int $number): Generator
    {
        return $this->fetch(sprintf('%s/issues/%d/labels', $this->url, $number));
    }

    /**
     * Fetch all milestones for the current repository.
     *
     * @param mixed[] $params Supported parameters are: "page", "state", "sort" and "direction".
     * @throws RuntimeException If a connection could not be established to the URL.
     * @return Generator An iterator that gradually resolves with more data as it becomes available.
     */
    public function getMilestones(array $params = []): Generator
    {
        return $this->fetch(sprintf('%s/milestones?%s', $this->url, http_build_query($params)));
    }

    /**
     * Make a request to one of GitHub's API endpoints and retrieve the response.
     *
     * @param string $url The full URL to the API resource.
     * @throws RuntimeException If a connection could not be established to the URL.
     * @return Generator An iterator that gradually resolves with more data as it becomes available.
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
     * Determine the "next" page from GitHub's pagination headers.
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
