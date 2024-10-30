<?php
/**
 *  Cssabovebelow
 *
 *  The Indexreloaded cssabovebelow-class splits CSS into CSS above the fold (critical CSS) and CSS below the fold (non-critical).
 *
 *  @package Indexreloaded\Classes
 */

defined( 'ABSPATH' ) || die( -1 );

/**
 * Cssabovebelow class.
 */
class Indexreloadedcssabovebelow {
	/**
	 * Tags found in model, array.
	 *
	 * @var array
	 */
	protected $bufferintags = array();
	/**
	 * Classes found in model, array.
	 *
	 * @var array
	 */
	protected $bufferinclasses = array();
	/**
	 * Input types found in model, array.
	 *
	 * @var array
	 */
	protected $bufferinputtypes = array();
	/**
	 * Ids found in model, array.
	 *
	 * @var array
	 */
	protected $bufferinids = array();
	/**
	 * Index of current filter for selector filtering.
	 *
	 * @var int
	 */
	protected $filterindex = 0;
	/**
	 * Number of filters for selector filtering.
	 *
	 * @var int
	 */
	protected $countfilters = 0;
	/**
	 * Array holding elements for filtering during selector filtering.
	 *
	 * @var array
	 */
	protected $filterelements = array();
	/**
	 * Parent object ($this of class-indexreloaded).
	 *
	 * @var object
	 */
	protected $p_obj;
	/**
	 * Enables and disables tracking of complex selectors to irld_trace_log.
	 *
	 * @var boolean
	 */
	protected $track_heavy_selectors = false;
	/**
	 * Enables and disables tracking of individual selectors to irld_trace_log.
	 *
	 * @var boolean
	 */
	protected $traceitrm_follow = false;
	/**
	 * Array holding selectors to track down to irld_trace_log.
	 *
	 * @var array
	 */
	protected $selectors_to_track = array( '' );
	/**
	 * Main function, called by class-indexreloaded.
	 *
	 * @param array  $post_in Array with data to process.
	 * @param object $p_obj parent object ($this of class-indexreloaded).
	 * @return array
	 */
	public function main( $post_in, &$p_obj ) {
		$this->p_obj = $p_obj;
		$data_str    = '';
		$data        = array();
		foreach ( $post_in as $postkey => $postvar ) {
			$data[ $postkey ] = $postvar;
		}

		$datavar          = $data['data'];
		$dataarray        = $datavar;
		$data['data']     = array();
		$data['data']     = $dataarray;
		$thisconf         = $data;
		$echo             = '';
		$spiltselector    = '.indexreloadedcontrol{}';
		$total_length_in  = 0;
		$total_length_out = 0;
		if ( 1 === $thisconf['data']['func'] ) {
			$this->bufferinclasses  = $thisconf['data']['bufferinclasses'];
			$this->bufferinids      = $thisconf['data']['bufferinids'];
			$this->bufferintags     = $thisconf['data']['bufferintags'];
			$this->bufferinputtypes = $thisconf['data']['bufferinputtypes'];
			$dtf                    = array();
			$dtf['cssbelow']        = '';
			$dtf['cssabove']        = '';
			$dtf['oPosabove']       = '';
			$dtf['oPosbelow']       = '';
			$filecontent            = $thisconf['data']['filecontent'];
			$filecontentarr         = explode( $spiltselector, $filecontent );
			foreach ( $filecontentarr as $filecontentpart ) {
				if ( '' !== trim( $filecontentpart ) ) {
					$filecontentsplitkeyframes   = $this->extractkeyframe( $filecontentpart );
					$filecontentpart             = $filecontentsplitkeyframes[0];
					$filecontentkeyframes        = $filecontentsplitkeyframes[1];
					$filecontentkeyframesbelow   = $filecontentsplitkeyframes[2];
					$filecontentsplitunsupported = $this->extract_unsupported( $filecontentpart );
					$filecontentpart             = $filecontentsplitunsupported[0];
					$filecontentunsupportedbelow = $filecontentsplitunsupported[1];
					$filecontentunsupportedabove = $filecontentsplitunsupported[2];
					$filecontents                = $this->split_css_below_above_the_fold( $filecontentpart );

					if ( strlen( trim( $filecontents[0] . $filecontentunsupportedabove ) ) > 4 ) {
						$dtf['cssbelow']  .= $filecontents[1] . $filecontentkeyframesbelow . $filecontentkeyframes . $filecontentunsupportedbelow;
						$dtf['cssabove']  .= $filecontents[0] . $filecontentunsupportedabove;
						$dtf['oPosabove'] .= $filecontents[2];
						$dtf['oPosbelow'] .= $filecontents[3];
					}
				}
			}

			$cf_dtf               = $this->criticalfonts( $dtf['cssabove'], $dtf['cssbelow'] );
			$dtf['cssabove']      = $cf_dtf['cssabove'];
			$dtf['cssbelow']      = $cf_dtf['cssbelow'];
			$p_obj->criticalfonts = $this->p_obj->criticalfonts;
			$echo                 = $dtf;
		}

		return $echo;
	}

	/**
	 * Extracts @font selectors, manages and uses parents $criticalfonts-array, identifies critical fonts and puts them accordingly in css above or below
	 *
	 * @param string $cssabove The present critical CSS.
	 * @param string $cssbelow The present CSS below (non critical).
	 *
	 * @return array $ret
	 */
	private function criticalfonts( $cssabove, $cssbelow ) {
		$criticalfonts = $this->p_obj->criticalfonts;
		$at_fonts_arr  = explode( '@font', $cssabove );
		$cnt_at_fonts  = count( $at_fonts_arr );
		$newcssbelow   = $cssbelow;
		$newcssabove   = $at_fonts_arr[0];
		// is strong-tag in use?.
		$strongtag_present = false;
		if ( str_replace( '</strong>', '', $this->p_obj->modelbuffer_body ) !== $this->p_obj->modelbuffer_body ) {
			$strongtag_present = true;
		}
		if ( str_replace( '</h1', '', $this->p_obj->modelbuffer_body ) !== $this->p_obj->modelbuffer_body ) {
			$strongtag_present = true;
		}
		if ( str_replace( '</h2', '', $this->p_obj->modelbuffer_body ) !== $this->p_obj->modelbuffer_body ) {
			$strongtag_present = true;
		}
		for ( $f = 1; $f < $cnt_at_fonts; $f++ ) {
			$complete_at_font  = '@font';
			$fontfamily        = '';
			$fontweight        = '';
			$at_fonts_arr_sel  = explode( '}', $at_fonts_arr[ $f ] );
			$complete_at_font .= array_shift( $at_fonts_arr_sel ) . '}';

			$newcssabove .= implode( '}', $at_fonts_arr_sel );

			// seek font-family.
			$fontfamily_arr = explode( 'font-family:', $complete_at_font );
			if ( 2 === count( $fontfamily_arr ) ) {
				$fontfamily_text_arr = explode( ';', $fontfamily_arr[1] );
				$fontfamily          = str_replace( "'", '', str_replace( '"', '', $fontfamily_text_arr[0] ) );
			}
			// seek font-weight.
			$fontweight_arr = explode( 'font-weight:', $complete_at_font );
			if ( 2 === count( $fontweight_arr ) ) {
				$fontweight_text_arr = explode( ';', $fontweight_arr[1] );
				$fontweight          = str_replace( "'", '', str_replace( '"', '', $fontweight_text_arr[0] ) );
				if ( strtolower( trim( $fontweight ) ) === 'normal' ) {
					$fontweight = '400';
				} elseif ( strtolower( trim( $fontweight ) ) === 'medium' ) {
					$fontweight = '500';
				} elseif ( strtolower( trim( $fontweight ) ) === 'bold' ) {
					$fontweight = '700';
				} elseif ( strtolower( trim( $fontweight ) ) === 'semi bold' ) {
					$fontweight = '600';
				} elseif ( strtolower( trim( $fontweight ) ) === 'thin' ) {
					$fontweight = '100';
				} elseif ( strtolower( trim( $fontweight ) ) === 'extra light' ) {
					$fontweight = '200';
				} elseif ( strtolower( trim( $fontweight ) ) === 'light' ) {
					$fontweight = '300';
				} elseif ( strtolower( trim( $fontweight ) ) === 'extra bold' ) {
					$fontweight = '800';
				} elseif ( strtolower( trim( $fontweight ) ) === 'black' ) {
					$fontweight = '900';
				}
			}

			// seek unicode-range.
			$unicoderange_arr = explode( 'unicode-range:', $complete_at_font );
			$unicoderange     = '';
			if ( 2 === count( $unicoderange_arr ) ) {
				$unicoderange_text_arr = explode( ';', $unicoderange_arr[1] );
				$unicoderange          = trim( $unicoderange_text_arr[0] );
			}

			// seek src.
			$src_arr = explode( 'src:', $complete_at_font );
			$src     = '';
			if ( 2 === count( $src_arr ) ) {
				$src_text_arr = explode( ';', $src_arr[1] );
				$src          = trim( $src_text_arr[0] );
			}
			$found_cf = false;
			foreach ( $criticalfonts as $criticalfont ) {
				if ( $criticalfont['fontfamily'] === $fontfamily ) {
					if ( $criticalfont['fontweight'] === $fontweight ) {
						if ( $criticalfont['unicoderange'] === $unicoderange ) {
							if ( $criticalfont['src'] === $unicoderange ) {
								$found_cf = true;
								break;
							}
						}
					}
				}
			}

			if ( false === $found_cf ) {
				$criticalfont                 = array();
				$criticalfont['fontfamily']   = trim( $fontfamily );
				$criticalfont['fontweight']   = trim( $fontweight );
				$criticalfont['unicoderange'] = trim( $unicoderange );
				$criticalfont['src']          = trim( $src );
				$criticalfont['selector']     = $complete_at_font;
				$criticalfont['critical']     = false;
				$criticalfont['distributed']  = 0;
				$criticalfonts[]              = $criticalfont;
			}
		}

		// inspect $newcssabove for font-family's.
		// seek font-family.
		$fontfamily_arr = explode( 'font-family:', $newcssabove );
		$cnt_fontfamily = count( $fontfamily_arr );
		for ( $fi = 1; $fi < $cnt_fontfamily; $fi++ ) {
			$ff_arrbef               = explode( '{', $fontfamily_arr[ $fi - 1 ] );
			$selector_before         = array_pop( $ff_arrbef );
			$selector_before_inspect = implode( '{', $ff_arrbef );
			$ff_arr                  = explode( '}', $fontfamily_arr[ $fi ] );
			$selector_end            = array_shift( $ff_arr );
			$selector_end_inspect    = implode( '}', $ff_arr );
			$fontfamily_text_arr     = explode( ';', $fontfamily_arr[ $fi ] );
			$fontfamily              = str_replace( "'", '', str_replace( '"', '', $fontfamily_text_arr[0] ) );
			$fontfamilypart_arr      = explode( ',', $fontfamily );

			$fontweight = '';

			$fontweight_arr = explode( 'font-weight:', $selector_before );
			if ( 2 === count( $fontweight_arr ) ) {
				$fontweight_text_arr = explode( ';', $fontweight_arr[1] );
				$fontweight          = str_replace( '"', '', $fontweight_text_arr[0] );
				if ( strtolower( $fontweight ) === 'normal' ) {
					$fontweight = '400';
				} elseif ( strtolower( trim( $fontweight ) ) === 'medium' ) {
					$fontweight = '500';
				} elseif ( strtolower( trim( $fontweight ) ) === 'bold' ) {
					$fontweight = '700';
				} elseif ( strtolower( trim( $fontweight ) ) === 'semi bold' ) {
					$fontweight = '600';
				} elseif ( strtolower( trim( $fontweight ) ) === 'thin' ) {
					$fontweight = '100';
				} elseif ( strtolower( trim( $fontweight ) ) === 'extra light' ) {
					$fontweight = '200';
				} elseif ( strtolower( trim( $fontweight ) ) === 'light' ) {
					$fontweight = '300';
				} elseif ( strtolower( trim( $fontweight ) ) === 'extra bold' ) {
					$fontweight = '800';
				} elseif ( strtolower( trim( $fontweight ) ) === 'black' ) {
					$fontweight = '900';
				}
			} else {
				$fontweight_arr = explode( 'font-weight:', $selector_end );
				if ( 2 === count( $fontweight_arr ) ) {
					$fontweight_text_arr = explode( ';', $fontweight_arr[1] );
					$fontweight          = str_replace( '"', '', $fontweight_text_arr[0] );
					if ( strtolower( $fontweight ) === 'normal' ) {
						$fontweight = '400';
					} elseif ( strtolower( trim( $fontweight ) ) === 'medium' ) {
						$fontweight = '500';
					} elseif ( strtolower( trim( $fontweight ) ) === 'bold' ) {
						$fontweight = '700';
					} elseif ( strtolower( trim( $fontweight ) ) === 'semi bold' ) {
						$fontweight = '600';
					} elseif ( strtolower( trim( $fontweight ) ) === 'thin' ) {
						$fontweight = '100';
					} elseif ( strtolower( trim( $fontweight ) ) === 'extra light' ) {
						$fontweight = '200';
					} elseif ( strtolower( trim( $fontweight ) ) === 'light' ) {
						$fontweight = '300';
					} elseif ( strtolower( trim( $fontweight ) ) === 'extra bold' ) {
						$fontweight = '800';
					} elseif ( strtolower( trim( $fontweight ) ) === 'black' ) {
						$fontweight = '900';
					}
				} else {
					// font-weight not found or multiple instances.
					$fontweight = '400';
				}
			}

			// now let's check $criticalfonts for this font-family and font-weight.
			$found_cf = false;
			foreach ( $criticalfonts as &$criticalfont ) {
				foreach ( $fontfamilypart_arr as $fontfamily_elem ) {
					if ( trim( $fontfamily_elem ) === $criticalfont['fontfamily'] ) {
						if ( $criticalfont['fontweight'] === $fontweight ) {
							if ( 0 === $criticalfont['distributed'] ) {
								$fontdisplayswap_fixed_selector = $this->checkfont_display_swap( $criticalfont['selector'] );
								$newcssabove                    = $fontdisplayswap_fixed_selector . "\n" . $newcssabove;
								$criticalfont['critical']       = true;
								$criticalfont['distributed']    = 1;
							}
							$found_cf = true;
						} elseif ( true === $strongtag_present ) {
							if ( '700' === $criticalfont['fontweight'] ) {
								if ( 0 === $criticalfont['distributed'] ) {
									$fontdisplayswap_fixed_selector = $this->checkfont_display_swap( $criticalfont['selector'] );
									$newcssabove                    = $fontdisplayswap_fixed_selector . "\n" . $newcssabove;
									$criticalfont['critical']       = true;
									$criticalfont['distributed']    = 1;
								}
							}
						}
					}
				}
			}

			$additional_inspect = $selector_before_inspect . $selector_end_inspect;
			// contains no font-family:, but may cointain other css-seletors using a font-family and font-weight.
			foreach ( $criticalfonts as &$criticalfont ) {
				if ( str_replace( $criticalfont['fontfamily'], '', $additional_inspect ) !== $additional_inspect ) {
					if ( str_replace( $criticalfont['fontweight'], '', $additional_inspect ) !== $additional_inspect ) {
						if ( 0 === $criticalfont['distributed'] ) {
							// these conditions enable additional checks.
							$additional_inspect_arr     = explode( $criticalfont['fontfamily'], $additional_inspect );
							$cnt_additional_inspect_arr = count( $additional_inspect_arr );
							$critical_font_confirm      = false;
							for ( $a = 1; $a < $cnt_additional_inspect_arr; $a++ ) {
								$aa_arrbef               = explode( '{', $additional_inspect_arr[ $a - 1 ] );
								$selector_before         = array_pop( $aa_arrbef );
								$selector_before_inspect = implode( '{', $aa_arrbef );
								$aa_arr                  = explode( '}', $additional_inspect_arr[ $a ] );
								$selector_end            = array_shift( $aa_arr );
								$selector_end_inspect    = implode( '}', $aa_arr );
								// now the font-weight value should be in $selector_end.
								if ( str_replace( $criticalfont['fontweight'], '', $selector_end ) !== $selector_end ) {
									$critical_font_confirm = true;
									break;
								}

								if ( str_replace( $criticalfont['fontweight'], '', $selector_before ) !== $selector_before ) {
									$critical_font_confirm = true;
									break;
								}
							}
							if ( true === $critical_font_confirm ) {
								$fontdisplayswap_fixed_selector = $this->checkfont_display_swap( $criticalfont['selector'] );
								$newcssabove                    = $fontdisplayswap_fixed_selector . "\n" . $newcssabove;
								$criticalfont['critical']       = true;
								$criticalfont['distributed']    = 1;
							}
						}
					}
				}
			}
		}

		// and the uncritical fonts go below.
		$found_cf = false;
		foreach ( $criticalfonts as &$criticalfont ) {
			if ( true !== $criticalfont['critical'] ) {
				if ( 0 === $criticalfont['distributed'] ) {
					$newcssbelow                .= "\n" . $criticalfont['selector'];
					$criticalfont['distributed'] = 1;
				}
			}
		}

		$ret                        = array();
		$ret['cssbelow']            = $newcssbelow;
		$ret['cssabove']            = $newcssabove;
		$this->p_obj->criticalfonts = $criticalfonts;

		return $ret;
	}

	/**
	 * Checks if a @font-face selector contains font-display:swap; and if not add it.
	 *
	 * @param string $selector The CSS-selector to check and fix if needed.
	 *
	 * @return string $ret_selector
	 */
	private function checkfont_display_swap( $selector ) {
		$ret_selector = $selector;
		$selector_arr = explode( 'font-display:', $selector );
		if ( 1 === count( $selector_arr ) ) {
			// no font-display present at all.
			$selector_text_arr = explode( '}', $selector );
			if ( str_replace( 'Genericons', '', $selector ) === $selector ) {
				$selector_text_arr[0] .= 'font-display:swap;' . "\n";
			}
			$ret_selector = implode( '}', $selector_text_arr );

		} elseif ( 2 === count( $selector_arr ) ) {
			if ( false === irld_str_starts_with( 'swap;', $selector_arr[1] ) ) {
				$selector_text_arr    = explode( ';', $selector_arr[1] );
				$selector_text_arr[0] = 'swap';
				$selector_arr[1]      = implode( ';', $selector_text_arr );
				$ret_selector         = implode( 'font-display:', $selector_arr );
			}
		}
		return $ret_selector;
	}
	/**
	 * Extracts -keyframe selectors, returns cleaned CSS and keyframes seperately, extracted keyframes get sorted and structured.
	 *
	 * @param string $content The CSS-content potentially containing kexframe instruction to extract.
	 *
	 * @return array $contentarr
	 */
	private function extractkeyframe( $content ) {
		$contentarr       = array();
		$add_utf8_charset = false;
		if ( str_replace( '@charset "UTF-8";', '', $content ) !== $content ) {
			$add_utf8_charset = true;
			$content          = str_replace( '@charset "UTF-8";', '', $content );
		}

		$wrkcontent                      = $content;
		$newcontentkeyframe              = '';
		$newcontent                      = '';
		$newfilteredcontentkeyframe      = '';
		$newfilteredcontentkeyframebelow = '';
		if ( str_replace( 'keyframe', '', $wrkcontent ) !== $wrkcontent ) {
			$wrkcontentarr       = explode( 'keyframe', $wrkcontent );
			$wrkcontentarr_count = count( $wrkcontentarr );
			if ( $wrkcontentarr_count > 0 ) {
				$c = 0;
				foreach ( $wrkcontentarr as $part ) {
					if ( 0 === $c ) {
						$parts               = explode( '@', ' ' . $part );
						$partaroundkeyframe  = '@' . trim( array_pop( $parts ) ) . 'keyframe';
						$partnokeyframe      = trim( implode( '@', $parts ) );
						$newcontentkeyframe .= $partaroundkeyframe;
						$newcontent         .= $partnokeyframe;
					} elseif ( ( $wrkcontentarr_count - 1 ) !== $c ) {
						$parts               = explode( '}}', ' ' . $part );
						$partaroundkeyframe  = trim( array_shift( $parts ) ) . '}}';
						$partafterkeyframe   = trim( implode( '}}', $parts ) );
						$partsb              = explode( '@', ' ' . $partafterkeyframe );
						$partaroundkeyframe .= '@' . trim( array_pop( $partsb ) ) . 'keyframe';
						$partnokeyframe      = trim( implode( '@', $partsb ) );
						$newcontentkeyframe .= $partaroundkeyframe;
						$newcontent         .= $partnokeyframe;
					} else {
						$parts               = explode( '}}', ' ' . $part );
						$partaroundkeyframe  = trim( array_shift( $parts ) ) . '}}';
						$partafterkeyframe   = trim( implode( '}}', $parts ) );
						$newcontentkeyframe .= $partaroundkeyframe;
						$newcontent         .= $partafterkeyframe;
					}

					++$c;
				}
			} else {
				$newcontent = $wrkcontent;
			}
		} else {
			$newcontent = $wrkcontent;
		}

		if ( '' !== $newcontentkeyframe ) {
			$keyframes       = explode( '@', $newcontentkeyframe );
			$keyframes_count = count( $keyframes );
			if ( $keyframes_count > 0 ) {
				$keyframeslibrary      = array();
				$keyframeslibrarybelow = array();
				$ctest                 = 0;
				foreach ( $keyframes as $keyframe ) {
					$keyframeparts = explode( ' ', $keyframe );
					if ( count( $keyframeparts ) > 1 ) {
						$keyframeselector          = array_shift( $keyframeparts );
						$keyframecontent           = implode( ' ', $keyframeparts );
						$keyframeparts             = explode( '{', $keyframecontent );
						$keyframetargetcssselector = trim( array_shift( $keyframeparts ) );
						if ( '' !== $keyframetargetcssselector ) {
							$keyframecontent = implode( '{', $keyframeparts );
							if ( str_replace( '.' . $keyframetargetcssselector, '', $newcontent ) !== $newcontent ) {
								if ( ! isset( $keyframeslibrary[ $keyframetargetcssselector ] ) ) {
									$keyframeslibrary[ $keyframetargetcssselector ]                    = array();
									$keyframeslibrary[ $keyframetargetcssselector ]['keyframecontent'] = $keyframecontent;
								}
							} elseif ( ! isset( $keyframeslibrarybelow[ $keyframetargetcssselector ] ) ) {
									$keyframeslibrarybelow[ $keyframetargetcssselector ]                    = array();
									$keyframeslibrarybelow[ $keyframetargetcssselector ]['keyframecontent'] = $keyframecontent;
							}

							++$ctest;
						}
					}
				}
			}

			$newfilteredcontentkeyframe = '';
			// Complete all $keyframeslibrary selector for all targetcss.
			if ( isset( $keyframeslibrary ) ) {
				foreach ( $keyframeslibrary as $targetcss => $keyframecss ) {
					$kfc                         = $keyframecss['keyframecontent'];
					$kfcnomoz                    = str_replace( '-moz-', '', $kfc );
					$newfilteredcontentkeyframe .= '@-webkit-keyframes ' . $targetcss . ' {' . $kfcnomoz .
					'@keyframes ' . $targetcss . ' {' . $kfcnomoz .
					'@-moz-keyframes ' . $targetcss . ' {' . $kfc .
					'@-o-keyframes ' . $targetcss . ' {' . $kfcnomoz;
				}
			}

			$newfilteredcontentkeyframebelow = '';
			// Complete all $keyframeslibrarybelow selector for all targetcss.
			if ( isset( $keyframeslibrarybelow ) ) {
				foreach ( $keyframeslibrarybelow as $targetcss => $keyframecss ) {
					$kfc                              = $keyframecss['keyframecontent'];
					$kfcnomoz                         = str_replace( '-moz-', '', $kfc );
					$newfilteredcontentkeyframebelow .= '@-webkit-keyframes ' . $targetcss . ' {' . $kfcnomoz .
					'@keyframes ' . $targetcss . ' {' . $kfcnomoz .
					'@-moz-keyframes ' . $targetcss . ' {' . $kfc .
					'@-o-keyframes ' . $targetcss . ' {' . $kfcnomoz;
				}
			}
		}

		$contentarr[0] = $newcontent;
		$contentarr[1] = $newfilteredcontentkeyframe;
		$contentarr[2] = $newfilteredcontentkeyframebelow;
		return $contentarr;
	}

