<?php
/**
 *  Final-changes-html
 *
 *  The Indexreloaded file final-changes-html.php extends indexreloaded-class, it implements additional, final processing on the html
 *
 *  @package Indexreloaded\Classes
 */

defined( 'ABSPATH' ) || die( -1 );
// Remove the <link rel="shortlink" href="https://site/?p=1821" />, based on option remove_shortlink.
if ( true === $this->remove_shortlink ) {
	$csswrkarr = explode( '<link rel="shortlink" href="', $bufferout );
	if ( count( $csswrkarr ) > 1 ) {
		$csswrkarrmtt = explode( '" />', $csswrkarr[1] );
		array_shift( $csswrkarrmtt );
		$csswrkarr[1] = implode( '" />', $csswrkarrmtt );
		$bufferout    = implode( '', $csswrkarr );
	}
}

// Add display-font to fonts.googleapis.com/css-fonts, if missing.
$csswrkarr = explode( 'href="https://fonts.googleapis.com/css', $bufferout );
if ( count( $csswrkarr ) > 1 ) {
	$csswrkarrmtt = explode( '"', $csswrkarr[1] );
	if ( str_replace( 'display=swap', '', $csswrkarrmtt[0] ) === $csswrkarrmtt[0] ) {
		$csswrkarrmtt[0] = $csswrkarrmtt[0] . '&display=swap';
		$csswrkarr[1]    = implode( '"', $csswrkarrmtt );
	}

	$bufferout = implode( 'href="https://fonts.googleapis.com/css', $csswrkarr );
}

// Fix twittercards.
$bufferout = str_replace( '<meta name="twitter:', '<meta property="twitter:', $bufferout );

// Add preload tags.
if ( '' === trim( $this->preload_tag ) ) {
	if ( str_replace( '<meta name="viewport"', '', $bufferout ) !== $bufferout ) {
		$this->preload_tag = '<meta name="viewport"';
	}
}
if ( '' !== $this->preload_tag ) {
	$preloadfonts = $this->preloadfontsarr;
	if ( 0 !== ( count( $this->preloadscriptsarr ) + count( $this->preloadstylesarr ) ) ) {
		$preloadstr    = '';
		$preloadwrkarr = explode( $this->preload_tag, $bufferout );
		if ( count( $preloadwrkarr ) > 1 ) {
			if ( '' !== trim( $this->preloadimage ) ) {
				$preloadimages = explode( ',', $this->preloadimage );
				if ( count( $preloadimages ) > 0 ) {
					foreach ( $preloadimages as $preload_img ) {
						$preloadimagearr  = explode( '.', $preload_img );
						$preloadimageext  = $preloadimagearr[ count( $preloadimagearr ) - 1 ];
						$preloadimagetype = $preloadimageext;
						if ( 'jpg' === $preloadimageext ) {
							$preloadimagetype = 'jpeg';
						}

						unset( $preloadimagearr[ count( $preloadimagearr ) - 1 ] );
						$preloadimagefilepathname     = implode( '.', $preloadimagearr );
						$preloadimagefilepathname_arr = explode( '/', trim( $preloadimagefilepathname ) );
						$preloadimage                 = end( $preloadimagefilepathname_arr );
						if ( str_replace( $preloadimage, '', $bufferout ) !== $bufferout ) {
							$preloadstr .= '<link rel="preload" fetchpriority="high" as="image" href="' . trim( $preloadimagefilepathname ) . '.' . trim( $preloadimageext ) . '" type="image/' . trim( $preloadimagetype ) . '">' . "\n";
						}
					}
				}
			}

			if ( count( $preloadfonts ) > 0 ) {
				foreach ( $preloadfonts as $preloadfont ) {
					if ( '' !== trim( $preloadfont ) ) {
						$preloadfontarr  = explode( '.', $preloadfont );
						$preloadfontext  = $preloadfontarr[ count( $preloadfontarr ) - 1 ];
						$preloadfonttype = ' type="font/' . $preloadfontext . '"';
						if ( 'eot' === $preloadfontext ) {
							$preloadfonttype = '';
						}

						unset( $preloadfontarr[ count( $preloadfontarr ) - 1 ] );
						$preloadfontfilepathname     = implode( '.', $preloadfontarr );
						$preloadfontfilepathname_arr = explode( '/', trim( $preloadfontfilepathname ) );
						$preloadfont                 = end( $preloadfontfilepathname_arr );
						if ( str_replace( $preloadfont, '', $bufferout ) !== $bufferout ) {
							$preloadstr .= '<link rel="preload" href="' . trim( $preloadfontfilepathname ) . '.' . trim( $preloadfontext ) . '" as="font"' . trim( $preloadfonttype ) . ' crossorigin="anonymous">' . "\n";
						}
					}
				}
			}

			if ( 0 !== count( $this->preloadscriptsarr ) ) {
				foreach ( $this->preloadscriptsarr as $preloadscript ) {
					if ( '' !== trim( $preloadscript ) ) {
						$preloadscript_arr = explode( '/', trim( $preloadscript ) );
						$preloadjs         = end( $preloadscript_arr );
						if ( str_replace( $preloadjs, '', $bufferout ) !== $bufferout ) {
							$preloadstr .= '<link rel="preload" href="' . trim( $preloadscript ) . '" as="script">' . "\n";
						}
					}
				}
			}

			if ( 0 !== count( $this->preloadstylesarr ) ) {
				foreach ( $this->preloadstylesarr as $preloadstyle ) {
					if ( '' !== trim( $preloadstyle ) ) {
						$preloadstyle_arr = explode( '/', trim( $preloadstyle ) );
						$preloadcss       = end( $preloadstyle_arr );
						if ( str_replace( $preloadcss, '', $bufferout ) !== $bufferout ) {
							$preloadstr .= '<link rel="preload" href="' . trim( $preloadstyle ) . '" as="style">' . "\n";
						}
					}
				}
			}

			$preloadwrkarr[0] = $preloadwrkarr[0] . $preloadstr;
			$bufferout        = implode( $this->preload_tag, $preloadwrkarr );
		}
	}
}
