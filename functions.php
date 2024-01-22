<?php
/**
 * Betheme Child Theme
 *
 * @package Betheme Child Theme
 * @author Muffin group
 * @link https://muffingroup.com
 */

/**
 * Load Textdomain
 */

add_action('after_setup_theme', 'mfn_load_child_theme_textdomain');

function mfn_load_child_theme_textdomain(){
	load_child_theme_textdomain('betheme', get_stylesheet_directory() . '/languages');
	load_child_theme_textdomain('mfn-opts', get_stylesheet_directory() . '/languages');
}

function mfnch_enqueue_styles()
{

	if ( is_rtl() ) {
		wp_enqueue_style('mfn-rtl', get_template_directory_uri() . '/rtl.css');
	}

	wp_dequeue_style('style');
	wp_enqueue_style('style', get_stylesheet_directory_uri() .'/style.css');
 	wp_enqueue_script('main-js', get_stylesheet_directory_uri() . '/main.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'mfnch_enqueue_styles', 101);

function enqueue_slick_scripts() {
    wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', array(), null, true);
    wp_enqueue_script('slick-script', 'https://unpkg.com/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), null, true);
    wp_enqueue_style('slick-style', 'https://unpkg.com/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_style('slick-theme-style', 'https://unpkg.com/slick-carousel@1.8.1/slick/slick-theme.css');
}

add_action('wp_enqueue_scripts', 'enqueue_slick_scripts');

add_shortcode('all_jobs', '_all_jobs_');
function _all_jobs_() {

    ob_start(); ?>

    <div class="job-filters">
        <select id="job-category">
            <option value="">All Categories</option>
            <?php
            // Get a list of unique job categories from the posts
            $args = array(
                'post_type' => 'my-job',
                'posts_per_page' => -1,
            );
            $query = new WP_Query($args);
            $job_categories = array();

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $category = get_field('job_category');
                    if (!in_array($category, $job_categories)) {
                        $job_categories[] = $category;
                    }
                }
                wp_reset_postdata();

                // Display job categories in the dropdown
                foreach ($job_categories as $category) {
                    echo '<option value="' . esc_attr($category) . '">' . esc_html($category) . '</option>';
                }
            }
            ?>
        </select>
    </div>

    <!-- Container for AJAX-loaded job posts -->
    <div id="job-posts-container" class="job-container">
        <?php
        $args = array(
            'post_type' => 'my-job',
            'posts_per_page' => -1,
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $post_title = get_the_title();
                $post_url = get_permalink();
                $post_image = get_the_post_thumbnail_url($post_id, 'full');
                $post_excerpt = get_the_excerpt();
                ?>
                <div class="jobs-list">
               <div class="job-card" data-post-id="<?php echo esc_attr($post_id); ?>">
                    <div class="left-col">
                        <img src="<?php echo $post_image; ?>" alt="<?php echo $post_title; ?>">
                        <h4><?php echo $post_title; ?></h4>
                    </div>
                    <div class="info">
					<p><?php the_field('job_category'); ?></p>
					<p><?php the_field('job_location'); ?></p>
				</div>
                </div>
            </div>
                <?php
            }
        } else {
            echo 'No jobs found.';
        }


        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $post_title = get_the_title();
                $post_url = get_permalink();
                $post_image = get_the_post_thumbnail_url($post_id, 'full');
                $post_excerpt = get_the_excerpt();
                ?>
            <div class="jobs-detail">
					<!-- Popup -->
					<div id="popup-<?php echo $post_id; ?>" class="popup">
					<div class="popup-cotent">
						<div class="job-img">
							<img src="<?php echo $post_image; ?>" alt="<?php echo $post_title; ?>">
						</div>
						<div class="job-content">
							<h2><?php echo $post_title; ?></h2>
							<p><?php echo $post_excerpt; ?></p>
						</div>
						<a href="javascript:void(0)" class="close-popup">Close</a>
					</div>
				</div>
            </div>
                <?php
            }
        } else {
            echo 'No jobs found.';
        }
        wp_reset_postdata();
        ?>
    </div>

    <script>
		document.addEventListener('DOMContentLoaded', function () {
			var popups = document.querySelectorAll('.popup');
			var jobCards = document.querySelectorAll('.job-card');
			var closePopups = document.querySelectorAll('.close-popup');

			popups.forEach(function (popup) {
				popup.style.display = 'none';
			});

			jobCards.forEach(function (jobCard) {
				jobCard.addEventListener('click', function () {
					var postId = jobCard.getAttribute('data-post-id');
					var popup = document.getElementById('popup-' + postId);

					popup.style.display = 'block';
					popup.classList.add("active");
				});
			});

			closePopups.forEach(function (closePopup) {
				closePopup.addEventListener('click', function () {
					var popup = closePopup.closest('.popup');

					popup.style.display = 'none';
					popup.classList.remove('active');
				});

            const jobCategorySelect = document.getElementById('job-category');
            const jobPostsContainer = document.getElementById('job-posts-container');

            jobCategorySelect.addEventListener('change', function () {
                const categoryValue = jobCategorySelect.value;
                filterJobs(categoryValue);
            });

            function filterJobs(categoryValue) {
                // Prepare the AJAX request to retrieve filtered job posts
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '<?php echo admin_url('admin-ajax.php'); ?>?action=filter_jobs&category=' + categoryValue);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        jobPostsContainer.innerHTML = xhr.responseText;
                    } else {
                        console.error('Error:', xhr.status, xhr.statusText);
                    }
                };
                xhr.send();
            }

            // Initial load
            filterJobs('');
        });
    });
    </script>

    <?php

    $output_string = ob_get_contents();
    ob_end_clean();
    return $output_string;
}

