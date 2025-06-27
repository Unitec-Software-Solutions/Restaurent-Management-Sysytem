/**
 * Enhanced Restaurant Management Admin Sidebar JavaScript
 * Handles sidebar interactions, mobile responsiveness, and state persistence
 */

document.addEventListener('alpine:init', () => {
    // Initialize Alpine store for sidebar
    Alpine.store('sidebar', {
        collapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebar_collapsed', this.collapsed);
            this.updateLayout();
        },
        
        collapse() {
            this.collapsed = true;
            localStorage.setItem('sidebar_collapsed', true);
            this.updateLayout();
        },
        
        expand() {
            this.collapsed = false;
            localStorage.setItem('sidebar_collapsed', false);
            this.updateLayout();
        },
        
        updateLayout() {
            // Trigger layout updates
            window.dispatchEvent(new Event('sidebar-toggle'));
        }
    });
});

class RestaurantSidebar {
    constructor() {
        this.sidebar = document.getElementById('sidebar');
        this.submenuToggles = document.querySelectorAll('.has-submenu');
        
        this.isMobile = window.innerWidth < 1024;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupKeyboardNavigation();
        this.setupResizeHandler();
        this.setupSubmenuToggles();
        this.loadSavedState();
        this.setupNotificationUpdates();
    }
    
