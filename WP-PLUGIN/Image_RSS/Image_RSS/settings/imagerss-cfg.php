<?php
global $wpsf_settings;
$CFG = wpsf_get_settings( $this->plugin_path .'settings/imagerss-cfg.php' );

$wpsf_settings[] = array(
    'section_id' => 'tags',
    'section_title' => __( 'Add Image RSS Feed tags', 'Image_RSS' ),
    'section_description' => __( '"enclosure" and "media:content" tag in RSS feed are used to tell RSS parser about post thumbnail.', 'Image_RSS' ),
    'section_order' => 10,
    'fields' => array(
		array(
            'id' => 'addTag_enclosure',
            'title' => __( 'Add "enclosure" tag to RSS feed', 'Image_RSS' ),
            'desc' => '',
            'type' => 'checkbox',
            'std' => 1
        ),
		array(
            'id' => 'addTag_mediaContent',
            'title' => __( 'Add "media:content" tag to RSS feed', 'Image_RSS' ),
            'desc' => '',
            'type' => 'checkbox',
            'std' => 1
        )
	)
);

$wpsf_settings[] = array(
    'section_id' => 'description',
    'section_title' => __( 'Extend HTML content', 'Image_RSS' ),
    'section_description' => __( 'This will extend the HTML code of "description" and "content:encoded" tags with 90% wide image before the text.', 'Image_RSS' ),
    'section_order' => 20,
    'fields' => array(
		array(
            'id' => 'extend_description',
            'title' => __( 'Extend "description" (excerpt)', 'Image_RSS' ),
            'desc' => '',
            'type' => 'checkbox',
            'std' => 1
        ),
		array(
            'id' => 'extend_content',
            'title' => __( 'Extend "content:encoded" HTML', 'Image_RSS' ),
            'desc' => '',
            'type' => 'checkbox',
            'std' => 1
        )
	)
);

$rss_use_excerpt = get_option('rss_use_excerpt');
$rss_fulltext_link = site_url() . '/feed/?fsk=';
$CFG['sbrssfeedcfg_fulltext_fulltext_override_secrete'] ? $rss_fulltext_link .= $CFG['sbrssfeedcfg_fulltext_fulltext_override_secrete'] : $rss_fulltext_link .= '-NOT-SET-';

$wpsf_settings[] = array(
    'section_id' => 'fulltext',
    'section_title' => __( 'RSS Feed fulltext override', 'Image_RSS' ),
    'section_description' => __( 'Override "excerpt only" RSS feed when requested with "secret" key.', 'Image_RSS' ),
    'section_order' => 25,
    'fields' => array(
		array(
            'id' => 'fulltext_wp_option',
            'title' => __( 'WordPress RSS Feed mode', 'Image_RSS' ),
            'desc' => '',
			'type' => 'custom',
			'std' => $rss_use_excerpt ? __( 'Excerpt only - there is only excerpt in the standard RSS Feed...<br />However, requesting feed url with special "secret key" will display full content of each post (great for services like Google Currents).', 'Image_RSS' ) : __( 'Fulltext - your feed already contains whole post content.', 'Image_RSS' )
        ),
		array(
            'id' => 'fulltext_override',
            'title' => __( 'Enable fulltext override', 'Image_RSS' ),
            'desc' => $rss_use_excerpt ? '<em>' . __( 'When enabled, you can request RSS Feed with full post content with special URL (added query string <strong>?fsk=</strong>)', 'Image_RSS' ) . '</em>' : '<em>' . __( 'You don\'t need to override WordPress settings - your feed already contains full post content.', Image_RSS ) . '</em>' ,
            'type' => 'checkbox',
            'std' => 0
        ),
		array(
            'id' => 'fulltext_override_secrete',
            'title' => __( 'Override "secret" key (?fsk= param)', 'Image_RSS' ),
            'desc' => __( 'Fulltext RSS Feed:', 'Image_RSS' ) . ' <a href="'.$rss_fulltext_link.'" target="_blank">' . $rss_fulltext_link . '</a>',
            'type' => 'text',
			'std' => uniqid()
        )
	)
);

$wpsf_settings[] = array(
    'section_id' => 'signature',
    'section_title' => __( 'RSS Feed signature', 'Image_RSS' ),
    'section_description' => __( 'Add "Source: XYZ" text to end of the content of each feed item.', 'Image_RSS' ),
    'section_order' => 30,
    'fields' => array(
		array(
            'id' => 'addSignature',
            'title' => __( 'Add signature', 'Image_RSS' ),
            'desc' => '',
            'type' => 'checkbox',
            'std' => 0
        )
	)
);

$wpsf_settings[] = array(
    'section_id' => 'inrssAd',
    'section_title' => __( 'RSS Feed advertisement', 'Image_RSS' ),
	'section_description' => __( 'Inject "ad" to RSS feed items (image with link). Ad will be inserted only to full text "content:encoded" tag.', 'Image_RSS' ),
    'section_order' => 40,
    'fields' => array(
		array(
            'id' => 'inrssAd_enabled',
            'title' => __( 'Inject ad to feed posts', 'Image_RSS' ),
            'desc' => '',
            'type' => 'checkbox',
            'std' => 0
        ),
		array(
			'id' => 'inrssAd_img',
			'title' => __( 'Ad image', 'Image_RSS' ),
			'desc' => __( 'image will be stretched up to 700px of width', 'Image_RSS' ),
			'type' => 'file',
			'std' => ''
        ),
		array(
            'id' => 'inrssAd_title',
            'title' => __( 'Ad title', 'Image_RSS' ),
            // 'desc' => __( 'Will be inserted as &lt;figure&gt; tag and alt attribute of img tag', 'Image_RSS' ),
            'type' => 'text'
        ),
		array(
            'id' => 'inrssAd_link',
            'title' => __( 'Ad target link', 'Image_RSS' ),
            'desc' => __( 'Recommendation: use bit.ly or similar service to track clicks...', 'Image_RSS' ),
            'type' => 'text'
        ),
		array(
            'id' => 'inrssAd_injectAfter',
            'title' => __( 'Inject ad after nth paragraph', 'Image_RSS' ),
            'type' => 'select',
			'type' => 'select',
            'std' => '2',
			'choices' => array(
				'1' => __( '1st paragraph', 'Image_RSS' ),
				'2' => __( '2nd paragraph', 'Image_RSS' ),
				'3' => __( '3rd paragraph', 'Image_RSS' ),
				'4' => __( '4th paragraph', 'Image_RSS' ),
				'5' => __( '5th paragraph', 'Image_RSS' ),
				'6' => __( '6th paragraph', 'Image_RSS' ),
				'7' => __( '7th paragraph', 'Image_RSS' ),
			)
        )
	)
);


?>