add_action('wp_ajax_filter_jobs', 'filter_jobs_callback');
add_action('wp_ajax_nopriv_filter_jobs', 'filter_jobs_callback');

function filter_jobs_callback() {
    $category = sanitize_text_field($_GET['category']);

    $args = array(
        'post_type' => 'my-job',
        'posts_per_page' => -1,
    );

    if (!empty($category)) {
        $args['meta_query'] = array(
            array(
                'key' => 'job_category',
                'value' => $category,
                'compare' => '=',
            ),
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $post_title = get_the_title();
            $post_url = get_permalink();
            $post_image = get_the_post_thumbnail_url($post_id, 'full');
            $post_excerpt = get_the_excerpt();
            ?>
             <div class="job-card" data-post-id="<?php echo esc_attr($post_id); ?>">
                <div class="left-col">
                    <img src="<?php echo $post_image; ?>" alt="<?php echo $post_title; ?>">
                    <h4><?php echo $post_title; ?></h4>
                </div>
                <div class="info">
                    <p><?php the_field('job_category'); ?></p>
                    <p><?php the_field('job_location'); ?></p>
                </div>
        </div>
				<!-- Popup -->
					<div id="popup-<?php echo $post_id; ?>" class="popup">
					<div class="popup-cotent">
						<div class="job-img">
							<img src="<?php echo $post_image; ?>" alt="<?php echo $post_title; ?>">
						</div>
						<div class="job-content">
							<h2><?php echo $post_title; ?></h2>
							<p><?php echo $post_excerpt; ?></p> <!-- Change to get_the_content() -->
						</div>
						<a href="javascript:void(0)" class="close-popup">Close</a>
					</div>
				</div>
            <?php
        }
    } else {
        echo 'No jobs found.';
    }
	
    wp_reset_postdata();
    wp_die();
}

function company_jobs_shortcode() {
    ob_start();

    $company_jobs = get_field('company_jobs');

    if ($company_jobs) :
        foreach ($company_jobs as $job) :
            $job_title = get_the_title($job);
            $job_url = get_permalink($job); ?>
            <div class="open-positions">
				<h3><?php echo esc_html($job_title); ?></h3>
				<a href="<?php echo esc_url($job_url); ?>">Apply Online</a>		
              </div>
            <?php
        endforeach;
    endif;

    $output_string = ob_get_contents();
    ob_end_clean();
    return $output_string;
}
add_shortcode('company_jobs', 'company_jobs_shortcode');


