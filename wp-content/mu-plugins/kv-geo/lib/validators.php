<?php
if (!defined('ABSPATH')) exit;

/**
 * "Uniqueness" guards for programmatic GEO pages.
 * Goal: do NOT index pages without enough local data to be genuinely useful.
 *
 * Scoring system (max 100):
 *  - name              10
 *  - region            10
 *  - population        5
 *  - coordinates       5
 *  - distance          5
 *  - unique_facts >= 2 10
 *  - unique_facts >= 5 10
 *  - photos >= 1       5
 *  - photos >= 3       5
 *  - delivery_terms    10
 *  - delivery_days     5
 *  - payment_terms     5
 *  - offices >= 1      10
 *  - offices >= 2      5
 *
 * Pages with score < min_unique_score (default 40) get noindex.
 */

function kv_geo_min_unique_score(): int {
  return (int) get_option('kv_geo_min_unique_score', 40);
}

function kv_geo_score_city(?array $city): int {
  if (!$city) return 0;

  $score = 0;

  // Basic identification
  if (!empty($city['name']))   $score += 10;
  if (!empty($city['region'])) $score += 10;

  // Geo data
  if (!empty($city['population']) && is_numeric($city['population'])) $score += 5;
  // Support both naming conventions (lat/lng and latitude/longitude)
  $lat = $city['lat'] ?? ($city['latitude'] ?? null);
  $lng = $city['lng'] ?? ($city['longitude'] ?? null);
  if (!empty($lat) && !empty($lng))                                   $score += 5;

  // Distance field varies across datasets (distance_km vs distance_from_moscow_km)
  $dist = $city['distance_km'] ?? ($city['distance_from_moscow_km'] ?? null);
  if ($dist !== null && $dist !== '' && is_numeric($dist))              $score += 5;

  // Unique content
  $facts = $city['unique_facts'] ?? [];
  if (is_array($facts)) {
    if (count($facts) >= 2) $score += 10;
    if (count($facts) >= 5) $score += 10;
  }

  // Visual content
  $photos = $city['photos'] ?? [];
  if (is_array($photos)) {
    if (count($photos) >= 1) $score += 5;
    if (count($photos) >= 3) $score += 5;
  }

  // Delivery & logistics
  if (!empty($city['delivery_terms'])) $score += 10;

  $dmin = $city['delivery_days_min'] ?? null;
  $dmax = $city['delivery_days_max'] ?? null;
  if (is_numeric($dmin) && is_numeric($dmax) && (int)$dmin >= 0 && (int)$dmax >= (int)$dmin) $score += 5;

  if (!empty($city['payment_terms'])) $score += 5;

  // Local partners/offices
  $offices = $city['offices'] ?? [];
  if (is_array($offices)) {
    if (count($offices) >= 1) $score += 10;
    if (count($offices) >= 2) $score += 5;
  }

  // Thin content penalty: district entries without population AND photos
  // are too thin for indexing (doorway pages risk).
  $has_pop = !empty($city['population']) && is_numeric($city['population']);
  $has_photos = is_array($photos) && count($photos) >= 1;
  if (!$has_pop && !$has_photos) {
    $score -= 15;
  }

  return max(0, min(100, $score));
}

function kv_geo_city_is_indexable(?array $city): bool {
  return kv_geo_score_city($city) >= kv_geo_min_unique_score();
}

function kv_geo_city_validation_errors(?array $city): array {
  $errs = [];
  if (!$city) return ['city_not_found'];
  if (empty($city['name']))   $errs[] = 'missing_name';
  if (empty($city['region'])) $errs[] = 'missing_region';

  $facts = $city['unique_facts'] ?? [];
  if (!is_array($facts) || count($facts) < 2)   $errs[] = 'insufficient_unique_facts';
  if (is_array($facts) && count($facts) < 5)    $errs[] = 'low_unique_facts';

  $photos = $city['photos'] ?? [];
  if (!is_array($photos) || count($photos) < 1) $errs[] = 'missing_photos';

  if (empty($city['population']))               $errs[] = 'missing_population';
  $lat = $city['lat'] ?? ($city['latitude'] ?? null);
  $lng = $city['lng'] ?? ($city['longitude'] ?? null);
  if (empty($lat) || empty($lng))               $errs[] = 'missing_coordinates';
  if (empty($city['delivery_terms']))            $errs[] = 'missing_delivery_terms';
  if (empty($city['payment_terms']))             $errs[] = 'missing_payment_terms';

  return $errs;
}