	/**
	 * Extracts all unsupported @-features, cleans invalid @media. returns cleaned css and unsupported css seperately.
	 *
	 * @param string $content The CSS-content potentially containing kexframe instruction to extract.
	 *
	 * @return array
	 */
	private function extract_unsupported( $content ) {
		$contentarr             = array();
		$wrkcontent             = $content;
		$strlencontent          = strlen( $content );
		$newcontent             = '';
		$unsupported_content    = '';
		$on_unsupported_content = false;
		$log_lengthdiff         = false;
		if ( '' !== $unsupported_content ) {
			$log_lengthdiff = true;
		}

		// All nested @ are now in $unsupported_content.
		$newcontent = '';
		if ( str_replace( '@', '', $wrkcontent ) !== $wrkcontent ) {
			$wrkcontentarr       = explode( '@', $wrkcontent );
			$wrkcontentarr_count = count( $wrkcontentarr );
			if ( $wrkcontentarr_count > 1 ) {
				$c = 0;
				foreach ( $wrkcontentarr as $part ) {
					if ( 0 === $c ) {
						$newcontent .= $part;
					} elseif ( ( 'media' !== substr( $part, 0, 5 ) ) && ( 'font' !== substr( $part, 0, 4 ) ) ) {
						if ( ( str_replace( '}}', '', $part ) !== $part ) && ( str_replace( '{', '', $part ) !== $part ) ) {
							if ( strpos( $part, '}}' ) > strpos( $part, '{' ) ) {
								$unsupported_content .= '@';
								$parts                = explode( '}}', $part );
								$unsupported_content .= $parts[0] . '}}';
								if ( isset( $parts[1] ) ) {
									$newcontent .= $parts[1];
								}
							} else {
								$newcontent .= $part;
							}
						} else {
							$newcontent .= $part;
						}
					} elseif ( 'font' === substr( $part, 0, 4 ) ) {
						$newcontent .= '@' . $part;
					} elseif ( str_replace( '}}', '', $part ) !== $part ) {
						// Drop if min-width or max-width is invalid.
						$parts = explode( '{', $part );
						if ( ( str_replace( 'max-width:-1', '', $parts[0] ) !== $parts[0] ) || ( str_replace( 'min-width:-1', '', $parts[0] ) !== $parts[0] ) ) {
							// Invalid at media.
							$parts = explode( '}}', $part );
							if ( isset( $parts[1] ) ) {
								$newcontent .= $parts[1];
							}
						} else {
							$newcontent .= '@' . $part;
						}
					} else {
						$newcontent .= $part;
					}

					++$c;
				}
			} else {
				$newcontent = $wrkcontent;
			}
		} else {
			$newcontent = $wrkcontent;
		}

		$contentarr[0]                          = $newcontent;
		$unsupported_content_supports_arr       = explode( '@supports', $unsupported_content );
		$unsupported_content_for_above          = '';
		$unsupported_content_for_below          = $unsupported_content_supports_arr[0];
		$count_unsupported_content_supports_arr = count( $unsupported_content_supports_arr );
		if ( $count_unsupported_content_supports_arr > 1 ) {
			for ( $k = 1; $k < $count_unsupported_content_supports_arr; $k++ ) {
				$supports_selectors_arr      = explode( '{', $unsupported_content_supports_arr[ $k ] );
				$supports_criteria           = trim( $supports_selectors_arr[0] );
				$supports_css_start          = array_shift( $supports_selectors_arr );
				$supports_css_base           = implode( '{', $supports_selectors_arr );
				$supports_css_base_lentopick = strlen( $supports_css_base ) - 1;
				$supports_css                = substr( $supports_css_base, 0, $supports_css_base_lentopick );
				$filecontents                = $this->split_css_below_above_the_fold( $supports_css );
				$dtf                         = array();
				$cssbelow                    = $filecontents[1];
				if ( ( '' !== trim( $cssbelow ) ) && ( '{}' !== trim( $cssbelow ) ) && ( strlen( $cssbelow ) > 8 ) ) {
					$unsupported_content_for_below .= '@supports' . $supports_css_start . '{' . $cssbelow . '}';
				}

				$cssabove = $filecontents[0];
				if ( ( '' !== trim( $cssabove ) ) && ( '{}' !== trim( $cssabove ) ) && ( strlen( $cssabove ) > 8 ) ) {
					$unsupported_content_for_above .= '@supports' . $supports_css_start . '{' . $cssabove . '}';
				}

				$cssbelow = '';
				$cssabove = '';
			}
		}

		$contentarr[1] = $unsupported_content_for_below;
		$contentarr[2] = $unsupported_content_for_above;
		return $contentarr;
	}

	/**
	 * Here the CSS @media array from below gets cut out all selectors present in the above CSS @media array.
	 *
	 * @param array $below CSS @media array from below.
	 * @param array $above CSS @media array from above.
	 *
	 * @return array
	 */
	private function media_below_rebuild( $below, $above ) {
		$wrk_below           = '';
		$media_below         = array();
		$media_below_rebuild = array();
		$mld                 = 0;
		foreach ( $below as $below_el ) {
			if ( '' === $wrk_below ) {
				$start_below_el = $below_el;
				$wrk_below      = $below_el['mediagroupsort'];
				$media_below    = array();
				$mi             = 0;
			} elseif ( ( '' !== $wrk_below ) && ( $wrk_below !== $below_el['mediagroupsort'] ) ) {
				if ( count( $media_below ) > 0 ) {
					$media_above = array();
					$oi          = 0;
					foreach ( $above as $above_el ) {
						if ( $wrk_below === $above_el['mediagroupsort'] ) {
							if ( ( 'mce' !== $above_el['mc'] ) && ( 'mca' !== $above_el['mc'] ) ) {
								$media_above[ $oi ] = $above_el;
								++$oi;
							}
						}
					}

					if ( count( $media_above ) > 0 ) {
						$media_below = $this->cut_above_in_below( $media_below, $media_above );
					}
				}

				$cmob = count( $media_below );
				if ( $cmob > 0 ) {
					$media_below_rebuild[ $mld ] = $start_below_el;
					++$mld;
					for ( $tmpi = 0; $tmpi < $cmob; $tmpi++ ) {
						$media_below_rebuild[ $mld ] = $media_below[ $tmpi ];
						++$mld;
					}

					$media_below_rebuild[ $mld ] = $end_below_el;
					++$mld;
				}

				$start_below_el = $below_el;
				$wrk_below      = $below_el['mediagroupsort'];
				$media_below    = array();
				$mi             = 0;
			}

			if ( ( 'mce' !== $below_el['mc'] ) && ( 'mca' !== $below_el['mc'] ) ) {
				$media_below[ $mi ] = $below_el;
				++$mi;
			} elseif ( ( 'mce' === $below_el['mc'] ) ) {
				$end_below_el = $below_el;
			}
		}

		if ( count( $media_below ) > 0 ) {
			$media_above = array();
			$oi          = 0;
			foreach ( $above as $above_el ) {
				if ( $wrk_below === $above_el['mediagroupsort'] ) {
					if ( ( 'mce' !== $above_el['mc'] ) && ( 'mca' !== $above_el['mc'] ) ) {
						$media_above[ $oi ] = $above_el;
						++$oi;
					}
				}
			}

			if ( count( $media_above ) > 0 ) {
				$media_below = $this->cut_above_in_below( $media_below, $media_above );
			}

			$cmob = count( $media_below );
			if ( $cmob > 0 ) {
				$media_below_rebuild[ $mld ] = $start_below_el;
				++$mld;
				for ( $tmpi = 0; $tmpi < $cmob; $tmpi++ ) {
					$media_below_rebuild[ $mld ] = $media_below[ $tmpi ];
					++$mld;
				}

				$media_below_rebuild[ $mld ] = $end_below_el;
				++$mld;
			}
		}

		$i                       = 0;
		$media_below_rebuild_out = array();
		foreach ( $media_below_rebuild as $media_below_rebuild_el ) {
			if ( '' !== $media_below_rebuild_el['oPosO'] ) {
				$media_below_rebuild_out[ $i ] = $media_below_rebuild_el;
				++$i;
			}
		}

		return $media_below_rebuild_out;
	}

	/**
	 * Here the CSS from above, either no @media CSS or @media CSS (per @media instruction) cuts out selectors present in the below CSS, if no selector found, the entire array element.
	 *
	 * @param array $below CSS @media array from below.
	 * @param array $above CSS @media array from above.
	 *
	 * @return array
	 */
	private function cut_above_in_below( $below, $above ) {
		$new_below = array();
		$nb        = 0;
		$b         = 0;
		$a         = 0;
		$cnt_below = count( $below );
		$cnt_above = count( $above );
		for ( $b = 0; $b < $cnt_below; $b++ ) {
			$found_all_selector                = -1;
			$rule_present_in_above             = 0;
			$below[ $b ]['rulepresentinAbove'] = 0;
			$cntbcss                           = count( $below[ $b ]['cssselectors'] );
			for ( $bc = 0;$bc < $cntbcss;++$bc ) {
				$below[ $b ]['cssselectors'][ $bc ]['drop'] = 0;
			}

			for ( $a = 0; $a < $cnt_above; $a++ ) {
				if ( ( '' !== $above[ $a ]['cssrules'] ) && ( $above[ $a ]['cssrules'] === $below[ $b ]['cssrules'] ) ) {
					$rule_present_in_above             = 1;
					$found_all_selector                = 1;
					$below[ $b ]['rulepresentinAbove'] = 1;
					$bc                                = 0;
					for ( $bc = 0; $bc < $cntbcss; ++$bc ) {
						$found_selector = 0;
						$ac             = 0;
						$cntacss        = count( $above[ $a ]['cssselectors'] );
						for ( $ac = 0; $ac < $cntacss; $ac++ ) {
							if ( ( '' !== $above[ $a ]['cssselectors'][ $ac ]['selector'] ) && ( $above[ $a ]['cssselectors'][ $ac ]['selector'] === $below[ $b ]['cssselectors'][ $bc ]['selector'] ) ) {
								$found_selector                             = 1;
								$below[ $b ]['cssselectors'][ $bc ]['drop'] = 1;
								break;
							}
						}

						if ( 0 === $found_selector ) {
							$found_all_selector = 0;
						}
					}
				}

				if ( 1 === $found_all_selector ) {
					break;
				}
			}
		}

		$nb = 0;
		for ( $b = 0; $b < $cnt_below; $b++ ) {
			if ( 1 === $below[ $b ]['rulepresentinAbove'] ) {
				$new_css_selectors = array();
				$cntbcss           = count( $below[ $b ]['cssselectors'] );
				$bc                = 0;
				$txtselectors      = '';
				for ( $bcsl = 0;$bcsl < $cntbcss;$bcsl++ ) {
					if ( 1 !== $below[ $b ]['cssselectors'][ $bcsl ]['drop'] ) {
						$new_css_selectors[ $bc ]['selector'] = $below[ $b ]['cssselectors'][ $bcsl ]['selector'];
						$new_css_selectors[ $bc ]['drop']     = $below[ $b ]['cssselectors'][ $bcsl ]['drop'];
						$txtselectors                        .= ',' . $below[ $b ]['cssselectors'][ $bcsl ]['selector'];
						++$bc;
					}
				}

				if ( count( $new_css_selectors ) > 0 ) {
					$new_below[ $nb ]                   = array();
					$new_below[ $nb ]['cssrules']       = $below[ $b ]['cssrules'];
					$new_below[ $nb ]['cssselectors']   = $new_css_selectors;
					$new_below[ $nb ]['mediagroup']     = $below[ $b ]['mediagroup'];
					$new_below[ $nb ]['mediagroupsort'] = $below[ $b ]['mediagroupsort'];
					$new_below[ $nb ]['oPos']           = $below[ $b ]['oPos'];
					$new_below[ $nb ]['oPosO']          = $below[ $b ]['oPosO'];
					$new_below[ $nb ]['nested']         = $below[ $b ]['nested'];
					$new_below[ $nb ]['mc']             = $below[ $b ]['mc'];
					$new_below[ $nb ]['dropline']       = 9;
					$new_below[ $nb ]['csstext']        = substr( $txtselectors, 1 ) . '{' . $below[ $b ]['cssrules'] . '}';
					++$nb;
				}
			} else {
				$new_below[ $nb ] = $below[ $b ];
				++$nb;
			}
		}

		if ( ( 0 === $found_all_selector ) || ( 0 === $rule_present_in_above ) ) {
			$new_below[ $nb ]                   = array();
			$new_below[ $nb ]['cssrules']       = $below[ $b ]['cssrules'];
			$new_below[ $nb ]['cssselectors']   = array();
			$new_below[ $nb ]['csstext']        = $below[ $b ]['csstext'];
			$new_below[ $nb ]['mediagroup']     = $below[ $b ]['mediagroup'];
			$new_below[ $nb ]['mediagroupsort'] = $below[ $b ]['mediagroupsort'];
			$new_below[ $nb ]['oPos']           = $below[ $b ]['oPos'];
			$new_below[ $nb ]['oPosO']          = $below[ $b ]['oPosO'];
			$new_below[ $nb ]['nested']         = $below[ $b ]['nested'];
			$new_below[ $nb ]['mc']             = $below[ $b ]['mc'];
			$new_below[ $nb ]['dropline']       = 9;
			$nbs                                = 0;
			$cntbcss                            = count( $below[ $b ]['cssselectors'] );
			$bc                                 = 0;
			if ( 0 === $rule_present_in_above ) {
				for ( $bc = 0;$bc < $cntbcss;++$bc ) {
					$new_below[ $nb ]['cssselectors'][ $nbs ]['selector'] = $below[ $b ]['cssselectors'][ $bc ]['selector'];
					$new_below[ $nb ]['cssselectors'][ $nbs ]['drop']     = $below[ $b ]['cssselectors'][ $bc ]['drop'];
					++$nbs;
				}
			} else {
				for ( $bc = 0;$bc < $cntbcss;++$bc ) {
					if ( 1 !== $below[ $b ]['cssselectors'][ $bc ]['drop'] ) {
						$new_below[ $nb ]['cssselectors'][ $nbs ]['selector'] = $below[ $b ]['cssselectors'][ $bc ]['selector'];
						$new_below[ $nb ]['cssselectors'][ $nbs ]['drop']     = 0;
						++$nbs;
					}
				}
			}

			++$nb;

		}

		return $new_below;
	}


	/**
	 * Splits $filecontent into an array, one holding CSS for above the fold, the other below the fold.
	 *
	 * @param string $filecontent String containing all CSS.
	 *
	 * @return array $filecontents, [0] = $filecontentabovethefold;[1] = $filecontentbelowthefold;
	 */
	protected function split_css_below_above_the_fold( $filecontent ) {
		// Remove comments.
		$filecontent = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $filecontent );
		// And other clean outs.
		$filecontent = str_replace( "\t", '', $filecontent );
		$filecontent = str_replace( "\r", '', $filecontent );
		$filecontent = str_replace( "\n", '', $filecontent );
		$filecontent = str_replace( '  ', ' ', $filecontent );
		$filecontent = str_replace( ': ', ':', $filecontent );
		// Clean. lets initialize.
		$filecontentabovethefold = $filecontent;
		$filecontentbelowthefold = '';
		$filecontents            = array();
		$filecontents[0]         = $filecontentabovethefold;
		$filecontents[1]         = $filecontentbelowthefold;
		// CSS-statement groups.
		$css_statementgroups = explode( '}', $filecontent );
		// CSS-statement groups respecting @media or other nested selectors.
		$css_statementgroupsmedia = array();
		$i                        = 0;
		$forcebelow               = 0;
		foreach ( $css_statementgroups as $css_statement ) {
			$css_statementgroupsmedia[ $i ]            = array();
			$css_statementgroupsmedia[ $i ]['csstext'] = $css_statement;

			$css_statementgroupsmediatest = explode( '{', $css_statement );
			if ( count( $css_statementgroupsmediatest ) > 2 ) {
				$css_statementgroupsmedia[ $i ]['csstext'] = $css_statementgroupsmediatest[0];
				$forcebelow                                = 0;
				if ( str_replace( ' print', '', $css_statementgroupsmedia[ $i ]['csstext'] ) !== $css_statementgroupsmedia[ $i ]['csstext'] ) {
					$forcebelow = 1;
				}

				$css_statementgroupsmedia[ $i ]['nestedcss'] = array();
				// is nested.
				$css_statementgroupsmedia[ $i ]['nested'] = 1;
				$css_statementgroupsmedia[ $i ]['oPos']   = $i;
				$j2                                       = -1;
				$i2                                       = 0;
				$gobelownestedparent                      = 1;
				foreach ( $css_statementgroupsmediatest as $css_statementmediatest ) {
					if ( $j2 >= 0 ) {
						if ( 0 === $i2 % 2 ) {
							// Pair.
							$css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['cssrules'] = $this->get_rules( $css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['csstext'] . '{' . $css_statementmediatest );
							$css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['csstext'] .= '{' . $css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['cssrules'];
							++$j2;
						} else {
							$css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]                 = array();
							$css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['csstext']      = $css_statementmediatest;
							$css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['cssselectors'] = array();
							$css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['cssselectors'] = $this->get_selectors( $css_statementmediatest );
							$sa      = 0;
							$gobelow = 1;
							foreach ( $css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['cssselectors'] as $selectorarray ) {
								if ( 1 !== intval( $selectorarray['selectorCanGoBelow'] ) ) {
									$css_selector_can_go_below = $this->check_below_the_fold( $selectorarray, $this->bufferinclasses, $this->bufferinids, $this->bufferintags, $this->bufferinputtypes );
									$css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['cssselectors'][ $sa ]['selectorCanGoBelow'] = $css_selector_can_go_below;
									if ( 0 === $css_selector_can_go_below ) {
										$gobelow             = 0;
										$gobelownestedparent = 0;
									}
								} else {
									$css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['cssselectors'][ $sa ]['selectorCanGoBelow'] = 2;
								}

								if ( 1 === $forcebelow ) {
									$css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['cssselectors'][ $sa ]['selectorCanGoBelow'] = 2;
									$gobelow             = 1;
									$gobelownestedparent = 1;
								}

								++$sa;
							}

							$css_statementgroupsmedia[ $i ]['nestedcss'][ $j2 ]['selectorCanGoBelow'] = $gobelow;
						}
					} else {
						++$j2;
					}

					++$i2;
				}

				$css_statementgroupsmedia[ $i ]['selectorCanGoBelow'] = $gobelownestedparent;
			} else {
				$css_statementgroupsmedia[ $i ]['nested']       = 0;
				$css_statementgroupsmedia[ $i ]['oPos']         = $i;
				$css_statementgroupsmedia[ $i ]['cssselectors'] = array();
				$css_statementgroupsmedia[ $i ]['cssselectors'] = $this->get_selectors( $css_statement );
				$sa      = 0;
				$gobelow = 1;
				foreach ( $css_statementgroupsmedia[ $i ]['cssselectors'] as $selectorarray ) {
					if ( 1 !== intval( $selectorarray['selectorCanGoBelow'] ) ) {
						$css_selector_can_go_below = $this->check_below_the_fold( $selectorarray, $this->bufferinclasses, $this->bufferinids, $this->bufferintags, $this->bufferinputtypes );
						$css_statementgroupsmedia[ $i ]['cssselectors'][ $sa ]['selectorCanGoBelow'] = $css_selector_can_go_below;
						if ( 0 === $css_selector_can_go_below ) {
							$gobelow = 0;
						}
					} else {
						$css_statementgroupsmedia[ $i ]['cssselectors'][ $sa ]['selectorCanGoBelow'] = 2;
					}

					if ( 1 === $forcebelow ) {
						$css_statementgroupsmedia[ $i ]['cssselectors'][ $sa ]['selectorCanGoBelow'] = 2;
						$gobelow             = 1;
						$gobelownestedparent = 1;
					}

					++$sa;
				}

				$css_statementgroupsmedia[ $i ]['selectorCanGoBelow'] = $gobelow;
				$css_statementgroupsmedia[ $i ]['cssrules']           = $this->get_rules( $css_statement );
				if ( 1 === $forcebelow ) {
					if ( '' === trim( $css_statementgroupsmedia[ $i ]['cssrules'] ) ) {
						$forcebelow = 0;
					}
				}
			}

