<?php

/*
=====================================================================================
                ﻿엠샵 문자 알림 / Copyright 2015 by CodeM(c)
=====================================================================================

  [ 우커머스 버전 지원 안내 ]

   워드프레스 버전 : WordPress 4.3.1

   우커머스 버전 : WooCommerce 2.4.7


  [ 코드엠 플러그인 라이센스 규정 ]

   (주)코드엠에서 개발된 워드프레스  플러그인을 사용하시는 분들에게는 다음 사항에 대한 동의가 있는 것으로 간주합니다.

   1. 코드엠에서 개발한 워드프레스 우커머스용 엠샵 문자 알림 플러그인의 저작권은 (주)코드엠에게 있습니다.
   
   2. 플러그인은 사용권을 구매하는 것이며, 프로그램 저작권에 대한 구매가 아닙니다.

   3. 플러그인을 구입하여 다수의 사이트에 복사하여 사용할 수 없으며, 1개의 라이센스는 1개의 사이트에만 사용할 수 있습니다. 
      이를 위반 시 지적 재산권에 대한 손해 배상 의무를 갖습니다.

   4. 플러그인은 구입 후 1년간 업데이트를 지원합니다.

   5. 플러그인은 워드프레스, 테마, 플러그인과의 호환성에 대한 책임이 없습니다.

   6. 플러그인 설치 후 버전에 관련한 운용 및 관리의 책임은 사이트 당사자에게 있습니다.

   7. 다운로드한 플러그인은 환불되지 않습니다.

=====================================================================================
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MSSMS_Settings_Send' ) ) :

	class MSSMS_Settings_Send {

		static function update_settings() {
			include_once MSSMS()->plugin_path() . '/includes/admin/setting-manager/mssms-helper.php';

			$_REQUEST = array_merge( $_REQUEST, json_decode( stripslashes( $_REQUEST['values'] ), true ) );

			MSSMS_Helper::update_settings( self::get_setting_fields() );

			wp_send_json_success();
		}
		static function get_roles() {
			$roles          = array ();
			$editable_roles = get_editable_roles();

			foreach ( $editable_roles as $role => $object ) {
				$roles[ $role ] = $object['name'];
			}

			return $roles;
		}

		static function get_setting_fields() {
			return array (
				'type'  => 'Page',
				'class' => 'active',
				'title' => __( '기본 설정', 'mshop-sms-s2' ),

				'elements' => array (
					array (
						'type'              => 'Section',
						'hideSectionHeader' => true,
						'elements'          => array (
							array (
								"id"        => "message_client_guide",
								"className" => "fluid",
								"type"      => "Label",
								"readonly"  => "yes",
								"default"   => '<div class="desc2">[안내] 문자 개별 발송은 장문자(LMS)로 처리 됩니다.</div>'
							)
						)
					),
					array (
						"id"             => "message_client",
						"className"      => "",
						"type"           => "SMSClient",
						"ajaxurl"        => admin_url( 'admin-ajax.php' ),
						"slug"           => MSSMS()->slug(),
						"sample_csv_url" => plugins_url( '/assets/data/sms_sample.csv', MSSMS_PLUGIN_FILE ),
						"default"        => array (
							'sender_number' => get_option( 'mssms_rep_send_no' ),
							'send_type'     => 'now',
							'title'         => sprintf( '[%s]', get_option( "blogname" ) ),
						),
						"elements"       => array (
							'header'          => array (
								'type'              => 'Section',
								"hideSectionHeader" => "true",
								'elements'          => array (
									array (
										"id"             => "sender_number",
										"title"          => "발신번호",
										"className"      => "",
										"titleClassName" => "center aligned title-bold",
										"type"           => "Select",
										"default"        => get_option( 'mssms_rep_send_no' ),
										"placeholder"    => __( "발신번호를 선택하세요.", 'mshop-sms-s2' ),
										"options"        => array_combine( MSSMS_Manager::get_send_nos(), MSSMS_Manager::get_send_nos() ),
									),
									array (
										"id"             => "send_type",
										"title"          => "발송방법",
										"className"      => "",
										"titleClassName" => "center aligned title-bold",
										"type"           => "Select",
										"default"        => 'now',
										"placeholder"    => __( "발송방법을 선택하세요.", 'mshop-sms-s2' ),
										"options"        => array (
											'now'      => __( '즉시발송', 'mshop-sms-s2' ),
											'reserved' => __( '예약발송', 'mshop-sms-s2' )
										),
									),
									array (
										"id"             => "request_date",
										"title"          => "예약발송시간",
										"showIf"         => array ( "send_type" => "reserved" ),
										"className"      => "fluid",
										"titleClassName" => "center aligned title-bold",
										"type"           => "Text",
										"placeholder"    => __( "YYYY-MM-DD HH:MM", 'mshop-sms-s2' ),
										'desc2'          => __( '<div class="desc2">예약발송시간은 "YYYY-MM-DD HH:MM" 형식으로 입력해주세요. 예) 2019-09-15 10:00</div>', 'mshop-sms-s2' ),
									),
								)
							),
							'customer_type'   => array (
								"id"      => "customer_type",
								"default" => "phone",
								"type"    => "Select",
								"options" => array (
									'phone'  => __( '휴대폰번호 입력', 'mshop-sms-s2' ),
									'search' => __( '사용자 검색', 'mshop-sms-s2' ),
									'role'   => __( '사용자 등급', 'mshop-sms-s2' ),
									'csv'    => __( 'CSV 업로드', 'mshop-sms-s2' ),
								)
							),
							'receivers'       => array (
								"id"           => "receivers",
								"rows"         => "5",
								"type"         => "TextArea",
								"wrapperStyle" => array ( 'flex' => 1 ),
								"placeholder"  => "한줄에 하나의 휴대폰번호만 입력 해 주세요.\n고객명을 입력하려면 \"휴대폰번호,고객명\"순으로 입력 해 주세요.",
							),
							'add_receivers'   => array (
								"id"         => "add_receivers",
								"type"       => "Button",
								"actionType" => "notification",
								"className"  => "yellow",
								"label"      => __( '추가하기', 'mshop-sms-s2' ),
							),
							'send_message'    => array (
								"id"         => "send_message",
								"type"       => "Button",
								"actionType" => "notification",
								"label"      => __( '문자 발송하기', 'mshop-sms-s2' ),
							),
							'customer_list'   => array (
								"id"       => "customer_list",
								"type"     => "SortableTable",
								"editable" => true,
								"sortable" => false,
								"template" => array (
									'phone_number'  => '',
									'customer_name' => '',
								),
								"elements" => array (
									array (
										"id"        => "phone_number",
										"type"      => "Text",
										"title"     => __( '휴대폰번호', 'mshop-sms-s2' ),
										"readonly"  => true,
										"className" => "center aligned eight wide column fluid",
									),
									array (
										"id"        => "customer_name",
										"type"      => "Text",
										"title"     => __( '고객명', 'mshop-sms-s2' ),
										"readonly"  => true,
										"className" => "center aligned eight wide column fluid",
									),
								)
							),
							'search_customer' => array (
								"id"           => "search_customer",
								"type"         => "SearchSelect",
								"placeholder"  => __( '사용자 선택', 'mshop-sms-s2' ),
								"className"    => "fluid search",
								"multiple"     => true,
								"searchSelect" => true,
								"action"       => "action=" . MSSMS()->slug() . "-user_search&keyword="
							),
							'search_role'     => array (
								"id"          => "search_role",
								"type"        => "Select",
								"placeholder" => __( '회원등급 선택', 'mshop-sms-s2' ),
								"className"   => "fluid",
								"multiple"    => false,
								"options"     => self::get_roles()
							),
							'title'           => array (
								"id"        => "title",
								"type"      => "Text",
								"title"     => __( '문자제목', 'mshop-sms-s2' ),
								"className" => "center aligned eight wide column fluid",
							),
							'messages'        => array (
								"id"           => "messages",
								"rows"         => "5",
								"type"         => "TextArea",
								"wrapperStyle" => array ( 'flex' => 1 ),
								"placeholder"  => "문자 내용을 입력 해 주세요. ( 한글 1,000자까지만 입력 가능합니다. )",
								"desc2"        => "<div class=desc2>[대체문자안내] 고객명은 {고객명}으로 입력해주세요.</div>",
							),
						)
					)
				)
			);
		}

		static function enqueue_scripts() {
			wp_enqueue_script( 'underscore' );
			wp_enqueue_style( 'mshop-setting-manager', MSSMS()->plugin_url() . '/includes/admin/setting-manager/css/setting-manager.min.css' );
			wp_enqueue_script( 'mshop-setting-manager', MSSMS()->plugin_url() . '/includes/admin/setting-manager/js/setting-manager.min.js', array (
				'jquery',
				'jquery-ui-core'
			) );
		}
		public static function output() {
			require_once( ABSPATH . 'wp-admin/includes/dashboard.php' );

			wp_dashboard_setup();

			require_once MSSMS()->plugin_path() . '/includes/admin/setting-manager/mssms-helper.php';

			$settings = self::get_setting_fields();

			self::enqueue_scripts();

			wp_localize_script( 'mshop-setting-manager', 'mshop_setting_manager', array (
				'element'     => 'mshop-setting-wrapper',
				'ajaxurl'     => admin_url( 'admin-ajax.php' ),
				'action'      => MSSMS()->slug() . '-update_settings',
				'settings'    => $settings,
				'slug'        => MSSMS()->slug(),
				'domain'      => preg_replace( '#^https?://#', '', home_url() ),
				'licenseInfo' => get_option( 'msl_license_' . MSSMS()->slug(), null )
			) );

			?>
            <script>
                jQuery( document ).ready( function () {
                    jQuery( this ).trigger( 'mshop-setting-manager', ['mshop-setting-wrapper', '100', <?php echo json_encode( MSSMS_Helper::get_settings( $settings ) ); ?>, null, null] );
                } );
            </script>
            <div id="mshop-setting-wrapper"></div>
			<?php
		}
	}
endif;



