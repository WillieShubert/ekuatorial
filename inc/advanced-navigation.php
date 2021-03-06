<?php

/*
 * Ekuatorial
 * Advanced navigation
 */


class Ekuatorial_AdvancedNav {

	var $prefix = 'ekuatorial_filter_';
	var $slug = 'explore';

	function __construct() {

		add_filter('query_vars', array($this, 'query_vars'));
		add_filter('body_class', array($this, 'body_class'));
		add_action('pre_get_posts', array($this, 'pre_get_posts'));
		add_action('generate_rewrite_rules', array($this, 'generate_rewrite_rules'));

	}

	function query_vars($vars) {
		$vars[] = 'ekuatorial_advanced_nav';
		return $vars;
	}

	function body_class($class) {
		if(get_query_var('ekuatorial_advanced_nav'))
			$class[] = 'advanced-nav';
		return $class;
	}

	function generate_rewrite_rules($wp_rewrite) {
		$widgets_rule = array(
			$this->slug . '$' => 'index.php?ekuatorial_advanced_nav=1'
		);
		$wp_rewrite->rules = $widgets_rule + $wp_rewrite->rules;
	}

	function pre_get_posts($query) {

		if($query->is_main_query() && $query->get('ekuatorial_advanced_nav')) {

			$query->is_home = false;

			$query->set('posts_per_page', 30);
			$query->set('ignore_sticky_posts', true);

			if(isset($_GET[$this->prefix . 's'])) {

				$query->set('s', $_GET[$this->prefix . 's']);

			}

			if(isset($_GET[$this->prefix . 'category'])) {

				$query->set('category__in', $_GET[$this->prefix . 'category']);

			}

			if(isset($_GET[$this->prefix . 'date_start'])) {

				$after = $_GET[$this->prefix . 'date_start'];
				$before = $_GET[$this->prefix . 'date_end'];

				if($after) {

					if(!$before)
						$before = date('Y-m-d H:i:s');

					$query->set('date_query', array(
						array(
							'after' => date('Y-m-d H:i:s', strtotime($after)),
							'before' => date('Y-m-d H:i:s', strtotime($before)),
							'inclusive' => true
						)
					));
				}

			}

		}

	}

	function form() {

		wp_enqueue_script('chosen');
		wp_enqueue_script('moment-js');
		wp_enqueue_style('chosen', get_stylesheet_directory_uri() . '/css/chosen.css');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui-smoothness', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');
		?>
		<form class="advanced-nav-filters row">
			<div class="three columns alpha">
				<div class="search-input adv-nav-input">
					<p class="label"><label for="<?php echo $this->prefix; ?>s"><?php _e('Text search', 'ekuatorial'); ?></label></p>
					<input type="text" id="<?php echo $this->prefix; ?>s" name="<?php echo $this->prefix; ?>s" placeholder="<?php _e('Type your search here', 'ekuatorial'); ?>" value="<?php echo (isset($_GET[$this->prefix . 's'])) ? $_GET[$this->prefix . 's'] : ''; ?>" />
				</div>
			</div>
			<?php
			$categories = get_categories();
			$active_cats = isset($_GET[$this->prefix . 'category']) ? $_GET[$this->prefix . 'category'] : array();
			if($categories) :
				?>
				<div class="three columns">
					<div class="category-input adv-nav-input">
						<p class="label"><label for="<?php echo $this->prefix; ?>category"><?php _e('Categories', 'ekuatorial'); ?></label></p>
						<select id="<?php echo $this->prefix; ?>category" name="<?php echo $this->prefix; ?>category[]" multiple>
							<?php foreach($categories as $category) : ?>
								<option value="<?php echo $category->term_id; ?>" <?php if(in_array($category->term_id, $active_cats)) echo 'selected'; ?>><?php echo $category->name; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			<?php endif; ?>
			<?php
			$oldest = array_shift(get_posts(array('posts_per_page' => 1, 'order' => 'ASC', 'orderby' => 'date')));
			$newest = array_shift(get_posts(array('posts_per_page' => 1, 'order' => 'DESC', 'orderby' => 'date')));

			$before = $oldest->post_date;
			$after = $newest->post_date;
			?>
			<div class="five columns omega">
				<div class="date-input adv-nav-input">
					<p class="label"><label for="<?php echo $this->prefix; ?>date_start"><?php _e('Date range', 'ekuatorial'); ?></label></p>
					<div class="date-range-inputs">
						<div class="date-from-container">
							<label class="sublabel" for="<?php echo $this->prefix; ?>date_start"><?php _e('From', 'ekuatorial'); ?></label>
							<input type="text" class="date-from" id="<?php echo $this->prefix; ?>date_start" name="<?php echo $this->prefix; ?>date_start" value="<?php echo (isset($_GET[$this->prefix . 'date_start'])) ? $_GET[$this->prefix . 'date_start'] : ''; ?>" />
						</div>
						<div class="date-to-container">
							<label class="sublabel" for="<?php echo $this->prefix; ?>date_end"><?php _e('To', 'ekuatorial'); ?></label>
							<input type="text" class="date-to" id="<?php echo $this->prefix; ?>date_end" name="<?php echo $this->prefix; ?>date_end" value="<?php echo (isset($_GET[$this->prefix . 'date_end'])) ? $_GET[$this->prefix . 'date_end'] : ''; ?>" />
						</div>
					</div>
				</div>
			</div>
			<div class="one column">
				<input type="submit" class="button" value="<?php _e('Filter', 'ekuatorial'); ?>" />
			</div>
		</form>
		<script type="text/javascript">
			(function($) {

				$(document).ready(function() {
					$('.category-input select').chosen();

					var min = moment('<?= $before; ?>').toDate();
					var max = moment('<?= $after; ?>').toDate();

					$('.date-range-inputs .date-from').datepicker({
						defaultDate: min,
						changeMonth: true,
						changeYear: true,
						numberOfMonths: 1,
						maxDate: max,
						minDate: min
					});

					$('.date-range-inputs .date-to').datepicker({
						defaultDate: max,
						changeMonth: true,
						changeYear: true,
						numberOfMonths: 1,
						maxDate: max,
						minDate: min
					});	

				});

			})(jQuery);
		</script>
		<?php

	}

}

$GLOBALS['ekuatorial_adv_nav'] = new Ekuatorial_AdvancedNav();

function ekuatorial_adv_nav_filters() {
	return $GLOBALS['ekuatorial_adv_nav']->form();
}

?>