    setupEventListeners() {
        // Sidebar toggle
        if (this.toggleButton) {
            this.toggleButton.addEventListener('click', () => this.toggleSidebar());
        }
        
        // Overlay click (mobile)
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.closeMobileSidebar());
        }
        
        // Escape key to close mobile sidebar
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMobile && this.isMobileOpen()) {
                this.closeMobileSidebar();
            }
        });
    }
    
    setupKeyboardNavigation() {
        const sidebarLinks = this.sidebar.querySelectorAll('.sidebar-link, .submenu-link');
        
        sidebarLinks.forEach((link, index) => {
            link.addEventListener('keydown', (e) => {
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        this.focusNextLink(sidebarLinks, index);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        this.focusPreviousLink(sidebarLinks, index);
                        break;
                    case 'Enter':
                    case ' ':
                        if (link.classList.contains('has-submenu')) {
                            e.preventDefault();
                            this.toggleSubmenu(link.closest('.sidebar-item'));
                        }
                        break;
                }
            });
        });
    }
    
    setupResizeHandler() {
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const wasMobile = this.isMobile;
                this.isMobile = window.innerWidth < 1024;
                
                if (wasMobile && !this.isMobile) {
                    // Switched from mobile to desktop
                    this.closeMobileSidebar();
                    this.restoreDesktopState();
                } else if (!wasMobile && this.isMobile) {
                    // Switched from desktop to mobile
                    this.saveDesktopState();
                    this.setupMobileState();
                }
            }, 250);
        });
    }
    
    setupSubmenuToggles() {
        this.submenuToggles.forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                if (!this.isCollapsed || this.isMobile) {
                    e.preventDefault();
                    this.toggleSubmenu(toggle.closest('.sidebar-item'));
                }
            });
        });
    }
    
    setupNotificationUpdates() {
        // Update badges periodically
        setInterval(() => {
            this.updateNotificationBadges();
        }, 30000); // Update every 30 seconds
    }
    
    toggleSidebar() {
        if (this.isMobile) {
            this.toggleMobileSidebar();
        } else {
            this.toggleDesktopSidebar();
        }
    }
    
    toggleDesktopSidebar() {
        this.isCollapsed = !this.isCollapsed;
        this.sidebar.classList.toggle('collapsed', this.isCollapsed);
        localStorage.setItem('sidebar_collapsed', this.isCollapsed);
        
        // Close all submenus when collapsing
        if (this.isCollapsed) {
            this.closeAllSubmenus();
        }
        
        // Dispatch custom event
        window.dispatchEvent(new CustomEvent('sidebar:toggle', {
            detail: { collapsed: this.isCollapsed }
        }));
    }
    
    toggleMobileSidebar() {
        const isOpen = this.isMobileOpen();
        this.sidebar.classList.toggle('mobile-open', !isOpen);
        
        if (this.overlay) {
            this.overlay.classList.toggle('active', !isOpen);
        }
        
        // Prevent body scroll when sidebar is open
        document.body.style.overflow = !isOpen ? 'hidden' : '';
    }
    
    closeMobileSidebar() {
        this.sidebar.classList.remove('mobile-open');
        if (this.overlay) {
            this.overlay.classList.remove('active');
        }
        document.body.style.overflow = '';
    }
    
    isMobileOpen() {
        return this.sidebar.classList.contains('mobile-open');
    }
    
    toggleSubmenu(sidebarItem) {
        const submenu = sidebarItem.querySelector('.submenu');
        const arrow = sidebarItem.querySelector('.submenu-arrow');
        const isOpen = submenu && submenu.style.display !== 'none';
        
        if (submenu) {
            submenu.style.display = isOpen ? 'none' : 'block';
            if (arrow) {
                arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
            }
            
            // Save submenu state
            const menuId = sidebarItem.dataset.menuId;
            if (menuId) {
                localStorage.setItem(`submenu_${menuId}`, !isOpen);
            }
        }
    }
    
    closeAllSubmenus() {
        const submenus = this.sidebar.querySelectorAll('.submenu');
        const arrows = this.sidebar.querySelectorAll('.submenu-arrow');
        
        submenus.forEach(submenu => {
            submenu.style.display = 'none';
        });
        
        arrows.forEach(arrow => {
            arrow.style.transform = 'rotate(0deg)';
        });
    }
    
    focusNextLink(links, currentIndex) {
        const nextIndex = currentIndex < links.length - 1 ? currentIndex + 1 : 0;
        links[nextIndex].focus();
    }
    
    focusPreviousLink(links, currentIndex) {
        const prevIndex = currentIndex > 0 ? currentIndex - 1 : links.length - 1;
        links[prevIndex].focus();
    }
    
    loadSavedState() {
        if (!this.isMobile && this.isCollapsed) {
            this.sidebar.classList.add('collapsed');
        }
        
        // Restore submenu states
        const sidebarItems = this.sidebar.querySelectorAll('.sidebar-item[data-menu-id]');
        sidebarItems.forEach(item => {
            const menuId = item.dataset.menuId;
            const isOpen = localStorage.getItem(`submenu_${menuId}`) === 'true';
            
            if (isOpen && !this.isCollapsed) {
                const submenu = item.querySelector('.submenu');
                const arrow = item.querySelector('.submenu-arrow');
                
                if (submenu) {
                    submenu.style.display = 'block';
                }
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        });
    }
    
    saveDesktopState() {
        localStorage.setItem('sidebar_collapsed', this.isCollapsed);
    }
    
    restoreDesktopState() {
        this.isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
        this.sidebar.classList.toggle('collapsed', this.isCollapsed);
    }
    
    setupMobileState() {
        this.sidebar.classList.remove('collapsed');
        this.closeMobileSidebar();
    }
    
    async updateNotificationBadges() {
        try {
            const response = await fetch('/api/admin/notifications/counts');
            if (response.ok) {
                const data = await response.json();
                this.updateBadge('orders', data.pending_orders || 0);
                this.updateBadge('inventory', data.low_stock_items || 0);
                this.updateBadge('reservations', data.pending_reservations || 0);
            }
        } catch (error) {
            console.warn('Failed to update notification badges:', error);
        }
    }
    
    updateBadge(type, count) {
        const badge = this.sidebar.querySelector(`[data-badge="${type}"]`);
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    // Public API methods
    expand() {
        if (!this.isMobile) {
            this.isCollapsed = false;
            this.sidebar.classList.remove('collapsed');
            localStorage.setItem('sidebar_collapsed', false);
        }
    }
    
    collapse() {
        if (!this.isMobile) {
            this.isCollapsed = true;
            this.sidebar.classList.add('collapsed');
            localStorage.setItem('sidebar_collapsed', true);
            this.closeAllSubmenus();
        }
    }
    
    openSubmenu(menuId) {
        const sidebarItem = this.sidebar.querySelector(`[data-menu-id="${menuId}"]`);
        if (sidebarItem && !this.isCollapsed) {
            const submenu = sidebarItem.querySelector('.submenu');
            const arrow = sidebarItem.querySelector('.submenu-arrow');
            
            if (submenu) {
                submenu.style.display = 'block';
            }
            if (arrow) {
                arrow.style.transform = 'rotate(180deg)';
            }
            localStorage.setItem(`submenu_${menuId}`, true);
        }
    }
    
    closeSubmenu(menuId) {
        const sidebarItem = this.sidebar.querySelector(`[data-menu-id="${menuId}"]`);
        if (sidebarItem) {
            const submenu = sidebarItem.querySelector('.submenu');
            const arrow = sidebarItem.querySelector('.submenu-arrow');
            
            if (submenu) {
                submenu.style.display = 'none';
            }
            if (arrow) {
                arrow.style.transform = 'rotate(0deg)';
            }
            localStorage.setItem(`submenu_${menuId}`, false);
        }
    }
    
    highlightActiveItem(path) {
        // Remove existing active states
        this.sidebar.querySelectorAll('.sidebar-item.active').forEach(item => {
            item.classList.remove('active');
        });
        
        // Add active state to matching item
        const activeLink = this.sidebar.querySelector(`[href="${path}"]`);
        if (activeLink) {
            const sidebarItem = activeLink.closest('.sidebar-item');
            if (sidebarItem) {
                sidebarItem.classList.add('active');
                
                // Ensure parent submenu is open
                const parentSubmenu = activeLink.closest('.submenu');
                if (parentSubmenu) {
                    const parentItem = parentSubmenu.closest('.sidebar-item');
                    if (parentItem && !this.isCollapsed) {
                        parentSubmenu.style.display = 'block';
                        const arrow = parentItem.querySelector('.submenu-arrow');
                        if (arrow) {
                            arrow.style.transform = 'rotate(180deg)';
                        }
                    }
                }
            }
        }
    }
}

// Initialize sidebar when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.restaurantSidebar = new RestaurantSidebar();
    
    // Highlight active item based on current URL
    window.restaurantSidebar.highlightActiveItem(window.location.pathname);
});

// Handle navigation updates (for SPA-like behavior)
window.addEventListener('popstate', () => {
    if (window.restaurantSidebar) {
        window.restaurantSidebar.highlightActiveItem(window.location.pathname);
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RestaurantSidebar;
}
