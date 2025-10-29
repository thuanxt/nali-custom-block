<?php
/**
 * Render callback for the sidebar menu block.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 * @return string Rendered block output.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Sidebar Menu Block Renderer Class
 * Handles all rendering logic for the sidebar menu block
 */
if ( ! class_exists( 'ChuyenNhaNaLi_SidebarMenu_Renderer' ) ) {
    class ChuyenNhaNaLi_SidebarMenu_Renderer {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {}
    
    /**
     * Main render method
     */
    public function render( $attributes, $content = '', $block = null ) {

        // Get attributes with defaults
        $menu_title = isset( $attributes['menuTitle'] ) ? $attributes['menuTitle'] : 'Menu Dashboard';
        $menu_items = isset( $attributes['menuItems'] ) && is_array( $attributes['menuItems'] ) ? $attributes['menuItems'] : array();
        $show_title = isset( $attributes['showTitle'] ) ? $attributes['showTitle'] : true;
        
        // Get color attributes with defaults
        $bg_color = isset( $attributes['backgroundColor'] ) ? $attributes['backgroundColor'] : '#ffffff';
        $text_color = isset( $attributes['textColor'] ) ? $attributes['textColor'] : '#64748b';
        $active_bg_color = isset( $attributes['activeBackgroundColor'] ) ? $attributes['activeBackgroundColor'] : '#3b82f6';
        $active_text_color = isset( $attributes['activeTextColor'] ) ? $attributes['activeTextColor'] : '#ffffff';
        $title_bg_color = isset( $attributes['titleBackgroundColor'] ) && ! empty( $attributes['titleBackgroundColor'] ) 
            ? $attributes['titleBackgroundColor'] 
            : '';
        $title_text_color = isset( $attributes['titleTextColor'] ) ? $attributes['titleTextColor'] : '#ffffff';
        $hover_bg_color = isset( $attributes['hoverBackgroundColor'] ) ? $attributes['hoverBackgroundColor'] : '#f8fafc';
        $hover_text_color = isset( $attributes['hoverTextColor'] ) ? $attributes['hoverTextColor'] : '#3b82f6';

        // Initialize with default menu items if empty
        if ( empty( $menu_items ) ) {
            $menu_items = array(
                array(
                    'label' => 'Dashboard',
                    'url' => '#',
                    'isActive' => true
                ),
                array(
                    'label' => 'Hồ sơ',
                    'url' => '#',
                    'isActive' => false
                ),
                array(
                    'label' => 'Cài đặt',
                    'url' => '#',
                    'isActive' => false
                )
            );
        }

        // Get current page URL for active menu detection
        $current_url = $this->getCurrentUrl();
        
        // Generate unique ID for this block instance
        $block_id = 'sidebar-menu-' . uniqid();
        if ( function_exists( 'wp_generate_uuid4' ) ) {
            $block_id = 'sidebar-menu-' . wp_generate_uuid4();
        }

        // Render the block
        return $this->renderHtml( $menu_title, $menu_items, $current_url, $block_id, $show_title, array(
            'bg_color' => $bg_color,
            'text_color' => $text_color,
            'active_bg_color' => $active_bg_color,
            'active_text_color' => $active_text_color,
            'title_bg_color' => $title_bg_color,
            'title_text_color' => $title_text_color,
            'hover_bg_color' => $hover_bg_color,
            'hover_text_color' => $hover_text_color,
        ), $block );
    }
    
    /**
     * Get current URL safely
     */
    private function getCurrentUrl() {
        if ( function_exists( 'home_url' ) && isset( $_SERVER['REQUEST_URI'] ) ) {
            return home_url( $_SERVER['REQUEST_URI'] );
        }
        return '';
    }

    /**
     * Helper function to resolve URL from various formats
     */
    private function resolveMenuUrl( $url ) {
        // If it's already a full URL, return as is
        if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return $url;
        }
        
        // If it starts with /, treat as relative URL
        if ( strpos( $url, '/' ) === 0 ) {
            if ( function_exists( 'home_url' ) ) {
                return home_url( $url );
            }
            return $url; // Fallback to relative URL
        }
        
        // If it's a number, treat as page/post ID
        if ( is_numeric( $url ) && function_exists( 'get_permalink' ) ) {
            $permalink = get_permalink( intval( $url ) );
            if ( $permalink ) {
                return $permalink;
            }
        }
        
        // Try to find page by slug
        if ( function_exists( 'get_page_by_path' ) ) {
            $page = get_page_by_path( $url );
            if ( $page && function_exists( 'get_permalink' ) ) {
                return get_permalink( $page->ID );
            }
        }
        
        // Try to find post by slug
        if ( function_exists( 'get_page_by_path' ) && defined( 'OBJECT' ) ) {
            $post = get_page_by_path( $url, OBJECT, 'post' );
            if ( $post && function_exists( 'get_permalink' ) ) {
                return get_permalink( $post->ID );
            }
        }
        
        // If nothing found, return as relative URL
        if ( function_exists( 'home_url' ) ) {
            return home_url( '/' . ltrim( $url, '/' ) );
        }
        
        // Final fallback
        return '/' . ltrim( $url, '/' );
    }

    /**
     * Check if menu item should be active
     */
    private function isMenuActive( $item, $current_url ) {
        // Check URL match first (has priority over manual setting)
        $item_url = $this->resolveMenuUrl( $item['url'] );
        
        // Remove query parameters and fragments for comparison
        $current_clean = strtok( $current_url, '?' );
        $item_clean = strtok( $item_url, '?' );

        // Normalize URLs by removing scheme, host and trailing slashes
        $current_path = rtrim( preg_replace( '#^[^/]*//[^/]+#', '', $current_clean ), '/' );
        $item_path = rtrim( preg_replace( '#^[^/]*//[^/]+#', '', $item_clean ), '/' );

        // Handle homepage case
        if ( empty( $item_path ) ) {
            return empty( $current_path );
        }

        // Check if current path starts with item path
        if ( strpos( $current_path, $item_path ) === 0 ) {
            // Ensure it's a full path segment match (e.g. /blog matches /blog/post but not /blog-something)
            $next_char = substr( $current_path, strlen( $item_path ), 1 );
            if ( $next_char === '' || $next_char === '/' ) {
                return true;
            }
        }
        
        // If no URL match, return false (don't use isActive from editor)
        return false;
    }
    
    /**
     * Check if menu item should be default active (when no URL matches)
     */
    private function hasAnyUrlMatch( $menu_items, $current_url ) {
        foreach ( $menu_items as $item ) {
            if ( ! isset( $item['url'] ) ) continue;
            
            $item_url = $this->resolveMenuUrl( $item['url'] );
            $current_clean = strtok( $current_url, '?' );
            $item_clean = strtok( $item_url, '?' );
            
            $current_path = rtrim( preg_replace( '#^[^/]*//[^/]+#', '', $current_clean ), '/' );
            $item_path = rtrim( preg_replace( '#^[^/]*//[^/]+#', '', $item_clean ), '/' );
            
            // Check for match
            if ( empty( $item_path ) && empty( $current_path ) ) {
                return true;
            }
            
            if ( strpos( $current_path, $item_path ) === 0 ) {
                $next_char = substr( $current_path, strlen( $item_path ), 1 );
                if ( $next_char === '' || $next_char === '/' ) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Safe escaping function for attributes
     */
    private function escAttr( $text ) {
        if ( function_exists( 'esc_attr' ) ) {
            return esc_attr( $text );
        }
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }

    /**
     * Safe escaping function for HTML
     */
    private function escHtml( $text ) {
        if ( function_exists( 'esc_html' ) ) {
            return esc_html( $text );
        }
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }

    /**
     * Safe escaping function for URLs
     */
    private function escUrl( $url ) {
        if ( function_exists( 'esc_url' ) ) {
            return esc_url( $url );
        }
        return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
    }

    /**
     * Render HTML output
     */
    private function renderHtml( $menu_title, $menu_items, $current_url, $block_id, $show_title, $colors = array(), $block = null ) {
        // Extract colors with defaults
        $bg_color = isset( $colors['bg_color'] ) ? $colors['bg_color'] : '#ffffff';
        $text_color = isset( $colors['text_color'] ) ? $colors['text_color'] : '#64748b';
        $active_bg_color = isset( $colors['active_bg_color'] ) ? $colors['active_bg_color'] : '#3b82f6';
        $active_text_color = isset( $colors['active_text_color'] ) ? $colors['active_text_color'] : '#ffffff';
        $title_bg_color = isset( $colors['title_bg_color'] ) && ! empty( $colors['title_bg_color'] ) 
            ? $colors['title_bg_color'] 
            : '';
        $title_text_color = isset( $colors['title_text_color'] ) ? $colors['title_text_color'] : '#ffffff';
        $hover_bg_color = isset( $colors['hover_bg_color'] ) ? $colors['hover_bg_color'] : '#f8fafc';
        $hover_text_color = isset( $colors['hover_text_color'] ) ? $colors['hover_text_color'] : '#3b82f6';
        
        // Generate inline styles for custom CSS variables
        $custom_styles = sprintf(
            'background-color: %s; --menu-text-color: %s; --menu-active-bg-color: %s; --menu-active-text-color: %s; --menu-hover-bg-color: %s; --menu-hover-text-color: %s;',
            $this->escAttr( $bg_color ),
            $this->escAttr( $text_color ),
            $this->escAttr( $active_bg_color ),
            $this->escAttr( $active_text_color ),
            $this->escAttr( $hover_bg_color ),
            $this->escAttr( $hover_text_color )
        );
        
        // Get block wrapper attributes from WordPress
        $wrapper_attributes = function_exists( 'get_block_wrapper_attributes' ) 
            ? get_block_wrapper_attributes( array(
                'class' => 'chuyennhanali-sidebar-menu-block',
                'id' => $block_id,
                'style' => $custom_styles,
            ) )
            : sprintf( 'class="chuyennhanali-sidebar-menu-block" id="%s" style="%s"', 
                $this->escAttr( $block_id ), 
                $custom_styles 
            );
        
        $title_styles = '';
        if ( ! empty( $title_bg_color ) ) {
            $title_styles = sprintf(
                'background: %s; color: %s;',
                $this->escAttr( $title_bg_color ),
                $this->escAttr( $title_text_color )
            );
        } else {
            $title_styles = sprintf( 'color: %s;', $this->escAttr( $title_text_color ) );
        }
        
        ob_start();
        ?>
        <div <?php echo $wrapper_attributes; ?>>
            <?php if ( $show_title && ! empty( $menu_title ) ) : ?>
                <h3 class="chuyennhanali-menu-title" style="<?php echo $title_styles; ?>"><?php echo $this->escHtml( $menu_title ); ?></h3>
            <?php endif; ?>
            
            <?php if ( ! empty( $menu_items ) && is_array( $menu_items ) ) : ?>
                <?php 
                    // Check if any menu item matches the current URL
                    $has_url_match = $this->hasAnyUrlMatch( $menu_items, $current_url );
                ?>
                <nav class="chuyennhanali-menu-nav" role="navigation" aria-label="<?php echo $this->escAttr( $menu_title ); ?>">
                    <ul class="chuyennhanali-menu-list">
                        <?php foreach ( $menu_items as $index => $item ) : 
                            if ( ! isset( $item['label'] ) || ! isset( $item['url'] ) ) continue;
                            
                            $item_url = $this->resolveMenuUrl( $item['url'] );
                            $is_url_active = $this->isMenuActive( $item, $current_url );
                            
                            // Use default active only if no URL matches
                            $is_default_active = ! $has_url_match && isset( $item['isActive'] ) && $item['isActive'];
                            $is_active = $is_url_active || $is_default_active;
                            
                            $item_classes = array( 'chuyennhanali-menu-item' );
                            
                            if ( $is_active ) {
                                $item_classes[] = 'is-active';
                            }
                            
                            // Generate inline styles for menu item
                            $item_style = sprintf(
                                'color: %s; background-color: %s;',
                                $this->escAttr( $is_active ? $active_text_color : $text_color ),
                                $this->escAttr( $is_active ? $active_bg_color : 'transparent' )
                            );
                        ?>
                            <li class="<?php echo $this->escAttr( implode( ' ', $item_classes ) ); ?>">
                                <a href="<?php echo $this->escUrl( $item_url ); ?>" 
                                   class="chuyennhanali-menu-link"
                                   style="<?php echo $item_style; ?>"
                                   <?php if ( $is_active ) echo 'aria-current="page"'; ?>>
                                    <span class="menu-text"><?php echo $this->escHtml( $item['label'] ); ?></span>
                                    <?php if ( $is_active ) : ?>
                                        <span class="active-indicator" aria-hidden="true"></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php else : ?>
                <p class="chuyennhanali-menu-empty">
                    <?php 
                    if ( function_exists( '_e' ) ) {
                        _e( 'Chưa có menu nào được cấu hình.', 'nali-custom-block' );
                    } else {
                        echo 'Chưa có menu nào được cấu hình.';
                    }
                    ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    } // End class
} // End if class_exists

// Initialize and render
$renderer = ChuyenNhaNaLi_SidebarMenu_Renderer::getInstance();
echo $renderer->render( $attributes, $content, $block );
