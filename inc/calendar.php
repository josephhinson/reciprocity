<?php
// [ra_calendar]
function ra_calendar($atts) {
        extract(shortcode_atts(array(
        ), $atts));
        ob_start(); ?>
        <?php global $current_user; get_currentuserinfo(); $userid = $current_user->ID; ?>
		<style>
		header {
			text-align: center;
		}

		#calendar {
			width: 100%;	
		}

		#calendar a {
			color: #8e352e;
			text-decoration: none;
		}

		#calendar ul {
			list-style: none;
			padding: 0;
			margin: 0;
			width: 100%;
		}

		#calendar li {
			display: block;
			float: left;
			width:14.342%;
			padding: 5px;
			box-sizing:border-box;
			border: 1px solid #ccc;
			margin-right: -1px;
			margin-bottom: -1px;
		}

		#calendar ul.weekdays {
			height: 40px;
			background: #8e352e;
		}

		#calendar ul.weekdays li {
			text-align: center;
			text-transform: uppercase;
			line-height: 20px;
			border: none !important;
			padding: 10px 6px;
			color: #fff;
			font-size: 13px;
		}

		#calendar .days li {
			height: 180px;
		}

		#calendar .days li:hover {
			background: #d3d3d3;
		}

		#calendar .date {
			text-align: center;
			margin-bottom: 5px;
			padding: 4px;
			background: #333;
			color: #fff;
			width: 20px;
			border-radius: 50%;
			float: right;
		}

		#calendar .event {
			clear: both;
			display: block;
			font-size: 13px;
			border-radius: 4px;
			padding: 5px;
			margin-top: 40px;
			margin-bottom: 5px;
			line-height: 14px;
			background: #e4f2f2;
			border: 1px solid #b5dbdc;
			color: #009aaf;
			text-decoration: none;
		}

		#calendar .event-desc {
			color: #666;
			margin: 3px 0 7px 0;
			text-decoration: none;	
		}

		#calendar .other-month {
			background: #f5f5f5;
			color: #666;
		}

		/* ============================
						Mobile Responsiveness
		============================*/


		@media(max-width: 768px) {

			#calendar .weekdays, #calendar .other-month {
				display: none;
			}

			#calendar li {
				height: auto !important;
				border: 1px solid #ededed;
				width: 100%;
				padding: 10px;
				margin-bottom: -1px;
			}

			#calendar .date {
				float: none;
			}
		}
</style>
<?php
		$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
		$current_year = isset($_GET['year_is']) ? intval($_GET['year_is']) : date('Y');

		$prev_month = mktime(12, 0, 0, $current_month - 1, 1, $current_year);
		$next_month = mktime(12, 0, 0, $current_month + 1, 1, $current_year);

		$calendar_ts_first = mktime(12, 0, 0, $current_month, 1, $current_year);
		$calendar_ts_last = mktime(12, 0, 0, $current_month + 1, -1, $current_year);

		$last_day = date('t', $calendar_ts_first);
		$total_month_days = date('t', $calendar_ts_first);

		$month_year = date('F Y', $calendar_ts_first);
		$day_names = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

		$day_index = date('w', $calendar_ts_first);
		$day_index *= -1;
		$day_index++;
		$meals = get_posts('numberposts=-1&orderby=menu_order&order=ASC&post_type=post&post_status=publish&cat=-32,-34');
	?>
    	<div class="float-right calendar-nav">
			<a href="?month=<?php echo date('n', $prev_month); ?>&year_is=<?php echo date('Y', $prev_month); ?>">
			Previous Month
			</a> &nbsp; |  &nbsp; <a href="?month=<?php echo date('n', $next_month); ?>&year_is=<?php echo date('Y', $next_month); ?>">
			Next Month
			</a>
		</div>
		<div id="calendar-wrap">
    		<header>
    			<h1>December 2017</h1>
    		</header>
    		<div id="calendar">
    			<ul class="weekdays">
				<?php foreach ($day_names as $day_name) { ?>
					<li><?php echo $day_name; ?></li>
                <?php } ?>
    			</ul>
				<!-- Placeholder -->
            <?php
				$day_counter = $day_index;
				for ($row = 1; $row <= 5; $row++) {
			?>
			<ul class="days">
                <?php for ($col = 1; $col <= 7; $col++) { ?>
					<?php
							if ($day_counter > 0 && $day_counter <= $last_day) {
//								if (in_array($day_counter, $event_dates)) {
//									printf('<a href="%s" class="calendar-date selected-calendar-date">%s</a>', $event_links[$day_counter][0], $day_counter);
//								} else { ?>
									<li class="day">
    									<?php printf('<div class="date">%s</div>', $day_counter); ?>
										<?php $thistime = gmmktime(0, 0, 0, $current_month, $day_counter, $current_year); ?>
    								</li>
									<?
							}
                        ?>
                    
                <?php
					$day_counter++;
                } ?>
            </ul>
            <?php
            	}
			?>

            </div><!-- /. calendar -->
    	</div><!-- /. wrap -->
		<div class="float-right calendar-nav noprint">
			<a href="?month=<?php echo date('n', $prev_month); ?>&year_is=<?php echo date('Y', $prev_month); ?>">
			Previous Month
			</a> &nbsp; |  &nbsp; <a href="?month=<?php echo date('n', $next_month); ?>&year_is=<?php echo date('Y', $next_month); ?>">
			Next Month
			</a>
		</div>
        <?php $return = ob_get_clean();
        return $return;
}
add_shortcode("ra_calendar", "ra_calendar");
// end shortcode