			++$i;
		}

		$nested        = 0;
		$mediacssabove = '';
		$mediacssbelow = '';
		$cssabove      = '';
		$cssbelow      = '';
		$opos_above    = '';
		$opos_below    = '';
		foreach ( $css_statementgroupsmedia as $cssdna ) {
			if ( 1 === $cssdna['nested'] ) {
				$hasbelow          = 0;
				$hasabove          = 0;
				$nested            = 1;
				$mediacssabove     = $cssdna['csstext'] . '{' . "\n";
				$mediacssbelow     = $cssdna['csstext'] . '{' . "\n";
				$cssselectorsabove = array();
				$csa               = 0;
				$cssselectorsbelow = array();
				$csb               = 0;
				foreach ( $cssdna['nestedcss'][0]['cssselectors'] as $nestedcssdna ) {
					if ( $nestedcssdna['selectorCanGoBelow'] >= 1 ) {
						$cssselectorsbelow[ $csb ] = $nestedcssdna['selector'];
						++$csb;
					} elseif ( 0 === $nestedcssdna['selectorCanGoBelow'] ) {
						$cssselectorsabove[ $csa ] = $nestedcssdna['selector'];
						++$csa;
					}
				}

				$mcssabove = '';
				$mcssbelow = '';
				if ( $csa > 0 ) {
					$mcssabove   = implode( ', ', $cssselectorsabove ) . ' {' . "\n" . str_replace( ';', ';' . "\n", $cssdna['nestedcss'][0]['cssrules'] ) . '}' . "\n";
					$hasabove    = 1;
					$opos_above .= $cssdna['oPos'] . ',@,' . $cssdna['csstext'] . ' {' . $mcssabove;
				}

				if ( $csb > 0 ) {
					$mcssbelow   = implode( ', ', $cssselectorsbelow ) . ' {' . "\n" . str_replace( ';', ';' . "\n", $cssdna['nestedcss'][0]['cssrules'] ) . '}' . "\n";
					$hasbelow    = 1;
					$opos_below .= $cssdna['oPos'] . ',@,' . $cssdna['csstext'] . ' {' . $mcssbelow;
				}

				$mediacssabove .= $mcssabove;
				$mediacssbelow .= $mcssbelow;
			} elseif ( 1 === $nested ) {
					// Remains nested until trim(csstext) = ''.
				if ( ( '' === trim( $cssdna['csstext'] ) ) && ( '' === trim( $cssdna['cssrules'] ) ) ) {
					$nested = 0;
					if ( 1 === $hasabove ) {
						$mediacssabove .= $cssdna['csstext'] . '}' . "\n";
						$opos_above    .= $cssdna['oPos'] . ',@,' . $cssdna['csstext'] . '}' . "\n";
					} else {
						$mediacssabove = '';
					}

					if ( 1 === $hasbelow ) {
						$mediacssbelow .= $cssdna['csstext'] . '}' . "\n";
						$opos_below    .= $cssdna['oPos'] . ',@,' . $cssdna['csstext'] . '}' . "\n";

					} else {
						$mediacssbelow = '';
					}
					$cssabove .= $mediacssabove;
					$cssbelow .= $mediacssbelow;
				} else {
					$cssselectorsabove = array();
					$csa               = 0;
					$cssselectorsbelow = array();
					$csb               = 0;
					foreach ( $cssdna['cssselectors'] as $nestedcssdna ) {
						if ( $nestedcssdna['selectorCanGoBelow'] >= 1 ) {
							$cssselectorsbelow[ $csb ] = $nestedcssdna['selector'];
							++$csb;
						} elseif ( 0 === $nestedcssdna['selectorCanGoBelow'] ) {
							$cssselectorsabove[ $csa ] = $nestedcssdna['selector'];
							++$csa;
						}
					}

					$mcssabove = '';
					$mcssbelow = '';
					if ( $csa > 0 ) {
						$mcssabove        = implode( ', ', $cssselectorsabove ) . ' {' . "\n" . str_replace( ';', ';' . "\n", $cssdna['cssrules'] ) . '}' . "\n";
						$hasabove         = 1;
						$mediacssabovecnt = count( explode( '{', $mediacssabove ) );
						if ( 2 === $mediacssabovecnt ) {
							$opos_above .= $cssdna['oPos'] . ',@,' . $mediacssabove . $mcssabove;
						} elseif ( str_replace( $mcssabove, '', $mediacssabove ) === $mediacssabove ) {
								$opos_above .= $cssdna['oPos'] . ',@,' . $mcssabove;
						}
					}

					if ( $csb > 0 ) {
						$mcssbelow        = implode( ', ', $cssselectorsbelow ) . ' {' . "\n" . str_replace( ';', ';' . "\n", $cssdna['cssrules'] ) . '}' . "\n";
						$hasbelow         = 1;
						$mediacssbelowcnt = count( explode( '{', $mediacssbelow ) );
						if ( 2 === $mediacssbelowcnt ) {
							$opos_below .= $cssdna['oPos'] . ',@,' . $mediacssbelow . $mcssbelow;
						} elseif ( str_replace( $mcssbelow, '', $mediacssbelow ) === $mediacssbelow ) {
								$opos_below .= $cssdna['oPos'] . ',@,' . $mcssbelow;
						}
					}

					$mediacssabove .= $mcssabove;
					$mediacssbelow .= $mcssbelow;
				}
			} else {
					$cssselectorsabove = array();
					$csa               = 0;
					$hasabove          = 0;
					$hasbelow          = 0;
					$cssselectorsbelow = array();
					$csb               = 0;
				foreach ( $cssdna['cssselectors'] as $nestedcssdna ) {
					if ( $nestedcssdna['selectorCanGoBelow'] >= 1 ) {
						$cssselectorsbelow[ $csb ] = $nestedcssdna['selector'];
						++$csb;
					} elseif ( 0 === $nestedcssdna['selectorCanGoBelow'] ) {
						$cssselectorsabove[ $csa ] = $nestedcssdna['selector'];
						++$csa;
					}
				}

					$mcssabove = '';
					$mcssbelow = '';
				if ( $csa > 0 ) {
					$mcssabove   = implode( ', ', $cssselectorsabove ) . ' {' . "\n" . str_replace( ';', ';' . "\n", $cssdna['cssrules'] ) . '}' . "\n";
					$hasabove    = 1;
					$opos_above .= $cssdna['oPos'] . ',@,' . $mcssabove;
				}

				if ( $csb > 0 ) {
					$mcssbelow   = implode( ', ', $cssselectorsbelow ) . ' {' . "\n" . str_replace( ';', ';' . "\n", $cssdna['cssrules'] ) . '}' . "\n";
					$hasbelow    = 1;
					$opos_below .= $cssdna['oPos'] . ',@,' . $mcssbelow;
				}

					$mediacssabove .= $mcssabove;
					$mediacssbelow .= $mcssbelow;
				if ( 1 === $hasabove ) {
					$cssabove .= $mcssabove;
				}

				if ( 1 === $hasbelow ) {
					$cssbelow .= $mcssbelow;
				}
			}
		}

		$cssabove        = str_replace( "\n" . ' {' . "\n" . '}', '', $cssabove );
		$cssbelow        = str_replace( "\n" . ' {' . "\n" . '}', '', $cssbelow );
		$opos_above      = str_replace( "\n" . ' {' . "\n" . '}', '', $opos_above );
		$pos             = strripos( $opos_above, ';' . "\n" . '}' );
		$opos_above      = substr( $opos_above, 0, ( $pos + 2 ) );
		$opos_below      = str_replace( "\n" . ' {' . "\n" . '}', '', $opos_below );
		$pos             = strripos( $opos_below, ';' . "\n" . '}' );
		$opos_below      = substr( $opos_below, 0, ( $pos + 2 ) );
		$filecontents[0] = $cssabove;
		$filecontents[1] = $cssbelow;
		$filecontents[2] = $this->crunchCSS( $opos_above );
		$filecontents[3] = $this->crunchCSS( $opos_below );
		return $filecontents;
	}

	/**
	 * Prepares selector.
	 *
	 * @param string $selectorarrayselector selector to be prepared.
	 * @param string $selectorarrayselectortrace selector in original form .
	 *
	 * @return array $ret, holding 2 elements, one with $clean_idstagsandclasses-array, the other with modified $selectorarrayselector
	 */
	protected function prepare_selector( $selectorarrayselector, $selectorarrayselectortrace ) {
		$selectorarrayselector = str_replace( '.', ' .', $selectorarrayselector );
		$selectorarrayselector = str_replace( '#', ' #', $selectorarrayselector );
		$selectorarrayselector = str_replace( ' + ', ' ', $selectorarrayselector );
		$selectorarrayselector = str_replace( '+ ', ' ', $selectorarrayselector );
		$selectorarrayselector = str_replace( '+', ' ', $selectorarrayselector );
		$selectorarrayselector = str_replace( ' > ', ' ', $selectorarrayselector );
		$selectorarrayselector = str_replace( '> ', ' ', $selectorarrayselector );
		$selectorarrayselector = str_replace( '>', ' ', $selectorarrayselector );
		$selectorarrayselector = str_replace( ' ~ ', ' ', $selectorarrayselector );
		$selectorarrayselector = str_replace( '~ ', ' ', $selectorarrayselector );
		$selectorarrayselector = str_replace( '~', ' ', $selectorarrayselector );
		if ( str_replace( '*', '', $selectorarrayselector ) !== $selectorarrayselector ) {
			if ( str_replace( '*=', '', $selectorarrayselector ) === $selectorarrayselector ) {
				$selectorarrayselector = str_replace( '*', ' ', $selectorarrayselector );
			}
		}

		$selectorarrayselector   = str_replace( '  ', ' ', $selectorarrayselector );
		$selectorarrayselector   = str_replace( ':not( ', ':not(', $selectorarrayselector );
		$selectorarrayselector   = trim( $selectorarrayselector );
		$idstagsandclasses       = array();
		$clean_idstagsandclasses = array();
		$idstagsandclasses       = explode( ' ', $selectorarrayselector );
		if ( true === $this->traceitrm_follow ) {
			if ( $selectorarrayselector !== $selectorarrayselectortrace ) {
				irld_trace_log( ' " ' . $selectorarrayselectortrace . ' " changed to " ' . $selectorarrayselector . ' " ', 0 );
			}
		}

		foreach ( $idstagsandclasses as $idtagorclass ) {
			$clean_idtagorclass = $idtagorclass;
			if ( str_replace( ':not', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, ':not' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( '::before', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, '::before' ) );
				if ( '' !== trim( $newselector ) ) {
					$clean_idtagorclass = $newselector;
				}
			} elseif ( str_replace( ':before', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, ':before' ) );
				if ( '' !== trim( $newselector ) ) {
					$clean_idtagorclass = $newselector;
				}
			}

			if ( str_replace( '::after', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, '::after' ) );
				if ( '' !== trim( $newselector ) ) {
					$clean_idtagorclass = $newselector;
				}
			} elseif ( str_replace( ':after', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, ':after' ) );
				if ( '' !== trim( $newselector ) ) {
					$clean_idtagorclass = $newselector;
				}
			}

			// ::-webkit-scrollbar-thumb, ::-webkit-scrollbar-track
			if ( str_replace( '::-webkit', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, '::-webkit' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( ':-webkit', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, ':-webkit' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( '::last', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, '::last' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( ':last', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, ':last' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( '::first', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, '::first' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( ':first', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, ':first' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( '::placeholder', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, '::placeholder' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( ':placeholder', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, ':placeholder' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( '::-moz', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, '::-moz' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( ':-moz', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, ':-moz' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( '::-ms', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, '::-ms' ) );
				$clean_idtagorclass = $newselector;
			}
			if ( str_replace( ':-ms', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, ':-ms' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( ':empty', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector        = substr( $clean_idtagorclass, 0, strpos( $clean_idtagorclass, ':empty' ) );
				$clean_idtagorclass = $newselector;
			}

			if ( str_replace( ':root', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				$newselector = str_replace( ':root ', '', $clean_idtagorclass );
				if ( '' !== trim( $newselector ) ) {
					if ( ':' !== trim( $newselector ) ) {
						$clean_idtagorclass = $newselector;
					}
				}
			}

			if ( str_replace( ')', '', $clean_idtagorclass ) !== $clean_idtagorclass ) {
				if ( strlen( $clean_idtagorclass ) < 5 ) {
					$newselector        = '';
					$clean_idtagorclass = $newselector;
				}
			}

			if ( '' !== trim( $clean_idtagorclass ) ) {
				$clean_idstagsandclasses[] = $clean_idtagorclass;
			}
		}

		$ret    = array();
		$ret[0] = $clean_idstagsandclasses;
		$ret[1] = $selectorarrayselector;

		return $ret;
	}

	/**
	 * Extracts :where(-clauses.
	 *
	 * @param string $cond_where string holding possible :where(.
	 * @param string $cond_wherenot string holding possible :where(:not(.
	 * @param string $cond_wherebodynot string holding possible :where(body:not(.
	 *
	 * @return array $arr_res holding metainformation on the examinated subject :where(-clauses.
	 */
	protected function extract_whereclauses( $cond_where, $cond_wherenot, $cond_wherebodynot ) {
		$ret     = array();
		$arr_res = array();

		if ( '' !== $cond_where ) {
			$arr_where           = explode( ':where(', $cond_where );
			$count_arr_where     = count( $arr_where );
			$idx_awn             = 0;
			$filtering_index     = -1;
			$content_after_where = '';
			$content_where       = '';
			$content_true        = array();
			$content_filter      = '';
			if ( 2 === $count_arr_where ) {
				foreach ( $arr_where as $where ) {
					if ( 0 === $idx_awn ) {
						$start_where = trim( $where );
					} else {
						$arr_content_where           = explode( ')', trim( $where ) );
						$arr_content_where_selectors = explode( ',', $arr_content_where[0] );
						if ( isset( $arr_content_where[1] ) ) {
							if ( '' !== trim( $arr_content_where[1] ) ) {
								array_shift( $arr_content_where );
								$content_after_where .= implode( ')', $arr_content_where );
							}

							if ( '' === trim( $content_after_where ) ) {
								$content_after_where = '';
							}
						}
					}

					if ( ( $count_arr_where - 1 ) === $idx_awn ) {
						if ( '' !== $start_where ) {
							$start_where = $start_where . ' ';
						}

						foreach ( $arr_content_where_selectors as $content_where_sel ) {
							$content_true[] = $start_where . $content_where_sel . $content_after_where;
						}
					}

					++$idx_awn;
				}
			} else {
				foreach ( $arr_where as $where ) {
					if ( 0 === $idx_awn ) {
						$start_where = trim( $where );
					} else {
						$arr_content_where           = explode( ')', trim( $where ) );
						$arr_content_where_selectors = explode( ',', $arr_content_where[0] );
						if ( isset( $arr_content_where[1] ) ) {
							if ( '' !== trim( $arr_content_where[1] ) ) {
								array_shift( $arr_content_where );
								$content_after_where .= implode( ')', $arr_content_where );
							}

							if ( '' === trim( $content_after_where ) ) {
								$content_after_where = '';
							}
						}
					}

					if ( ( $count_arr_where - 1 ) === $idx_awn ) {
						if ( '' !== $start_where ) {
							$start_where = $start_where . ' ';
						}
					}

					if ( $idx_awn > 0 ) {
						foreach ( $arr_content_where_selectors as $content_where_sel ) {
							$content_true[] = $start_where . $content_where_sel . $content_after_where;
						}
					}

					++$idx_awn;
				}
			}

			$count_content_true = count( $content_true );
			for ( $f = 0; $f < $count_content_true; $f++ ) {
				$arr_res[ 'true' . $f ] = $content_true[ $f ];
			}
		}

		if ( '' !== $cond_wherenot ) {
			$arr_wherenot           = explode( ':where(:not(', $cond_wherenot );
			$count_arr_wherenot     = count( $arr_wherenot );
			$idx_awn                = 0;
			$filtering_index        = -1;
			$content_after_wherenot = '';
			$content_wherenot       = '';
			$content_true           = '';
			$content_filter         = array();
			if ( 2 === $count_arr_wherenot ) {
				foreach ( $arr_wherenot as $wherenot ) {
					if ( 0 === $idx_awn ) {
						$start_wherenot = trim( $wherenot );
						if ( strlen( $start_wherenot ) > 0 ) {
							$arr_start_wherenot = explode( ' ', $start_wherenot );
							$filtering_index    = count( $arr_start_wherenot );
						} else {
							$filtering_index = 0;
						}
					} else {
						$arr_content_wherenot = explode( '))', $wherenot );
						$content_wherenot     = $arr_content_wherenot[0];
						// supposing the not is directly followed by another not inside the where.
						$content_wn_not_arr = explode( '):not(', $content_wherenot );
						// supposing the not is indirectly followed by another set back not inside the where.
						$content_wn_not_setback_arr = explode( ':not(', $content_wherenot );

						if ( isset( $arr_content_wherenot[1] ) ) {
							// inspection of the part after the entire where not.
							array_shift( $arr_content_wherenot );
							$content_after_wherenot .= implode( '))', $arr_content_wherenot );
							if ( '' === trim( $content_after_wherenot ) ) {
								$content_after_wherenot = '';
							}
						}

						// resolving the more complicated situation, where there's something between the nots.
						if ( count( $content_wn_not_setback_arr ) > count( $content_wn_not_arr ) ) {
							$setback_index = 0;
							foreach ( $content_wn_not_setback_arr as $content_wn_not_setback ) {
								// finding true and condition parts.
								if ( 2 > $setback_index ) {
									$content_wn_not_setback_elem_arr = explode( ')', $content_wn_not_setback );
									if ( 2 === count( $content_wn_not_setback_elem_arr ) ) {
										// resolving multiple selectors inside not.
										$notcomma_arr = explode( ',', $content_wn_not_setback_elem_arr[0] );
										foreach ( $notcomma_arr as $notcomma_arr_elem ) {
											$content_filter[] = $start_wherenot . $notcomma_arr_elem;
										}

										$start_wherenot = $content_wn_not_setback_elem_arr[1];
									} elseif ( 1 === count( $content_wn_not_setback_elem_arr ) ) {
										// resolving multiple selectors inside not.
										$notcomma_arr = explode( ',', $content_wn_not_setback_elem_arr[0] );
										foreach ( $notcomma_arr as $notcomma_arr_elem ) {
											$content_filter[] = $start_wherenot . $notcomma_arr_elem;
										}
									} elseif ( ( true === $this->track_heavy_selectors ) || ( true === $this->traceitrm_follow ) ) {
											irld_trace_log( 'found ' . count( $content_wn_not_setback_elem_arr ) . ' closing parathesis of a "not" inside a "where not" containing multiple "nots"', 0 );
									}
								} elseif ( ( true === $this->track_heavy_selectors ) || ( true === $this->traceitrm_follow ) ) {
										irld_trace_log( 'found ' . count( $content_wn_not_setback_arr ) . ' 3 "not"s inside a "where not", skipping last "nots"', 0 );
								}
								++$setback_index;
							}
						}
					}

					if ( ( $count_arr_wherenot - 1 ) === $idx_awn ) {
						$content_true = $start_wherenot . $content_after_wherenot;
						if ( count( $content_wn_not_setback_arr ) <= count( $content_wn_not_arr ) ) {
							foreach ( $content_wn_not_arr as $content_wn_not ) {
								$content_filter[] = $start_wherenot . $content_wn_not;
							}
						}
					}

					++$idx_awn;
				}
			} else {
				if ( ( true === $this->track_heavy_selectors ) || ( true === $this->traceitrm_follow ) ) {
					irld_trace_log( 'found 2 or more :where(:not( in ' . $cond_wherenot, 0 );
				}
				foreach ( $arr_wherenot as $wherenot ) {
					if ( 0 === $idx_awn ) {
						$start_wherenot = trim( $wherenot );
					} else {
						$arr_content_wherenot = explode( '))', $wherenot );
						$content_wherenot     = '';
						if ( isset( $arr_content_wherenot[1] ) ) {
							array_shift( $arr_content_wherenot );
							$content_after_wherenot .= implode( '))', $arr_content_wherenot );
							if ( '' === trim( $content_after_wherenot ) ) {
								$content_after_wherenot = '';
							}
						}
					}

					if ( ( $count_arr_wherenot - 1 ) === $idx_awn ) {
						$content_true     = $start_wherenot . $content_after_wherenot;
						$content_filter[] = '';
					}

					++$idx_awn;
				}
			}

			$arr_res['true'] = $content_true;
			if ( count( $content_filter ) > 0 ) {
				$wni = 0;
				foreach ( $content_filter as $content_wn_not ) {
					$arr_res[ 'filter' . $wni ] = $content_wn_not;
					++$wni;
				}

				$arr_res['filter_index'] = $filtering_index;
			}
			if ( true === $this->traceitrm_follow ) {
				irld_trace_log( 'ending where not extract with array: ' . wp_json_encode( $arr_res, JSON_PRETTY_PRINT ), 0 );
			}
		}

		if ( '' !== $cond_wherebodynot ) {
			$arr_wherebodynot           = explode( ':where(body:not(', $cond_wherebodynot );
			$count_arr_wherebodynot     = count( $arr_wherebodynot );
			$idx_awn                    = 0;
			$filtering_index            = -1;
			$content_after_wherebodynot = '';
			$content_wherebodynot       = '';
			$content_true               = '';
			$content_filter             = '';
			if ( 2 === $count_arr_wherebodynot ) {
				foreach ( $arr_wherebodynot as $where ) {
					if ( 0 === $idx_awn ) {
						$start_wherebodynot = trim( $where );
					} else {
						$arr_content_wherebodynot = explode( '))', $where );
						$content_wherebodynot     = $arr_content_wherebodynot[0];
						if ( count( explode( '(', $where ) ) > 1 ) {
							$content_wherebodynot = $content_wherebodynot . ')';
						}

						if ( isset( $arr_content_wherebodynot[1] ) ) {
							array_shift( $arr_content_wherebodynot );
							$content_after_wherebodynot .= implode( '))', $arr_content_wherebodynot );
							if ( '' === trim( $content_after_wherebodynot ) ) {
								$content_after_wherebodynot = '';
							}
						}
					}

					if ( ( $count_arr_wherebodynot - 1 ) === $idx_awn ) {
						if ( '' !== $start_wherebodynot ) {
							$start_wherebodynot = $start_wherebodynot;
						}

						$content_true   = $start_wherebodynot . $content_after_wherebodynot;
						$content_filter = $content_wherebodynot;
					}

					++$idx_awn;
				}
			} else {
				if ( true === $this->track_heavy_selectors ) {
					irld_trace_log( 'found 2 or more :where(body:not(: in ' . $cond_wherebodynot . ', count:' . $count_arr_wherebodynot, 0 );
				}

				foreach ( $arr_wherebodynot as $where ) {
					if ( 0 === $idx_awn ) {
						$start_wherebodynot = trim( $where );
					} else {
						$arr_content_wherebodynot = explode( ')', $where );
						$content_wherebodynot     = '';
						if ( isset( $arr_content_wherebodynot[1] ) ) {
							array_shift( $arr_content_wherebodynot );
							$content_after_wherebodynot .= implode( ')', $arr_content_wherebodynot );
							if ( '' === trim( $content_after_wherebodynot ) ) {
								$content_after_wherebodynot = '';
							}
						}
					}

					if ( ( $count_arr_wherebodynot - 1 ) === $idx_awn ) {
						if ( '' !== $start_wherebodynot ) {
							$start_wherebodynot = $start_wherebodynot;
						}

						$content_true   = $start_wherebodynot . $content_after_wherebodynot;
						$content_filter = $content_wherebodynot;
					}

					++$idx_awn;
				}
			}

			$arr_res['true'] = trim( $content_true );
			if ( '' !== $content_filter ) {
				$arr_res['false'] = $content_filter;
			}
		}

		return $arr_res;
	}

	/**
	 * Extracts :not(-clauses.
	 *
	 * @param string $cond_not string holding possible :not(.
	 *
	 * @return array $arr_res holding metainformation on the examinated subject :not(-clauses.
	 */
	protected function extract_notclauses( $cond_not ) {
		$arr_res = array();
		if ( '' !== $cond_not ) {
			$arr_not           = explode( ':not(', $cond_not );
			$count_arr_not     = count( $arr_not );
			$idx_awn           = 0;
			$arr_res           = array();
			$filtering_index   = -1;
			$content_after_not = '';
			$content_not       = '';
			$content_true      = '';
			$content_filter    = array();
			if ( 2 === $count_arr_not ) {
				foreach ( $arr_not as $not ) {
					if ( 0 === $idx_awn ) {
						$start_not = trim( $not );
						if ( strlen( $start_not ) > 0 ) {
							$arr_start_not   = explode( ' ', $start_not );
							$filtering_index = count( $arr_start_not );
						} else {
							$filtering_index = 0;
						}
					} else {
						$arr_content_not           = explode( ')', trim( $not ) );
						$arr_content_not_selectors = explode( ',', $arr_content_not[0] );
						if ( isset( $arr_content_not[1] ) ) {
							if ( '' !== trim( $arr_content_not[1] ) ) {
								array_shift( $arr_content_not );
								$content_after_not .= implode( ')', $arr_content_not );
							}

							if ( '' === trim( $content_after_not ) ) {
								$content_after_not = '';
							}
						}
					}

					if ( ( $count_arr_not - 1 ) === $idx_awn ) {
						$content_true = $start_not . $content_after_not;
						foreach ( $arr_content_not_selectors as $content_not_sel ) {
							$content_filter[] = $start_not . $content_not_sel;
						}
					}

					++$idx_awn;
				}
			} else {
				foreach ( $arr_not as $not ) {
					if ( 0 === $idx_awn ) {
						$start_not = trim( $not );
						if ( strlen( $start_not ) > 0 ) {
							$arr_start_not   = explode( ' ', $start_not );
							$filtering_index = count( $arr_start_not );
						} else {
							$filtering_index = 0;
						}
					} else {
						$arr_content_not           = explode( ')', $not );
						$arr_content_not_selectors = explode( ',', $arr_content_not[0] );
						if ( isset( $arr_content_not[1] ) ) {
							if ( '' !== trim( $arr_content_not[1] ) ) {
								array_shift( $arr_content_not );
								$content_after_not .= implode( ')', $arr_content_not );
							}

							if ( '' === trim( $content_after_not ) ) {
								$content_after_not = '';
							}
						}
					}

					if ( ( $count_arr_not - 1 ) === $idx_awn ) {
						$content_true = $start_not . $content_after_not;
					}

					if ( $idx_awn > 0 ) {
						foreach ( $arr_content_not_selectors as $content_not_sel ) {
							$content_filter[] = $start_not . $content_not_sel;
						}
					}

					++$idx_awn;
				}
			}

			$arr_res['true'] = $content_true;
			if ( '' !== $content_filter[0] ) {
				$count_content_filter = count( $content_filter );
				for ( $f = 0; $f < $count_content_filter; $f++ ) {
					$arr_res[ 'filter' . $f ] = $content_filter[ $f ];
				}

				$arr_res['filter_index'] = $filtering_index;
			}
		}

		return $arr_res;
	}

	/**
	 * Extracts :has( and :is(-clauses.
	 *
	 * @param string $cond_has string holding possible :has(.
	 * @param string $cond_is string holding possible :is(.
	 * @return array $ret holding metainformation on the examinated subject :is( or :has(-clauses.
	 */
	protected function extract_ishasclauses( $cond_has, $cond_is ) {
		$ret = array();
		// Handling for [ is here, because of potential +,~s .
		$searchterms    = array( 'is', 'has' );
		$trace_selector = $cond_has . ',' . $cond_is;
		for ( $j = 0; $j < 2; $j++ ) {
			$searchterm         = $searchterms[ $j ];
			$ret[ $searchterm ] = array();
			if ( 1 === $j ) {
				$cond_is = $cond_has;
			}

			$orig_cond_ishas = $cond_is;
			$hasis           = false;
			$iss             = array();
			$newselectors    = array();
			$countiss        = 1;
			do {
				if ( str_replace( ':' . $searchterm . '(', '', $cond_is ) !== $cond_is ) {
					$hasis          = true;
					$startis        = strpos( $cond_is, ':' . $searchterm . '(' );
					$is_wrk         = substr( $cond_is, $startis );  // complete string after :has(.
					$is_wrk         = substr( $is_wrk, 0, strpos( $is_wrk, ')' ) + 1 );   // string after :has( up to first ).
					$is             = $is_wrk;
					$iss[]          = str_replace( ':' . $searchterm . '(', '', str_replace( ')', '', $is ) );
					$newselectors[] = substr( $cond_is, 0, strpos( $cond_is, ':' . $searchterm . '(' ) ) . ' IS' . $countiss . '@ ' . substr( $cond_is, strpos( $cond_is, ')', strpos( $cond_is, ':' . $searchterm . '(' ) ) + 1 );
					$cond_is        = substr( $cond_is, strpos( $cond_is, ')', $startis ) + 1 );
					++$countiss;

					if ( $countiss > 8 ) {
						if ( true === $this->track_heavy_selectors ) {
							irld_trace_log( 'INFO: found heavy selector with more than eight (' . $countiss . ') elements to handle: ' . "\n" . 'Selector: ' . $trace_selector, 0 );
							$this->traceitrm_follow = true;
						}

						break;
					}
				} else {
					$countiss = 0;
				}
			} while ( 0 !== $countiss );

			$countis = count( $iss );
			for ( $k = 0; $k < $countis; $k++ ) {
				$isselectors      = explode( ',', $iss[ $k ] );
				$countisselectors = count( $isselectors );
				$baseselector     = $newselectors[ $k ];
				for ( $m = 0; $m < $countisselectors; $m++ ) {
					$isselector = trim( $isselectors[ $m ] );
					if ( 1 === $j ) {
						// has.
						$isselector = ' ' . $isselector;
					} elseif ( 0 !== strpos( $isselector, '.' ) ) {
						if ( 0 !== strpos( $isselector, '#' ) ) {
							$isselector = ' ' . $isselector;
						}
					}

					if ( str_replace( ' IS' . ( $k + 1 ) . '@ ', '', $baseselector ) !== $baseselector ) {
						if ( ' IS' . ( $k + 1 ) . '@ ' !== $baseselector ) {
							$ret[ $searchterm ][] = trim( str_replace( ' IS' . ( $k + 1 ) . '@ ', $isselector, $baseselector ) );
						}
					}
				}
			}
		}

		if ( ',' !== trim( $trace_selector ) ) {
			if ( str_replace( ':not(', '', $trace_selector ) !== $trace_selector ) {
				if ( true === $this->track_heavy_selectors ) {
					irld_trace_log( 'INFO: exceptional :is( or :has( with :not( found: ' . wp_json_encode( $ret, JSON_PRETTY_PRINT ) . "\n" . 'Selector: ' . $trace_selector, 0 );
					$this->traceitrm_follow = true;
				}
			}
		}

		return $ret;
	}

	/**
	 * Checks if $selectorarray can move to CSS below.
	 *
	 * @param array $selectorarray array holding found selectors of a CSS-argument.
	 * @param array $bufferinclasses classes found in the model.
	 * @param array $bufferinids ids found in the model.
	 * @param array $bufferintags tags found in the model.
	 * @param array $bufferinputtypes types (of inputs) found in the model.
	 *
	 * @return int $ret code indicating if selector can be moved to below css.
	 */
	protected function check_below_the_fold( $selectorarray, $bufferinclasses, $bufferinids, $bufferintags, $bufferinputtypes ) {
		$ret = 0;
		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$browser_lang = substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ), 0, 2 );
		} else {
			$browser_lang = 'en';
		}

		// @font-face could be processed once all critical CSS is there - here we skip and leave them above.
		if ( ( str_replace( '@font-face', '', $selectorarray['selector'] ) === $selectorarray['selector'] ) && ( '' !== trim( $selectorarray['selector'] ) ) ) {
			$selectorarrayselector = trim( $selectorarray['selector'] );
			// list of selectors that will be traced to errorlog.
			$traceitrm        = $this->selectors_to_track;
			$traceitrm_follow = false;
			if ( str_replace( 'input.button', '', $selectorarrayselector ) !== $selectorarrayselector ) {
				// hold on. if a button is used as an input type, then the button can be referenced as input in CSS ... which is quite a fail.
				// Source: Woocommerce.
				$selectorarrayselector = str_replace( 'input.button', 'button.button', $selectorarrayselector );
			}

			// As $selectorarrayselector will be changed we store away the original in $selectorarrayselectortrace.
			$selectorarrayselectortrace = trim( $selectorarrayselector );

			// Is tracing active?
			if ( true === in_array( $selectorarrayselectortrace, $traceitrm, true ) ) {
				irld_trace_log( 'LOGGING " ' . $selectorarrayselectortrace . ' " , it was found in check_below_the_fold', 0 );
				$traceitrm_follow       = true;
				$this->traceitrm_follow = true;
			} else {
				foreach ( $traceitrm as $traceitem ) {
					if ( str_replace( $traceitem, '', $selectorarrayselectortrace ) !== $selectorarrayselectortrace ) {
						irld_trace_log( 'LOGGING " ' . $traceitem . ' " , it was found in check_below_the_fold in selector: ' . $selectorarrayselectortrace, 0 );

						$traceitrm_follow       = true;
						$this->traceitrm_follow = true;
						break;
					}
				}
			}

			if ( false === $traceitrm_follow ) {
				$this->traceitrm_follow = false;
			}

			// If any of these pseudoclass is present in $selectorarrayselector the selector may go below with $ret = 2.
			$pseudoclasses_go_below     = ':hover,:active,:focus,:target,::-moz-focus-inner,:-moz-focusring,::-webkit-file-upload-button';
			$pseudoclasses_go_below_arr = explode( ',', $pseudoclasses_go_below );
			foreach ( $pseudoclasses_go_below_arr as $pseudoclass ) {
				if ( str_replace( $pseudoclass, '', $selectorarrayselector ) !== $selectorarrayselector ) {
					$ret = 2;
					if ( true === $this->traceitrm_follow ) {
						irld_trace_log( 'Resuming in go below (pseudoclass) with $ret: ' . $ret . ' on ' . $selectorarrayselector, 0 );
					}

					return $ret;
				}
			}

			$stays_above = false;
			// On the other hand some selectors always stay above.
			$selectors_must_stay_above     = ':root,:after,:before,*,::after,::before,::selection';
			$selectors_must_stay_above_arr = explode( ',', $selectors_must_stay_above );
			foreach ( $selectors_must_stay_above_arr as $selector_must_stay_above ) {
				if ( $selector_must_stay_above === $selectorarrayselector ) {
					$stays_above = true;
					break;
				}
			}

			if ( str_replace( '@media', '', $selectorarrayselector ) !== $selectorarrayselector ) {
				$stays_above = true;
			}

			if ( true === $stays_above ) {
				return $ret;
			}

			$cond_isnot        = '';
			$cond_is           = '';
			$cond_hasnot       = '';
			$cond_has          = '';
			$cond_wherebodynot = '';
			$cond_wherenot     = '';
			$cond_not          = '';
			$cond_where        = '';
			if ( str_replace( ':is(:not(', '', $selectorarrayselector ) !== $selectorarrayselector ) {
				$cond_isnot = $selectorarrayselector;
			} elseif ( str_replace( ':is(', '', $selectorarrayselector ) !== $selectorarrayselector ) {
					$cond_is = $selectorarrayselector;
			}

			if ( str_replace( ':has(:not(', '', $selectorarrayselector ) !== $selectorarrayselector ) {
				$cond_hasnot = $selectorarrayselector;
			} elseif ( str_replace( ':has(', '', $selectorarrayselector ) !== $selectorarrayselector ) {
					$cond_has = $selectorarrayselector;
			}

			$retarr = $this->extract_ishasclauses( $cond_has, $cond_is );
			$selarr = array();
			if ( isset( $retarr['is'] ) ) {
				$count_retarr_is = count( $retarr['is'] );
				if ( $count_retarr_is > 0 ) {
					for ( $p = 0; $p < $count_retarr_is; $p++ ) {
						if ( '' !== trim( $retarr['is'][ $p ] ) ) {
							$selarr[] = $retarr['is'][ $p ];
						}
					}
				}
			}

			if ( isset( $retarr['has'] ) ) {
				$count_retarr_has = count( $retarr['has'] );
				if ( $count_retarr_has > 0 ) {
					for ( $p = 0; $p < $count_retarr_has; $p++ ) {
						if ( '' !== trim( $retarr['has'][ $p ] ) ) {
							$selarr[] = $retarr['has'][ $p ];
						}
					}
				}
			}

			if ( 0 === count( $selarr ) ) {
				$selarr[] = $selectorarrayselector;
			}

			if ( true === $this->traceitrm_follow ) {
				if ( count( $selarr ) > 0 ) {
					irld_trace_log( 'selarr ' . wp_json_encode( $selarr, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselectortrace, 0 );
				} else {
					irld_trace_log( 'selarr empty for selector: ' . $selectorarrayselectortrace, 0 );
				}
			}

			$retarr       = array();
			$selarr_where = array();
			$d            = 0;
			$count_selarr = count( $selarr );
			if ( $count_selarr > 0 ) {
				for ( $p = 0; $p < $count_selarr; $p++ ) {
					if ( str_replace( ':where(body:not(', '', $selarr[ $p ] ) !== $selarr[ $p ] ) {
						$cond_wherebodynot = $selarr[ $p ];
					} elseif ( str_replace( ':where(:not(', '', $selarr[ $p ] ) !== $selarr[ $p ] ) {
							$cond_wherenot = $selarr[ $p ];
					} else {
						if ( str_replace( ':not(', '', $selarr[ $p ] ) !== $selarr[ $p ] ) {
							$cond_not = $selarr[ $p ];
						}

						if ( str_replace( ':where(', '', $selarr[ $p ] ) !== $selarr[ $p ] ) {
							$cond_where = $selarr[ $p ];
						}
					}

					$retarr = $this->extract_whereclauses( $cond_where, $cond_wherenot, $cond_wherebodynot );
					if ( count( $retarr ) > 0 ) {
						foreach ( $retarr as $retsel => $retselval ) {
							if ( 'true' === substr( $retsel, 0, 4 ) ) {
								$selarr_where[ $p + $d ]                 = array();
								$selarr_where[ $p + $d ]['selector']     = $retselval;
								$selarr_where[ $p + $d ]['selectorrole'] = $retsel;
								++$d;
							}

							if ( 'false' === $retsel ) {
								$selarr_where[ $p + $d ]                 = array();
								$selarr_where[ $p + $d ]['selector']     = $retselval;
								$selarr_where[ $p + $d ]['selectorrole'] = 'false';
								++$d;
							}

							if ( 'filter_index' === $retsel ) {
								$selarr_where[ $p + $d ]                 = array();
								$selarr_where[ $p + $d ]['selector']     = $retselval;
								$selarr_where[ $p + $d ]['selectorrole'] = 'filterindex';
								++$d;
							} elseif ( 'filter' === substr( $retsel, 0, 6 ) ) {
								$selarr_where[ $p + $d ]                 = array();
								$selarr_where[ $p + $d ]['selector']     = $retselval;
								$selarr_where[ $p + $d ]['selectorrole'] = $retsel;
								++$d;
							}
						}
					} else {
						$selarr_where[ $p + $d ]                 = array();
						$selarr_where[ $p + $d ]['selector']     = $selarr[ $p ];
						$selarr_where[ $p + $d ]['selectorrole'] = 'true';
					}

					$cond_wherebodynot = '';
					$cond_wherenot     = '';
					$cond_where        = '';
					$retarr            = array();
				}
			}

			if ( count( $selarr_where ) > 0 ) {
				if ( true === $this->traceitrm_follow ) {
					irld_trace_log( 'selarr_where: ' . wp_json_encode( $selarr_where, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselectortrace, 0 );
				}
			} elseif ( true === $this->traceitrm_follow ) {
					irld_trace_log( 'selarr_where empty for  selector: ' . $selectorarrayselectortrace, 0 );
			}

			$retarr             = array();
			$selarr_not         = array();
			$irregular_msg_made = false;
			$d                  = 0;
			$count_selarr_where = count( $selarr_where );
			if ( $count_selarr_where > 0 ) {
				for ( $p = 0; $p < $count_selarr_where; $p++ ) {
					// Proceeding string ":is(:not(".
					if ( ! isset( $selarr_where[ $p ]['selector'] ) ) {
						if ( false === $irregular_msg_made ) {
							if ( true === $this->track_heavy_selectors ) {
								irld_trace_log( 'INFO: $selarr_where is irregular: ' . wp_json_encode( $selarr_where, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselectortrace, 0 );
								$this->traceitrm_follow = true;
								$irregular_msg_made     = true;
							}
						}

						++$count_selarr_where;
					} else {
						if ( str_replace( ':is(:not(', '', $selarr_where[ $p ]['selector'] ) !== $selarr_where[ $p ]['selector'] ) {
							$cond_isnot = $selarr_where[ $p ]['selector'];
						} elseif ( str_replace( ':has(:not(', '', $selarr_where[ $p ]['selector'] ) !== $selarr_where[ $p ]['selector'] ) {
								$cond_hasnot = $selarr_where[ $p ]['selector'];
						} elseif ( ( str_replace( ':where(body:not(', '', $selarr_where[ $p ]['selector'] ) === $selarr_where[ $p ]['selector'] ) &&
								( str_replace( ':where(:not(', '', $selarr_where[ $p ]['selector'] ) === $selarr_where[ $p ]['selector'] ) &&
								( str_replace( ':not(', '', $selarr_where[ $p ]['selector'] ) !== $selarr_where[ $p ]['selector'] ) ) {

								$cond_not = $selarr_where[ $p ]['selector'];
						}

						$retarr = $this->extract_notclauses( $cond_not );
						if ( count( $retarr ) > 0 ) {
							foreach ( $retarr as $retsel => $retselval ) {
								if ( 'true' === substr( $retsel, 0, 4 ) ) {
									$selarr_not[ $p + $d ]                 = array();
									$selarr_not[ $p + $d ]['selector']     = $retselval;
									$selarr_not[ $p + $d ]['selectorrole'] = $retsel;
									++$d;
								} elseif ( 'false' === $retsel ) {
									$selarr_not[ $p + $d ]                 = array();
									$selarr_not[ $p + $d ]['selector']     = $retselval;
									$selarr_not[ $p + $d ]['selectorrole'] = 'false';
									++$d;
								} elseif ( 'filter_index' === $retsel ) {
									$selarr_not[ $p + $d ]                 = array();
									$selarr_not[ $p + $d ]['selector']     = $retselval;
									$selarr_not[ $p + $d ]['selectorrole'] = 'filterindex';
									++$d;
								} elseif ( 'filter' === substr( $retsel, 0, 6 ) ) {
									$selarr_not[ $p + $d ]                 = array();
									$selarr_not[ $p + $d ]['selector']     = $retselval;
									$selarr_not[ $p + $d ]['selectorrole'] = $retsel;
									++$d;
								}
							}
						} else {
							$selarr_not[ $p + $d ]                 = array();
							$selarr_not[ $p + $d ]['selector']     = $selarr_where[ $p ]['selector'];
							$selarr_not[ $p + $d ]['selectorrole'] = $selarr_where[ $p ]['selectorrole'];
						}

						$cond_isnot  = '';
						$cond_hasnot = '';
						$cond_not    = '';
						$retarr      = array();

					}
				}
			}

			if ( count( $selarr_not ) > 0 ) {
				if ( true === $this->traceitrm_follow ) {
					irld_trace_log( 'selarr_not: ' . wp_json_encode( $selarr_not, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselectortrace, 0 );
				}
			} elseif ( true === $this->traceitrm_follow ) {
					irld_trace_log( 'selarr_not empty for selector: ' . $selectorarrayselectortrace, 0 );
			}

			$sas_arr              = $selarr_not;   // sas means selectorarrayselector.
			$sas                  = '';
			$this->filterindex    = 0;
			$this->countfilters   = 0;
			$this->filterelements = array();
			$count_false          = 0;
			$count_true           = 0;
			$count_filter         = 0;
			$all_true             = true;
			$filtering            = false;
			// inspecting sas_arr.
			foreach ( $sas_arr as $selectorarray ) {
				if ( 'filterindex' === $selectorarray['selectorrole'] ) {
					$this->filterindex = intval( $selectorarray['selector'] );
				} elseif ( 'false' === $selectorarray['selectorrole'] ) {
					++$count_false;
					$all_true = false;
				} elseif ( 'true' === substr( $selectorarray['selectorrole'], 0, 4 ) ) {
					++$count_true;
					if ( 'true' === $selectorarray['selectorrole'] ) {
						$all_true = false;
					}
				} elseif ( 'filter' === substr( $selectorarray['selectorrole'], 0, 6 ) ) {
					++$count_filter;
					$all_true = false;
				}
			}

			$this->countfilters = $count_filter;
			$sastrue            = '';
			// handling all elements in sas_arr.
			foreach ( $sas_arr as $selectorarray ) {
				$filtering = false;
				if ( 'true' === substr( $selectorarray['selectorrole'], 0, 4 ) ) {
					$sas              = $selectorarray['selector'];
					$sastrue          = $sas;
					$ret_zero_as_true = true;
				} elseif ( 'false' === $selectorarray['selectorrole'] ) {
					$sas              = $selectorarray['selector'];
					$ret_zero_as_true = false;
				} elseif ( 'filterindex' === $selectorarray['selectorrole'] ) {
					$sas = '';
				} elseif ( 'filter' === substr( $selectorarray['selectorrole'], 0, 6 ) ) {
					$sas = $selectorarray['selector'];
					if ( ( str_replace( ':first-child', '', $sas ) === $sastrue ) ||
						( str_replace( ':last-child', '', $sas ) === $sastrue ) ) {
						$sas = '';
					}

					$ret_zero_as_true = true;
					$filtering        = true;
				}

				if ( '' !== $sas ) {
					if ( true === $this->traceitrm_follow ) {
						irld_trace_log( $selectorarray['selectorrole'] . ' for "' . $selectorarrayselectortrace . '" $sas: "' . $sas . '", filterindex: ' . $this->filterindex, 0 );
					}

					$ret                   = 0;
					$selectorarrayselector = $sas;
					// handling for :nth is here, because of potential +s.
					$hasnth = false;
					$nth    = '';
					if ( str_replace( ':nth', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$hasnth                = true;
						$nth                   = substr( $selectorarrayselector, strpos( $selectorarrayselector, ':nth' ) );
						$newselector           = substr( $selectorarrayselector, 0, strpos( $selectorarrayselector, ':nth' ) );
						$selectorarrayselector = $newselector;
					}

					// handling for [ is here, because of potential +,~s.
					$hasbracket    = false;
					$brackets      = array();
					$countbrackets = -1;
					do {
						if ( str_replace( '[', '', $selectorarrayselector ) !== $selectorarrayselector ) {
							$hasbracket            = true;
							$countbrackets         = count( explode( '[', $selectorarrayselector ) ) - 1;
							$bracket               = substr( $selectorarrayselector, strpos( $selectorarrayselector, '[' ), strpos( $selectorarrayselector, ']' ) - strpos( $selectorarrayselector, '[' ) ) . ']';
							$brackets[]            = $bracket;
							$newselector           = substr( $selectorarrayselector, 0, strpos( $selectorarrayselector, '[' ) ) . substr( $selectorarrayselector, strpos( $selectorarrayselector, ']' ) + 1 );
							$selectorarrayselector = $newselector;
							if ( true === $this->traceitrm_follow ) {
								irld_trace_log( 'bracket ' . $bracket . ' in selectorarrayselector ' . $selectorarrayselector, 0 );
							}
						} else {
							$countbrackets = 0;
						}
					} while ( 0 !== $countbrackets );

					$remove_doubledot = false;
					if ( str_replace( ':disabled', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$brackets[]       = '[disabled]';
						$remove_doubledot = true;
					}

					if ( str_replace( ':enabled', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$brackets[]       = '[enabled]';
						$remove_doubledot = true;
					}

					if ( str_replace( ':checked', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$brackets[]       = '[checked]';
						$remove_doubledot = true;
					}

					if ( count( $brackets ) > 0 ) {
						$retbracket = $this->check_brackets( $brackets, $selectorarrayselector, $count_false, $count_filter );

						if ( $retbracket > 0 ) {
							$ret = $retbracket;
							if ( true === $this->traceitrm_follow ) {
								irld_trace_log( 'brackets ' . wp_json_encode( $brackets, JSON_PRETTY_PRINT ) . ' return selector go down in selectorarrayselector ' . $selectorarrayselector, 0 );
							}

							break;
						} elseif ( true === $this->traceitrm_follow ) {
							irld_trace_log( 'brackets ' . wp_json_encode( $brackets, JSON_PRETTY_PRINT ) . ' returns bracket is valid ' . $selectorarrayselector, 0 );
						}
					}

					if ( true === $remove_doubledot ) {
						$selectorarrayselector = str_replace( ':disabled', '', $selectorarrayselector );
						$selectorarrayselector = str_replace( ':enabled', '', $selectorarrayselector );
						$selectorarrayselector = str_replace( ':checked', '', $selectorarrayselector );
					}

					// Now we identify possible '+', '~' and '>' in the $selectorarrayselector.

					/*
						+ sign
					 *  It will only select the first element that is immediately preceded by the former selector.
					 */
					if ( str_replace( ' + ', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$selector_plus_array = explode( ' + ', $selectorarrayselector );
					} elseif ( str_replace( '+ ', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$selector_plus_array = explode( '+ ', $selectorarrayselector );
					} elseif ( str_replace( ' +', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$selector_plus_array = explode( ' +', $selectorarrayselector );
					} else {
						$selector_plus_array = explode( '+', $selectorarrayselector );
					}

					$count_selector_plus_array = count( $selector_plus_array );
					$index_plus                = 0;
					$selector_plus_arr         = array();
					if ( $count_selector_plus_array > 1 ) {
						for ( $pli = 1; $pli < $count_selector_plus_array; $pli++ ) {
							$before_the_plus_arr = explode( ' ', $selector_plus_array[ $pli - 1 ] );
							$dotchainedelements  = 0;
							foreach ( $before_the_plus_arr as $before_the_plus_arr_elem ) {
								$dotsarr      = explode( '.', $before_the_plus_arr_elem );
								$countdotsarr = count( $dotsarr );
								if ( '.' === substr( $before_the_plus_arr_elem, 0, 1 ) ) {
									$dotchainedelements = $dotchainedelements + $countdotsarr - 2;
								} else {
									$dotchainedelements = $dotchainedelements + $countdotsarr - 1;
								}
							}

							$before_the_plusthan_arr = explode( '>', $selector_plus_array[ $pli - 1 ] );
							$before_the_tilde_arr    = explode( '~', $selector_plus_array[ $pli - 1 ] );
							$index_plus              = $index_plus + count( $before_the_plus_arr ) - count( $before_the_plusthan_arr ) - count( $before_the_tilde_arr ) + 2 + $dotchainedelements;
							$before_the_plus         = end( $before_the_plus_arr );
							$after_the_plus_arr      = explode( ' ', $selector_plus_array[ $pli ] );
							$after_the_plus          = array_shift( $after_the_plus_arr );
							if ( ! isset( $selector_plus_arr['before'] ) ) {
								$selector_plus_arr['before']     = array();
								$selector_plus_arr['before_mdl'] = array();
								$selector_plus_arr['after']      = array();
								$selector_plus_arr['after_mdl']  = array();
								$selector_plus_arr['index']      = array();
							}

							$prepare_before_the_plus_arr                   = $this->prepare_selector( $before_the_plus, $selectorarrayselectortrace );
							$clean_idstagsandclasses_before_the_plus       = $prepare_before_the_plus_arr[0];
							$store_clean_idstagsandclasses_before_the_plus = $prepare_before_the_plus_arr[0];
							$selectorarrayselector_before_the_plus         = $prepare_before_the_plus_arr[1];
							$before_mdl                                    = str_replace( '.', '', str_replace( '#', '', end( $clean_idstagsandclasses_before_the_plus ) ) );
							$prepare_after_the_plus_arr                    = $this->prepare_selector( $after_the_plus, $selectorarrayselectortrace );
							$clean_idstagsandclasses_after_the_plus        = $prepare_after_the_plus_arr[0];
							$store_clean_idstagsandclasses_after_the_plus  = $prepare_after_the_plus_arr[0];
							$selectorarrayselector_after_the_plus          = $prepare_after_the_plus_arr[1];
							$arrshift                                      = array_shift( $clean_idstagsandclasses_after_the_plus );
							if ( ! is_null( $arrshift ) ) {
								$after_mdl = str_replace( '.', '', str_replace( '#', '', $arrshift ) );
							} else {
								$after_mdl = '';
							}
							$selector_plus_arr['before'][]     = trim( $before_the_plus );
							$selector_plus_arr['before_mdl'][] = $before_mdl;
							$selector_plus_arr['after'][]      = trim( $after_the_plus );
							$selector_plus_arr['after_mdl'][]  = $after_mdl;
							$selector_plus_arr['index'][]      = $index_plus;

						}

						if ( true === $this->traceitrm_follow ) {
							irld_trace_log( 'selector_plus_arr ' . wp_json_encode( $selector_plus_arr, JSON_PRETTY_PRINT ) . '  with ' . $selectorarrayselector, 0 );
						}
					}

					/*
								> Sign:
					It is a child selector, which selects DIRECT child elements of a specified parent element.
					*/

					if ( str_replace( ' > ', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$selector_plusthan_array = explode( ' > ', $selectorarrayselector );
					} elseif ( str_replace( '> ', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$selector_plusthan_array = explode( '> ', $selectorarrayselector );
					} elseif ( str_replace( ' >', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$selector_plusthan_array = explode( ' >', $selectorarrayselector );
					} else {
						$selector_plusthan_array = explode( '>', $selectorarrayselector );
					}

					$count_selector_plusthan_array = count( $selector_plusthan_array );
					$index_plusthan                = 0;
					$selector_plusthan_arr         = array();
					if ( $count_selector_plusthan_array > 1 ) {
						for ( $pli = 1; $pli < $count_selector_plusthan_array; $pli++ ) {
							$before_the_plus_arr = explode( ' ', $selector_plusthan_array[ $pli - 1 ] );
							$dotchainedelements  = 0;
							foreach ( $before_the_plus_arr as $before_the_plus_arr_elem ) {
								$dotsarr      = explode( '.', $before_the_plus_arr_elem );
								$countdotsarr = count( $dotsarr );
								if ( '.' === substr( $before_the_plus_arr_elem, 0, 1 ) ) {
									$dotchainedelements = $dotchainedelements + $countdotsarr - 2;
								} else {
									$dotchainedelements = $dotchainedelements + $countdotsarr - 1;
								}
							}

							$before_the_plusthan_arr = explode( '+', $selector_plusthan_array[ $pli - 1 ] );
							$before_the_tilde_arr    = explode( '~', $selector_plusthan_array[ $pli - 1 ] );
							$index_plusthan          = $index_plusthan + count( $before_the_plus_arr ) - count( $before_the_plusthan_arr ) - count( $before_the_tilde_arr ) + 2 + $dotchainedelements;
							$before_the_plus         = end( $before_the_plus_arr );
							$after_the_plus_arr      = explode( ' ', $selector_plusthan_array[ $pli ] );
							$after_the_plus          = array_shift( $after_the_plus_arr );
							if ( ! isset( $selector_plusthan_arr['before'] ) ) {
								$selector_plusthan_arr['before']     = array();
								$selector_plusthan_arr['before_mdl'] = array();
								$selector_plusthan_arr['after']      = array();
								$selector_plusthan_arr['after_mdl']  = array();
								$selector_plusthan_arr['index']      = array();
							}

							$prepare_before_the_plus_arr                   = $this->prepare_selector( $before_the_plus, $selectorarrayselectortrace );
							$clean_idstagsandclasses_before_the_plus       = $prepare_before_the_plus_arr[0];
							$store_clean_idstagsandclasses_before_the_plus = $prepare_before_the_plus_arr[0];
							$selectorarrayselector_before_the_plus         = $prepare_before_the_plus_arr[1];
							$before_mdl                                    = str_replace( '.', '', str_replace( '#', '', end( $clean_idstagsandclasses_before_the_plus ) ) );
							$prepare_after_the_plus_arr                    = $this->prepare_selector( $after_the_plus, $selectorarrayselectortrace );
							$clean_idstagsandclasses_after_the_plus        = $prepare_after_the_plus_arr[0];
							$store_clean_idstagsandclasses_after_the_plus  = $prepare_after_the_plus_arr[0];
							$selectorarrayselector_after_the_plus          = $prepare_after_the_plus_arr[1];
							$arrshift                                      = array_shift( $clean_idstagsandclasses_after_the_plus );
							if ( ! is_null( $arrshift ) ) {
								$after_mdl = str_replace( '.', '', str_replace( '#', '', $arrshift ) );
							} else {
								$after_mdl = '';
							}

							$selector_plusthan_arr['before'][]     = trim( $before_the_plus );
							$selector_plusthan_arr['before_mdl'][] = $before_mdl;
							$selector_plusthan_arr['after'][]      = trim( $after_the_plus );
							$selector_plusthan_arr['after_mdl'][]  = $after_mdl;
							$selector_plusthan_arr['index'][]      = $index_plusthan;

						}

						if ( true === $this->traceitrm_follow ) {
							irld_trace_log( 'selector_plusthan_arr: ' . wp_json_encode( $selector_plusthan_arr, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselectortrace, 0 );
						}
					}

					/*
					~ (tilde) Sign:
					 * It is general sibling combinator and similar to Adjacent sibling combinator.
					 * The difference is that the second selector does NOT have to immediately follow the first one means It will select all elements that is preceded by the former selector.
					 */
					if ( str_replace( ' ~ ', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$selector_tilde_array = explode( ' ~ ', $selectorarrayselector );
					} elseif ( str_replace( ' ~', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$selector_tilde_array = explode( ' ~', $selectorarrayselector );
					} elseif ( str_replace( '~ ', '', $selectorarrayselector ) !== $selectorarrayselector ) {
						$selector_tilde_array = explode( '~ ', $selectorarrayselector );
					} else {
						$selector_tilde_array = explode( '~', $selectorarrayselector );
					}

					$count_selector_tilde_array = count( $selector_tilde_array );
					$index_tilde                = 0;
					$selector_tilde_arr         = array();
					if ( $count_selector_tilde_array > 1 ) {
						for ( $pli = 1; $pli < $count_selector_tilde_array; $pli++ ) {
							$before_the_tilde_arr = explode( ' ', $selector_tilde_array[ $pli - 1 ] );
							$dotchainedelements   = 0;
							foreach ( $before_the_tilde_arr as $before_the_tilde_arr_elem ) {
								$dotsarr      = explode( '.', $before_the_tilde_arr_elem );
								$countdotsarr = count( $dotsarr );
								if ( '.' === substr( $before_the_tilde_arr_elem, 0, 1 ) ) {
									$dotchainedelements = $dotchainedelements + $countdotsarr - 2;
								} else {
									$dotchainedelements = $dotchainedelements + $countdotsarr - 1;
								}
							}

							$before_the_plus_arr     = explode( '+', $selector_tilde_array[ $pli - 1 ] );
							$before_the_plusthan_arr = explode( '>', $selector_tilde_array[ $pli - 1 ] );
							$index_tilde             = $index_tilde + count( $before_the_tilde_arr ) - count( $before_the_plusthan_arr ) - count( $before_the_plus_arr ) + 2 + $dotchainedelements;
							$before_the_tilde        = end( $before_the_tilde_arr );
							$after_the_tilde_arr     = explode( ' ', $selector_tilde_array[ $pli ] );
							$after_the_tilde         = array_shift( $after_the_tilde_arr );
							if ( ! isset( $selector_tilde_arr['before'] ) ) {
								$selector_tilde_arr['before']     = array();
								$selector_tilde_arr['before_mdl'] = array();
								$selector_tilde_arr['after']      = array();
								$selector_tilde_arr['after_mdl']  = array();
								$selector_tilde_arr['index']      = array();
							}

							$prepare_before_the_tilde_arr                   = $this->prepare_selector( $before_the_tilde, $selectorarrayselectortrace );
							$clean_idstagsandclasses_before_the_tilde       = $prepare_before_the_tilde_arr[0];
							$store_clean_idstagsandclasses_before_the_tilde = $prepare_before_the_tilde_arr[0];
							$selectorarrayselector_before_the_tilde         = $prepare_before_the_tilde_arr[1];
							$before_mdl                                     = str_replace( '.', '', str_replace( '#', '', end( $clean_idstagsandclasses_before_the_tilde ) ) );
							$prepare_after_the_tilde_arr                    = $this->prepare_selector( $after_the_tilde, $selectorarrayselectortrace );
							$clean_idstagsandclasses_after_the_tilde        = $prepare_after_the_tilde_arr[0];
							$store_clean_idstagsandclasses_after_the_tilde  = $prepare_after_the_tilde_arr[0];
							$selectorarrayselector_after_the_tilde          = $prepare_after_the_tilde_arr[1];
							$arrshift                                       = array_shift( $clean_idstagsandclasses_after_the_tilde );
							if ( ! is_null( $arrshift ) ) {
								$after_mdl = str_replace( '.', '', str_replace( '#', '', $arrshift ) );
							} else {
								$after_mdl = '';
							}

							$selector_tilde_arr['before'][]     = trim( $before_the_tilde );
							$selector_tilde_arr['before_mdl'][] = $before_mdl;
							$selector_tilde_arr['after'][]      = trim( $after_the_tilde );
							$selector_tilde_arr['after_mdl'][]  = $after_mdl;
							$selector_tilde_arr['index'][]      = $index_tilde;
						}

						if ( true === $this->traceitrm_follow ) {
							irld_trace_log( 'selector_tilde_arr: ' . wp_json_encode( $selector_tilde_arr, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselectortrace, 0 );
						}
					}

					$selectorarrayselector_for_inon = $selectorarrayselector;
					$prepare_selector_arr           = $this->prepare_selector( $selectorarrayselector, $selectorarrayselectortrace );
					$clean_idstagsandclasses        = $prepare_selector_arr[0];
					$selectorarrayselector          = $prepare_selector_arr[1];
					if ( true === $this->traceitrm_follow ) {
						$selectorarrayselector_after = implode( ' ', $clean_idstagsandclasses );
						if ( $selectorarrayselector !== $selectorarrayselector_after ) {
							irld_trace_log( ' " ' . $selectorarrayselector . ' " changed to " ' . $selectorarrayselector_after . ' " ', 0 );
						}
					}

					$fullidstagsandclasses = array();
					$k                     = 0;
					$whereclause_filled    = false;
					$whereclause_has_comma = false;
					if ( false === $stays_above ) {
						$scotch_pos = 0;
						foreach ( $clean_idstagsandclasses as $idstagsandclasscand ) {
							$scotch_ctrl = '';
							if ( '' !== trim( $idstagsandclasscand ) ) {
								$fullidstagsandclasses[ $k ] = array();
								$whereclause_activity        = 0;
								if ( ( str_replace( 'type="', '', $idstagsandclasscand ) === $idstagsandclasscand ) && ( str_replace( 'type=', '', $idstagsandclasscand ) === $idstagsandclasscand ) ) {
									// No input tag.
									// attr[title] and :before, :after, :link.
									$arrelem             = explode( '[', $idstagsandclasscand );
									$idstagsandclasscand = $arrelem[0];
								}

								if ( str_replace( ',', '', $idstagsandclasscand ) !== $idstagsandclasscand ) {
									// Then this class is out of a :where.
									$idstagsandclasscand   = str_replace( ',', '', $idstagsandclasscand );
									$whereclause_activity  = 1;
									$whereclause_has_comma = true;
								} elseif ( str_replace( ')', '', $idstagsandclasscand ) !== $idstagsandclasscand ) {
									// Then this class is last one out of a :where.
									$idstagsandclasscand  = str_replace( ')', '', $idstagsandclasscand );
									$whereclause_activity = 2;
								}

								$arrelem                = explode( ':', $idstagsandclasscand );
								$idstagsandclasscand    = $arrelem[0];
								$idstagsandclasscandarr = explode( '.', $idstagsandclasscand );
								$frstpos                = strpos( $idstagsandclasscand, '.' );
								if ( ( 0 === $frstpos ) && ( 2 === count( $idstagsandclasscandarr ) ) ) {
									// Proceeding class in idstagsandclasscand, like ".thisclass".
									$fullidstagsandclasses[ $k ]['val']                  = $idstagsandclasscandarr[1];
									$fullidstagsandclasses[ $k ]['type']                 = 'class';
									$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
									$fullidstagsandclasses[ $k ]['connects']             = 'in';
									if ( $k > 0 ) {
										if ( true === $this->traceitrm_follow ) {
											irld_trace_log( '1 scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
										}

										$scotch_pos    = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k - 1 ]['val'], $scotch_pos ) + strlen( $fullidstagsandclasses[ $k - 1 ]['val'] );
										$scotch_to_pos = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k ]['val'], $scotch_pos - 1 ) - 1;
										if ( $scotch_to_pos === $scotch_pos ) {
											$fullidstagsandclasses[ $k ]['connects'] = 'on';
											$scotch_ctrl                             = 'pos ' . $scotch_pos . ', ' . $fullidstagsandclasses[ $k - 1 ]['val'] . ' on ' . $fullidstagsandclasses[ $k ]['val'] . ' to pos ' . $scotch_to_pos . ', in ' . $selectorarrayselector_for_inon;
										} else {
											$fullidstagsandclasses[ $k ]['connects'] = 'in';
											$scotch_ctrl                             = 'pos ' . $scotch_pos . ', ' . $fullidstagsandclasses[ $k - 1 ]['val'] . ' in pos ' . $scotch_to_pos . ', ' . $fullidstagsandclasses[ $k ]['val'] . ' in ' . $selectorarrayselector_for_inon;

										}

										if ( true === $this->traceitrm_follow ) {
											irld_trace_log( '1 scotch_ctrl: ' . $scotch_ctrl, 0 );
										}
									}

									++$k;
								} elseif ( ( 0 === $frstpos ) && ( count( $idstagsandclasscandarr ) > 2 ) ) {
									// classes, like ".thisclass.thatclass".
									foreach ( $idstagsandclasscandarr as $elemsel ) {
										if ( '' !== trim( $elemsel ) ) {
											$fullidstagsandclasses[ $k ]['val']                  = $elemsel;
											$fullidstagsandclasses[ $k ]['type']                 = 'class';
											$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
											$fullidstagsandclasses[ $k ]['connects']             = '';
											if ( $k > 0 ) {
												if ( true === $this->traceitrm_follow ) {
													irld_trace_log( '2 scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
												}

												$scotch_pos    = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k - 1 ]['val'], $scotch_pos ) + strlen( $fullidstagsandclasses[ $k - 1 ]['val'] );
												$scotch_to_pos = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k ]['val'], $scotch_pos - 1 ) - 1;
												if ( $scotch_to_pos === $scotch_pos ) {
													$fullidstagsandclasses[ $k ]['connects'] = 'on';
													$scotch_ctrl                             = 'pos ' . $scotch_pos . ', ' . $fullidstagsandclasses[ $k - 1 ]['val'] . ' on ' . $fullidstagsandclasses[ $k ]['val'] . ' to pos ' . $scotch_to_pos . ', in ' . $selectorarrayselector_for_inon;
												} else {
													$fullidstagsandclasses[ $k ]['connects'] = 'in';
													$scotch_ctrl                             = 'pos ' . $scotch_pos . ', ' . $fullidstagsandclasses[ $k - 1 ]['val'] . ' in pos ' . $scotch_to_pos . ', ' . $fullidstagsandclasses[ $k ]['val'] . ' in ' . $selectorarrayselector_for_inon;

												}

												if ( true === $this->traceitrm_follow ) {
													irld_trace_log( '2 scotch_ctrl: ' . $scotch_ctrl, 0 );
												}
											}

											++$k;
										}
									}
								} elseif ( ( $frstpos > 0 ) & count( $idstagsandclasscandarr ) >= 2 ) {
									// first tag or id, then classes, like "#idsel.thisclass.thatclass".
									if ( str_replace( '#', '', $idstagsandclasscandarr[0] ) !== $idstagsandclasscandarr[0] ) {
										$fullidstagsandclasses[ $k ]['val']                  = str_replace( '#', '', $idstagsandclasscandarr[0] );
										$fullidstagsandclasses[ $k ]['type']                 = 'id';
										$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
										$fullidstagsandclasses[ $k ]['connects']             = 'in';
										if ( $k > 0 ) {
											if ( true === $this->traceitrm_follow ) {
												irld_trace_log( '3 scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
											}

											$scotch_pos    = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k - 1 ]['val'], $scotch_pos ) + strlen( $fullidstagsandclasses[ $k - 1 ]['val'] );
											$scotch_to_pos = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k ]['val'], $scotch_pos - 1 ) - 1;
											if ( $scotch_to_pos === $scotch_pos ) {
												$fullidstagsandclasses[ $k ]['connects'] = 'on';
												$scotch_ctrl                             = 'pos ' . $scotch_pos . ', ' . $fullidstagsandclasses[ $k - 1 ]['val'] . ' on ' . $fullidstagsandclasses[ $k ]['val'] . ' to pos ' . $scotch_to_pos . ', in ' . $selectorarrayselector_for_inon;
											} else {
												$fullidstagsandclasses[ $k ]['connects'] = 'in';
												$scotch_ctrl                             = 'pos ' . $scotch_pos . ', ' . $fullidstagsandclasses[ $k - 1 ]['val'] . ' in pos ' . $scotch_to_pos . ', ' . $fullidstagsandclasses[ $k ]['val'] . ' in ' . $selectorarrayselector_for_inon;
											}

											if ( true === $this->traceitrm_follow ) {
												irld_trace_log( '3 scotch_ctrl: ' . $scotch_ctrl, 0 );
											}
										}

										++$k;
									} elseif ( '' !== trim( strtolower( $idstagsandclasscandarr[0] ) ) ) {
										// tag.
										$fullidstagsandclasses[ $k ]['val']                  = strtolower( $idstagsandclasscandarr[0] );
										$fullidstagsandclasses[ $k ]['type']                 = 'tag';
										$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
										$fullidstagsandclasses[ $k ]['connects']             = 'in';
										if ( $k > 0 ) {
											$scotch_pos = strpos( $selectorarrayselector_for_inon, ' ' . $fullidstagsandclasses[ $k ]['val'], $scotch_pos );
											if ( true === $this->traceitrm_follow ) {
												irld_trace_log( '3b scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
											}
										}

										++$k;
									}

									$cntelem = count( $idstagsandclasscandarr );
									for ( $ix = 1; $ix < $cntelem; $ix++ ) {
										if ( '' !== trim( $idstagsandclasscandarr[ $ix ] ) ) {
											$fullidstagsandclasses[ $k ]['val']                  = trim( $idstagsandclasscandarr[ $ix ] );
											$fullidstagsandclasses[ $k ]['type']                 = 'class';
											$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
											$fullidstagsandclasses[ $k ]['connects']             = '';
											if ( $k > 0 ) {
												if ( true === $this->traceitrm_follow ) {
													irld_trace_log( '4 scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
												}

												$scotch_pos    = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k - 1 ]['val'], $scotch_pos ) + strlen( $fullidstagsandclasses[ $k - 1 ]['val'] );
												$scotch_to_pos = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k ]['val'], $scotch_pos - 1 ) - 1;
												if ( $scotch_to_pos === $scotch_pos ) {
													$fullidstagsandclasses[ $k ]['connects'] = 'on';
													$scotch_ctrl                             = 'pos ' . $scotch_pos . ', ' . $fullidstagsandclasses[ $k - 1 ]['val'] . ' on ' . $fullidstagsandclasses[ $k ]['val'] . ' to pos ' . $scotch_to_pos . ', in ' . $selectorarrayselector_for_inon;
												} else {
													$fullidstagsandclasses[ $k ]['connects'] = 'in';
													$scotch_ctrl                             = 'pos ' . $scotch_pos . ', ' . $fullidstagsandclasses[ $k - 1 ]['val'] . ' in pos ' . $scotch_to_pos . ', ' . $fullidstagsandclasses[ $k ]['val'] . ' in ' . $selectorarrayselector_for_inon;

												}

												if ( true === $this->traceitrm_follow ) {
													irld_trace_log( '4 scotch_ctrl: ' . $scotch_ctrl, 0 );
												}
											}

											++$k;
										}
									}
								} else {
									// id?.
									$idstagsandclasscandarr = explode( '#', $idstagsandclasscand );
									$frstpos                = strpos( $idstagsandclasscand, '#' );
									if ( ( 0 === $frstpos ) && ( 2 === count( $idstagsandclasscandarr ) ) ) {
										// Proceeding ID, like for example "#thisid".
										$fullidstagsandclasses[ $k ]['val']                  = $idstagsandclasscandarr[1];
										$fullidstagsandclasses[ $k ]['type']                 = 'id';
										$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
										$fullidstagsandclasses[ $k ]['connects']             = 'in';
										if ( $k > 0 ) {
											if ( true === $this->traceitrm_follow ) {
												irld_trace_log( '5 scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
											}

											$scotch_pos    = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k - 1 ]['val'], $scotch_pos ) + strlen( $fullidstagsandclasses[ $k - 1 ]['val'] );
											$scotch_to_pos = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k ]['val'], $scotch_pos - 1 ) - 1;
											if ( $scotch_to_pos === $scotch_pos ) {
												$fullidstagsandclasses[ $k ]['connects'] = 'on';
												$scotch_ctrl                             = 'pos ' . $scotch_pos . ', ' . $fullidstagsandclasses[ $k - 1 ]['val'] . ' on ' . $fullidstagsandclasses[ $k ]['val'] . ' to pos ' . $scotch_to_pos . ', in ' . $selectorarrayselector_for_inon;
											} else {
												$fullidstagsandclasses[ $k ]['connects'] = 'in';
												$scotch_ctrl                             = 'pos ' . $scotch_pos . ', ' . $fullidstagsandclasses[ $k - 1 ]['val'] . ' in pos ' . $scotch_to_pos . ', ' . $fullidstagsandclasses[ $k ]['val'] . ' in ' . $selectorarrayselector_for_inon;

											}

											if ( true === $this->traceitrm_follow ) {
												irld_trace_log( '5 scotch_ctrl: ' . $scotch_ctrl, 0 );
											}
										}

										++$k;
									} elseif ( ( $frstpos > 0 ) && ( 2 === count( $idstagsandclasscandarr ) ) ) {
										// first tag, then id, like "div#gaga".
										if ( '' !== trim( strtolower( $idstagsandclasscandarr[0] ) ) ) {
											$fullidstagsandclasses[ $k ]['val']                  = strtolower( $idstagsandclasscandarr[0] );
											$fullidstagsandclasses[ $k ]['type']                 = 'tag';
											$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
											$fullidstagsandclasses[ $k ]['connects']             = 'in';
											if ( $k > 0 ) {
												$scotch_pos = strpos( $selectorarrayselector_for_inon, ' ' . $fullidstagsandclasses[ $k ]['val'], $scotch_pos );
												if ( true === $this->traceitrm_follow ) {
													irld_trace_log( 'tag scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
												}
											}

											++$k;
										}

										$fullidstagsandclasses[ $k ]['val']                  = $idstagsandclasscandarr[1];
										$fullidstagsandclasses[ $k ]['type']                 = 'id';
										$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
										$fullidstagsandclasses[ $k ]['connects']             = 'on';
										if ( $k > 0 ) {
											$scotch_pos = strpos( $selectorarrayselector_for_inon, $fullidstagsandclasses[ $k - 1 ]['val'], $scotch_pos ) + strlen( $fullidstagsandclasses[ $k - 1 ]['val'] );
											if ( true === $this->traceitrm_follow ) {
												irld_trace_log( 'id scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
											}
										}

										++$k;
									} elseif ( str_replace( 'type="', '', $idstagsandclasscand ) !== $idstagsandclasscand ) {
										// tag or input.
										// type followed by =".
										$typearr                             = explode( 'type="', $idstagsandclasscand );
										$typearr2                            = explode( '"', $typearr[1] );
										$fullidstagsandclasses[ $k ]['val']  = $typearr2[0];
										$fullidstagsandclasses[ $k ]['type'] = 'type';
										$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
										$fullidstagsandclasses[ $k ]['connects']             = 'in';
										if ( $k > 0 ) {
											$scotch_pos = strpos( $selectorarrayselector_for_inon, ' ' . $fullidstagsandclasses[ $k ]['val'], $scotch_pos );
											if ( true === $this->traceitrm_follow ) {
												irld_trace_log( 'type 1 scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
											}
										}

										++$k;
									} elseif ( str_replace( 'type=', '', $idstagsandclasscand ) !== $idstagsandclasscand ) {
										// It's another type.
										$typearr                             = explode( 'type=', $idstagsandclasscand );
										$typearr2                            = explode( ']', $typearr[1] );
										$fullidstagsandclasses[ $k ]['val']  = $typearr2[0];
										$fullidstagsandclasses[ $k ]['type'] = 'type';
										$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
										$fullidstagsandclasses[ $k ]['connects']             = 'in';
										if ( $k > 0 ) {
											$scotch_pos = strpos( $selectorarrayselector_for_inon, ' ' . $fullidstagsandclasses[ $k ]['val'], $scotch_pos );
											if ( true === $this->traceitrm_follow ) {
												irld_trace_log( 'type 2 scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
											}
										}

										++$k;
									} elseif ( trim( strtolower( $idstagsandclasscand ) ) !== '' ) {
										// It's a tag.
										$fullidstagsandclasses[ $k ]['val']                  = strtolower( $idstagsandclasscand );
										$fullidstagsandclasses[ $k ]['type']                 = 'tag';
										$fullidstagsandclasses[ $k ]['whereclause_activity'] = $whereclause_activity;
										$fullidstagsandclasses[ $k ]['connects']             = 'in';
										if ( $k > 0 ) {
											$scotch_pos = strpos( $selectorarrayselector_for_inon, ' ' . $fullidstagsandclasses[ $k ]['val'], ( $scotch_pos + 1 ) );
											if ( true === $this->traceitrm_follow ) {
												irld_trace_log( 'tag end scotch_pos: ' . $scotch_pos . ', k=' . $k, 0 );
											}
										}

										++$k;
									}
								}
							}
						}

						if ( true === $this->traceitrm_follow ) {
							irld_trace_log( 'Resulting in this test against the cloud-list: ' . wp_json_encode( $fullidstagsandclasses, JSON_PRETTY_PRINT ) . '', 0 );
						}

						// reworking the connects for follows, plused on and tildes.

						$countfullidstagsandclasses = count( $fullidstagsandclasses );
						if ( isset( $selector_plus_arr['after_mdl'] ) ) {
							$countselector_plus_arr = count( $selector_plus_arr['after_mdl'] );
						} else {
							$countselector_plus_arr = 0;
						}

						if ( $countselector_plus_arr > 0 ) {
							for ( $sp = 0; $sp < $countselector_plus_arr; $sp++ ) {
								for ( $k = 1; $k < $countfullidstagsandclasses; $k++ ) {
									if ( isset( $fullidstagsandclasses[ $k ]['val'] ) ) {
										if ( $fullidstagsandclasses[ $k ]['val'] === $selector_plus_arr['after_mdl'][ $sp ] ) {
											if ( $fullidstagsandclasses[ $k - 1 ]['val'] === $selector_plus_arr['before_mdl'][ $sp ] ) {
												if ( $k === $selector_plus_arr['index'][ $sp ] ) {
													$fullidstagsandclasses[ $k ]['connects'] = 'follows';
													if ( true === $this->traceitrm_follow ) {
														irld_trace_log( 'follows for  ' . $fullidstagsandclasses[ $k ]['val'] . ' on element ' . ( $k + 1 ), 0 );
													}
												}
											}
										}
									}
								}
							}
						}

						// $selector_plusthan_arr
						if ( isset( $selector_plusthan_arr['after_mdl'] ) ) {
							$countselector_plusthan_arr = count( $selector_plusthan_arr['after_mdl'] );
						} else {
							$countselector_plusthan_arr = 0;
						}

						if ( $countselector_plusthan_arr > 0 ) {
							for ( $sp = 0; $sp < $countselector_plusthan_arr; $sp++ ) {
								for ( $k = 1; $k < $countfullidstagsandclasses; $k++ ) {
									if ( isset( $fullidstagsandclasses[ $k ]['val'] ) ) {
										if ( $fullidstagsandclasses[ $k ]['val'] === $selector_plusthan_arr['after_mdl'][ $sp ] ) {
											if ( $fullidstagsandclasses[ $k - 1 ]['val'] === $selector_plusthan_arr['before_mdl'][ $sp ] ) {
												if ( $k === $selector_plusthan_arr['index'][ $sp ] ) {
													$fullidstagsandclasses[ $k ]['connects'] = 'plused on';
													if ( true === $this->traceitrm_follow ) {
														irld_trace_log( 'selectorarray: "' . $selectorarrayselector . '" plused on for  ' . $fullidstagsandclasses[ $k ]['val'] . ' on element ' . ( $k + 1 ) . ', selector: ' . $selectorarrayselectortrace, 0 );
													}
												}
											}
										}
									}
								}
							}
						}

						// $selector_tilde_arr
						if ( isset( $selector_tilde_arr['after_mdl'] ) ) {
							$countselector_tilde_arr = count( $selector_tilde_arr['after_mdl'] );
						} else {
							$countselector_tilde_arr = 0;
						}

						if ( $countselector_tilde_arr > 0 ) {
							for ( $sp = 0; $sp < $countselector_tilde_arr; $sp++ ) {
								for ( $k = 1; $k < $countfullidstagsandclasses; $k++ ) {
									if ( isset( $fullidstagsandclasses[ $k ]['val'] ) ) {
										if ( $fullidstagsandclasses[ $k ]['val'] === $selector_tilde_arr['after_mdl'][ $sp ] ) {
											if ( $fullidstagsandclasses[ $k - 1 ]['val'] === $selector_tilde_arr['before_mdl'][ $sp ] ) {
												if ( $k === $selector_tilde_arr['index'][ $sp ] ) {
													$fullidstagsandclasses[ $k ]['connects'] = 'tildes';
													if ( true === $this->traceitrm_follow ) {
														irld_trace_log( 'selectorarray: "' . $selectorarrayselector . '" plused on for  ' . $fullidstagsandclasses[ $k ]['val'] . ' on element ' . ( $k + 1 ) . ', selector: ' . $selectorarrayselectortrace, 0 );
													}
												}
											}
										}
									}
								}
							}
						}

						// check for simple existence in the model for every element present.
						for ( $k = 0; $k < $countfullidstagsandclasses; $k++ ) {
							if ( isset( $fullidstagsandclasses[ $k ]['type'] ) ) {
								if ( 'tag' === $fullidstagsandclasses[ $k ]['type'] ) {
									$idtagclaasstype = 'tag';
									if ( false === array_key_exists( $fullidstagsandclasses[ $k ]['val'], $bufferintags ) ) {
										$ret = 2;
									}
								}

								if ( 'class' === $fullidstagsandclasses[ $k ]['type'] ) {
									$idtagclaasstype = 'class';
									if ( false === array_key_exists( $fullidstagsandclasses[ $k ]['val'], $bufferinclasses ) ) {
										if ( false === $this->numericjail_array_keys_check( $fullidstagsandclasses[ $k ]['val'], false ) ) {
											if ( 0 === $fullidstagsandclasses[ $k ]['whereclause_activity'] ) {
												$ret = 1;
											} elseif ( 2 === $fullidstagsandclasses[ $k ]['whereclause_activity'] ) {
												if ( ( true !== $whereclause_filled ) || ( false === $whereclause_has_comma ) ) {
													$ret = 1;
												}
											}
										}
									} elseif ( 1 === $fullidstagsandclasses[ $k ]['whereclause_activity'] ) {
										$whereclause_filled = true;
									}
								}

								if ( 'id' === $fullidstagsandclasses[ $k ]['type'] ) {
									$idtagclaasstype = 'id';
									if ( false === array_key_exists( $fullidstagsandclasses[ $k ]['val'], $bufferinids ) ) {
										if ( false === $this->numericjail_array_keys_check( $fullidstagsandclasses[ $k ]['val'], true ) ) {
											$ret = 2;
										}
									}
								}

								if ( 'type' === $fullidstagsandclasses[ $k ]['type'] ) {
									$idtagclaasstype = 'type';
									if ( false === array_key_exists( $fullidstagsandclasses[ $k ]['val'], $bufferinputtypes ) ) {
										$ret = 2;
									}
								}

								if ( $ret > 0 ) {
									if ( true === $this->traceitrm_follow ) {
										irld_trace_log( 'Resuming in go below in $ret: ' . $ret . ' on ' . $idtagclaasstype . ' with val: ' . $fullidstagsandclasses[ $k ]['val'], 0 );

									}

									break;
								}
							} elseif ( $countfullidstagsandclasses > 1 ) {
									$ret = 2;
								if ( true === $this->traceitrm_follow ) {
									irld_trace_log( 'Resuming in go below (unhandlable) with $ret: ' . $ret . ' on selector: ' . $selectorarrayselectortrace, 0 );
								}
							} elseif ( true === $this->traceitrm_follow ) {
									irld_trace_log( 'Resuming  with $ret: ' . $ret . ' on selector: ' . $selectorarrayselectortrace, 0 );
							}
						}

						if ( 0 === $ret ) {
							// after basic check against model it should stay above.
							// now check against models document cloud meta-data to see if sequence is present in page or not.
							if ( $countfullidstagsandclasses > 1 ) {
								$ret = $this->check_selector_sequence( $fullidstagsandclasses, $selectorarrayselector, $bufferinclasses, $bufferinids, $bufferintags, $bufferinputtypes );
							}
							if ( true === $this->traceitrm_follow ) {
								if ( 0 === $ret ) {
									irld_trace_log( 'Resuming in stay above in $ret: ' . $ret . ' on ' . wp_json_encode( $fullidstagsandclasses, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselectortrace, 0 );
								} else {
									irld_trace_log( 'Resuming in go below after check_selector_sequence in $ret: ' . $ret . ' on ' . wp_json_encode( $fullidstagsandclasses, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselectortrace, 0 );

								}
							}

							if ( 0 !== $ret ) {
								if ( true === $this->traceitrm_follow ) {
									irld_trace_log( 'Resuming in go below after check_selector_sequence in $ret: ' . $ret . ' on ' . wp_json_encode( $fullidstagsandclasses, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselector, 0 );
								}
							} else {
								// browser_lang eliminates another bunch of selectors.
								$ret = $this->check_browser_compat( $browser_lang, $selectorarrayselectortrace );
								if ( 0 !== $ret ) {
									if ( true === $this->traceitrm_follow ) {
										irld_trace_log( 'Resuming in go below after check_browser_compat in $ret: ' . $ret . ' on ' . wp_json_encode( $fullidstagsandclasses, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselectortrace, 0 );
									}
								}
							}
						}
					}

					// now find orientation depending on the result $ret and the situation with multiple selectors to check (true, filter, false...).
					// first the case when we have no false to handle.
					if ( ( 0 === $ret ) && ( 0 === $count_false ) ) {
						if ( $count_filter > 0 ) {
							if ( true === $filtering ) {
								// if we are about to filter.
								if ( isset( $this->filterelements['starts'] ) ) {
									if ( 0 === count( $this->filterelements['starts'] ) ) {
										// and the filter list has been filtered down, well then the selector can go below.
										$ret = 2;
										break;
									}
								}
							}
						} elseif ( false === $all_true ) {
							break;
						}
					} elseif ( ( 0 !== $ret ) && ( 0 === $count_false ) ) {
						if ( $count_filter > 0 ) {
							if ( true === $filtering ) {
								if ( isset( $this->filterelements['starts'] ) ) {
									if ( true === $this->traceitrm_follow ) {
										irld_trace_log( 'filterelements: ' . count( $this->filterelements['starts'] ) . ', selector: ' . $selectorarrayselectortrace, 0 );
									}

									if ( count( $this->filterelements['starts'] ) > 0 ) {
										// our filter didn't filter so we set the $ret to 0 and continue the loop.
										$ret = 0;
									}
								} else {
									if ( true === $this->traceitrm_follow ) {
										irld_trace_log( 'no filterelements found, selector: ' . $selectorarrayselectortrace, 0 );
									}
									if ( 0 !== $ret ) {
										$ret = 0;
									} else {
										$ret = 1;
									}
								}
							} else {
								if ( true === $this->traceitrm_follow ) {
									irld_trace_log( 'count filter is ' . $count_filter . ' but filtering is false', 0 );
								}
								break;
							}
						}
					}

					// 2nd case when there is "false"-records present.
					if ( ( 0 === $ret ) && ( $count_false > 0 ) ) {
						if ( false === $ret_zero_as_true ) {
							// we are on a false-record.
							$ret = 2;
							break;
						}
					}

					if ( ( 0 !== $ret ) && ( $count_false > 0 ) ) {
						if ( false === $ret_zero_as_true ) {
							$ret = 0;
						} else {
							// the 'true' is invalid, so we can quit.
							break;
						}
					}
				}
			} // end for.

			if ( true === $this->traceitrm_follow ) {
				irld_trace_log( 'Final resume for selector "' . $selectorarrayselectortrace . '", returning ' . $ret, 0 );
			}
		}

		return $ret;
	}

	/**
	 * Checks if CSS-Selector implies language dependency.
	 *
	 * @param string $browser_lang language used in client.
	 * @param string $selectorarrayselector selector to inspect.
	 *
	 * @return int
	 */
	protected function check_browser_compat( $browser_lang, $selectorarrayselector ) {
		$ret = 0;
		if ( str_replace( 'html[lang', '', $selectorarrayselector ) !== $selectorarrayselector ) {
			if ( str_replace( 'html[lang=' . $browser_lang . ']', '', $selectorarrayselector ) === $selectorarrayselector ) {
				$ret = 2;
			}
		}

		if ( str_replace( '[dir="rtl"]', '', $selectorarrayselector ) !== $selectorarrayselector ) {
			if ( 'ar' !== $browser_lang ) {
				$ret = 2;
			}
		}

		return $ret;
	}

	/**
	 * Checks if a bracket inside CSS-Selector could theoretically apply.
	 *
	 * @param array  $brackets array with isolated bracket expressions found in selector.
	 * @param string $selectorarrayselector selector to inspect.
	 * @param int    $count_false number of false-selectors.
	 * @param int    $count_filter number of selector filters.
	 *
	 * @return int $ret
	 */
	protected function check_brackets( $brackets, $selectorarrayselector, $count_false, $count_filter ) {
		$ret = 0;
		if ( ( ! ( ( str_replace( 'html[lang', '', $selectorarrayselector ) !== $selectorarrayselector ) ) ) && ( ! ( ( str_replace( '[dir="rtl"]', '', $selectorarrayselector ) !== $selectorarrayselector ) ) ) ) {
			if ( ( 0 === $count_false ) && ( 0 === $count_filter ) ) {
				foreach ( $brackets as $bracket ) {
					$bracket = str_replace( '[', '', $bracket );
					$bracket = str_replace( ']', '', $bracket );
					$bracket = trim( $bracket );
					if ( str_replace( '=', '', $bracket ) === $bracket ) {
						// there is no = .
						// For example [target] Selects all elements with a target attribute.
						// and also stuff like checked="checked".
						if ( ( str_replace( ' ' . $bracket . ' ', '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) &&
							( str_replace( ' ' . $bracket . '"', '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) &&
							( str_replace( $bracket . '="' . $bracket . '"', '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) &&
							( str_replace( 'aria-' . $bracket, '="true"', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) &&
							( str_replace( ' ' . $bracket, '="', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) &&
							( str_replace( 'type="' . $bracket, '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) ) {
								$ret = 2;
						}
					} elseif ( str_replace( '*', '', $bracket ) === $bracket ) {
						// there's no * .
						if ( str_replace( '$', '', $bracket ) === $bracket ) {
							// no $ .
							if ( str_replace( '~', '', $bracket ) === $bracket ) {
								// no ~ .
								if ( str_replace( '^', '', $bracket ) === $bracket ) {
									// no ^.
									$originalbracket = trim( $bracket );
									$bracket         = str_replace( '"', '', $originalbracket );
									$bracket         = str_replace( "'", '', $bracket );
									$bracketparts    = explode( '=', $bracket );
									// For example [target="_blank"]    Selects all elements with target="_blank".
									if ( ( str_replace( '' . $originalbracket, '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) && ( '' !== trim( $bracketparts[1] ) ) ) {
										if ( str_replace( '' . strtolower( trim( $bracketparts[0] ) ) . '="' . $bracketparts[1] . '"', '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) {
											if ( str_replace( '' . strtolower( trim( $bracketparts[0] ) ) . "='" . $bracketparts[1] . "'", '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) {
												$ret = 2;
											}
										}
									}
								} else {
									$bracket      = str_replace( '"', '', $bracket );
									$bracket      = str_replace( "'", '', $bracket );
									$bracketparts = explode( '^=', $bracket );
									// For example a[href^="https"] Selects every <a> element whose href attribute value begins with "https".
									if ( ( str_replace( ' ' . strtolower( trim( $bracketparts[0] ) ) . '=', '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) ) {
										$ret = 2;
									} else {
										$arrbracket1      = explode( ' ' . strtolower( trim( $bracketparts[0] ) ) . '="', $this->p_obj->modelbuffer_body );
										$found_value      = false;
										$countarrbracket1 = count( $arrbracket1 );
										for ( $mb = 1; $mb < $countarrbracket1; $mb++ ) {
											$arrbracket11   = explode( '"', $arrbracket1[ $mb ] );
											$bracket1_found = $arrbracket11[0];
											if ( isset( $bracketparts[1] ) ) {
												if ( true === irld_str_starts_with( $bracket1_found, $bracketparts[1] ) ) {
													$found_value = true;
												} elseif ( str_replace( $bracketparts[1], '', $bracket1_found ) !== $bracket1_found ) {
														$arrbracket11_single_elements = explode( ' ', $bracket1_found );
													foreach ( $arrbracket11_single_elements as $bracket11_single_element ) {
														if ( true === irld_str_starts_with( $bracket11_single_element, $bracketparts[1] ) ) {
															$found_value = true;
														}
													}
												}
											}
										}

										if ( false === $found_value ) {
											$ret = 2;
										}
									}
								}
							} else {
								$bracket      = str_replace( '"', '', $bracket );
								$bracket      = str_replace( "'", '', $bracket );
								$bracketparts = explode( '~=', $bracket );
								// For example [title~="flower"]    Selects all elements with a title attribute containing the word "flower".
								if ( ( str_replace( ' ' . strtolower( trim( $bracketparts[0] ) ) . '=', '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) ) {
									$ret = 2;
								} else {
									$arrbracket1      = explode( ' ' . strtolower( trim( $bracketparts[0] ) ) . '="', $this->p_obj->modelbuffer_body );
									$found_value      = false;
									$countarrbracket1 = count( $arrbracket1 );
									for ( $mb = 1; $mb < $countarrbracket1; $mb++ ) {
										$arrbracket11   = explode( '"', $arrbracket1[ $mb ] );
										$bracket1_found = $arrbracket11[0];
										if ( isset( $bracketparts[1] ) ) {
											if ( str_replace( $bracketparts[1], '', $bracket1_found ) !== $bracket1_found ) {
												$arrbracket11_single_elements = explode( ' ', $bracket1_found );
												foreach ( $arrbracket11_single_elements as $bracket11_single_element ) {
													if ( $bracket11_single_element === $bracketparts[1] ) {
														$found_value = true;
													}
												}
											}
										}
									}

									if ( false === $found_value ) {
										$ret = 2;
									}
								}
							}
						} else {
							$bracket      = str_replace( '"', '', $bracket );
							$bracket      = str_replace( "'", '', $bracket );
							$bracketparts = explode( '$=', $bracket );
							// For example a[href$=".pdf"]  Selects every <a> element whose href attribute value ends with ".pdf".
							if ( ( str_replace( ' ' . strtolower( trim( $bracketparts[0] ) ) . '=', '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) ) {
								$ret = 2;
							} else {
								$arrbracket1      = explode( ' ' . strtolower( trim( $bracketparts[0] ) ) . '="', $this->p_obj->modelbuffer_body );
								$found_value      = false;
								$countarrbracket1 = count( $arrbracket1 );
								for ( $mb = 1; $mb < $countarrbracket1; $mb++ ) {
									$arrbracket11   = explode( '"', $arrbracket1[ $mb ] );
									$bracket1_found = $arrbracket11[0];
									if ( isset( $bracketparts[1] ) ) {
										if ( true === irld_str_ends_with( $bracket1_found, $bracketparts[1] ) ) {
											$found_value = true;
										} elseif ( str_replace( $bracketparts[1], '', $bracket1_found ) !== $bracket1_found ) {
												$arrbracket11_single_elements = explode( ' ', $bracket1_found );
											foreach ( $arrbracket11_single_elements as $bracket11_single_element ) {
												if ( true === irld_str_ends_with( $bracket11_single_element, $bracketparts[1] ) ) {
													$found_value = true;
												}
											}
										}
									}
								}

								if ( false === $found_value ) {
									$ret = 2;
								}
							}
						}
					} else {
						// there's = and * .
						$bracket      = str_replace( '"', '', $bracket );
						$bracket      = str_replace( "'", '', $bracket );
						$bracketparts = explode( '*=', $bracket );
						// For example a[href*="w3schools"] Selects every <a> element whose href attribute value contains the substring "w3schools".
						if ( ( str_replace( ' ' . strtolower( trim( $bracketparts[0] ) ) . '=', '', $this->p_obj->modelbuffer_body ) === $this->p_obj->modelbuffer_body ) ) {
							$ret = 2;
						} else {
							$arrbracket1      = explode( ' ' . strtolower( trim( $bracketparts[0] ) ) . '="', $this->p_obj->modelbuffer_body );
							$found_value      = false;
							$countarrbracket1 = count( $arrbracket1 );
							for ( $mb = 1; $mb < $countarrbracket1; $mb++ ) {
								$arrbracket11   = explode( '"', $arrbracket1[ $mb ] );
								$bracket1_found = $arrbracket11[0];
								if ( isset( $bracketparts[1] ) ) {
									if ( str_replace( $bracketparts[1], '', $bracket1_found ) !== $bracket1_found ) {
										$found_value = true;
									}
								}
							}

							if ( false === $found_value ) {
								$ret = 2;
							}
						}
					}
				}
			}
		}

		return $ret;
	}
	/**
	 * Checks if CSS-Selector has an according DOM-sequence, if not we can move to CSS below.
	 * Works on a reversed version of the selectors elements, so we go bottum-up.
	 *
	 * @param array  $fullidstagsandclasses array with sorted selector parts, will be reversed for sequence check.
	 * @param string $selectorarrayselector selector to inspect.
	 * @param array  $bufferinclasses classes found in the model.
	 * @param array  $bufferinids ids found in the model.
	 * @param array  $bufferintags tags found in the model.
	 * @param array  $bufferinputtypes types (of inputs) found in the model.
	 *
	 * @return int
	 */
	protected function check_selector_sequence( $fullidstagsandclasses, $selectorarrayselector, $bufferinclasses, $bufferinids, $bufferintags, $bufferinputtypes ) {
		$ret                            = 0;
		$countfullidstagsandclasses     = count( $fullidstagsandclasses );
		$reversed_fullidstagsandclasses = array_reverse( $fullidstagsandclasses );
		$last_dom_arr                   = array();
		$bashing_selector               = '';
		if ( true === $this->traceitrm_follow ) {
			irld_trace_log( 'check_selector_sequence: ' . wp_json_encode( $reversed_fullidstagsandclasses, JSON_PRETTY_PRINT ) . ', selector: ' . $selectorarrayselector . ', counting: ' . $countfullidstagsandclasses . ' elements, we have ' . $this->countfilters . ' filters to handle', 0 );
		}

		if ( $this->countfilters > 0 ) {
			$reversed_filterindex = $countfullidstagsandclasses - $this->filterindex;
		}

		if ( $countfullidstagsandclasses > 1 ) {
			for ( $k = 0; $k < $countfullidstagsandclasses; $k++ ) {
				$bashing_selector = $reversed_fullidstagsandclasses[ $k ]['val'];
				$dom_arr          = array();
				if ( 0 === $reversed_fullidstagsandclasses[ $k ]['whereclause_activity'] ) {
					if ( 'class' === $reversed_fullidstagsandclasses[ $k ]['type'] ) {
						if ( isset( $bufferinclasses[ $reversed_fullidstagsandclasses[ $k ]['val'] ] ) ) {
							$dom_arr = $bufferinclasses[ $reversed_fullidstagsandclasses[ $k ]['val'] ];
						} else {
							$dom_arr = $this->numericjail_array_of_element( $reversed_fullidstagsandclasses[ $k ]['val'], false );
						}
					} elseif ( 'tag' === $reversed_fullidstagsandclasses[ $k ]['type'] ) {
						$dom_arr = $bufferintags[ $reversed_fullidstagsandclasses[ $k ]['val'] ];

					} elseif ( 'type' === $reversed_fullidstagsandclasses[ $k ]['type'] ) {
						$dom_arr = $bufferinputtypes[ $reversed_fullidstagsandclasses[ $k ]['val'] ];

					} elseif ( 'id' === $reversed_fullidstagsandclasses[ $k ]['type'] ) {
						if ( isset( $bufferinids[ $reversed_fullidstagsandclasses[ $k ]['val'] ] ) ) {
							$dom_arr = $bufferinids[ $reversed_fullidstagsandclasses[ $k ]['val'] ];
						} else {
							$dom_arr = $this->numericjail_array_of_element( $reversed_fullidstagsandclasses[ $k ]['val'], true );
						}
					}

					$valid_dom_arr = array();
					$valids        = 0;
					$dom_arr_count = count( $dom_arr['starts'] );
					if ( count( $last_dom_arr ) > 0 ) {
						$lastdom_arr_count = count( $last_dom_arr['starts'] );
						if ( true === $this->traceitrm_follow ) {
							irld_trace_log( 'lastdom_arr: ' . wp_json_encode( $last_dom_arr, JSON_PRETTY_PRINT ) . ', selector: ' . $reversed_fullidstagsandclasses[ $k - 1 ]['val'] . ', connects to child by: ' . $reversed_fullidstagsandclasses[ $k - 1 ]['connects'] . ' $k =' . ( $k - 1 ), 0 );
							irld_trace_log( 'domarr: ' . wp_json_encode( $dom_arr, JSON_PRETTY_PRINT ) . ', selector: ' . $reversed_fullidstagsandclasses[ $k ]['val'] . ' $k =' . $k, 0 );
						}

						$parent_rule_on_child = $reversed_fullidstagsandclasses[ $k - 1 ]['connects'];
						// on, in, follows, plused on.
						for ( $l = 0; $l < $lastdom_arr_count; $l++ ) {
							$child_starts_at = $last_dom_arr['starts'][ $l ];
							$child_ends_at   = $last_dom_arr['ends'][ $l ];
							$child_on_tag    = '';
							if ( isset( $last_dom_arr['tag'] ) ) {
								if ( isset( $last_dom_arr['tag'][ $l ] ) ) {
									$child_on_tag = $last_dom_arr['tag'][ $l ];
								}
							}

							if ( '' === $child_on_tag ) {
								$child_on_tag = 'is tag';
							}

							if ( count( $dom_arr ) > 0 ) {
								$dom_arr_count = count( $dom_arr['starts'] );
								for ( $d = 0; $d < $dom_arr_count; $d++ ) {
									$parent_starts_at = $dom_arr['starts'][ $d ];
									$parent_ends_at   = $dom_arr['ends'][ $d ];
									if ( 'tag' !== $reversed_fullidstagsandclasses[ $k ]['type'] ) {
										$parent_on_tag = $dom_arr['tag'][ $d ];
									} else {
										$parent_on_tag = 'is_tag';
									}

									if ( 'in' === $parent_rule_on_child ) {
										$require_strict_include = true;
										if ( true === $require_strict_include ) {
											if ( ( $parent_starts_at < $child_starts_at ) || ( -1 === $child_starts_at ) || ( -1 === $parent_starts_at ) ) {
												if ( ( $parent_ends_at > $child_ends_at ) || ( -1 === $child_starts_at ) || ( -1 === $parent_starts_at ) ) {
													// valid parent found.
													if ( ! isset( $valid_dom_arr['starts'] ) ) {
														$valid_dom_arr['starts'] = array();
														$valid_dom_arr['ends']   = array();
														$valid_dom_arr['tag']    = array();
													}

													if ( ! in_array( $parent_starts_at, $valid_dom_arr['starts'], true ) ) {
														$valid_dom_arr['starts'][] = $parent_starts_at;
														$valid_dom_arr['ends'][]   = $parent_ends_at;
														$valid_dom_arr['tag'][]    = $parent_on_tag;
														++$valids;
													}
												}
											}
										}
									} elseif ( 'on' === $parent_rule_on_child ) {
										// like "div.someclass".
										if ( ( $parent_starts_at === $child_starts_at ) || ( -1 === $child_starts_at ) || ( -1 === $parent_starts_at ) ) {
											if ( ( $parent_ends_at === $child_ends_at ) || ( -1 === $child_starts_at ) || ( -1 === $parent_starts_at ) ) {
												if ( ! isset( $valid_dom_arr['starts'] ) ) {
													$valid_dom_arr['starts'] = array();
													$valid_dom_arr['ends']   = array();
													$valid_dom_arr['tag']    = array();
												}

												if ( ! in_array( $parent_starts_at, $valid_dom_arr['starts'], true ) ) {
													$valid_dom_arr['starts'][] = $parent_starts_at;
													$valid_dom_arr['ends'][]   = $parent_ends_at;
													$valid_dom_arr['tag'][]    = $parent_on_tag;
													++$valids;
												}
											}
										}
									} elseif ( 'follows' === $parent_rule_on_child ) {
										// + sign.
										if ( ( ( $child_starts_at - 1 ) === $parent_ends_at ) || ( -1 === $child_starts_at ) || ( -1 === $parent_starts_at ) ) {
											if ( ! isset( $valid_dom_arr['starts'] ) ) {
												$valid_dom_arr['starts'] = array();
												$valid_dom_arr['ends']   = array();
												$valid_dom_arr['tag']    = array();
											}

											if ( ! in_array( $parent_starts_at, $valid_dom_arr['starts'], true ) ) {
												$valid_dom_arr['starts'][] = $parent_starts_at;
												$valid_dom_arr['ends'][]   = $parent_ends_at;
												$valid_dom_arr['tag'][]    = $parent_on_tag;
												++$valids;
											}
										}
									} elseif ( 'plused on' === $parent_rule_on_child ) {
										// > Sign.
										if ( ( $parent_starts_at < $child_starts_at ) || ( -1 === $child_starts_at ) || ( -1 === $parent_starts_at ) ) {
											if ( ( $parent_ends_at > $child_ends_at ) || ( -1 === $child_starts_at ) || ( -1 === $parent_starts_at ) ) {
												if ( ! isset( $valid_dom_arr['starts'] ) ) {
													$valid_dom_arr['starts'] = array();
													$valid_dom_arr['ends']   = array();
													$valid_dom_arr['tag']    = array();
												}

												if ( ! in_array( $parent_starts_at, $valid_dom_arr['starts'], true ) ) {
													if ( true === $this->is_parent_direct( $parent_starts_at, $parent_ends_at, $child_starts_at, $child_ends_at ) ) {
														$valid_dom_arr['starts'][] = $parent_starts_at;
														$valid_dom_arr['ends'][]   = $parent_ends_at;
														$valid_dom_arr['tag'][]    = $parent_on_tag;
														++$valids;
													}
												}
											}
										}
									} elseif ( 'tildes' === $parent_rule_on_child ) {
										// ~ (tilde) Sign.
										if ( ( $child_starts_at - 1 >= $parent_ends_at ) || ( -1 === $child_starts_at ) || ( -1 === $parent_starts_at ) ) {
											if ( ! isset( $valid_dom_arr['starts'] ) ) {
												$valid_dom_arr['starts'] = array();
												$valid_dom_arr['ends']   = array();
												$valid_dom_arr['tag']    = array();
											}

											if ( ! in_array( $parent_starts_at, $valid_dom_arr['starts'], true ) ) {
												if ( true === $this->is_sibling( $parent_starts_at, $parent_ends_at, $child_starts_at ) ) {
													$valid_dom_arr['starts'][] = $parent_starts_at;
													$valid_dom_arr['ends'][]   = $parent_ends_at;
													$valid_dom_arr['tag'][]    = $parent_on_tag;
													++$valids;
												}
											}
										}
									}
								}
							}
						}

						if ( true === $this->traceitrm_follow ) {
							irld_trace_log(
								'valid_dom_arr: ' . wp_json_encode( $valid_dom_arr, JSON_PRETTY_PRINT ) . ', selector: ' . $reversed_fullidstagsandclasses[ $k ]['val'] . ' $k =' . $k .
								' vs selector ' . $reversed_fullidstagsandclasses[ $k - 1 ]['val'] . ' $k-1 =' . ( $k - 1 ),
								0
							);
						}
					} elseif ( ( $k > 0 ) && ( $k < $countfullidstagsandclasses ) ) {
							$ret = 2;
					}

					if ( $this->countfilters > 0 ) {
						if ( true === $this->traceitrm_follow ) {
							irld_trace_log( '$this->countfilters ' . $this->countfilters, 0 );
						}

						if ( count( $this->filterelements ) === 0 ) {
							if ( true === $this->traceitrm_follow ) {
								irld_trace_log( 'filterelements is empty', 0 );
							}

							if ( $k === $reversed_filterindex ) {
								if ( count( $last_dom_arr ) > 0 ) {
									$this->filterelements = $valid_dom_arr;
								} else {
									$this->filterelements = $dom_arr;
								}

								if ( true === $this->traceitrm_follow ) {
									irld_trace_log(
										'setting filter elements ' . wp_json_encode( $this->filterelements, JSON_PRETTY_PRINT ) . ', selectors: ' .
										wp_json_encode( $reversed_fullidstagsandclasses, JSON_PRETTY_PRINT ) . ' reversed_filterindex =' . $k . ', on selector: ' . $selectorarrayselector,
										0
									);
								}
							}
						} elseif ( $k === $reversed_filterindex ) {
							if ( 0 === $k ) {
								$comp_dom_arr = $dom_arr;
							} elseif ( count( $valid_dom_arr ) > 0 ) {
								$comp_dom_arr = $valid_dom_arr;
							} else {
								$comp_dom_arr = array();
							}
							if ( isset( $comp_dom_arr['starts'] ) ) {
								$countcomp_dom_arr = count( $comp_dom_arr['starts'] );
							} else {
								$countcomp_dom_arr = 0;
							}

								$newfilterelements   = array();
								$countfilterelements = count( $this->filterelements['starts'] );
							for ( $f = 0; $f < $countfilterelements; $f++ ) {
								$filter_starts_at = $this->filterelements['starts'][ $f ];
								$filter_ends_at   = $this->filterelements['ends'][ $f ];
								$drop_elem        = false;
								for ( $d = 0; $d < $countcomp_dom_arr; $d++ ) {
									if ( ( $filter_starts_at === $comp_dom_arr['starts'][ $d ] ) && ( $filter_ends_at === $comp_dom_arr['ends'][ $d ] ) ) {
										$drop_elem = true;
										break;
									}
								}

								if ( false === $drop_elem ) {
									if ( ! isset( $newfilterelements['starts'] ) ) {
										$newfilterelements['starts'] = array();
										$newfilterelements['ends']   = array();
									}

									$newfilterelements['starts'][] = $filter_starts_at;
									$newfilterelements['ends'][]   = $filter_ends_at;
								}
							}

							if ( isset( $newfilterelements['starts'] ) ) {
								$countfilterelementsnew = count( $newfilterelements['starts'] );
							} else {
								$countfilterelementsnew = 0;
							}

							if ( 0 === $countfilterelementsnew ) {
								if ( true === $this->traceitrm_follow ) {
									irld_trace_log(
										'filtered out all ' . $countfilterelements . ' elements from ' . wp_json_encode( $this->filterelements, JSON_PRETTY_PRINT ) . ', selectors: ' .
										wp_json_encode( $reversed_fullidstagsandclasses, JSON_PRETTY_PRINT ) . ' reversed_filterindex =' . $k . ', on selector: ' . $selectorarrayselector,
										0
									);
								}
							} elseif ( $countfilterelementsnew !== $countfilterelements ) {
								if ( true === $this->traceitrm_follow ) {
									irld_trace_log(
										'filtered out ' . ( $countfilterelements - $countfilterelementsnew ) . ' elements from ' . wp_json_encode( $this->filterelements, JSON_PRETTY_PRINT ) . ', single selector: ' . $reversed_fullidstagsandclasses[ $k ]['val'] .
										', on selector: ' . $selectorarrayselector . ', selectors: ' . wp_json_encode( $reversed_fullidstagsandclasses, JSON_PRETTY_PRINT ) . ' reversed_filterindex =' . $k,
										0
									);
								}
							}

								$this->filterelements = $newfilterelements;
						}
					}

					if ( 0 === $k ) {
						$last_dom_arr = $dom_arr;
					} elseif ( count( $valid_dom_arr ) > 0 ) {
						$last_dom_arr = $valid_dom_arr;
					} else {
						$last_dom_arr = array();
						if ( $k < $countfullidstagsandclasses ) {
							$ret = 2;
							if ( true === $this->traceitrm_follow ) {
								irld_trace_log( 'bashing_selector: ' . $bashing_selector . ' on $k: ' . $k, 0 );
							}

							break;
						}
					}
				}
			}
		} else {
			for ( $k = 0; $k < $countfullidstagsandclasses; $k++ ) {
				$dom_arr = array();
				if ( 'class' === $reversed_fullidstagsandclasses[ $k ]['type'] ) {
					if ( isset( $bufferinclasses[ $reversed_fullidstagsandclasses[ $k ]['val'] ] ) ) {
						$dom_arr = $bufferinclasses[ $reversed_fullidstagsandclasses[ $k ]['val'] ];
					} else {
						$dom_arr = $this->numericjail_array_of_element( $reversed_fullidstagsandclasses[ $k ]['val'], false );
					}
				} elseif ( 'tag' === $reversed_fullidstagsandclasses[ $k ]['type'] ) {
					$dom_arr = $bufferintags[ $reversed_fullidstagsandclasses[ $k ]['val'] ];

				} elseif ( 'type' === $reversed_fullidstagsandclasses[ $k ]['type'] ) {
					$dom_arr = $bufferinputtypes[ $reversed_fullidstagsandclasses[ $k ]['val'] ];

				} elseif ( 'id' === $reversed_fullidstagsandclasses[ $k ]['type'] ) {
					if ( isset( $bufferinids[ $reversed_fullidstagsandclasses[ $k ]['val'] ] ) ) {
						$dom_arr = $bufferinids[ $reversed_fullidstagsandclasses[ $k ]['val'] ];
					} else {
						$dom_arr = $this->numericjail_array_of_element( $reversed_fullidstagsandclasses[ $k ]['val'], true );
					}
				}

				if ( $this->countfilters > 0 ) {
					if ( true === $this->traceitrm_follow ) {
						irld_trace_log(
							'solo selector this->countfilters ' . $this->countfilters . ', count( $this->filterelements ) = ' . count( $this->filterelements ) .
							', contents: ' . wp_json_encode( $this->filterelements, JSON_PRETTY_PRINT ),
							0
						);
					}

					if ( count( $this->filterelements ) === 0 ) {
						if ( true === $this->traceitrm_follow ) {
							irld_trace_log( 'solo selector filterelements is empty, $reversed_filterindex ' . $reversed_filterindex, 0 );
						}

						if ( $k === $reversed_filterindex ) {
							$this->filterelements = $dom_arr;
							if ( true === $this->traceitrm_follow ) {
								irld_trace_log(
									'setting filter elements ' . wp_json_encode( $this->filterelements, JSON_PRETTY_PRINT ) . ', selectors: ' .
									wp_json_encode( $reversed_fullidstagsandclasses, JSON_PRETTY_PRINT ) . ' reversed_filterindex =' . $k . ', on selector: ' . $selectorarrayselector,
									0
								);
							}
						}
					}
				}
			}
		}

		return $ret;
	}
	/**
	 * Checks the model if parent and child are siblings.
	 *
	 * @param int $parent_starts_at candidate start of direct parent.
	 * @param int $parent_ends_at candidate start of direct parent.
	 * @param int $child_starts_at candidate start of direct parent.
	 *
	 * @return bool $is_sibling True if proposed parent is a sibling following child, false if not.
	 */
	protected function is_sibling( $parent_starts_at, $parent_ends_at, $child_starts_at ) {
		$is_sibling = false;
		if ( ( $child_starts_at - 1 === $parent_ends_at ) || ( -1 === $child_starts_at ) || ( -1 === $parent_starts_at ) ) {
			$is_sibling = true;
		} else {
			$siblingsarr           = array();
			$child_start           = $child_starts_at - 1;
			$no_more_sibling_found = false;
			do {
				$another_sibling_found = false;
				foreach ( $this->bufferintags as $modeltag => $modeldata ) {
					$count_modeldataelements = count( $modeldata['starts'] );
					for ( $m = 0; $m < $count_modeldataelements; $m++ ) {
						$modeldatastarts = $modeldata['starts'][ $m ];
						$modeldataends   = $modeldata['ends'][ $m ];
						if ( $child_start === $modeldataends ) {
							$another_sibling_found = true;
							if ( ( $modeldatastarts === $parent_starts_at ) && ( $modeldataends === $parent_ends_at ) ) {
								$is_sibling            = true;
								$no_more_sibling_found = true;
							}
							$child_start = $modeldatastarts - 1;
							break;
						}
					}
					if ( true === $another_sibling_found ) {
						break;
					}
				}

				if ( false === $another_sibling_found ) {
					$no_more_sibling_found = true;
				}
			} while ( false === $no_more_sibling_found );
		}
		return $is_sibling;
	}

	/**
	 * Checks the model if parent is direct parent of child.
	 *
	 * @param int $parent_starts_at candidate start of direct parent.
	 * @param int $parent_ends_at candidate start of direct parent.
	 * @param int $child_starts_at candidate start of direct parent.
	 * @param int $child_ends_at candidate start of direct parent.
	 *
	 * @return bool $is_parent_direct True if proposed parent is direcct parent, false if not.
	 */
	protected function is_parent_direct( $parent_starts_at, $parent_ends_at, $child_starts_at, $child_ends_at ) {
		$is_parent_direct = true;
		foreach ( $this->bufferintags as $modeltag => $modeldata ) {
			$count_modeldataelements = count( $modeldata['starts'] );
			for ( $m = 0; $m < $count_modeldataelements; $m++ ) {
				$modeldatastarts = $modeldata['starts'][ $m ];
				$modeldataends   = $modeldata['ends'][ $m ];
				if ( ( $modeldatastarts < $child_starts_at ) && ( $modeldataends > $child_ends_at ) ) {
					if ( ( $modeldatastarts > $parent_starts_at ) && ( $modeldataends < $parent_ends_at ) ) {
						$is_parent_direct = false;
						break;
					}
				}
			}
			if ( false === $is_parent_direct ) {
				break;
			}
		}
		return $is_parent_direct;
	}

	/**
	 * Extracts CSS-Selectors from given CSS-fragment, adds first info if Selector can move to CSS below.
	 *
	 * @param string $cssfragment CSS part holding the selectors to extract.
	 * @param bool   $set_can_go_below TRUE; Selector can move to CSS below.
	 *
	 * @return array Array holding selectors [$i]['selector'] and info if selector can be moved to below css [$i]['selectorCanGoBelow'].
	 */
	protected function get_selectors( $cssfragment, $set_can_go_below = true ) {
		$cssfragment                    = str_replace( "\n", '', $cssfragment );
		$cssfragment                    = str_replace( "\r", '', $cssfragment );
		$cssfragments_arr               = explode( '{', $cssfragment );
		$cssfragment_selectors          = trim( $cssfragments_arr[0] );
		$cssfragment_selectors          = str_replace( '?', '', $cssfragment_selectors );
		$cssfragment_selector_arr       = explode( ',', $cssfragment_selectors );
		$cssretarr                      = array();
		$i                              = 0;
		$rawselectorbuffer              = '';
		$searching_endparanthesis       = false;
		$found_countarr_openbrackets    = 0;
		$found_countarr_closingbrackets = 0;
		foreach ( $cssfragment_selector_arr as $rawselector ) {
			if ( true === $searching_endparanthesis ) {
				$rawselectorbuffer        = $rawselectorbuffer . ',' . trim( $rawselector );
				$arr_openbrackets         = explode( '(', $rawselector );
				$arr_closingbrackets      = explode( ')', $rawselector );
				$countarr_openbrackets    = count( $arr_openbrackets );
				$countarr_closingbrackets = count( $arr_closingbrackets );
				if ( ( $countarr_openbrackets + $found_countarr_openbrackets ) !== ( $countarr_closingbrackets + $found_countarr_closingbrackets ) ) {
					$searching_endparanthesis = true;
				} else {
					// found end paranthesis.
					$searching_endparanthesis    = false;
					$cssretarr[ $i ]             = array();
					$cssretarr[ $i ]['selector'] = trim( $rawselectorbuffer );
					$cssretarr[ $i ]['drop']     = 0;
					if ( true === $set_can_go_below ) {
						$cssretarr[ $i ]['selectorCanGoBelow'] = 0;
					}

					$rawselectorbuffer = '';
					++$i;
				}
			} elseif ( ( str_replace( ':where(', '', $rawselector ) !== $rawselector ) ||
				( str_replace( ':has(', '', $rawselector ) !== $rawselector ) ||
				( str_replace( ':is(', '', $rawselector ) !== $rawselector ) ||
				( str_replace( ':not(', '', $rawselector ) !== $rawselector ) ) {
					$arr_openbrackets         = explode( '(', $rawselector );
					$arr_closingbrackets      = explode( ')', $rawselector );
					$countarr_openbrackets    = count( $arr_openbrackets );
					$countarr_closingbrackets = count( $arr_closingbrackets );
				if ( $countarr_openbrackets !== $countarr_closingbrackets ) {
					$rawselectorbuffer               = trim( $rawselector );
					$searching_endparanthesis        = true;
					$found_countarr_openbrackets    += $countarr_openbrackets;
					$found_countarr_closingbrackets += $countarr_closingbrackets;
				} else {
					// found end paranthesis, entire :xx( i9 in $rawselector.
					$searching_endparanthesis    = false;
					$cssretarr[ $i ]             = array();
					$cssretarr[ $i ]['selector'] = trim( $rawselector );
					$cssretarr[ $i ]['drop']     = 0;
					if ( true === $set_can_go_below ) {
						$cssretarr[ $i ]['selectorCanGoBelow'] = 0;
					}

					++$i;
				}
			} else {
				$cssretarr[ $i ]             = array();
				$cssretarr[ $i ]['selector'] = trim( $rawselector );
				$cssretarr[ $i ]['drop']     = 0;
				if ( true === $set_can_go_below ) {
					$cssretarr[ $i ]['selectorCanGoBelow'] = 0;
				}

				++$i;
			}
		}

		return $cssretarr;
	}

	/**
	 * Extracts CSS-Rules from given CSS-fragment.
	 *
	 * @param string $cssfragment CSS-fragment holding rules to extract.
	 *
	 * @return string Rules, ends up then in eg. [cssrules] => float:right;.
	 */
	protected function get_rules( $cssfragment ) {
		$cssfragment      = str_replace( "\n", '', $cssfragment );
		$cssfragment      = str_replace( "\r", '', $cssfragment );
		$cssfragmentsplit = explode( '{', $cssfragment );
		$cssfragment      = '';
		if ( isset( $cssfragmentsplit[1] ) ) {
			$cssfragment = $cssfragmentsplit[1];
			if ( '' !== trim( $cssfragment ) ) {
				if ( ';' !== substr( $cssfragment, -1 ) ) {
					$cssfragment .= ';';

				}
			}
		}

		return $cssfragment;
	}

	/**
	 * Crunches CSS.
	 *
	 * @param string  $buffer String to crunch (compress).
	 * @param boolean $minimal when true only a minimal cruch will be applied.
	 *
	 * @return string
	 */
	protected function crunchcss( $buffer, $minimal = false ) {
		/* remove comments */
		$buffer = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer );
		if ( false === $minimal ) {
			/* remove tabs, spaces, new lines, etc. */
			$buffer = str_replace( array( "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ), '', $buffer );
			/* remove unnecessary spaces */
			$buffer = str_replace( '{ ', '{', $buffer );
			$buffer = str_replace( ' }', '}', $buffer );
			$buffer = str_replace( '; ', ';', $buffer );
			$buffer = str_replace( ', ', ',', $buffer );
			$buffer = str_replace( ' {', '{', $buffer );
			$buffer = str_replace( '} ', '}', $buffer );
			$buffer = str_replace( ': ', ':', $buffer );
			$buffer = str_replace( ' :', ':', $buffer );
			$buffer = str_replace( ' ,', ',', $buffer );
			$buffer = str_replace( ' ;', ';', $buffer );
		} else {
			$buffer = str_replace( "\r\n", "\n", $buffer );
		}

		return $buffer;
	}

	/**
	 * Checks if selector is numeric and if so returns the _num-jailstring-class- or -ids-array
	 *
	 * @param string  $class_or_id To check.
	 * @param boolean $is_id when true only a minimal cruch will be applied.
	 *
	 * @return array
	 */
	protected function numericjail_array_of_element( $class_or_id, $is_id = false ) {
		$numericjailarray   = $this->p_obj->numericjailarray;
		$numericnojailarray = $this->p_obj->numericnojailarray;
		$in_numericjail_arr = array();
		$classinputnn       = preg_replace( '/\d/', '', trim( $class_or_id ) );
		if ( trim( $class_or_id ) !== $classinputnn ) {
			$innocent               = true;
			$current_jailidentifier = '';
			if ( false === $is_id ) {
				foreach ( $numericjailarray as $jailidentifier ) {
					if ( str_replace( $jailidentifier, '', substr( $classinputnn, 0, strlen( $jailidentifier ) ) ) !== substr( $classinputnn, 0, strlen( $jailidentifier ) ) ) {
						$current_jailidentifier = $jailidentifier;
						$innocent               = false;
						break;
					}
				}

				foreach ( $numericnojailarray as $jailidentifier ) {
					if ( str_replace( $jailidentifier, '', $classinputnn ) !== $classinputnn ) {
						$current_jailidentifier = '';
						$innocent               = true;
						break;
					}
				}

				if ( false === $innocent ) {
					if ( true === array_key_exists( $current_jailidentifier, $this->p_obj->bufferinclasses_num ) ) {
						$in_numericjail_arr = $this->p_obj->bufferinclasses_num[ $current_jailidentifier ];
					}
				}
			} else {
				foreach ( $numericjailarray as $jailidentifier ) {
					if ( str_replace( $jailidentifier, '', $classinputnn ) !== $classinputnn ) {
						$current_jailidentifier = $jailidentifier;
						$innocent               = false;
						break;
					}
				}

				foreach ( $numericnojailarray as $jailidentifier ) {
					if ( str_replace( $jailidentifier, '', $classinputnn ) !== $classinputnn ) {
						$current_jailidentifier = '';
						$innocent               = true;
						break;
					}
				}

				if ( false === $innocent ) {
					if ( true === array_key_exists( $current_jailidentifier, $this->p_obj->bufferinids_num ) ) {
						$in_numericjail_arr = $this->p_obj->bufferinids_num[ $current_jailidentifier ];
					}
				}
			}
		}

		return $in_numericjail_arr;
	}
	/**
	 * Checks if selector is numeric and if so checks its presence in the _num-jailstring-class or -ids-array
	 *
	 * @param string  $class_or_id To check.
	 * @param boolean $is_id when true only a minimal cruch will be applied.
	 *
	 * @return boolean
	 */
	protected function numericjail_array_keys_check( $class_or_id, $is_id = false ) {
		$is_in_numericjail_array_keys = false;
		$numericjailarray             = $this->p_obj->numericjailarray;
		$numericnojailarray           = $this->p_obj->numericnojailarray;
		$classinputnn                 = preg_replace( '/\d/', '', trim( $class_or_id ) );
		if ( trim( $class_or_id ) !== $classinputnn ) {
			$innocent               = true;
			$current_jailidentifier = '';
			if ( false === $is_id ) {
				foreach ( $numericjailarray as $jailidentifier ) {
					if ( str_replace( $jailidentifier, '', substr( $classinputnn, 0, strlen( $jailidentifier ) ) ) !== substr( $classinputnn, 0, strlen( $jailidentifier ) ) ) {
						$current_jailidentifier = $jailidentifier;
						$innocent               = false;
						break;
					}
				}

				foreach ( $numericnojailarray as $jailidentifier ) {
					if ( str_replace( $jailidentifier, '', $classinputnn ) !== $classinputnn ) {
						$current_jailidentifier = '';
						$innocent               = true;
						break;
					}
				}

				if ( false === $innocent ) {
					if ( true === array_key_exists( $current_jailidentifier, $this->p_obj->bufferinclasses_num ) ) {
						$is_in_numericjail_array_keys = true;
					}
				}
			} else {
				foreach ( $numericjailarray as $jailidentifier ) {
					if ( str_replace( $jailidentifier, '', $classinputnn ) !== $classinputnn ) {
						$current_jailidentifier = $jailidentifier;
						$innocent               = false;
						break;
					}
				}

				foreach ( $numericnojailarray as $jailidentifier ) {
					if ( str_replace( $jailidentifier, '', $classinputnn ) !== $classinputnn ) {
						$current_jailidentifier = '';
						$innocent               = true;
						break;
					}
				}

				if ( false === $innocent ) {
					if ( true === array_key_exists( $current_jailidentifier, $this->p_obj->bufferinids_num ) ) {
						$is_in_numericjail_array_keys = true;
					}
				}
			}
		}

		return $is_in_numericjail_array_keys;
	}
}

new Indexreloadedcssabovebelow();
