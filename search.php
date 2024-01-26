<?php get_header(); ?>

<section id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="page-conatiner">
            <header class="page-header">
                <h1 class="page-title"><?php printf(__('Search Results for: %s', 'theyellowtruck'), '<span>' . get_search_query() . '</span>'); ?></h1>
            </header>
        </div>

        <?php
        // Check if there are search results
        if (have_posts()) : ?>

            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class(); ?>>
                    <div class="entry-content">
                        <?php
                        // Display the post thumbnail
                        if (has_post_thumbnail()) {
                            echo '<div class="post-thumbnail">';
                            the_post_thumbnail('thumbnail');
                            echo '</div>';
                        } ?>

                        <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php

                        // Display ACF fields (modify the field names as needed)
                        $company_location = get_field('company_location');
                        $company_website = get_field('company_website');

                        if ($company_location) {
                            echo '<p>' . esc_html($company_location) . '</p>';
                        }

                        if ($company_website) {
                            echo '<p>' . esc_html($company_website) . '</p>';
                        }
                        echo '<div class="entry-content">';
                        the_excerpt(); 
                        echo '</div>';
                        ?>
                        <a href="<?php the_permalink(); ?>">Read more</a>
                    </div>
                </article>
            <?php endwhile; ?>

            <?php the_posts_pagination(array('mid_size' => 2)); ?>

        <?php else : ?>

            <p><?php _e('No results found. Try a different search term.', 'textdomain'); ?></p>

        <?php endif;
        // Restore the global $post variable
        wp_reset_postdata(); ?>

    </main>
</section>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
