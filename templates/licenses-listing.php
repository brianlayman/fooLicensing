<?php
// Retrieve all license keys for the current user
$license_keys = foolic_get_licensekeys_by_user();

if ( $license_keys ) {
	?>
	<div id="foolic_license_listing">
		<?php foreach ( $license_keys as $key ) {
			$domains          = foolic_license_listing_get_sorted_domains( $key );
			$attached_domains = $key->get_attached_domains();
			$renewals		  = foolic_get_renewals( $key->ID );
			$upgrades   	  = foolic_get_upgrades( $key->ID );
			?>
			<div class="foolic_license_listing_item">
				<h2><?php echo $key->get_license()->name; ?></h2>
				<table>
					<tr>
						<th><?php _e( 'Date Issued', 'foolicensing' ); ?></th>
						<td><?php echo $key->date_issued; ?></td>
					</tr>
					<tr>
						<th><?php _e( 'License Key', 'foolicensing' ); ?></th>
						<td><code class="foolic-hide-licensekey"><?php echo $key->license_key; ?></code></td>
					</tr>
					<tr>
						<th><?php _e( 'Status', 'foolicensing' ); ?></th>
						<td><?php
							$valid = foolic_licensekey_checker::validate_license_key( $key ); ?>
							<span style="color:<?php echo $valid['color']; ?>"><?php echo $valid['message']; ?></span>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Expires', 'foolicensing' ); ?></th>
						<td>
							<?php echo $key->expires; ?>
							<?php do_action( 'foolic_license_listing_item-expires', $key, $valid ); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Usage Limits', 'foolicensing' ); ?></th>
						<td><?php echo $key->exceeded ? "<strong style='color:#f00'>" : ""; ?>
							<?php echo count( $attached_domains ); ?><?php echo $key->exceeded ? "</strong>" : ""; ?>
							/ <?php echo ($key->domain_limit == 0) ? __( 'Unlimited', 'foolicensing' ) : $key->domain_limit; ?>
							<?php do_action( 'foolic_license_listing_item-limits', $key, $attached_domains ); ?>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Activations', 'foolicensing' ); ?></th>
						<td>
							<?php
							if ( $domains != false && count( $domains ) > 0 ) {
								foolic_license_listing_render_domain( $domains[0], $key );
							} else {
								_e( 'The license key has not been activated yet.', 'foolicensing' );
							}
							?>
						</td>
					</tr>
					<?php if ( $domains != false && count( $domains ) > 1 ) {
						for ( $i = 1; $i < count( $domains ); $i++ ) {
							echo '<tr><th></th><td>';
							foolic_license_listing_render_domain( $domains[$i], $key );
							echo '</td></tr>';
						}
					}
					?>
					<?php if ( count( $renewals ) > 0 ) { ?>
					<tr>
						<th><?php _e( 'Renewals', 'foolicensing' ); ?></th>
						<td>
							<?php echo $renewals[0]['renewal_date']; ?>
						</td>
					</tr>
						<?php if ( count( $renewals ) > 1 ) {
							for ( $i = 1; $i < count( $renewals ); $i++ ) {
								echo '<tr><th></th><td>';
								echo $renewals[$i]['renewal_date'];
								echo '</td></tr>';
							}
						}
						?>
					<?php } ?>
					<?php if ( count( $upgrades ) > 0 ) { ?>
						<tr>
							<th><?php _e( 'Upgrades', 'foolicensing' ); ?></th>
							<td>
								<?php
									echo $renewals[0]['renewal_date'];
									if (array_key_exists('upgrade_details', $renewals[0])) {
										echo ' -&gt; ' . $renewals[0]['upgrade_details'];
									}
								?>
							</td>
						</tr>
						<?php if ( count( $upgrades ) > 1 ) {
							for ( $i = 1; $i < count( $upgrades ); $i++ ) {
								echo '<tr><th></th><td>';
								echo $renewals[0]['renewal_date'];
								if (array_key_exists('upgrade_details', $renewals[0])) {
									echo ' -&gt; ' . $renewals[0]['upgrade_details'];
								}
								echo '</td></tr>';
							}
						}
						?>
					<?php } ?>
				</table>
			</div>
		<?php } ?>
	</div>
<?php
} else {
	?>
	<p class="foolic_license_listing_none"><?php _e( 'You have no license keys', 'foolicensing' ); ?></p>
<?php
}

/**
 * @param foolic_licensekey $key
 *
 * @return array
 */
function foolic_license_listing_get_sorted_domains($key) {
	$domain_array = $key->get_domains();

	$domain_objects = array();

	if ( $domain_array !== false ) {
		foreach ( $domain_array as $domain ) {
			$domain_object           = foolic_domain::get( $domain );
			$domain_object->attached = $key->is_domain_attached( $domain_object->url );

			if ( $domain_object->attached ) {
				$domain_object->sort_order = 0;
			} else if ( $domain_object->localhost ) {
				$domain_object->sort_order = 2;
			} else if ( $domain_object->blacklisted ) {
				$domain_object->sort_order = 3;
			} else {
				$domain_object->sort_order = 1;
			}

			$domain_objects[] = $domain_object;
		}
		usort( $domain_objects, 'foolic_license_listing_sort_compare' );
	}

	return $domain_objects;
}

function foolic_license_listing_sort_compare($a, $b) {
	return $a->sort_order > $b->sort_order;
}

function foolic_license_listing_render_domain($domain, $key) {
	if ( $domain == null ) return;

	$attached  = $domain->attached;
	$localhost = $domain->localhost;

	if ( $localhost ) {
		echo '<span style="opacity:0.6">' . $domain->url . '</span> (' . __( 'for developer use only', 'foolicensing' ) . ')';
	} else {
		if ( $attached ) {
			echo $domain->url;
			echo ' <a class="foolic-action foolic-action-detach" data-domain-id="' . $domain->ID . '" data-licensekey-id="' . $key->ID . '" data-action="foolic_detach_domain_from_licensekey" href="#detach">' . __( 'Detach', 'foolicensing' ) . '</a>';
		} else {
			echo '<span style="text-decoration: line-through">' . $domain->url . '</span> (' . __( 'detached', 'foolicensing' ) . ')';
			echo ' <a class="foolic-action foolic-action-attach" data-domain-id="' . $domain->ID . '" data-licensekey-id="' . $key->ID . '" data-action="foolic_attach_domain_to_licensekey" href="#attach">' . __( 'Attach', 'foolicensing' ) . '</a>';
		}
	}
}