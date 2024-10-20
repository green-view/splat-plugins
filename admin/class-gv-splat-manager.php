<?php

/**
 * GV Splat Manager
 *
 * This class handles the listing and management of "Splats" in the WordPress admin.
 *
 * @link       https://green-view.nl
 * @since      1.0.0
 * @package    Gv_Splat
 * @subpackage Gv_Splat/admin
 */

require_once plugin_dir_path( __FILE__ ) . '../includes/class-gv-splat-http.php';

class Gv_Splat_Manager {

	private $limit = 10;

	// Add the management page to the WordPress admin menu
	public function add_management_page() {
		add_menu_page(
			'GV Splat Manager',      // Page title
			'GV Splats',             // Menu title
			'manage_options',        // Capability
			'gv_splat_manager',      // Menu slug
			array( $this, 'display_management_page' ), // Callback to display page content
			'dashicons-admin-generic', // Menu icon
			26                       // Position in the menu
		);
	}

	// Display the management page
	public function display_management_page() {
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'add' ) {
			$this->display_add_splat_form();
		} else {
			$this->display_splats_list();
		}
	}

	public function display_add_splat_form() {
		?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Add New Splat</h1>
            <form method="post" enctype="multipart/form-data">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="splat_file">Upload Splat (.splat only)</label></th>
                        <td><input type="file" id="splat_file" name="splat_file" accept=".splat" required/></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="thumbnail_file">Upload Thumbnail (images only)</label></th>
                        <td><input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*"/></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="title">Title</label></th>
                        <td><input type="text" id="title" name="title" class="regular-text" required/></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description">Description</label></th>
                        <td><textarea id="description" name="description" rows="5" class="large-text"></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="is_animated">Is Animated</label></th>
                        <td><input type="checkbox" id="is_animated" name="is_animated"/></td>
                    </tr>
                </table>
				<?php submit_button( 'Create Splat' ); ?>
            </form>
        </div>
		<?php

		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			// Handle the form submission here
			$splat_file     = $_FILES['splat_file'];
			$thumbnail_file = ! empty( $_FILES['thumbnail_file']['name'] ) ? $_FILES['thumbnail_file'] : null;

			$storage_id   = $this->upload_file( $splat_file );
			$thumbnail_id = $thumbnail_file ? $this->upload_file( $thumbnail_file ) : null;

			if ( $storage_id ) {
				// Proceed with creating the splat after both file uploads
				$payload = array(
					'storage_id'  => $storage_id,
					'title'       => sanitize_text_field( $_POST['title'] ),
					'description' => sanitize_textarea_field( $_POST['description'] ),
					'is_animated' => isset( $_POST['is_animated'] ) ? true : false,
				);
				if ( $thumbnail_id ) {
					$payload['thumbnail_id'] = $thumbnail_id;
				}

				// Call your function to create the Splat using the payload
				$create_response = $this->create_splat( $payload );

				if ( $create_response['success'] ) {
					// Success: Show a success message and a button to go back to the list
					echo '<div class="notice notice-success is-dismissible">
            <p>' . esc_html__( 'Splat created successfully!', 'gv-splat' ) . '</p>
            <p><a href="' . esc_url( admin_url( 'admin.php?page=gv_splat_manager' ) ) . '" class="button button-primary">' . esc_html__( 'Back to List', 'gv-splat' ) . '</a></p>
          </div>';
				} else {
					// Handle error: Show an error message and a button to go back to the list
					echo '<div class="notice notice-error is-dismissible">
            <p>' . esc_html__( 'Error creating splat: ', 'gv-splat' ) . esc_html( $create_response['message'] ) . '</p>
            <p><a href="' . esc_url( admin_url( 'admin.php?page=gv_splat_manager' ) ) . '" class="button button-secondary">' . esc_html__( 'Back to List', 'gv-splat' ) . '</a></p>
          </div>';
				}

			} else {
				// Handle file upload error
				echo '<div class="notice notice-error">Error uploading file. Please try again.</div>';
			}

		}
	}

	public function upload_file( $file ) {
		$response = Gv_Splat_HTTP::upload_file( $file );
		if ( $response ) {
			return $response;
		}

		return false;
	}

	// Fetch Splats from API with pagination

	private function create_splat( $payload ) {
		$response = Gv_Splat_HTTP::create_splat( $payload );
		if ( $response ) {
			return $response;
		}

		return false;
	}

	// Hook to initialize management page

	public function display_splats_list() {
		$current_page = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
		$splats       = $this->get_splats( $this->limit, $current_page );

		?>
        <div class="wrap">
            <h1 class="wp-heading-inline">GV Splats Management</h1>
            <a href="<?php echo admin_url( 'admin.php?page=gv_splat_manager&action=add' ); ?>"
               class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                <tr>
                    <th scope="col" class="manage-column column-id">ID</th>
                    <th scope="col" class="manage-column column-name">Name</th>
                    <th scope="col" class="manage-column column-description">Description</th>
                    <th scope="col" class="manage-column column-thumbnail">Thumbnail</th>
                    <th scope="col" class="manage-column column-created-at">Created At</th>
                    <th scope="col" class="manage-column column-shortcode">Shortcode</th>
                    <th scope="col" class="manage-column column-action">Action</th>
                </tr>
                </thead>
                <tbody>
				<?php if ( $splats && isset( $splats['success'] ) && $splats['success'] ) : ?>
					<?php foreach ( $splats['responseObject'] as $splat ) : ?>
                        <tr>
                            <td><?php echo esc_html( $splat['id'] ); ?></td>
                            <td><?php echo esc_html( $splat['title'] ); ?></td>
                            <td><?php echo esc_html( $splat['description'] ); ?></td>
                            <td>
								<?php if ( ! empty( $splat['thumbnail_url'] ) ) : ?>
                                    <img src="<?php echo esc_url( $splat['thumbnail_url'] ); ?>" alt="Thumbnail"
                                         width="50" height="50">
								<?php else : ?>
                                    <div class="placeholder-thumbnail"
                                         style="width:50px; height:50px; background:#ccc; display:flex; align-items:center; justify-content:center;">
                                        N/A
                                    </div>
								<?php endif; ?>
                            </td>
                            <td><?php echo esc_html( date( 'Y-m-d H:i', strtotime( $splat['created_at'] ) ) ); ?></td>
                            <td>
                                <input type="text" readonly
                                       value="[splat_shortcode id='<?php echo esc_attr( $splat['id'] ); ?>']"
                                       onclick="this.select();" style="width:150px;">
                            </td>
                            <td>
                                <form method="post" action="">
                                    <input type="hidden" name="splat_id"
                                           value="<?php echo esc_attr( $splat['id'] ); ?>">
                                    <input type="submit" name="delete_splat" value="Delete"
                                           class="button button-link-delete"
                                           onclick="return confirm('Are you sure you want to delete this splat?');">
                                </form>
                            </td>
                        </tr>
					<?php endforeach; ?>
				<?php else : ?>
                    <tr>
                        <td colspan="7">No Splats available.</td>
                    </tr>
				<?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination Controls -->
            <div class="tablenav bottom">
                <div class="tablenav-pages">
					<?php
					$total_items = isset( $splats['total'] ) ? $splats['total'] : 0;
					$total_pages = ceil( $total_items / $this->limit );

					if ( $total_pages > 1 ) {
						$current_url   = admin_url( 'admin.php?page=gv_splat_manager' );
						$prev_disabled = $current_page <= 1 ? 'disabled' : '';
						$next_disabled = $current_page >= $total_pages ? 'disabled' : '';

						echo '<span class="pagination-links">';

						// Previous Page
						if ( $current_page > 1 ) {
							$prev_page_url = add_query_arg( 'paged', $current_page - 1, $current_url );
							echo '<a class="prev-page button" href="' . esc_url( $prev_page_url ) . '">&laquo; Previous</a>';
						} else {
							echo '<span class="prev-page button ' . $prev_disabled . '">&laquo; Previous</span>';
						}

						// Page Info
						echo '<span class="paging-input">' . $current_page . ' of ' . $total_pages . '</span>';

						// Next Page
						if ( $current_page < $total_pages ) {
							$next_page_url = add_query_arg( 'paged', $current_page + 1, $current_url );
							echo '<a class="next-page button" href="' . esc_url( $next_page_url ) . '">Next &raquo;</a>';
						} else {
							echo '<span class="next-page button ' . $next_disabled . '">Next &raquo;</span>';
						}

						echo '</span>';
					}
					?>
                </div>
            </div>
        </div>
		<?php
		if ( isset( $_POST['delete_splat'] ) && isset( $_POST['splat_id'] ) ) {
			Gv_Splat_HTTP::delete_splat( intval( $_POST['splat_id'] ) );
		}
	}

	private function get_splats( $limit, $page ) {
		$data = Gv_Splat_HTTP::get_splats( $limit, $page );

		// Decode JSON response
		return json_decode( $data, true );
	}

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_management_page' ) );
	}
}

?>
