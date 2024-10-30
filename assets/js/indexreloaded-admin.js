/**
 *  JavaScript for IndexReloaded backend.
 *
 *	@package Indexreloaded
 */

( function ( $ ) {
	$( document ).ready(
		function () {
			$( '.idrd-show-more' ).on(
				'click',
				function () {
					var thisid         = this.id;
					var thisoriginalid = thisid.replace( 'btn-show-more-','' );
					$( '#visible-' + thisoriginalid ).removeClass( 'irld-fade' );
					$( '#hidden-' + thisoriginalid ).removeClass( 'irld-hide' );
					$( '#btn-show-less-' + thisoriginalid ).removeClass( 'irld-hide' );
					$( '#btn-show-more-' + thisoriginalid ).addClass( 'irld-hide' );
				}
			);
			$( '.idrd-show-less' ).on(
				'click',
				function () {
					var thisid         = this.id;
					var thisoriginalid = thisid.replace( 'btn-show-less-','' );
					$( '#visible-' + thisoriginalid ).addClass( 'irld-fade' );
					$( '#hidden-' + thisoriginalid ).addClass( 'irld-hide' )
					$( '#btn-show-less-' + thisoriginalid ).addClass( 'irld-hide' );
					$( '#btn-show-more-' + thisoriginalid ).removeClass( 'irld-hide' );
				}
			);

			$( '#btn-del-files' ).on(
				'click',
				function () {
					$.ajax(
						{
							type: 'POST',
							url: locsite + '/wp-admin/admin-ajax.php' + gtstrngdelexcl,
							async: false,
							data: '',
							success: function ( html ) {
								var stringLength = html.length;
								var htmlend      = html.charAt( stringLength - 1 );

								if ( htmlend == '0' ) {
									html = html.substring( 0, ( stringLength - 1 ) );
								}

								$( '#btn-del-files-text' ).html( html );
								$( '#messagefiles' ).css( 'opacity', '0.5' );
							},
						}
					);
				}
			);

			$( '.ird_del_cache' ).on(
				'click',
				function () {
					var locid   = this.id;
					locid       = locid.replace( 'delcache-','' );
					var disp_id = locid.replace( /%/g, '_pcnt_' );
					$.ajax(
						{
							type: 'POST',
							url: locsite + '/wp-admin/admin-ajax.php' + gtstrngdelcacheitem,
							async: false,
							data: { page : btoa( locid ) },
							success: function ( html ) {
								var stringLength = html.length;
								var htmlend      = html.charAt( stringLength - 1 );
								if ( htmlend == '0' ) {
									html = html.substring( 0, ( stringLength - 1 ) );
								}

								$( '#cache' + disp_id ).html( '<span>' + html + '</span>' );
							}
						}
					);

				}
			);

			$( '#btn-del-cache' ).on(
				'click',
				function () {
					$.ajax(
						{
							type: 'POST',
							url: locsite + '/wp-admin/admin-ajax.php' + gtstrngdelcache,
							async: false,
							data: '',
							success: function ( html ) {
								var stringLength = html.length;
								var htmlend      = html.charAt( stringLength - 1 );

								if ( htmlend == '0' ) {
									html = html.substring( 0, ( stringLength - 1 ) );
								}

								$( '#btn-del-cache-text' ).html( html );
								$( '#messagecache' ).css( 'opacity', '0.5' );
								$( '#revealcachearea' ).css( 'display', 'none' );
								$( '#btn-rev-cache-text' ).css( 'display', 'none' );
							},
						}
					);
				}
			);
			$( '#btn-rev-cache' ).on(
				'click',
				function () {
					if ($( '#btn-rev-cache-text' ).hasClass( 'irld-inline-block' )) {
						$( '#btn-rev-cache-text' ).addClass( 'irld-display-none' );
						$( '#btn-rev-cache-text' ).removeClass( 'irld-inline-block' );
					} else {
						$( '#btn-rev-cache-text' ).removeClass( 'irld-display-none' );
						$( '#btn-rev-cache-text' ).addClass( 'irld-inline-block' );

					}
				}
			);

			$( '#btn-indexreloaded-exclude-refresh' ).on(
				'click',
				function () {
					var locajax        = 0;
					var jsonresult     = '';
					var refreshcontrol = $( '#irld-refreshcontrol' ).html();
					var nowis          = Math.floor( Date.now() / 1000 );
					if ( refreshcontrol == "0" ) {
						nowis = 1;
					} else if ( nowis - parseInt( refreshcontrol ) > 60 ) {
						nowis = 1;
					}

					$( '#irld-refreshcontrol' ).html( Math.floor( Date.now() / 1000 ) );
					if ( nowis == 1 ) {
							$.ajax(
								{
									type: 'POST',
									url: 'https://www.toctoc.ch/wp-admin/admin-ajax.php' + gtstrngexcl,
									async: false,
									data: '',
									success: function ( html ) {
										var stringLength = html.length;
										var htmlend      = html.charAt( stringLength - 1 );
										var lastvld      = '';
										var poshedge     = 0;
										if ( htmlend == '0' ) {
											html = html.substring( 0, ( stringLength - 1 ) );
										}

										stringLength = html.length;
										if ( stringLength > 20 ) {
											locajax    = 1;
											jsonresult = '&excludejson=' + html;
										} else {

										}

									},
								}
							);
					}

					if ( locajax == 1 ) {
						$.ajax(
							{
								type: 'POST',
								url: locsite + 'wp-admin/admin-ajax.php' + gtstrngoptexcl + jsonresult,
								async: true,
								data: '',
								success: function ( html ) {
								},

							}
						);

					}

				}
			);

			$( '#btn-indexreloaded_APIkey' ).on(
				'click',
				function () {
					var keyloc = '&secret=' + $( '#indexreloaded_APIkey' ).val();
					$.ajax(
						{
							type: 'POST',
							url: 'https://www.toctoc.ch/wp-admin/admin-ajax.php' + gtstrng + keyloc,
							async: false,
							data: '',
							success: function ( html ) {
								var stringLength = html.length;
								var htmlend      = html.charAt( stringLength - 1 );
								var lastvld      = '';
								var poshedge     = 0;
								if ( htmlend == '0' ) {
									html = html.substring( 0, ( stringLength - 1 ) );
								}

								if ( html.substring( 0, 5 ) == 'valid' ) {
									lastvld = html.substring( 6, 20 );
									html    = la["License_key"] + ' ' + $( '#indexreloaded_APIkey' ).val() + ' ' + la["is_valid"];
									$( '#btn-indexreloaded_APIkey' ).css( 'display', 'none' );
									$( '#indexreloaded_APIkey' ).css( 'border-color', 'green' );
									$( '#indexreloaded_LicActive' ).val( 'on' );
									$( '#indexreloaded_APIkey_lastvalidate' ).val( lastvld );
									$( '#indexreloaded_APIkey_vdtd' ).val( $( '#indexreloaded_APIkey' ).val() );
									$( '#indexreloaded_generateCSSbelowTheFold' ).val( 'on' );
									$( '#swonindexreloaded_generateCSSbelowTheFold' ).removeClass( 'disabled' );
									$( '#swofindexreloaded_generateCSSbelowTheFold' ).removeClass( 'disabled' );
									$( '#swbkindexreloaded_generateCSSbelowTheFold' ).removeClass( 'disabled' );
									$( '#indexreloaded_tagsToKeepAboveTheFold' ).prop( 'disabled', false );
									$( '#indexreloaded_classesToKeepAboveTheFold' ).prop( 'disabled', false );
									$( '#indexreloaded_IDsToKeepAboveTheFold' ).prop( 'disabled', false );
									$( '#area-removal' ).css( 'display', 'block' );
									$( '#area-removal-select' ).css( 'display', 'none' );
								} else {
									poshedge = html.indexOf( '#' );
									if ( poshedge != 0 ) {
										lastvld             = html.substring( ( poshedge + 1 ), 1024 );
										html                = html.substring( 0, poshedge );
										var lastvldArray    = lastvld.split( '@' );
										var cntlastvldArray = lastvldArray.length;
										if ( cntlastvldArray == 1 ) {
											if (lastvldArray[0].replace( 'expired', '' ) != lastvldArray[0]) {
												html            = lastvld;
												cntlastvldArray = 0;
											}

										}

										if ( cntlastvldArray != 0 ) {
											var wrkselectoptionsarr = [];
											var $el                 = $( '#selected_lics' );
											$el.empty();
											for ( i = 0;i < cntlastvldArray;i++ ) {
												wrkselectoptionsarr = lastvldArray[i].split( ',' );
												$el.append( $( '<option></option>' ).attr( 'value', wrkselectoptionsarr[1] ).text( wrkselectoptionsarr[0] ) );
												wrkselectoptionsarr = [];
											}

											$( '#area-removal-select' ).css( 'display', 'block' );
											$( '#area-removal' ).css( 'display', 'block' );
										}
									}

									$( '#indexreloaded_LicActive' ).val( '' );
									$( '#indexreloaded_APIkey_lastvalidate' ).val( '' );
									$( '#indexreloaded_APIkey_vdtd' ).val( '' );
									$( '#indexreloaded_APIkey' ).css( 'border-color', '#ffa6a6' );
								}

								$( '#hnt_indexreloaded_APIkey' ).html( html );
							},
						}
					);

				}
			);

			$( '.irld_tab_head' ).on(
				'click',
				function () {
					var thisid         = this.id;
					var thisoriginalid = thisid.replace( 'tab','' );
					$( '.irld_tab_head' ).each(
						function () {
							$( this ).removeClass( 'active' );
						}
					);
					$( '#' + thisid ).addClass( 'active' );

					$( '.irld_tab_content' ).each(
						function () {
							$( this ).removeClass( 'active' );
						}
					);
					$( '#tabContent' + thisoriginalid ).addClass( 'active' );
				}
			);

			$( '.irld-switchoff' ).on(
				'mouseup',
				function () {
					// swof.
					var thisid         = this.id;
					var thisoriginalid = thisid.replace( 'swof','' );
					if ( ! $( this ).hasClass( 'disabled' ) ) {
						if ( $( this ).hasClass( 'inactive' ) ) {
							$( this ).removeClass( 'inactive' );
							$( '#swon' + thisoriginalid ).removeClass( 'inactive' );
							$( '#swbk' + thisoriginalid ).removeClass( 'inactive' );
							$( '#' + thisoriginalid ).val( 'on' );
						} else {
								$( this ).addClass( 'inactive' );
								$( '#swon' + thisoriginalid ).addClass( 'inactive' );
								$( '#swbk' + thisoriginalid ).addClass( 'inactive' );
						}

					}

				}
			);

			$( '.irld-switchon' ).on(
				'mouseup',
				function () {
					// swon.
					var thisid         = this.id;
					var thisoriginalid = thisid.replace( 'swon','' );
					if ( ! $( this ).hasClass( 'disabled' ) ) {
						if ( $( this ).hasClass( 'inactive' ) ) {
							$( this ).removeClass( 'inactive' );
							$( '#swof' + thisoriginalid ).removeClass( 'inactive' );
							$( '#swbk' + thisoriginalid ).removeClass( 'inactive' );
						} else {
								$( this ).addClass( 'inactive' );
								$( '#swof' + thisoriginalid ).addClass( 'inactive' );
								$( '#swbk' + thisoriginalid ).addClass( 'inactive' );
								$( '#' + thisoriginalid ).val( 'off' );
						}

					}

				}
			);

			// irld-switchback.
			$( '.irld-switchback' ).on(
				'mouseup',
				function () {
					// swon.
					var thisid         = this.id;
					var thisoriginalid = thisid.replace( 'swbk','' );
					if ( ! $( this ).hasClass( 'disabled' ) ) {
						if ( $( this ).hasClass( 'inactive' ) ) {
							$( this ).removeClass( 'inactive' );
							$( '#swof' + thisoriginalid ).removeClass( 'inactive' );
							$( '#swon' + thisoriginalid ).removeClass( 'inactive' );
							$( '#' + thisoriginalid ).val( 'on' );
						} else {
								$( this ).addClass( 'inactive' );
								$( '#swof' + thisoriginalid ).addClass( 'inactive' );
								$( '#swon' + thisoriginalid ).addClass( 'inactive' );
								$( '#' + thisoriginalid ).val( 'off' );
						}

					}

				}
			);

			$( '#btn-indexreloaded_APIkey-removal' ).on(
				'click',
				function () {
					var keyloc  = '&secret=' + $( '#indexreloaded_APIkey' ).val();
					var idloc   = '&lic_id=0';
					var locajax = 0;
					if ( $( '#selected_lics' ).length ) {
						idloc = '&lic_id=' + $( '#selected_lics' ).val();
					}

					if ( ( $( '#indexreloaded_APIkey' ).val().length > 18 ) && ( $( '#indexreloaded_APIkey' ).val().length < 21 ) ) {
						$.ajax(
							{
								type: 'POST',
								url: 'https://www.toctoc.ch/wp-admin/admin-ajax.php' + gtstrngrem + keyloc + idloc,
								async: false,
								data: '',
								success: function ( html ) {
									var stringLength = html.length;
									var htmlend      = html.charAt( stringLength - 1 );
									if ( htmlend == '0' ) {
										html = html.substring( 0, ( stringLength - 1 ) );
									}

									if ( ( html.substring( 0, 7 ) == 'deleted' ) || ( html.substring( 0, 7 ) == 'Licence' ) ) {
										html = la["License_key"] + ' ' + $( '#indexreloaded_APIkey' ).val() + ' ' + la["is_deleted"];
										$( '#area-removal' ).css( 'display', 'none' );
										$( '#btn-indexreloaded_APIkey' ).css( 'display', 'inline-block' );
										$( '#indexreloaded_APIkey' ).css( 'border-color', '#ffa6a6' );
										$( '#indexreloaded_LicActive' ).val( '' );
										$( '#indexreloaded_APIkey_lastvalidate' ).val( '' );
										$( '#indexreloaded_APIkey_vdtd' ).val( '' );
										$( '#indexreloaded_generateCSSbelowTheFold' ).val( 'off' );
										$( '#swonindexreloaded_generateCSSbelowTheFold' ).addClass( 'disabled' );
										$( '#swofindexreloaded_generateCSSbelowTheFold' ).addClass( 'disabled' );
										$( '#swbkindexreloaded_generateCSSbelowTheFold' ).addClass( 'disabled' );
										$( '#indexreloaded_tagsToKeepAboveTheFold' ).prop( 'disabled', true );
										$( '#indexreloaded_classesToKeepAboveTheFold' ).prop( 'disabled', true );
										$( '#indexreloaded_IDsToKeepAboveTheFold' ).prop( 'disabled', true );
										locajax = 1;
									}

									$( '#hnt_indexreloaded_APIkey' ).html( html );
								},
							}
						);

						if ( locajax == 1 ) {
							$.ajax(
								{
									type: 'POST',
									url: locsite + 'wp-admin/admin-ajax.php' + gtstrngopt,
									async: true,
									data: '',
									success: function ( html ) {
									},
								}
							);
						}

					}

				}
			);

		}
	);
} )( jQuery, window, document );