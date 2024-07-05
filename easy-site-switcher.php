<?php
/**
 * Plugin Name: Easy Site Switcher
 * Description: 개발 플러그인. 개발/로컬 사이트 전환을 쉽게 하기 위한 플러그인 도구
 * Author:      쇼플릭
 * Version:     1.0.0
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
        'enabled' => false,
        'site_a'  => '',
        'site_b'  => '',
    ];

    $result = $default;

    $result['enabled'] = filter_var(
        $data['enabled'] ?? $default['enabled'],
        FILTER_VALIDATE_BOOLEAN,
    );
    $result['site_a']  = untrailingslashit(
        esc_url_raw($data['site_a'] ?? $default['site_a']),
    );
    $result['site_b']  = untrailingslashit(
        esc_url_raw($data['site_b'] ?? $default['site_b']),
    );

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
            <?php
        },
        'easy-site-switcher',
        'general',
        [
            'label_for' => 'site-b',
            'value'     => $option['site_b'] ?? '',
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
    $option  = get_option('shoplic_easy_site_switcher');
    $enabled = $option['enabled'] ?? false;
    $siteA   = $option['site_a'] ?? '';
    $siteB   = $option['site_b'] ?? '';
    $current = untrailingslashit(site_url());

    if ($enabled) {
        if ($current === $siteA) {
            $bar->add_node(
                [
                    'id'    => 'easy-site-switcher',
                    'title' => '사이트 B로 이동',
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
                    'title' => '사이트 A로 이동',
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
