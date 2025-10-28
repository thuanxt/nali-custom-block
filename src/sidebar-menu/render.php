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
        return $this->renderHtml( $menu_title, $menu_items, $current_url, $block_id );
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
        // If manually set as active
        if ( isset( $item['isActive'] ) && $item['isActive'] ) {
            return true;
        }
        
        // Check URL match
        $item_url = $this->resolveMenuUrl( $item['url'] );
        
        // Remove query parameters and fragments for comparison
        $current_clean = strtok( $current_url, '?' );
        $item_clean = strtok( $item_url, '?' );
        
        return $current_clean === $item_clean;
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
    private function renderHtml( $menu_title, $menu_items, $current_url, $block_id ) {
        ob_start();
        ?>
        <div class="chuyennhanali-sidebar-menu-block" id="<?php echo $this->escAttr( $block_id ); ?>">
            <?php if ( ! empty( $menu_title ) ) : ?>
                <h3 class="chuyennhanali-menu-title"><?php echo $this->escHtml( $menu_title ); ?></h3>
            <?php endif; ?>
            
            <?php if ( ! empty( $menu_items ) && is_array( $menu_items ) ) : ?>
                <nav class="chuyennhanali-menu-nav" role="navigation" aria-label="<?php echo $this->escAttr( $menu_title ); ?>">
                    <ul class="chuyennhanali-menu-list">
                        <?php foreach ( $menu_items as $index => $item ) : 
                            if ( ! isset( $item['label'] ) || ! isset( $item['url'] ) ) continue;
                            
                            $item_url = $this->resolveMenuUrl( $item['url'] );
                            $is_active = $this->isMenuActive( $item, $current_url );
                            $item_classes = array( 'chuyennhanali-menu-item' );
                            
                            if ( $is_active ) {
                                $item_classes[] = 'is-active';
                            }
                        ?>
                            <li class="<?php echo $this->escAttr( implode( ' ', $item_classes ) ); ?>">
                                <a href="<?php echo $this->escUrl( $item_url ); ?>" 
                                   class="chuyennhanali-menu-link"
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
