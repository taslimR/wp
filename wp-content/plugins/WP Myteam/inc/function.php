<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
add_action( 'init', 'wmt_register_shortcode' );

function wmt_register_shortcode(){
	if( ! function_exists( 'wmt_myteam' )): 
		add_shortcode( 'wmt_myteam', 'wmt_myteam' );
		function wmt_myteam( $atts ){ 
			
			$wmt_options = get_post_meta( $atts['id'], 'wmt_myteam', true );
			$table_style = array( 	'burnt_orange'   => 'my-burnt-orange.css',
									'dark_blue'      => 'my-dark-blue.css',
									'green'          => 'my-green.css',
									'light_yellow'   => 'my-light-yellow.css',
									'ny_pink'        => 'my-ny-pink.css',
									'orange'         => 'my-orange.css',
									'piction_blue'   => 'my-piction-blue.css',
									'purple'         => 'my-purpul.css',
									'red'            => 'my-red.css',
									'rose'           => 'my-rose.css',
									'shamrock_green' => 'my-shamrock-green.css	',
									'wistful'        => 'my-wistful.css',
									'default'        => 'my-style.css' );

			$style_file = isset( $wmt_options['wmt_color'] ) ? $wmt_options['wmt_color'] : "default";
			if( empty( $style_file )){
				$style_file = "default";
			}
			wp_register_style( 'admin-wmt-custom-formstyle', wp_wmt_myteam()->css_uri . $table_style[ $style_file ] );

			wp_enqueue_style('admin-wmt-bootstrap-formstyle');
			wp_enqueue_style('admin-wmt-fontawesome');
			wp_enqueue_style('admin-wmt-ubuntu-font');
			wp_enqueue_style('admin-wmt-custom-formstyle');

			ob_start();
			if( !empty( $atts['id'] ) ):
				
				$wmt_columns = isset( $wmt_options[ 'wmt_columns' ] )? $wmt_options[ 'wmt_columns' ]: 2;
				$wmt_type = isset( $wmt_options[ 'wmt_type' ]) ? $wmt_options[ 'wmt_type' ] : "b";
				$wmt_bg_image = isset( $wmt_options[ 'wmt_bg_image' ]) ? $wmt_options[ 'wmt_bg_image' ] : "b";
				$container = 'container-fluid';

				if( !isset( $wmt_options['wmt_style'] ) ) return;
				
				if( $wmt_type == 'b' )
					$container = 'container';
				if( $wmt_type == 'fw' )
					$container = 'container-fluid';
				$special_column = "";
				if( $wmt_columns == 2)
					$column_class = 'col-md-6';
				if( $wmt_columns == 3)
					$column_class = 'col-md-4';
				if( $wmt_columns == 4)
					$column_class = 'col-md-3';
				if( $wmt_columns == 6)
					$column_class = 'col-md-2';

				if( $wmt_options['wmt_style'] == 1 ): ?>

					<!-- Team Style 1 -->
					<div class="my-team team-style-1 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>		
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>	
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
											<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
		                                <?php } ?>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
								</div>
								<div class="my-member-social">
									<ul>
									<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>	
							<?php } ?>	
						</div>
					</div>
					<!-- End Team Style 1 -->

				<?php endif; ?>

				<?php if( $wmt_options['wmt_style'] == 2 ): ?>
					<!-- Team Style 2 -->
					<div class="my-team team-style-2 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-block <?php echo $column_class; ?>">
								<div class="my-team-member">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
										<div class="my-member-social">
											<ul>
											<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php 
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>
									</div>
								</div>
							</div>	
							<?php } ?>		
						</div>		
					</div>		
					<!-- End Team Style 2 -->
				<?php endif; ?>

				<?php if( $wmt_options['wmt_style'] == 3 ): ?>
					<!-- Team Style 3 -->
					<div class="my-team team-style-3 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>	
							<div class="my-team-member <?php echo $column_class; ?>">
								<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<ul>
									<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>
							<?php } ?> 		
						</div>
					</div>
					<!-- End Team Style 3 -->
				<?php endif; ?>

				<?php if( $wmt_options['wmt_style'] == 4 ): ?>
					<!-- Team Style 4 -->	
					<div class="my-team team-style-4 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>	
							<div class="my-team-block <?php echo $column_class; ?>">
								<div class="my-team-member">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
										
										<ul class="my-member-social">
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>
							</div>		
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 4 -->
				<?php endif; ?>

				<?php if( $wmt_options['wmt_style'] == 5 ): ?>
					<!-- Team Style 5 -->	
					<div class="my-team team-style-5 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>	
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
								</div>
								<div class="my-member-social">
									<ul>
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>	
							<?php } ?>	
						</div>
					</div>
					<!-- End Team Style 5 -->	
				<?php endif; ?>

				<?php if( $wmt_options['wmt_style'] == 6 ): ?>
					<!-- Team Style 6 -->
					<div class="my-team team-style-6 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>	
							<div class="my-team-member <?php echo $column_class; ?>">
								<?php if( $wmt_options['image_'.$i] != '' ){ ?>
									<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                            <?php } ?>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<ul class="my-member-social">
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 6 -->
				<?php endif; ?>

				<?php if( $wmt_options['wmt_style'] == 7 ): ?>
					<!-- Team Style 7 -->	
					<div class="my-team team-style-7 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>	
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="overlay"></div>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>	
									<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
								</div>
								
								<div class="my-member-social">
									<ul>
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ):
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )): ?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 7 -->
				<?php endif; ?>

				<?php if( $wmt_options['wmt_style'] == 8 ): ?>
					<!-- Team Style 8 -->	
					<div class="my-team team-style-8 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>	
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="overlay"></div>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>	
									<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
								</div>
								<div class="my-member-social">
									<ul>
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 8 -->
				<?php endif; ?>

				<?php if( $wmt_options['wmt_style'] == 9 ): ?>
				    <!-- Team Style 9 -->	
				    <div class="my-team team-style-9 <?php echo $container; ?>">
				    	<div class="row">
				    		<div class="section text-center">
				    			<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
				    		</div>
				    		<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>	
				    		<div class="my-team-member <?php echo $column_class; ?>">
				    			<div class="my-team-member-bg">
				    				<div class="my-member-img">
				    					<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
				    					<div class="overlay"></div>
				    				</div>
				    				<div class="my-team-detail">
				    					<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
				    					<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
				    					<hr>	
				    					<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
				    				</div>
				    				<div class="my-member-social">
				    					<ul>
				    						<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ):
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												 ?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
				    				</div>
				    			</div>
				    		</div>	
				    		<?php } ?>	
				    	</div>
				    </div>
				    <!-- End Team Style 9 -->
				<?php endif; ?>

				<?php if( $wmt_options['wmt_style'] == 10 ): ?>
					<!-- Team Style 10 -->	
						<div class="my-team team-style-10 <?php echo $container; ?>">
							<div class="row">
								<div class="section text-center">
									<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
								</div>
								<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>	
								<div class="my-team-member <?php echo $column_class; ?>">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
										<div class="overlay"></div>
									</div>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<hr>	
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
									</div>
									<div class="my-member-social">
										<ul>
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>	
								<?php } ?>
							</div>
						</div>
						<!-- End Team Style 10 -->
				<?php endif; ?>

				<?php if( $wmt_options['wmt_style'] == 11 ): ?>
					<!-- Team Style 11 -->
					<div class="my-team team-style-11 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>		
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>		
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>
									<p class="my-member-description"><?php echo $wmt_options['detail_'.$i]; ?></p>		
								</div>				
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 11 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 12 ): ?>
					<!-- Team Style 12 -->
					<div class="my-team team-style-12" style="background: url('<?php echo $wmt_bg_image; ?>');">
						<div class="<?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>	
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>			
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>
									<p class="my-member-description"><?php echo $wmt_options['detail_'.$i]; ?></p>		
								</div>				
							</div>
							<?php } ?>
						</div>
						</div>
					</div>
					<!-- End Team Style 12 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 13 ): ?>
					<!-- Team Style 13 -->
					<div class="my-team team-style-13 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>		
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>		
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">					
									<p class="my-member-description"><?php echo $wmt_options['detail_'.$i]; ?></p>	
									<hr>	
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
								</div>				
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 13 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 14 ): ?>
					<!-- Team Style 14 -->
					<div class="my-team team-style-14" style="background: url('<?php echo $wmt_bg_image; ?>');">
						<div class="my-overlay-bg"></div>
						<div class="<?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>	
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>			
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">					
									<p class="my-member-description"><?php echo $wmt_options['detail_'.$i]; ?></p>	
									<hr>	
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
								</div>				
							</div>
							<?php } ?>
						</div>
						</div>
					</div>
					<!-- End Team Style 14 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 15 ): ?>
					<!-- Team Style 15 -->
					<div class="my-team team-style-15 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>		
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>		
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>
									<p class="my-member-description"><?php echo $wmt_options['detail_'.$i]; ?></p>		
								</div>				
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 15 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 16 ): ?>
					<!-- Team Style 16 -->
					<div class="my-team team-style-16 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>	
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>			
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>
									<p class="my-member-description"><?php echo $wmt_options['detail_'.$i]; ?></p>		
								</div>				
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 16 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 17 ): 
						$column_class = 'col-md-6';
				?>
					<!-- Team Style 17 -->
					<div class="my-team team-style-17 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>		
							<?php for( $i = 1; $i <= 4; $i++ ){ ?>		
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="col-xs-6">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									</div>
								</div>
								<div class="col-xs-6 text-center">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<div class="my-member-social">
											<ul>
												<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
											</ul>
										</div>					
									</div>						
								</div>			
							</div>	
							<?php } ?>		
						</div>
					</div>
					<!-- End Team Style 17 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 18 ): 
					$column_class = 'col-md-6';
				?>
					<!-- Team Style 18 -->
					<div class="my-team team-style-18">
						<div class="<?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>		
							<?php for( $i = 1; $i <= 4; $i++ ){ ?>		
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-team-content">
								<div class="col-xs-6 my-member-img-col">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
											<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
		                                <?php } ?>
									</div>
								</div>
								<div class="col-xs-6 text-center">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-description hidden-xs"><?php echo $wmt_options['detail_'.$i]; ?></p>	
										
										<div class="my-member-social">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php endif;
													endforeach;
												endif; ?>
											</ul>
										</div>					
									</div>						
								</div>	
								</div>		
							</div>	
							<?php } ?>		
						</div>
						</div>
					</div>
					<!-- End Team Style 18 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 19 ): 
					$column_class = 'col-md-6';
					?>
					<!-- Team Style 19 -->
					<div class="my-team team-style-19">
						<div class="<?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>	
							<?php for( $i = 1; $i <= 4; $i++ ){ ?>			
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-team-content">
								<div class="col-xs-6 my-member-img-col">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
											<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
		                                <?php } ?>
									</div>
								</div>
								<div class="col-xs-6 text-center">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-description hidden-xs"><?php echo $wmt_options['detail_'.$i]; ?></p>	
										
										<div class="my-member-social">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php 
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>					
									</div>						
								</div>	
								</div>		
							</div>			
							<?php } ?>
						</div>
						</div>
					</div>
					<!-- End Team Style 19 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 20 ): 
					$column_class = 'col-md-6';
				?>
					<!-- Team Style 20 -->
					<div class="my-team team-style-20">
						<div class="<?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>		
							<?php for( $i = 1; $i <= 4; $i++ ){ ?>		
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-team-content">
								<div class="col-xs-6 my-member-img-col">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									</div>
								</div>
								<div class="col-xs-6 text-center">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-description hidden-xs"><?php echo $wmt_options['detail_'.$i]; ?></p>	
										<div class="my-member-btn">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php 
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>					
									</div>						
								</div>	
								</div>		
							</div>			
							<?php } ?>
						</div>
						</div>
					</div>
					<!-- End Team Style 20 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 21 ): 
					$column_class = 'col-md-6';
				?>
					<!-- Team Style 21 -->
					<div class="my-team team-style-21">
						<div class="<?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= 4; $i++ ){ ?>				
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-team-content">
								<div class="col-xs-6 my-member-img-col">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									</div>
								</div>
								<div class="col-xs-6 text-center">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-description hidden-xs"><?php echo $wmt_options['detail_'.$i]; ?></p>	
										<div class="my-member-btn">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php 
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>					
									</div>						
								</div>	
								</div>		
							</div>				
							<?php } ?>
						</div>
						</div>
					</div>
					<!-- End Team Style 21 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 22 ): 
					$column_class = 'col-md-6';
					?>
					<!-- Team Style 22 -->
					<div class="my-team team-style-22">
						<div class="<?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>	
							<?php for( $i = 1; $i <= 4; $i++ ){ ?>			
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-team-content">
								<div class="col-xs-6 my-member-img-col">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									</div>
								</div>
								<div class="col-xs-6 text-center">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-description hidden-xs"><?php echo $wmt_options['detail_'.$i]; ?></p>	
										
										<div class="my-member-social">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>						
									</div>						
								</div>	
								</div>		
							</div>			
							<?php } ?>
						</div>
						</div>
					</div>
					<!-- End Team Style 22 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 23 ): 
					$column_class = 'col-md-6';
				?>
					<!-- Team Style 23 -->
					<div class="my-team team-style-23 <?php echo $container; ?>"> 		
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>	
							<?php for( $i = 1; $i <= 4; $i++ ){ ?>					
							<div class="<?php echo $column_class; ?>">
								<div class=" my-team-member">
								<div class="col-xs-6">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									</div>
								</div>
								<div class="col-xs-6 text-center my-team-content">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-description hidden-xs"><?php echo $wmt_options['detail_'.$i]; ?></p>			
										<div class="my-member-social">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php 
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>					
									</div>						
								</div>	
								</div>		
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 23 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 24 ): 
					$column_class = 'col-md-6';
				?>
					<!-- Team Style 24 -->
					<div class="my-team team-style-24"> <?php echo $container; ?>		
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>			
							<?php for( $i = 1; $i <= 4; $i++ ){ ?>			
							<div class="<?php echo $column_class; ?>">
								<div class=" my-team-member">
								<div class="col-xs-6">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									</div>
								</div>
								<div class="col-xs-6 text-center my-team-content">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-description hidden-xs"><?php echo $wmt_options['detail_'.$i]; ?></p>			
										
										<div class="my-member-social">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php 
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>					
									</div>						
								</div>	
								</div>		
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 24 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 25 ): ?>
					<!-- Team Style 25 -->	
					<div class="my-team team-style-25 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>	
									<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
								</div>
								<div class="my-member-social">
									<ul>
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 25 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 26 ): ?>
					<!-- Team Style 26 -->	
					<div class="my-team team-style-26">
						<div class="container">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="border"></div>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>	
									<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
								</div>
								<div class="my-member-social">
									<ul>
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>
							<?php } ?>
						</div>
						</div>
					</div>
					<!-- End Team Style 26 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 27 ): ?>
					<!-- Team Style 27 -->	
					<div class="my-team team-style-27 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="border"></div>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>	
									<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
								</div>
								<div class="my-member-social">
									<ul>
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>		
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 27 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 28 ): ?>
					<!-- Team Style 28 -->	
					<div class="my-team team-style-28 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="border"></div>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<div class="my-member-social">
										<ul>
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>				
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 28 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 29 ): ?>
					<!-- Team Style 29 -->	
					<div class="my-team team-style-29 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="border"></div>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<div class="my-member-social">
										<ul>
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
								</div>
								</div>				
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 29 -->

				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 30 ): ?>
					<!-- Team Style 30 -->	
					<div class="my-team team-style-30 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member text-center <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="border"></div>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<div class="my-member-social">
										<ul>
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>				
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 30 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 31 ): 
					$column_class = 'col-md-6';
				?>
					<!-- Team Style 31 -->
					<div class="my-team team-style-31 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>	
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>		
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-team-content">
								<div class="col-xs-6 my-member-img-col">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									</div>
								</div>
								<div class="col-xs-6 text-center">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<hr class="hidden-xs">
										<p class="my-member-description hidden-xs"><?php echo $wmt_options['detail_'.$i]; ?></p>	
										<hr>
										<div class="my-member-btn">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php 
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>					
									</div>						
								</div>	
								</div>		
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 31 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 32 ): ?>
					<!-- Team Style 32 -->	
					<div class="my-team team-style-32 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-block <?php echo $column_class; ?>">
								<div class="my-team-member">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
										<ul class="my-member-social">
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 32 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 33 ): ?>
					<!-- Team Style 33 -->
					<div class="my-team team-style-33 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>		
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>	
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail text-center">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<div class="my-member-social">
										<ul>
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>				
								</div>				
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 33 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 34 ): ?>
					<!-- Team Style 34 -->
					<div class="my-team team-style-34 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>			
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail text-center">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<div class="my-member-social">
										<ul>
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>				
								</div>				
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 34 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 35 ): ?>
					<!-- Team Style 35 -->	
					<div class="my-team team-style-35">
						<div class="<?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-block text-center <?php echo $column_class; ?>">
								<div class="my-team-member">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									</div>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<hr>	
									</div>
									<div class="my-member-social">
										<ul>
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
						</div>
					</div>
					<!-- End Team Style 35 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 36 ): ?>
					<!-- Team Style 36 -->
					<div class="my-team team-style-36 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-01.jpg" class="img-responsive" alt="team01">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-02.jpg" class="img-responsive" alt="team02">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-03.jpg" class="img-responsive" alt="team03">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>	
								</div>	
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-04.jpg" class="img-responsive" alt="team04">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>			
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-01.jpg" class="img-responsive" alt="team01">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-02.jpg" class="img-responsive" alt="team02">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-03.jpg" class="img-responsive" alt="team03">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>	
								</div>	
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-04.jpg" class="img-responsive" alt="team04">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-01.jpg" class="img-responsive" alt="team01">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-02.jpg" class="img-responsive" alt="team02">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-03.jpg" class="img-responsive" alt="team03">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>	
								</div>	
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-04.jpg" class="img-responsive" alt="team04">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
						</div>		
					</div>		
					<!-- End Team Style 36 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 37 ): ?>
					<!-- Team Style 37 -->
					<div class="my-team team-style-37 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-01.jpg" class="img-responsive" alt="team01">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-02.jpg" class="img-responsive" alt="team02">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-03.jpg" class="img-responsive" alt="team03">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>	
								</div>	
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-04.jpg" class="img-responsive" alt="team04">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>			
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-01.jpg" class="img-responsive" alt="team01">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-02.jpg" class="img-responsive" alt="team02">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-03.jpg" class="img-responsive" alt="team03">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>	
								</div>	
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-04.jpg" class="img-responsive" alt="team04">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-01.jpg" class="img-responsive" alt="team01">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-02.jpg" class="img-responsive" alt="team02">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-03.jpg" class="img-responsive" alt="team03">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>	
								</div>	
							</div>
							<div class="col-md-2 my-team-block">
								<div class="my-team-member">
									<img src="images/team-style-04-04.jpg" class="img-responsive" alt="team04">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									</div>
								</div>
							</div>
						</div>		
					</div>		
					<!-- End Team Style 37 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 38 ): ?>
					<!-- Team Style 38 -->	
					<div class="my-team team-style-38">
						<div class="<?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-block text-center <?php echo $column_class; ?>">
								<div class="my-team-member">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
											<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
		                                <?php } ?>
									</div>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<div class="my-member-social">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php 
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
						</div>
					</div>
					<!-- End Team Style 38 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 39 ): ?>
					<!-- Team Style 39 -->	
					<div class="my-team team-style-39">
						<div class="<?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-block text-center <?php echo $column_class; ?>">
								<div class="my-team-member">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
											<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
		                                <?php } ?>
									</div>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<div class="my-member-social">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ):
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													 ?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php 
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
						</div>
					</div>
					<!-- End Team Style 39 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 40 ): ?>
					<!-- Team Style 40 -->
					<div class="my-team team-style-40 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= 4; $i++ ){ ?>			
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-team-content">
								<div class="col-xs-6 text-center">
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<hr>
										<p class="my-member-description hidden-xs"><?php echo $wmt_options['detail_'.$i]; ?></p>	
										<hr class="hidden-xs">
										<div class="my-member-btn">
											<ul>
												<?php 
												if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
													foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ):
														if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
														 ?>
														<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
													<?php 
														endif;
													endforeach;
												endif; ?>
											</ul>
										</div>					
									</div>						
								</div>	
								<div class="col-xs-6 my-member-img-col">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									</div>
								</div>					
								</div>		
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 40 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 41 ): ?>
					<!-- Team Style 41 -->
					<div class="my-team team-style-41 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-block <?php echo $column_class; ?>">
								<div class="my-team-member">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
										<ul class="my-member-social">
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>		
					</div>		
					<!-- End Team Style 41 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 42 ): ?>
					<!-- Team Style 42 -->
					<div class="my-team team-style-42 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member <?php echo $column_class; ?>">
								<?php if( $wmt_options['image_'.$i] != '' ){ ?>
									<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                            <?php } ?>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<ul class="my-member-social">
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 42 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 43 ): ?>
					<!-- Team Style 43 -->	
					<div class="my-team team-style-43 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-block <?php echo $column_class; ?>">
								<div class="my-team-member">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
										
										<ul class="my-member-social">
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 43 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 44 ): ?>
					<!-- Team Style 44 -->	
					<div class="my-team team-style-44 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="overlay"></div>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>	
									<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
								</div>
								<div class="my-member-social">
									<ul>
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 44 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 45 ): ?>
					<!-- Team Style 45 -->	
					<div class="my-team team-style-45 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-team-member-bg">
									<div class="my-member-img">
										<?php if( $wmt_options['image_'.$i] != '' ){ ?>
											<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
		                                <?php } ?>
										<div class="overlay"></div>
									</div>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<hr>	
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
									</div>
									<div class="my-member-social">
										<ul>
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 45 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 46 ): ?>
					<!-- Team Style 46 -->	
					<div class="my-team team-style-46 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>	
									<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
								</div>
								<div class="my-member-social">
									<ul>
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 46 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 47 ): ?>
					<!-- Team Style 47 -->
					<div class="my-team team-style-47 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-block <?php echo $column_class; ?>">
								<div class="my-team-member">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
										<ul class="my-member-social">
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>		
					</div>		
					<!-- End Team Style 47 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 48 ): ?>
					<!-- Team Style 48 -->
					<div class="my-team team-style-48 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-block <?php echo $column_class; ?>">
								<div class="my-team-member">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
									<div class="my-team-detail">
										<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
										<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
										<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
										<ul class="my-member-social">
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>		
					</div>		
					<!-- End Team Style 48 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 49 ): ?>
					<!-- Team Style 49 -->	
					<div class="my-team team-style-49 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<hr>	
									<p class="my-member-details"><?php echo $wmt_options['detail_'.$i]; ?></p>
								</div>
								<div class="my-member-social">
									<ul>
										<?php 
										if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
											foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
												if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
												?>
												<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
											<?php 
												endif;
											endforeach;
										endif; ?>
									</ul>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 49 -->
				<?php endif; ?>
				<?php if( $wmt_options['wmt_style'] == 50 ): ?>
					<!-- Team Style 50 -->	
					<div class="my-team team-style-50 <?php echo $container; ?>">
						<div class="row">
							<div class="section text-center">
								<h2 class="my-section-title"><?php echo esc_attr( $wmt_options['wmt_title'] ); ?></h2>
							</div>
							<?php for( $i = 1; $i <= $wmt_columns; $i++ ){ ?>
							<div class="my-team-member <?php echo $column_class; ?>">
								<div class="my-member-img">
									<?php if( $wmt_options['image_'.$i] != '' ){ ?>
										<img src="<?php echo $wmt_options['image_'.$i]; ?>" class="img-responsive" alt="img">
	                                <?php } ?>
								</div>
								<div class="my-team-detail">
									<h6 class="my-member-name"><?php echo $wmt_options['name_'.$i]; ?></h6>
									<p class="my-member-post"><?php echo $wmt_options['designation_'.$i]; ?></p>
									<div class="my-member-social">
										<ul>
											<?php 
											if( isset( $wmt_options[ 'social_url_'.$i] ) && !empty( $wmt_options[ 'social_url_'.$i] ) ): 
												foreach( $wmt_options[ 'social_url_'.$i] as $key=>$social ): 
													if( !empty($wmt_options[ 'social_url_'.$i][$key] )):
													?>
													<li><a href="<?php echo esc_url( $wmt_options[ 'social_url_'.$i][$key] ); ?>" target="_blank"><i class="<?php echo $wmt_options[ 'icon_'.$i][$key]; ?>"></i></a></li>
												<?php 
													endif;
												endforeach;
											endif; ?>
										</ul>
									</div>				
								</div>				
							</div>
							<?php } ?>
						</div>
					</div>
					<!-- End Team Style 50 -->	
				<?php endif; ?>

				
			<?php endif; ?>
			<?php return ob_get_clean(); ?>
		<?php }
	endif; 
}

/**
 * Public enqueue script
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
if( ! function_exists( 'wmt_public_script' )): 
	add_action( 'wp_enqueue_scripts', 'wmt_public_script' );
	function wmt_public_script() { 
		
		$wmt_support = isset( $wmt_options['wmt_support'] ) ? $wmt_options['wmt_support'] : "";
		if( empty( $wmt_support ) ){
			wp_register_style( 'admin-wmt-bootstrap-formstyle', wp_wmt_myteam()->css_uri . 'bootstrap.min.css' );
		}
		wp_register_style( 'admin-wmt-fontawesome', wp_wmt_myteam()->css_uri . 'font-awesome.min.css' );
		wp_register_style( 'admin-wmt-ubuntu-font', 'https://fonts.googleapis.com/css?family=Ubuntu:400,700' );
		global $post;
		
	}
endif;