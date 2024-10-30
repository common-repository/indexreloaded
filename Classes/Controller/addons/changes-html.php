<?php
/**
 *  Changes in html.
 *
 *  The Indexreloaded file changes-html.php extends indexreloaded-class, it implements additional processing on the html.
 *
 *  @package Indexreloaded\Classes
 */

defined( 'ABSPATH' ) || die( -1 );
// changing ' to " in link-tags, fixing triple-/ typo errors, setting baseurl notation in stylesheet links.
$corrections = explode( '<link ', $buffer );
$crcnt       = count( $corrections );
if ( $crcnt > 1 ) {
	for ( $i = 1; $i < $crcnt; $i++ ) {
		$correctionstags    = explode( '>', $corrections[ $i ] );
		$correctionstags[0] = str_replace( "'", '"', $correctionstags[0] );
		$correctionstags[0] = str_replace( '  ', ' ', $correctionstags[0] );
		if ( str_replace( 'stylesheet', '', $correctionstags[0] ) !== $correctionstags[0] ) {
			$correctionstypotremens = explode( '//', $correctionstags[0] );
			if ( count( $correctionstypotremens ) > 1 ) {
				$correctionstags[0] = implode( '/', $correctionstypotremens );
				$correctionstags[0] = str_replace( 'https:/', 'https://', $correctionstags[0] );
				$correctionstags[0] = str_replace( 'http:/', 'http://', $correctionstags[0] );
				$correctionstags[0] = str_replace( 'href="/ajax', 'href="//ajax', $correctionstags[0] );
				$correctionstags[0] = str_replace( 'href="/cdn', 'href="//cdn', $correctionstags[0] );
				$correctionstags[0] = str_replace( 'href="/fonts', 'href="//fonts', $correctionstags[0] );
				$correctionstags[0] = str_replace( 'href="/use', 'href="//use', $correctionstags[0] );
			}
		}

		if ( '' !== $baseurl ) {
			if ( str_replace( 'stylesheet', '', $correctionstags[0] ) !== $correctionstags[0] ) {
				$baseurlraw         = str_replace( 'https://', '', $baseurl );
				$baseurlraw         = str_replace( 'http://', '', $baseurlraw );
				$correctionstags[0] = str_replace( $baseurl, '/', $correctionstags[0] );
				$correctionstags[0] = str_replace( '/' . $baseurlraw, '/', $correctionstags[0] );
			}
		}

		$corrections[ $i ] = implode( '>', $correctionstags );

	}

	$buffer = implode( '<link ', $corrections );
}

// changing ' to " in style-tags.
$corrections = explode( '<style ', $buffer );
$crcnt       = count( $corrections );
if ( $crcnt > 1 ) {
	for ( $i = 1; $i < $crcnt; $i++ ) {
		$correctionstags    = explode( '>', $corrections[ $i ] );
		$correctionstags[0] = str_replace( "'", '"', $correctionstags[0] );
		$correctionstags[0] = str_replace( '  ', ' ', $correctionstags[0] );
		$corrections[ $i ]  = implode( '>', $correctionstags );
	}

	$buffer = implode( '<style ', $corrections );
}

// cleaning some potential w3c.org-crimes.
$buffer = str_replace( 'itemprop="logo"', '', $buffer );
$buffer = str_replace( 'itemprop="url"', '', $buffer );

// clean "Archive*"-strings in pagetitle.
if ( true === $this->clean_archive_strings_in_pagetitle ) {
	$buffer = str_replace( 'Archives - ', '', $buffer );
	$buffer = str_replace( 'Archives des ', '', $buffer );
	$buffer = str_replace( 'Archive - ', '', $buffer );
}

// determine page language and convert to (almost every) Language Code.
$wplocale    = determine_locale();
$wplocalearr = explode( '_', $wplocale );
if ( 2 === count( $wplocalearr ) ) {
	$lan = $wplocalearr[0];
} else {
	$lan = $wplocale;
}

// add language to potential render=explicit statements.
if ( str_replace( 'lang="render=explicit', '', $buffer ) !== $buffer ) {
	$buffer = str_replace( 'render=explicit&#038;', 'render=explicit&#038;hl=' . $lan . '&#038;', $buffer );
	$buffer = str_replace( '&#038;render=explicit" async', '&#038;render=explicit&#038;hl=' . $lan . '" async', $buffer );
}

// remove generator metatags.
if ( true === $this->clean_plugin_notes ) {
	$corrections = explode( '<meta name="generator"', $buffer );
	$crcnt       = count( $corrections );
	if ( $crcnt > 1 ) {
		for ( $i = 1; $i < $crcnt;$i++ ) {
			$correctionstags   = explode( '>', $corrections[ $i ] );
			$forget            = array_shift( $correctionstags );
			$corrections[ $i ] = implode( '>', $correctionstags );
		}

		$buffer = implode( '', $corrections );
	}
}

