<?php
/**
 * Breadcrumbs (crackers) Component
 *
 * Name 'Crackers' after Mayumi the Great.
 *
 * Handles:
 * - Custom Post Types: Home / Services / Primary Care
 * - Hierarchical pages: Home / About / Team
 *
 * To add a new CPT parent, edit $breadcrumb_parent_map below.
 */

//////////////////////////////////////
// CONFIG

// Map CPTs to a parent breadcrumb.
// Values can be:
//   - a page path (string), e.g. 'providers' -> the Page at /providers/
//   - another post type slug, e.g. 'service' -> uses that CPT's archive
$breadcrumb_parent_map = [
  // Add CPT-to-parent mappings here, e.g.:
  //   'taxonomy-slug' => 'cpt-slug',   // child taxonomy → parent CPT archive
  //   'provider'      => 'providers',  // CPT → /providers/ Page
];


//////////////////////////////////////
// HELPERS

if (!function_exists('crackers_crumb')) {
  function crackers_crumb($title, $url = null, $is_current = false) {
    return ['title' => $title, 'url' => $url, 'is_current' => $is_current];
  }
}

if (!function_exists('crackers_get_cpt_parent')) {
  /**
   * Resolve the parent breadcrumb for a CPT.
   * Returns a crumb array or null.
   */
  function crackers_get_cpt_parent($post_type, $map) {
    $override = $map[$post_type] ?? null;

    // 1. Override points to a real Page
    if ($override && ($page = get_page_by_path($override))) {
      return crackers_crumb(get_the_title($page->ID), get_permalink($page->ID));
    }

    // 2. Fall back to archive (mapped CPT's archive, or this CPT's own)
    $target_type   = $override ?: $post_type;
    $post_type_obj = get_post_type_object($target_type);
    $archive_link  = get_post_type_archive_link($target_type);

    if ($post_type_obj && $archive_link) {
      return crackers_crumb($post_type_obj->labels->name, $archive_link);
    }

    // 3. No archive - look for a page matching the rewrite slug
    if ($post_type_obj) {
      $rewrite_slug = $post_type_obj->rewrite['slug'] ?? $target_type;
      if ($page = get_page_by_path($rewrite_slug)) {
        return crackers_crumb(get_the_title($page->ID), get_permalink($page->ID));
      }
    }

    return null;
  }
}


//////////////////////////////////////
// BUILD TRAIL

$breadcrumbs = [crackers_crumb('Home', home_url('/'))];

if (is_singular()) {
  $post_id   = get_queried_object_id();
  $post_type = get_post_type();
  $is_cpt    = $post_type && !in_array($post_type, ['page', 'post']);

  if ($is_cpt) {
    if ($parent = crackers_get_cpt_parent($post_type, $breadcrumb_parent_map)) {
      $breadcrumbs[] = $parent;
    }
  } else {
    // Hierarchical page - walk ancestors top-down
    foreach (array_reverse(get_post_ancestors($post_id)) as $ancestor_id) {
      $breadcrumbs[] = crackers_crumb(get_the_title($ancestor_id), get_permalink($ancestor_id));
    }
  }

  // Current page (no link)
  $breadcrumbs[] = crackers_crumb(get_the_title($post_id), null, true);
}
?>

<nav class="crackers" aria-label="Breadcrumb">
  <ul>
    <?php foreach ($breadcrumbs as $crumb) : ?>
      <li<?php echo $crumb['is_current'] ? ' aria-current="page"' : ''; ?>>
        <?php if ($crumb['url']) : ?>
          <a href="<?php echo esc_url($crumb['url']); ?>"><?php echo esc_html($crumb['title']); ?></a>
        <?php else : ?>
          <span><?php echo esc_html($crumb['title']); ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