function display_leaderboard() {
    ob_start();

    $args = array(
        'post_type'      => 'site-review',
			 'posts_per_page' => 12,
			'orderby'        => 'date',
			'order'          => 'DESC',
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<table class="leaderboard">';
        echo '<thead><tr><th>Name</th><th>Rating</th><th>Total Count</th><th>Stars</th></tr></thead><tbody>';

        // Initialize the array to store aggregated ratings and counts
        $aggregated_ratings = array();

        while ($query->have_posts()) {
            $query->the_post();

            $custom_fields = get_post_meta(get_the_ID(), '_submitted', true);

            if (isset($custom_fields['assigned_posts']) && !empty($custom_fields['assigned_posts'])) {
                $assigned_posts = explode(',', $custom_fields['assigned_posts']);

                foreach ($assigned_posts as $assigned_post_id) {
                    $assigned_post_id = trim($assigned_post_id);
                    if (!isset($aggregated_ratings[$assigned_post_id])) {
                        $aggregated_ratings[$assigned_post_id] = array(
                            'total_rating' => 0,
                            'count' => 0,
                        );
                    }

                    $aggregated_ratings[$assigned_post_id]['total_rating'] += intval($custom_fields['rating']);
                    $aggregated_ratings[$assigned_post_id]['count']++;
                }
            }
        }

        foreach ($aggregated_ratings as $post_id => $data) {
            $post_title = get_the_title($post_id);
            $total_rating = $data['total_rating'];
            $count = $data['count'];
            $average_rating = ($count > 0) ? round($total_rating / $count, 1) : 0;

            echo '<tr>';
            echo '<td>' . esc_html($post_title) . '</td>';
            echo '<td>' . esc_html($average_rating) . '</td>';
            echo '<td>' . esc_html($count) . '</td>'; // New column for total count
            echo '<td>' . get_star_rating_html($average_rating) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('leaderboard', 'display_leaderboard');

function display_leader() {
    ob_start();

		$args = array(
			'post_type'      => 'site-review',
			 'posts_per_page' => 12,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<table class="leader">';
        echo '<thead></thead><tbody>';

        // Initialize the array to store aggregated ratings and counts
        $aggregated_ratings = array();
        $counter = 1;
        while ($query->have_posts()) {
            $query->the_post();

            $custom_fields = get_post_meta(get_the_ID(), '_submitted', true);

            if (isset($custom_fields['assigned_posts']) && !empty($custom_fields['assigned_posts'])) {
                $assigned_posts = explode(',', $custom_fields['assigned_posts']);

                foreach ($assigned_posts as $assigned_post_id) {
                    $assigned_post_id = trim($assigned_post_id);
                    if (!isset($aggregated_ratings[$assigned_post_id])) {
                        $aggregated_ratings[$assigned_post_id] = array(
                            'total_rating' => 0,
                            'count' => 0,
                        );
                    }

                    $aggregated_ratings[$assigned_post_id]['total_rating'] += intval($custom_fields['rating']);
                    $aggregated_ratings[$assigned_post_id]['count']++;
                }
            }
        }

        foreach ($aggregated_ratings as $post_id => $data) {
            $post_title = get_the_title($post_id);
            $total_rating = $data['total_rating'];
            $count = $data['count'];
            $average_rating = ($count > 0) ? round($total_rating / $count, 1) : 0;

            echo '<tr>';
            echo '<td>' . esc_html($counter) . '</td>'; // Add this line for the numbers column
            echo '<td>' . esc_html($post_title) . '</td>';
            echo '<td>' . get_star_rating_html($average_rating) . '</td>';
            echo '<td>' . esc_html($average_rating) . '</td>';
            echo '</tr>';

            $counter++;
        }

        echo '</tbody></table>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('leader', 'display_leader');

function get_star_rating_html($rating) {
    $html = '<div class="star-rating">';
    $rounded_rating = round($rating);
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span class="star ' . (($i <= $rounded_rating) ? 'filled' : 'empty') . '">&#9733;</span>';
    }
    $html .= '</div>';

    return $html;
}

function display_latest_reviews() {
    ob_start();

    $args = array(
        'post_type'      => 'site-review',
        'posts_per_page' => 10, // Display the latest 10 reviews
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div class="slick-slider">';
        while ($query->have_posts()) {
            $query->the_post();

            $custom_fields = get_post_meta(get_the_ID(), '_submitted', true);

            if (isset($custom_fields['rating'])) {
                $post_title = get_the_title();
                $rating = esc_html($custom_fields['rating']);
                $stars_html = get_star_rating_html($rating);
				$description = wp_trim_words(get_the_excerpt(), 20);

                echo '<div class="card">';
                echo '<strong>' . esc_html($post_title) . '</strong>';
                echo  $stars_html;
				echo '<p>' . esc_html($description) . '</p>';
                echo '</div>';
            }
        }
        echo '</div>';

        echo '<script>
        jQuery(document).ready(function($) {
            $(".slick-slider").slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 4000,
                dots: false,
                arrows: false,
                variableWidth: false, 
            });
        });
    </script>';

    } else {
        echo '<p>No reviews found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('latest_reviews', 'display_latest_reviews');


// Function to handle form submission and save data
function process_company_profile_form() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_company_profile'])) {
        $company_name = sanitize_text_field($_POST['company_name']);

        $new_post = array(
            'post_title'   => $company_name,
            'post_type'    => 'company-profile',
            'post_status'  => 'draft',
        );

        $post_id = wp_insert_post($new_post);

        update_post_meta($post_id, 'company_name', sanitize_text_field($_POST['company_name']));
        update_post_meta($post_id, 'company_description', sanitize_text_field($_POST['company_description']));
        update_post_meta($post_id, 'company_location', sanitize_text_field($_POST['company_location']));
        update_post_meta($post_id, 'company_website', esc_url($_POST['company_website']));
        update_post_meta($post_id, 'reviews', sanitize_text_field($_POST['reviews']));
        update_post_meta($post_id, 'company_employees', sanitize_text_field($_POST['company_employees']));
		wp_update_post(array('ID' => $post_id, 'post_content' => wp_kses_post($_POST['post_type_description'])));

		if (!empty($_FILES['featured_image']['tmp_name'])) {
            $upload_dir = wp_upload_dir();
            $file_name = $_FILES['featured_image']['name'];
            $file_tmp = $_FILES['featured_image']['tmp_name'];
            $upload_path = $upload_dir['path'] . '/' . $file_name;

            move_uploaded_file($file_tmp, $upload_path);

            $attachment = array(
                'post_mime_type' => $_FILES['featured_image']['type'],
                'post_title'     => $company_name,
                'post_content'   => '',
                'post_status'    => 'inherit',
            );

            $attachment_id = wp_insert_attachment($attachment, $upload_path, $post_id);

            set_post_thumbnail($post_id, $attachment_id);
        }

        if (isset($_POST['company_jobs']) && is_array($_POST['company_jobs'])) {
            $company_jobs = array_map('intval', $_POST['company_jobs']);
            update_post_meta($post_id, 'company_jobs', $company_jobs);
        }

    }
}

// Shortcode to display the company profile form
function company_profile_form_shortcode() {
    ob_start();

    process_company_profile_form();

    $job_args = array(
        'post_type' => 'my-job',
        'posts_per_page' => -1,
    );
    $job_posts = get_posts($job_args);


    ?>
    <form action="" method="post" enctype="multipart/form-data" class="company_form">
<div class="c_row">
        <div>
        <label for="company_name">Company Name <span class="req-label">*</span></label>
        <input type="text" name="company_name" required>
</div>
<div>
        <label for="company_location">Company Location <span class="req-label">*</span></label>
        <input type="text" name="company_location" required>
        </div>
<div>
        <label for="company_website">Company Website <span class="req-label">*</span></label>
        <input type="url" name="company_website" required>
        </div>
<div>
        <label for="featured_image">Company Logo <span class="req-label">*</span> (Preferred Size 200*100)</label>
        <input type="file" name="featured_image" required>
        </div>
<div>
		<label for="company_employees">Company Employees <span class="req-label">*</span></label>
        <input type="text" name="company_employees" required>
        </div>
        <div>
        <label for="company_description">Company Description (Optional)</label>
        <textarea name="company_description"></textarea>
        </div>
<!--         <div>
        <label for="company_jobs">Company Jobs:</label>
        <select name="company_jobs[]" multiple>
            <?php
            foreach ($job_posts as $job_post) {
                echo '<option value="' . esc_attr($job_post->ID) . '">' . esc_html($job_post->post_title) . '</option>';
            }
            ?>
        </select>
        </div> -->
        </div>
<div class="c_submit">
        <input type="submit" name="submit_company_profile" value="Submit">
        </div>

    </form>
    <?php

    return ob_get_clean();
}
add_shortcode('company_profile_form', 'company_profile_form_shortcode');

function add_draft_count_to_menu() {
    $draft_count = wp_count_posts('company-profile')->draft; ?>

    <!-- Enqueue jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        jQuery(document).ready(function($) {
            var menuName = $('#menu-posts-company-profile .wp-menu-name');

            menuName.append('<span class="update-plugins"><?php echo $draft_count; ?></span>');

            console.log("test");
        });
    </script>

    <?php
}

add_action('admin_menu', 'add_draft_count_to_menu');

function jobs_listing() {
    ob_start();

    $job_args = array(
        'post_type'      => 'my-job',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $job_posts = get_posts($job_args);

    echo '<div class="job-wrapper">';
    echo '<div class="tabs">';

    foreach ($job_posts as $index => $job_post) {
        $tab_class = ($index === 0) ? 'tab active' : 'tab';
        echo '<a href="#" class="job-card ' . esc_attr($tab_class) . '" data-toggle-target=".tab-content-' . ($index + 1) . '"><b>' . esc_html(get_the_title($job_post->ID)) . '</b>';
        $job_category = get_field('job_category', $job_post->ID);
        $job_location = get_field('job_location', $job_post->ID);

        echo '<p>Category: ' . esc_html($job_category) . '</p>';
        echo '<p>Location: ' . esc_html($job_location) . '</p>';
        echo '</a>';
            }

    echo '</div>';
    echo '<div class="job-content">';

    foreach ($job_posts as $index => $job_post) {
        $content_class = ($index === 0) ? 'tab-content tab-content-' . ($index + 1) . ' active' : 'tab-content tab-content-' . ($index + 1);
        echo '<div class="' . esc_attr($content_class) . '">';

        $thumbnail_url = get_the_post_thumbnail_url($job_post->ID, 'thumbnail');
        if ($thumbnail_url) {
            echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr(get_the_title($job_post->ID)) . '">';
        }
        echo '<h3>' . esc_html(get_the_title($job_post->ID)) . '</h3>';

        $job_category = get_field('job_category', $job_post->ID);
        $job_location = get_field('job_location', $job_post->ID);
        $employment_type = get_field('employment_type', $job_post->ID);
        $compensation = get_field('compensation', $job_post->ID);
        $job_type = get_field('job_type', $job_post->ID);

        echo '<p>Job Category: ' . esc_html($job_category) . '</p>';
        echo '<p>Job Location: ' . esc_html($job_location) . '</p>';
        echo '<p>Employment Type: ' . esc_html($employment_type) . '</p>';
        echo '<p>Compensation: ' . esc_html($compensation) . '</p>';
        echo '<p>Job Type: ' . esc_html($job_type) . '</p>';

        // Get the complete detail page content
        $post_content = get_post_field('job_description', $job_post->ID);
      
        echo wpautop($post_content); // Apply paragraph tags and other formatting

        echo '<a href="#applyjobform" class="apply-btn"> Apply Now </a>';

        echo '</div>';
    }

    echo '</div></div>';

    return ob_get_clean();
}

add_shortcode('jobs-listing', 'jobs_listing');
