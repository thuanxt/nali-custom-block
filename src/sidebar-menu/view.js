/**
 * Frontend JavaScript for NaLi Sidebar Menu Block
 * 
 * Handles dynamic menu interactions and active state management
 */

document.addEventListener('DOMContentLoaded', function() {
  // Initialize all sidebar menu blocks on the page
  const sidebarMenus = document.querySelectorAll('.chuyennhanali-sidebar-menu-block');
  
  sidebarMenus.forEach(function(menu) {
    initializeSidebarMenu(menu);
  });
});

/**
 * Initialize a single sidebar menu block
 * @param {Element} menuBlock - The sidebar menu block element
 */
function initializeSidebarMenu(menuBlock) {
  const menuLinks = menuBlock.querySelectorAll('.chuyennhanali-menu-link');
  const currentUrl = window.location.href;
  const currentPathname = window.location.pathname;
  
  // Auto-detect active menu item based on current URL
  menuLinks.forEach(function(link) {
    const menuItem = link.closest('.chuyennhanali-menu-item');
    const linkUrl = link.getAttribute('href');
    
    // Remove existing active classes first
    menuItem.classList.remove('is-active');
    link.removeAttribute('aria-current');
    
    // Check if this link matches current page
    if (isUrlMatch(linkUrl, currentUrl, currentPathname)) {
      setActiveMenuItem(menuItem, link);
    }
    
    // Add click handler for smooth transitions
    link.addEventListener('click', function(e) {
      // Add loading state
      link.classList.add('loading');
      
      // Remove loading state after a short delay
      setTimeout(function() {
        link.classList.remove('loading');
      }, 300);
    });
  });
  
  // Add keyboard navigation support
  addKeyboardNavigation(menuBlock);
}

/**
 * Check if a menu URL matches the current page
 * @param {string} linkUrl - The menu item URL
 * @param {string} currentUrl - Current page full URL
 * @param {string} currentPathname - Current page pathname
 * @returns {boolean} Whether the URLs match
 */
function isUrlMatch(linkUrl, currentUrl, currentPathname) {
  if (!linkUrl || linkUrl === '#') {
    return false;
  }
  
  // Normalize URLs for comparison
  const normalizedLinkUrl = normalizeUrl(linkUrl);
  const normalizedCurrentUrl = normalizeUrl(currentUrl);
  const normalizedCurrentPath = normalizeUrl(currentPathname);
  
  // Exact match
  if (normalizedLinkUrl === normalizedCurrentUrl) {
    return true;
  }
  
  // Pathname match
  if (normalizedLinkUrl === normalizedCurrentPath) {
    return true;
  }
  
  // Check if link URL ends with current pathname
  if (normalizedLinkUrl.endsWith(normalizedCurrentPath) && normalizedCurrentPath !== '/') {
    return true;
  }
  
  return false;
}

/**
 * Normalize URL for comparison
 * @param {string} url - URL to normalize
 * @returns {string} Normalized URL
 */
function normalizeUrl(url) {
  if (!url) return '';
  
  // Remove query parameters and fragments
  const cleanUrl = url.split('?')[0].split('#')[0];
  
  // Remove trailing slash except for root
  return cleanUrl === '/' ? '/' : cleanUrl.replace(/\/$/, '');
}

/**
 * Set a menu item as active
 * @param {Element} menuItem - Menu item element
 * @param {Element} menuLink - Menu link element
 */
function setActiveMenuItem(menuItem, menuLink) {
  // Remove active class from all other items in this menu
  const allMenuItems = menuItem.closest('.chuyennhanali-menu-list').querySelectorAll('.chuyennhanali-menu-item');
  allMenuItems.forEach(function(item) {
    item.classList.remove('is-active');
    const link = item.querySelector('.chuyennhanali-menu-link');
    if (link) {
      link.removeAttribute('aria-current');
    }
  });
  
  // Add active class to current item
  menuItem.classList.add('is-active');
  menuLink.setAttribute('aria-current', 'page');
  
  // Add active indicator if not exists
  let indicator = menuLink.querySelector('.active-indicator');
  if (!indicator) {
    indicator = document.createElement('span');
    indicator.className = 'active-indicator';
    indicator.setAttribute('aria-hidden', 'true');
    menuLink.appendChild(indicator);
  }
}

/**
 * Add keyboard navigation support
 * @param {Element} menuBlock - The menu block element
 */
function addKeyboardNavigation(menuBlock) {
  const menuLinks = menuBlock.querySelectorAll('.chuyennhanali-menu-link');
  
  menuLinks.forEach(function(link, index) {
    link.addEventListener('keydown', function(e) {
      let targetIndex = -1;
      
      switch(e.key) {
        case 'ArrowDown':
          e.preventDefault();
          targetIndex = (index + 1) % menuLinks.length;
          break;
          
        case 'ArrowUp':
          e.preventDefault();
          targetIndex = (index - 1 + menuLinks.length) % menuLinks.length;
          break;
          
        case 'Home':
          e.preventDefault();
          targetIndex = 0;
          break;
          
        case 'End':
          e.preventDefault();
          targetIndex = menuLinks.length - 1;
          break;
      }
      
      if (targetIndex >= 0) {
        menuLinks[targetIndex].focus();
      }
    });
  });
}

/**
 * Update active menu item programmatically (for SPA usage)
 * @param {string} url - URL to set as active
 */
window.chuyennhanaliUpdateActiveMenu = function(url) {
  const sidebarMenus = document.querySelectorAll('.chuyennhanali-sidebar-menu-block');
  
  sidebarMenus.forEach(function(menu) {
    const menuLinks = menu.querySelectorAll('.chuyennhanali-menu-link');
    
    menuLinks.forEach(function(link) {
      const menuItem = link.closest('.chuyennhanali-menu-item');
      const linkUrl = link.getAttribute('href');
      
      menuItem.classList.remove('is-active');
      link.removeAttribute('aria-current');
      
      if (isUrlMatch(linkUrl, url, new URL(url).pathname)) {
        setActiveMenuItem(menuItem, link);
      }
    });
  });
};

// CSS classes for loading states
const style = document.createElement('style');
style.textContent = `
  .chuyennhanali-menu-link.loading {
    opacity: 0.7;
    pointer-events: none;
  }
  
  .chuyennhanali-menu-link.loading::after {
    content: '';
    display: inline-block;
    width: 12px;
    height: 12px;
    margin-left: 8px;
    border: 2px solid currentColor;
    border-top-color: transparent;
    border-radius: 50%;
    animation: chuyennhanali-spin 0.8s linear infinite;
  }
  
  @keyframes chuyennhanali-spin {
    to {
      transform: rotate(360deg);
    }
  }
`;
document.head.appendChild(style);
