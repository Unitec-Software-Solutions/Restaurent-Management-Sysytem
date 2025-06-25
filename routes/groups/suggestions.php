<?php

return array (
  0 => 
  array (
    'type' => 'middleware_coverage',
    'group' => 'organization',
    'severity' => 'high',
    'message' => 'Group \'organization\' has low middleware coverage (0%)',
  ),
  1 => 
  array (
    'type' => 'middleware_coverage',
    'group' => 'branch',
    'severity' => 'high',
    'message' => 'Group \'branch\' has low middleware coverage (0%)',
  ),
  2 => 
  array (
    'type' => 'middleware_coverage',
    'group' => 'api',
    'severity' => 'high',
    'message' => 'Group \'api\' has low middleware coverage (0%)',
  ),
  3 => 
  array (
    'type' => 'naming_consistency',
    'group' => 'auth',
    'severity' => 'medium',
    'message' => 'Group \'auth\' has inconsistent route naming (0% consistent)',
    'issues' => 
    array (
      0 => 
      array (
        'route' => 'login',
        'expected_prefix' => 'auth.',
      ),
    ),
  ),
);
