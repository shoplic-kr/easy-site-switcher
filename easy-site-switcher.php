<?php
/**
 * Plugin Name:       Easy Site Switcher
 * Plugin URI:        https://github.com/shoplic-kr/easy-site-switcher
 * Description:       개발 플러그인. 개발/로컬 사이트 전환을 쉽게 하기 위한 플러그인 도구.
 * Author:            쇼플릭
 * Author URI:        https://shoplic.kr
 * Version:           1.1.0
 * Requires PHP:      7.4
 * Requires at least: 3.1.0
 * Tested up to:      6.6.1
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace ShoplicKr\EasySiteSwitcher;

if ( ! defined('ABSPATH')) {
    exit;
}

function init(): void
{
    register_setting(
        'shoplic_easy_site_switcher',
        'shoplic_easy_site_switcher',
        [
            'default'           => [],
            'type'              => 'array',
            'sanitize_callback' => __NAMESPACE__ . '\\sanitize',
            'show_in_rest'      => false,
        ],
    );
}

function sanitize($data): array
{
    $default = [
        'enabled'      => false,
        'site_a'       => '',
        'site_a_label' => '사이트 A',
        'site_b'       => '',
        'site_b_label' => '사이트 B',
    ];

    $result = $default;

    $result['enabled'] = filter_var(
        $data['enabled'] ?? $default['enabled'],
        FILTER_VALIDATE_BOOLEAN,
    );

    $result['site_a'] = untrailingslashit(
        esc_url_raw($data['site_a'] ?? $default['site_a']),
    );

    $result['site_b'] = untrailingslashit(
        esc_url_raw($data['site_b'] ?? $default['site_b']),
    );

    $siteALabel = sanitize_text_field($data['site_a_label'] ?? '');

    $result['site_a_label'] = empty($siteALabel) ?
        $default['site_a_label'] :
        $siteALabel;

    $siteBLabel = sanitize_text_field($data['site_b_label'] ?? '');

    $result['site_b_label'] = empty($siteBLabel) ?
        $default['site_b_label'] :
        $siteBLabel;

    return $result;
}

add_action('init', __NAMESPACE__ . '\\init');

function admin_menu(): void
{
    add_management_page(
        'Easy Site Switcher',
        'Easy Site Switcher',
        'administrator',
        'easy-site-switcher',
        __NAMESPACE__ . '\\output_admin_menu',
    );
}

function output_admin_menu(): void
{
    $option = get_option('shoplic_easy_site_switcher');

    add_settings_section(
        'general',
        '일반',
        '__return_empty_string',
        'easy-site-switcher',
    );

    add_settings_field(
        'enabled',
        '활성화',
        function (array $args) {
            ?>
            <input id="enabled"
                   name="shoplic_easy_site_switcher[enabled]"
                   type="checkbox"
                <?php checked($args['value']); ?>
            />
            <label for="enabled">스위쳐 사용하기.</label>
            <?php
        },
        'easy-site-switcher',
        'general',
        [
            'label_for' => 'enabled',
            'value'     => $option['enabled'] ?? false,
        ],
    );

    add_settings_field(
        'site-a',
        '사이트 A',
        function (array $args) {
            ?>
            <input id="site-a"
                   name="shoplic_easy_site_switcher[site_a]"
                   class="text regular-text"
                   type="url"
                   value="<?php echo esc_attr($args['value']); ?>"
            />
            <p class="description">레퍼런스가 되는 원격지 개발 사이트를 지정합니다.</p>
            <?php
        },
        'easy-site-switcher',
        'general',
        [
            'label_for' => 'site-a',
            'value'     => $option['site_a'] ?? '',
        ],
    );

    add_settings_field(
        'site-a-label',
        '사이트 A 레이블',
        function (array $args) {
            ?>
            <input id="site-a-label"
                   name="shoplic_easy_site_switcher[site_a_label]"
                   class="text"
                   type="text"
                   value="<?php echo esc_attr($args['value']); ?>"
            />
            <p class="description">불릴 이름을 지정합니다.</p>
            <?php
        },
        'easy-site-switcher',
        'general',
        [
            'label_for' => 'site-a-label',
            'value'     => $option['site_a_label'] ?? '',
        ],
    );

    add_settings_field(
        'site-b',
        '사이트 B',
        function (array $args) {
            ?>
            <input id="site-b"
                   name="shoplic_easy_site_switcher[site_b]"
                   class="text regular-text"
                   type="url"
                   value="<?php echo esc_attr($args['value']); ?>"
            />
            <p class="description">개발자의 로컬 개발 사이트를 지정합니다.</p>
            <?php
        },
        'easy-site-switcher',
        'general',
        [
            'label_for' => 'site-b',
            'value'     => $option['site_b'] ?? '',
        ],
    );

    add_settings_field(
        'site-b-label',
        '사이트 B 레이블',
        function (array $args) {
            ?>
            <input id="site-b-label"
                   name="shoplic_easy_site_switcher[site_b_label]"
                   class="text"
                   type="text"
                   value="<?php echo esc_attr($args['value']); ?>"
            />
            <p class="description">불릴 이름을 지정합니다.</p>
            <?php
        },
        'easy-site-switcher',
        'general',
        [
            'label_for' => 'site-b-label',
            'value'     => $option['site_b_label'] ?? '',
        ],
    );

    ?>
    <div class="wrap">
        <h1 class="">설정</h1>
        <hr class="wp-header-end"/>
        <form action="options.php" method="post">
            <?php
            settings_fields('shoplic_easy_site_switcher');
            do_settings_sections('easy-site-switcher');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_menu', __NAMESPACE__ . '\\admin_menu');


function admin_bar_menu(\WP_Admin_Bar $bar): void
{
    $option     = get_option('shoplic_easy_site_switcher');
    $enabled    = $option['enabled'] ?? false;
    $siteA      = $option['site_a'] ?? '';
    $siteALabel = $option['site_a_label'] ?? '사이트 A';
    $siteB      = $option['site_b'] ?? '';
    $siteBLabel = $option['site_b_label'] ?? '사이트 B';
    $current    = untrailingslashit(site_url());

    if ($enabled) {
        if ($current === $siteA) {
            $bar->add_node(
                [
                    'id'    => 'easy-site-switcher',
                    'title' => sprintf('%s로 이동', $siteBLabel),
                    'href'  => esc_url(
                        $siteB . ($_SERVER['REQUEST_URI'] ?? ''),
                    ),
                    'meta'  => ['target' => '_blank'],
                ],
            );
        } elseif ($current === $siteB) {
            $bar->add_node(
                [
                    'id'    => 'easy-site-switcher',
                    'title' => sprintf('%s로 이동', $siteALabel),
                    'href'  => esc_url(
                        $siteA . ($_SERVER['REQUEST_URI'] ?? ''),
                    ),
                    'meta'  => ['target' => '_blank'],
                ],
            );
        }
    }
}

add_action('admin_bar_menu', __NAMESPACE__ . '\\admin_bar_menu', 1000);
