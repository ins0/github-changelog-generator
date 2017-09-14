<?php

namespace ins0\GitHub;

use Exception;
use DateTime;

/**
 * Generates a changelog using your GitHub repository's releases, issues and pull-requests.
 *
 * @version 0.2.1
 * @author Marco Rieger (ins0)
 * @author Nathan Bishop (nbish11) (Contributor and Refactorer)
 * @copyright (c) 2015 Marco Rieger
 * @license MIT
 */
 class ChangelogGenerator
 {
     const LABEL_TYPE_ADDED = 'type_added';
     const LABEL_TYPE_CHANGED = 'type_changed';
     const LABEL_TYPE_DEPRECATED = 'type_deprecated';
     const LABEL_TYPE_REMOVED = 'type_removed';
     const LABEL_TYPE_FIXED = 'type_fixed';
     const LABEL_TYPE_SECURITY = 'type_security';
     const LABEL_TYPE_PR = 'type_pr';

     private $repository;
     private $currentIssues;

     private $issueLabelMapping = [
         self::LABEL_TYPE_ADDED      => ['feature'],
         self::LABEL_TYPE_CHANGED    => ['enhancement'],
         //self::LABEL_TYPE_DEPRECATED => [],
         //self::LABEL_TYPE_REMOVED    => [],
         self::LABEL_TYPE_FIXED      => ['bug'],
         //self::LABEL_TYPE_SECURITY   => []
     ];

     protected static $supportedEvents = ['merged', 'referenced', 'closed', 'reopened'];

     /**
      * Constructs a new instance.
      *
      * @param Repository $repository    [description]
      * @param array      $issueMappings [description]
      */
     public function __construct(Repository $repository, array $issueMappings = [])
     {
         $this->repository = $repository;
         $this->issueLabelMapping = array_merge($this->issueLabelMapping, $issueMappings);
     }

     /**
      * Generate changelog data.
      *
      * @return string [description]
      */
     public function generate()
     {
         $this->currentIssues = null;
         $releases = $this->collectReleaseIssues();
         $data = "# Changelog\n> This project adheres to [Semantic Versioning](http://semver.org/).\n\n";

         foreach ($releases as $release) {
             // ignore pre-releases or releases that have no issues
             if (empty($release->issues)) {
                 continue;
             }

             $publishDate = date('Y-m-d', strtotime($release->published_at));
             $data .= sprintf("## [%s](%s) - %s\n", $release->tag_name, $release->html_url, $publishDate);

             foreach ($release->issues as $type => $currentIssues) {
                 switch ($type) {
                     case self::LABEL_TYPE_FIXED:
                         $data .= sprintf("### Fixed\n");
                         break;

                     case self::LABEL_TYPE_ADDED:
                         $data .= sprintf("### Added\n");
                         break;

                     case self::LABEL_TYPE_PR:
                         $data .= sprintf("### Merged pull requests:\n");
                         break;

                     case self::LABEL_TYPE_CHANGED:
                         $data .= sprintf("### Changed:\n");
                         break;
                 }

                 foreach ($currentIssues as $issue) {
                     $data .= sprintf("- %s [\#%s](%s)\n", $issue->title, $issue->number, $issue->html_url);
                 }

                 $data .= "\n";
             }
         }

         return $data;
     }

     /**
      * Get all issues from release tags.
      *
      * @param mixed $startDate [description]
      *
      * @return array [description]
      *
      * @throws Exception
      */
     private function collectReleaseIssues($startDate = null)
     {
         $releases = $this->repository->getReleases();

         if (empty($releases)) {
             throw new Exception('No releases found for this repository');
         }

         do {
             $currentRelease = current($releases);

            if ($startDate &&
                date_diff(new DateTime($currentRelease->published_at), new DateTime($startDate))->days <= 0
            ) {
                continue;
            }

             $lastRelease = next($releases);
             $lastReleaseDate = $lastRelease ? $lastRelease->published_at : null;
             prev($releases);

             $currentRelease->issues = $this->collectIssues($lastReleaseDate);

         } while (next($releases));

         return $releases;
     }

     /**
      * Get all issues from release date.
      *
      * @param mixed $lastReleaseDate [description]
      *
      * @return array [description]
      */
     private function collectIssues($lastReleaseDate)
     {
         if (!$this->currentIssues) {
             $this->currentIssues = $this->repository->getIssues(['state' => 'closed']);
         }

         $issues = [];

         foreach ($this->currentIssues as $x => $issue) {
             if (new \DateTime($issue->closed_at) > new \DateTime($lastReleaseDate) || $lastReleaseDate == null) {
                 unset($this->currentIssues[$x]);

                 $type = $this->getTypeFromLabels($issue->labels);

                 if (!$type && isset($issue->pull_request)) {
                     $type = $this::LABEL_TYPE_PR;
                 }

                 if ($type) {
                     $events = $this->repository->getIssueEvents($issue->number);
                     $isMerged = false;

                     foreach ($events as $event) {
                         if (in_array($event->event, self::$supportedEvents) && !empty($event->commit_id)) {
                             $isMerged = true;
                             break;
                         }
                     }

                     if ($isMerged) {
                         $issues[$type][] = $issue;
                     }
                 }
             }
         }

         return $issues;
     }

     /**
      * Gets the issue type from issue labels.
      *
      * @param array $labels [description]
      *
      * @return mixed [description]
      */
     private function getTypeFromLabels(array $labels)
     {
         foreach ($labels as $label) {
             if ($foundLabel = $this->getTypeFromLabel($label->name)) {
                 return $foundLabel;
             }
         }

         return null;
     }

     /**
      * Get type from label.
      *
      * @param string $label    [description]
      * @param mixed  $haystack [description]
      *
      * @return mixed [description]
      */
     private function getTypeFromLabel($label, $haystack = null)
     {
        $haystack = !$haystack ? $this->issueLabelMapping : $haystack;

        foreach ($haystack as $key => $value) {
            $current_key = $key;

            if ((is_array($value) && $this->getTypeFromLabel($label, $value) !== false) ||
                (!is_array($value) && strcasecmp($label, $value) === 0)
            ) {
                return $current_key;
            }
        }

        return false;
     }
 }