// removing pingback links.
if ( true === $this->remove_pingbacks ) {
	$bufferarr = explode( '<link rel="pingback" ', $buffer );
	if ( 2 === count( $bufferarr ) ) {
		$startfrom = strpos( $bufferarr[1], '>' ) + 1;
		$buffer    = $bufferarr[0] . substr( $bufferarr[1], $startfrom );
	}
}
// removing rss xml links.
if ( true === $this->remove_rss ) {
	$bufferarr = explode( '<link rel="alternate" type="application/rss+xml" title="', $buffer );
	if ( 3 === count( $bufferarr ) ) {
		$startfrom = strpos( $bufferarr[2], '>' ) + 1;
		$buffer    = $bufferarr[0] . '<link rel="alternate" type="application/rss+xml" title="' . $bufferarr[1] . substr( $bufferarr[2], $startfrom );
	}

	$bufferarr = explode( '<link rel="alternate" type="application/rss+xml" title="', $buffer );
	if ( 2 === count( $bufferarr ) ) {
		$startfrom = strpos( $bufferarr[1], '>' ) + 1;
		$buffer    = $bufferarr[0] . substr( $bufferarr[1], $startfrom );
	}
}
// removing HTML-comments.
if ( true === $this->clean_plugin_notes ) {
	$corrections = explode( '<!-- ', $buffer );
	$crcnt       = count( $corrections );
	if ( $crcnt > 1 ) {
		for ( $i = 1; $i < $crcnt;$i++ ) {
			if ( ( 'ko ' === substr( $corrections[ $i ], 0, 3 ) ) || ( '/ko' === substr( $corrections[ $i ], 0, 3 ) ) ) {
				$corrections[ $i ] = '<!-- ' . $corrections[ $i ];
			} else {
				$correctionstags   = explode( ' -->', $corrections[ $i ] );
				$forget            = array_shift( $correctionstags );
				$corrections[ $i ] = implode( ' -->', $correctionstags );
			}
		}

		$buffer = implode( '', $corrections );
	}
}

// Fix missing alt in img tags.
$corrections = explode( '<img', $buffer );
$crcnt       = count( $corrections );
if ( $crcnt > 1 ) {
	for ( $i = 1; $i < $crcnt;$i++ ) {
		$str_expl_full         = '/>';
		$str_expl              = '>';
		$correctionstags_full  = explode( $str_expl_full, $corrections[ $i ] );
		$len_elem_zero_full    = strlen( $correctionstags_full[0] );
		$correctionstags_red   = explode( $str_expl, $corrections[ $i ] );
		$len_elem_zero_reduced = strlen( $correctionstags_red[0] );
		if ( $len_elem_zero_full < $len_elem_zero_reduced ) {
			$implode_str     = $str_expl_full;
			$correctionstags = $correctionstags_full;
		} else {
			$implode_str     = $str_expl;
			$correctionstags = $correctionstags_red;
		}

		$imagetagtext = $correctionstags[0];
		if ( str_replace( '\\', '', $imagetagtext ) === $imagetagtext ) {
			if ( str_replace( 'alt="', '', $imagetagtext ) === $imagetagtext ) {
				// no alt present.
				$alt_text = '';
				if ( str_replace( ' title="', '', $imagetagtext ) !== $imagetagtext ) {
					// but title present.
					$imagetagtextarr    = explode( ' title="', $imagetagtext );
					$imagetagtextarrarr = explode( '"', $imagetagtextarr[1] );
					$title_text         = $imagetagtextarrarr[0];
					$alt_text           = $title_text;
				}

				$imagetagtext       = $imagetagtext . ' alt="' . $alt_text . '" ';
				$correctionstags[0] = str_replace( '  ', ' ', $imagetagtext );
			}
			if ( str_replace( 'width="', '', $imagetagtext ) === $imagetagtext ) {
				// no width present.
				$src_arr = explode( 'src="', $imagetagtext );
				if ( isset( $src_arr[1] ) ) {
					$imagetagtextarrarr = explode( '"', $src_arr[1] );
					$height             = '1';
					$width              = '1';
					if ( str_replace( 'https://', '', $imagetagtextarrarr[0] ) === $imagetagtextarrarr[0] ) {
						$src_text = realpath( str_replace( 'wp-content\plugins\indexreloaded\Classes\Controller\addons', '', str_replace( 'wp-content/plugins/indexreloaded/Classes/Controller/addons', '', __DIR__ ) ) ) . DIRECTORY_SEPARATOR .
						str_replace( '/', DIRECTORY_SEPARATOR, $imagetagtextarrarr[0] );
						if ( str_replace( '.svg', '', $src_text ) === $src_text ) {
							if ( str_replace( '.pdf', '', $src_text ) !== $src_text ) {
								$box             = 'BleedBox';
								$widthheight_arr = $this->get_pdf_dimensions( $src_text, $box );
								if ( isset( $widthheight_arr['width'] ) ) {
									$width  = $widthheight_arr['width'];
									$height = $widthheight_arr['height'];
								}
							} elseif ( file_exists( $src_text ) ) {
									list( $width, $height, $imagetype, $attr ) = getimagesize( $src_text );
							}
						} else {
							$svg_contents     = file_get_contents( $src_text ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
							$svg_contents_arr = explode( 'width="', $svg_contents );
							if ( count( $svg_contents_arr ) > 1 ) {
								$svg_contents_1     = $svg_contents_arr[1];
								$svg_contents_1_arr = explode( '"', $svg_contents_1 );
								$width              = $svg_contents_1_arr[0];
							}
							$svg_contents_arr = explode( 'height="', $svg_contents );
							if ( count( $svg_contents_arr ) > 1 ) {
								$svg_contents_1     = $svg_contents_arr[1];
								$svg_contents_1_arr = explode( '"', $svg_contents_1 );
								$height             = $svg_contents_1_arr[0];
							}
						}
					}
					$width_text         = $width;
					$imagetagtext       = $imagetagtext . ' width="' . $width_text . '" ';
					$correctionstags[0] = str_replace( '  ', ' ', $imagetagtext );
					if ( str_replace( 'height="', '', $imagetagtext ) === $imagetagtext ) {
						// no height present.
						$height_text        = $height;
						$imagetagtext       = $imagetagtext . ' height="' . $height_text . '" ';
						$correctionstags[0] = str_replace( '  ', ' ', $imagetagtext );
					}
				}
			}
		}

		$corrections[ $i ] = implode( $implode_str, $correctionstags );
	}

	$buffer = implode( '<img', $corrections );
}
