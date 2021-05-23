<?php
/**
 * Job dashboard shortcode content.
 *
 * This template overrides wp-job-manager/templates/job-dashboard.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @version     1.32.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="job-manager-job-dashboard">
    <p><?php esc_html_e( 'Your listings are shown in the table below.', 'wp-job-manager' ); ?></p>
	<table class="job-manager-jobs">
		<thead>
			<tr>
				<?php foreach ( $job_dashboard_columns as $key => $column ) : ?>
                    <th class="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $column ); ?></th>
				<?php endforeach; ?>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! $jobs ) : ?>
				<tr>
                    <td colspan="<?php echo intval( count( $job_dashboard_columns ) ) + 1; ?>"><?php esc_html_e( 'You do not have any active listings.', 'wp-job-manager' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $jobs as $job ) : ?>
					<tr>
						<?php foreach ( $job_dashboard_columns as $key => $column ) : ?>
							<td data-label="<?php echo esc_html( $column ); ?>" class="<?php echo esc_attr( $key ); ?>">
								<?php if ('job_title' === $key ) : ?>
									<?php if ( $job->post_status == 'publish' ) : ?>
										<a href="<?php echo get_permalink( $job->ID ); ?>"><?php echo $job->post_title; ?></a>
									<?php else : ?>
										<?php echo $job->post_title; ?> <small>(<?php the_job_status( $job ); ?>)</small>
									<?php endif; ?>
								<?php elseif ('date' === $key ) : ?>
									<?php echo date_i18n( get_option( 'date_format' ), strtotime( $job->post_date ) ); ?>
								<?php elseif ('expires' === $key ) : ?>
									<?php echo $job->_job_expires ? date_i18n( get_option( 'date_format' ), strtotime( $job->_job_expires ) ) : '&ndash;'; ?>
								<?php elseif ('filled' === $key ) : ?>
									<?php echo is_position_filled( $job ) ? '&#10004;' : '&ndash;'; ?>
								<?php else : ?>
									<?php do_action( 'job_manager_job_dashboard_column_' . $key, $job ); ?>
								<?php endif; ?>
							</td>
						<?php endforeach; ?>
						<td>
							<ul class="job-dashboard-actions">
								<?php
								$actions = [];

								switch ( $job->post_status ) {
									case 'publish' :
										if ( wpjm_user_can_edit_published_submissions() ) {
											$actions[ 'edit' ] = [ 'label' => __( 'Edit', 'wp-job-manager' ), 'nonce' => false ];
										}
										if ( is_position_filled( $job ) ) {
											$actions['mark_not_filled'] = [ 'label' => __( 'Mark not filled', 'wp-job-manager' ), 'nonce' => true ];
										} else {
											$actions['mark_filled'] = [ 'label' => __( 'Mark filled', 'wp-job-manager' ), 'nonce' => true ];
										}

										$actions['duplicate'] = [ 'label' => __( 'Duplicate', 'wp-job-manager' ), 'nonce' => true ];
										break;
									case 'expired' :
										if ( job_manager_get_permalink( 'submit_job_form' ) ) {
											$actions['relist'] = [ 'label' => __( 'Relist', 'wp-job-manager' ), 'nonce' => true ];
										}
										break;
									case 'pending_payment' :
									case 'pending' :
										if ( job_manager_user_can_edit_pending_submissions() ) {
											$actions['edit'] = [ 'label' => __( 'Edit', 'wp-job-manager' ), 'nonce' => false ];
										}
										break;
									case 'draft' :
									case 'preview' :
										$actions['continue'] = [ 'label' => __( 'Continue Submission', 'wp-job-manager' ), 'nonce' => true ];
										break;
								}

								$actions['delete'] = [ 'label' => __( 'Delete', 'wp-job-manager' ), 'nonce' => true ];
								$actions           = apply_filters( 'job_manager_my_job_actions', $actions, $job );

								foreach ( $actions as $action => $value ) {
									$action_url = add_query_arg( [ 'action' => $action, 'job_id' => $job->ID ] );
									if ( $value['nonce'] ) {
										$action_url = wp_nonce_url( $action_url, 'job_manager_my_job_actions' );
									}
									echo '<li><a href="' . esc_url( $action_url ) . '" class="job-dashboard-action-' . esc_attr( $action ) . '">' . esc_html( $value['label'] ) . '</a></li>';
								}
								?>
							</ul>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php get_job_manager_template( 'pagination.php', [ 'max_num_pages' => $max_num_pages ] ); ?>
</div>
