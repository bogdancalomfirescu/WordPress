<?php
	
	if ( count( $components ) > 2 )
	{
		$span = 6;
	}

	else
	{
		$span = 12;
	}

?>
<div class="mmtl-screen mmtl-component-picklist">

	<div class="mmtl-screen-header">
		<h2><?php _e( 'Components', 'table-layout' ); ?></h2>
	</div>

	<div class="mmtl-screen-content">

		<p><?php _e( 'Click a component to insert it into the editor.' ) ?></p>

		<div class="mmtl-row mmtl-compact">

			<?php foreach ( $components as $id => $component ) : ?>

			<div class="mmtl-col mmtl-col-xs-12 mmtl-col-sm-<?php echo esc_attr( $span ); ?>">

				<div id="mmtl-component-id-<?php echo esc_attr( $id ); ?>" class="mmtl-component mmtl-box" data-type="<?php echo esc_attr( $id ); ?>" title="<?php esc_attr_e( 'Click to add', 'table-layout' ); ?>">

					<div class="mmtl-component-icon"><?php echo $component['icon']; ?></div>

					<div class="mmtl-component-content">

						<h4 class="mmtl-component-title"><?php echo $component['title']; ?></h4>

						<?php if ( $component['description'] ) : ?>
						<div class="mmtl-component-description">
							<?php echo $component['description']; ?>
						</div>
						<?php endif; ?>

					</div>
						
				</div><!-- .mmtl-component -->

			</div>

			<?php endforeach; ?>
			
		</div><!-- .mmtl-row -->

	</div><!-- .mmtl-screen-content -->

</div><!-- .mmtl-screen -->